<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackagePhoto;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackagePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUser(): User
    {
        $tenant = Tenant::create([
            'name' => 'Studio Test',
            'slug' => 'studio-test',
            'email' => 'halo@studio.test',
        ]);

        return User::create([
            'name' => 'Owner',
            'email' => 'owner@studio.test',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_admin_form_shows_delete_button_with_required_classes(): void
    {
        $user = $this->tenantUser();
        $package = Package::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Paket Uji',
            'category' => 'graduation',
            'description' => 'Deskripsi',
            'price' => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons' => 2,
            'is_active' => true,
        ]);

        PackagePhoto::create([
            'package_id' => $package->id,
            'photo_url' => 'packages/'.$package->id.'/foto.jpg',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)->get('/app/packages/'.$package->id.'/edit');

        $response->assertOk();
        $response->assertSee('delete-btn', false);
        // Class wajib dari user (tombol hapus mengambang di pojok kanan atas)
        $response->assertSee('absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full z-20', false);
        // Wrapper gambar harus relative agar tombol mengambang
        $response->assertSee('photo-item relative group', false);
        // Overlay tidak boleh menutupi klik ke tombol
        $response->assertSee('pointer-events-none', false);
        $response->assertSee('data-action="delete"', false);
        $response->assertSee('data-action="reorder"', false);
    }

    public function test_api_returns_absolute_photo_url(): void
    {
        $user = $this->tenantUser();
        $tenant = Tenant::find($user->tenant_id);
        $package = Package::create([
            'tenant_id' => $tenant->id,
            'name' => 'Paket API',
            'category' => 'graduation',
            'description' => 'Deskripsi',
            'price' => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons' => 2,
            'is_active' => true,
        ]);

        PackagePhoto::create([
            'package_id' => $package->id,
            'photo_url' => 'packages/'.$package->id.'/foto.jpg',
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/studio/'.$tenant->slug);

        $response->assertOk();
        $url = $response->json('data.packages.0.photos.0.photo_url');

        $this->assertNotNull($url);
        $this->assertStringStartsWith('http', $url);
        $this->assertStringContainsString('/storage/packages/'.$package->id.'/foto.jpg', $url);
    }

    public function test_upload_stores_relative_path_and_limits_to_four_photos(): void
    {
        Storage::fake('public');
        $user = $this->tenantUser();
        $package = Package::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Paket Upload',
            'category' => 'graduation',
            'description' => 'Deskripsi',
            'price' => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons' => 2,
            'is_active' => true,
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->actingAs($user)
                ->post('/app/packages/'.$package->id.'/photos', [
                    'photo' => UploadedFile::fake()->image('foto'.$i.'.jpg'),
                ])
                ->assertCreated();
        }

        $this->assertSame(4, $package->photos()->count());

        // Foto ke-5 harus ditolak
        $this->actingAs($user)
            ->post('/app/packages/'.$package->id.'/photos', [
                'photo' => UploadedFile::fake()->image('kelima.jpg'),
            ])
            ->assertStatus(400);

        $this->assertSame(4, $package->photos()->count());

        // photo_url harus path relatif (bukan URL absolut)
        $this->assertStringStartsWith('packages/'.$package->id.'/', $package->photos()->first()->photo_url);
    }

    public function test_delete_removes_photo_and_file(): void
    {
        Storage::fake('public');
        $user = $this->tenantUser();
        $package = Package::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Paket Hapus',
            'category' => 'graduation',
            'description' => 'Deskripsi',
            'price' => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('/app/packages/'.$package->id.'/photos', [
                'photo' => UploadedFile::fake()->image('hapus.jpg'),
            ])
            ->assertCreated();

        $photo = $package->photos()->first();
        Storage::disk('public')->assertExists($photo->photo_url);

        $this->actingAs($user)
            ->delete('/app/photos/'.$photo->id)
            ->assertOk();

        $this->assertSame(0, $package->photos()->count());
        Storage::disk('public')->assertMissing($photo->photo_url);
    }

    public function test_reorder_swaps_sort_order(): void
    {
        $user = $this->tenantUser();
        $package = Package::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Paket Reorder',
            'category' => 'graduation',
            'description' => 'Deskripsi',
            'price' => 100000,
            'down_payment_min' => 30000,
            'duration_minutes' => 30,
            'max_persons' => 2,
            'is_active' => true,
        ]);

        $a = PackagePhoto::create([
            'package_id' => $package->id,
            'photo_url' => 'packages/'.$package->id.'/a.jpg',
            'sort_order' => 0,
        ]);
        $b = PackagePhoto::create([
            'package_id' => $package->id,
            'photo_url' => 'packages/'.$package->id.'/b.jpg',
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->post('/app/packages/'.$package->id.'/photos/reorder', [
                'photo_id' => $a->id,
                'swap_with_id' => $b->id,
            ])
            ->assertOk();

        $this->assertSame(1, $a->fresh()->sort_order);
        $this->assertSame(0, $b->fresh()->sort_order);
    }
}
