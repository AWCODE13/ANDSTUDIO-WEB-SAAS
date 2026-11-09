<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\ReviewDeletionRequest;
use App\Models\StudioReview;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $slug = 'studio-a', string $name = 'Studio A'): Tenant
    {
        return Tenant::create([
            'name'  => $name,
            'slug'  => $slug,
            'email' => $slug.'@test.id',
            'phone' => '081234567890',
        ]);
    }

    private function tenantAdmin(Tenant $tenant): User
    {
        return User::create([
            'name'      => 'Admin '.$tenant->name,
            'email'     => 'admin@'.$tenant->slug.'.test',
            'password'  => bcrypt('password'),
            'role'      => 'tenant_admin',
            'tenant_id' => $tenant->id,
        ]);
    }

    private function superAdmin(): User
    {
        return User::create([
            'name'     => 'Super Admin',
            'email'    => 'super@test.id',
            'password' => bcrypt('password'),
            'role'     => 'superadmin',
        ]);
    }

    private function package(Tenant $tenant): Package
    {
        return Package::create([
            'tenant_id'        => $tenant->id,
            'name'             => 'Paket Uji',
            'category'         => 'graduation',
            'description'      => 'Deskripsi',
            'price'            => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons'      => 2,
            'is_active'        => true,
        ]);
    }

    private function review(Tenant $tenant, Package $package, int $rating = 5, string $comment = 'Mantap'): StudioReview
    {
        $booking = Booking::create([
            'tenant_id'      => $tenant->id,
            'package_id'     => $package->id,
            'booking_code'   => 'FS-'.strtoupper(substr(md5((string) random_int(1, 999999)), 0, 6)),
            'customer_name'  => 'Ahmad Fauzi',
            'customer_email' => 'pelanggan@mail.test',
            'customer_phone' => '081234567890',
            'booking_date'   => now()->toDateString(),
            'start_time'     => '09:00:00',
            'end_time'       => '10:00:00',
            'total_amount'   => 100000,
            'payment_status' => 'paid',
            'booking_status' => 'completed',
        ]);

        return StudioReview::create([
            'tenant_id'     => $tenant->id,
            'booking_id'    => $booking->id,
            'package_id'    => $package->id,
            'customer_name' => 'Ahmad Fauzi',
            'rating'        => $rating,
            'comment'       => $comment,
        ]);
    }

    public function test_tenant_can_request_deletion_and_review_stays_public(): void
    {
        $tenant = $this->tenant();
        $admin = $this->tenantAdmin($tenant);
        $review = $this->review($tenant, $this->package($tenant));

        $this->actingAs($admin)
            ->post('/app/ulasan/'.$review->id.'/hapus', ['reason' => 'Ulasan tidak relevan.'])
            ->assertRedirect();

        $request = ReviewDeletionRequest::first();
        $this->assertNotNull($request);
        $this->assertSame('pending', $request->status);
        $this->assertSame($tenant->id, $request->tenant_id);
        $this->assertSame($review->id, $request->review_id);

        // Selama pending, ulasan masih tampil di profil publik
        $response = $this->getJson('/api/studio/'.$tenant->slug.'/reviews');
        $response->assertOk();
        $this->assertSame(1, $response->json('data.reviews_count'));
    }

    public function test_duplicate_pending_request_is_rejected(): void
    {
        $tenant = $this->tenant();
        $admin = $this->tenantAdmin($tenant);
        $review = $this->review($tenant, $this->package($tenant));

        $this->actingAs($admin)
            ->post('/app/ulasan/'.$review->id.'/hapus', ['reason' => 'Pertama'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post('/app/ulasan/'.$review->id.'/hapus', ['reason' => 'Kedua'])
            ->assertRedirect();

        $this->assertSame(1, ReviewDeletionRequest::count());
    }

    public function test_tenant_cannot_request_deletion_of_other_tenant_review(): void
    {
        $tenantA = $this->tenant('studio-a');
        $tenantB = $this->tenant('studio-b', 'Studio B');
        $adminB = $this->tenantAdmin($tenantB);
        $reviewA = $this->review($tenantA, $this->package($tenantA));

        $this->actingAs($adminB)
            ->post('/app/ulasan/'.$reviewA->id.'/hapus', ['reason' => 'Nope'])
            ->assertNotFound();

        $this->assertSame(0, ReviewDeletionRequest::count());
    }

    public function test_superadmin_approve_hides_review_and_updates_rating(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);

        $tenant = $this->tenant();
        $admin = $this->tenantAdmin($tenant);
        $super = $this->superAdmin();
        $package = $this->package($tenant);
        $reviewHigh = $this->review($tenant, $package, 5);
        $reviewLow = $this->review($tenant, $package, 4);

        $this->actingAs($admin)
            ->post('/app/ulasan/'.$reviewHigh->id.'/hapus', ['reason' => 'Ulasan spam.'])
            ->assertRedirect();

        $request = ReviewDeletionRequest::first();

        $this->actingAs($super)
            ->put('/superadmin/permintaan-hapus/'.$request->id.'/setujui')
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('approved', $request->status);
        $this->assertNotNull($request->reviewed_by);

        $reviewHigh->refresh();
        $this->assertSame('hidden', $reviewHigh->status);
        $this->assertSame('Ulasan spam.', $reviewHigh->hidden_reason);

        // Ulasan hidden tidak tampil publik & tidak menghitung rating
        $response = $this->getJson('/api/studio/'.$tenant->slug.'/reviews');
        $response->assertOk();
        $this->assertSame(1, $response->json('data.reviews_count'));
        $this->assertEquals(4.0, $response->json('data.rating'));

        // Studio dikabari lewat WhatsApp
        Http::assertSent(fn ($request) => str_contains($request->url(), 'fonnte.com'));
    }

    public function test_superadmin_reject_keeps_review_visible(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);

        $tenant = $this->tenant();
        $admin = $this->tenantAdmin($tenant);
        $super = $this->superAdmin();
        $review = $this->review($tenant, $this->package($tenant));

        $this->actingAs($admin)
            ->post('/app/ulasan/'.$review->id.'/hapus', ['reason' => 'Minta dihapus.'])
            ->assertRedirect();

        $request = ReviewDeletionRequest::first();

        $this->actingAs($super)
            ->put('/superadmin/permintaan-hapus/'.$request->id.'/tolak', [
                'admin_note' => 'Ulasan masih relevan.',
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('rejected', $request->status);
        $this->assertSame('Ulasan masih relevan.', $request->admin_note);

        $this->assertSame('visible', $review->refresh()->status);

        $response = $this->getJson('/api/studio/'.$tenant->slug.'/reviews');
        $this->assertSame(1, $response->json('data.reviews_count'));
    }

    public function test_superadmin_can_hide_review_directly(): void
    {
        $tenant = $this->tenant();
        $super = $this->superAdmin();
        $review = $this->review($tenant, $this->package($tenant));

        $this->actingAs($super)
            ->put('/superadmin/ulasan/'.$review->id.'/sembunyikan', ['reason' => 'Spam.'])
            ->assertRedirect();

        $this->assertSame('hidden', $review->refresh()->status);

        $this->actingAs($super)
            ->put('/superadmin/ulasan/'.$review->id.'/tampilkan')
            ->assertRedirect();

        $this->assertSame('visible', $review->refresh()->status);
    }

    public function test_hidden_review_still_visible_in_tenant_admin_page(): void
    {
        $tenant = $this->tenant();
        $admin = $this->tenantAdmin($tenant);
        $review = $this->review($tenant, $this->package($tenant));

        $review->update([
            'status'        => 'hidden',
            'hidden_at'     => now(),
            'hidden_reason' => 'Ulasan spam.',
        ]);

        $response = $this->actingAs($admin)->get('/app/ulasan');

        $response->assertOk();
        $response->assertSee('Disembunyikan (disetujui super admin)', false);
        $response->assertSee('Ulasan spam.', false);
    }

    public function test_superadmin_pages_are_reachable(): void
    {
        $super = $this->superAdmin();

        $this->actingAs($super)->get('/superadmin/dashboard')->assertOk();
        $this->actingAs($super)->get('/superadmin/ulasan')->assertOk();
        $this->actingAs($super)->get('/superadmin/permintaan-hapus')->assertOk();
    }

    public function test_tenant_admin_cannot_access_superadmin_pages(): void
    {
        $admin = $this->tenantAdmin($this->tenant());

        $this->actingAs($admin)->get('/superadmin/ulasan')->assertForbidden();
    }
}
