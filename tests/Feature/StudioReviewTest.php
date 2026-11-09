<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\StudioReview;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudioReviewTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Studio Ulasan',
            'slug' => 'studio-ulasan',
            'email' => 'halo@ulasan.test',
        ]);
    }

    private function package(Tenant $tenant): Package
    {
        return Package::create([
            'tenant_id'        => $tenant->id,
            'name'             => 'Paket Ulasan',
            'category'         => 'graduation',
            'description'      => 'Deskripsi',
            'price'            => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons'      => 2,
            'is_active'        => true,
        ]);
    }

    private function booking(Tenant $tenant, Package $package, string $code, string $status = 'completed', string $email = 'pelanggan@mail.test'): Booking
    {
        return Booking::create([
            'tenant_id'      => $tenant->id,
            'package_id'     => $package->id,
            'booking_code'   => $code,
            'customer_name'  => 'Ahmad Fauzi',
            'customer_email' => $email,
            'customer_phone' => '081234567890',
            'booking_date'   => now()->toDateString(),
            'start_time'     => '09:00:00',
            'end_time'       => '10:00:00',
            'total_amount'   => 100000,
            'payment_status' => 'paid',
            'booking_status' => $status,
        ]);
    }

    public function test_review_rejected_when_booking_not_completed(): void
    {
        $tenant = $this->tenant();
        $booking = $this->booking($tenant, $this->package($tenant), 'FS-PENDING-01', 'confirmed');

        $response = $this->postJson('/api/reviews', [
            'booking_code' => $booking->booking_code,
            'email'        => 'pelanggan@mail.test',
            'rating'       => 5,
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, StudioReview::count());
    }

    public function test_review_rejected_when_email_does_not_match(): void
    {
        $tenant = $this->tenant();
        $booking = $this->booking($tenant, $this->package($tenant), 'FS-SALAH-01');

        $response = $this->postJson('/api/reviews', [
            'booking_code' => $booking->booking_code,
            'email'        => 'oranglain@mail.test',
            'rating'       => 5,
        ]);

        $response->assertStatus(403);
        $this->assertSame(0, StudioReview::count());
    }

    public function test_review_saved_with_booking_data_and_masked_name(): void
    {
        $tenant = $this->tenant();
        $booking = $this->booking($tenant, $this->package($tenant), 'FS-OK-01');

        $response = $this->postJson('/api/reviews', [
            'booking_code' => $booking->booking_code,
            'email'        => 'pelanggan@mail.test',
            'rating'       => 4,
            'comment'      => 'Mantap, hasilnya bagus.',
        ]);

        $response->assertCreated();

        $review = StudioReview::first();
        $this->assertNotNull($review);
        $this->assertSame(4, $review->rating);
        $this->assertSame($booking->id, $review->booking_id);
        $this->assertSame($tenant->id, $review->tenant_id);
        // Nama diambil dari booking, bukan dari input pelanggan
        $this->assertSame('Ahmad Fauzi', $review->customer_name);
        // Nama yang dikirim ke publik dimasking
        $this->assertSame('Ahmad F.', $response->json('data.review.customer_name'));
    }

    public function test_duplicate_review_rejected(): void
    {
        $tenant = $this->tenant();
        $booking = $this->booking($tenant, $this->package($tenant), 'FS-DUP-01');

        $this->postJson('/api/reviews', [
            'booking_code' => $booking->booking_code,
            'email'        => 'pelanggan@mail.test',
            'rating'       => 5,
        ])->assertCreated();

        $this->postJson('/api/reviews', [
            'booking_code' => $booking->booking_code,
            'email'        => 'pelanggan@mail.test',
            'rating'       => 1,
        ])->assertStatus(409);

        $this->assertSame(1, StudioReview::count());
    }

    public function test_review_via_email_only_when_single_completed_booking(): void
    {
        $tenant = $this->tenant();
        $package = $this->package($tenant);
        $booking = $this->booking($tenant, $package, 'FS-SATU-01');

        $this->postJson('/api/reviews', [
            'email'  => 'pelanggan@mail.test',
            'rating' => 5,
        ])->assertCreated();

        $this->assertSame(1, StudioReview::count());
        $this->assertSame($booking->id, StudioReview::first()->booking_id);
    }

    public function test_review_via_email_ambiguous_returns_candidates(): void
    {
        $tenant = $this->tenant();
        $package = $this->package($tenant);
        $this->booking($tenant, $package, 'FS-A-01');
        $this->booking($tenant, $package, 'FS-B-01');

        $response = $this->postJson('/api/reviews', [
            'email'  => 'pelanggan@mail.test',
            'rating' => 5,
        ]);

        $response->assertStatus(422);
        $this->assertCount(2, $response->json('candidates'));
        $this->assertSame(0, StudioReview::count());
    }

    public function test_studio_reviews_endpoint_returns_rounded_average(): void
    {
        $tenant = $this->tenant();
        $package = $this->package($tenant);

        // Rating 5, 4, 4 -> rata-rata 4.33 -> 4.3
        foreach ([['FS-R1-01', 5], ['FS-R2-01', 4], ['FS-R3-01', 4]] as [$code, $rating]) {
            $booking = $this->booking($tenant, $package, $code);

            $this->postJson('/api/reviews', [
                'booking_code' => $booking->booking_code,
                'email'        => 'pelanggan@mail.test',
                'rating'       => $rating,
                'comment'      => 'Ulasan '.$code,
            ])->assertCreated();
        }

        $response = $this->getJson('/api/studio/'.$tenant->slug.'/reviews');

        $response->assertOk();
        $this->assertSame(4.3, $response->json('data.rating'));
        $this->assertSame(3, $response->json('data.reviews_count'));
        $this->assertCount(3, $response->json('data.reviews'));
        $this->assertSame('Ahmad F.', $response->json('data.reviews.0.customer_name'));
    }

    public function test_studio_reviews_endpoint_without_reviews_has_null_rating(): void
    {
        $tenant = $this->tenant();

        $response = $this->getJson('/api/studio/'.$tenant->slug.'/reviews');

        $response->assertOk();
        $this->assertNull($response->json('data.rating'));
        $this->assertSame(0, $response->json('data.reviews_count'));
        $this->assertCount(0, $response->json('data.reviews'));
    }

    public function test_order_api_exposes_can_review_and_masks_contact(): void
    {
        $tenant = $this->tenant();
        $booking = $this->booking($tenant, $this->package($tenant), 'FS-ORDER-01');

        $response = $this->getJson('/api/pesanan/'.$booking->booking_code);

        $response->assertOk();
        $this->assertTrue($response->json('data.can_review'));
        $this->assertNull($response->json('data.review'));
        // Email tidak boleh bocor lewat halaman status pesanan
        $this->assertStringNotContainsString('pelanggan@mail.test', $response->json('data.customer_email'));
    }
}
