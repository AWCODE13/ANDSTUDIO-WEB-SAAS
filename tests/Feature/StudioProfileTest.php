<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudioProfileTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUser(): User
    {
        $tenant = Tenant::create([
            'name' => 'Studio Profil',
            'slug' => 'studio-profil',
            'email' => 'halo@profil.test',
        ]);

        return User::create([
            'name' => 'Owner',
            'email' => 'owner@profil.test',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);
    }

    /**
     * Payload minimal form profil studio.
     */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'name'                => 'Studio Profil',
            'email'               => 'halo@profil.test',
            'operational_start'   => '09:00',
            'operational_end'     => '21:00',
        ], $extra);
    }

    public function test_update_profile_stores_logo_and_social_links(): void
    {
        Storage::fake('public');
        $user = $this->tenantUser();
        $tenant = Tenant::find($user->tenant_id);

        $this->actingAs($user)
            ->put('/app/profile', $this->payload([
                'logo'          => UploadedFile::fake()->image('logo.jpg'),
                'instagram_url' => 'instagram.com/studioprofil',
            ]))
            ->assertRedirect();

        $tenant->refresh();

        $this->assertNotNull($tenant->logo_path);
        $this->assertStringStartsWith('tenants/'.$tenant->id.'/', $tenant->logo_path);
        Storage::disk('public')->assertExists($tenant->logo_path);

        // URL medsos dinormalisasi jadi skema lengkap
        $this->assertSame('https://instagram.com/studioprofil', $tenant->instagram_url);
    }

    public function test_update_profile_replaces_and_deletes_logo_file(): void
    {
        Storage::fake('public');
        $user = $this->tenantUser();
        $tenant = Tenant::find($user->tenant_id);

        // Logo pertama
        $this->actingAs($user)
            ->put('/app/profile', $this->payload([
                'logo' => UploadedFile::fake()->image('pertama.jpg'),
            ]))
            ->assertRedirect();

        $oldPath = $tenant->refresh()->logo_path;
        Storage::disk('public')->assertExists($oldPath);

        // Upload baru -> file lama dihapus
        $this->actingAs($user)
            ->put('/app/profile', $this->payload([
                'logo' => UploadedFile::fake()->image('kedua.jpg'),
            ]))
            ->assertRedirect();

        $newPath = $tenant->refresh()->logo_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        // Centang "Hapus Foto" -> file dihapus, kolom dikosongkan
        $this->actingAs($user)
            ->put('/app/profile', $this->payload(['remove_logo' => 1]))
            ->assertRedirect();

        $this->assertNull($tenant->refresh()->logo_path);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_instagram_handle_is_normalized(): void
    {
        $user = $this->tenantUser();
        $tenant = Tenant::find($user->tenant_id);

        $this->actingAs($user)
            ->put('/app/profile', $this->payload(['instagram_url' => '@studioprofil']))
            ->assertRedirect();

        $this->assertSame('https://instagram.com/studioprofil', $tenant->refresh()->instagram_url);
    }

    public function test_api_studios_returns_logo_rating_and_instagram(): void
    {
        $user = $this->tenantUser();
        $tenant = Tenant::find($user->tenant_id);

        Package::create([
            'tenant_id'         => $tenant->id,
            'name'              => 'Paket API Profil',
            'category'          => 'graduation',
            'description'       => 'Deskripsi',
            'price'             => 100000,
            'down_payment_min'  => 30000,
            'duration_minutes'  => 30,
            'max_persons'       => 2,
            'is_active'         => true,
        ]);

        $tenant->update([
            'logo_path'      => 'tenants/'.$tenant->id.'/logo.jpg',
            'instagram_url'  => 'https://instagram.com/studioprofil',
        ]);

        $response = $this->getJson('/api/studios');

        $response->assertOk();

        $studio = collect($response->json('data.studios'))->firstWhere('slug', $tenant->slug);

        $this->assertNotNull($studio);
        $this->assertStringStartsWith('http', $studio['logo_url']);
        $this->assertStringContainsString('/storage/tenants/'.$tenant->id.'/logo.jpg', $studio['logo_url']);
        $this->assertSame('https://instagram.com/studioprofil', $studio['instagram_url']);
        // Belum ada ulasan -> rating null (bukan angka dummy)
        $this->assertNull($studio['rating']);
        $this->assertSame(0, $studio['reviews_count']);
    }
}
