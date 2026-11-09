<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Seeder tenant utama ANDstudio.
 *
 * - Superadmin platform (Andcode Digital)
 * - Tenant Kaviagraduation (akun partner: plan free, gratis selamanya)
 * - Katalog paket + slot jadwal contoh
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperadmin();
        $this->seedTenant();
    }

    /**
     * 1. Akun Superadmin Platform (Andcode Digital).
     */
    private function seedSuperadmin(): void
    {
        if (User::where('email', 'admin@andcode.digital')->exists()) {
            return;
        }

        User::create([
            'name'     => 'Superadmin Andcode',
            'email'    => 'admin@andcode.digital',
            'password' => Hash::make('password123'),
            'role'     => 'superadmin',
            'phone'    => '081234567890',
        ]);

        $this->command?->info('Superadmin dibuat: admin@andcode.digital');
    }

    /**
     * 2. Tenant Kaviagraduation (akun partner).
     */
    private function seedTenant(): void
    {
        if (Tenant::where('slug', 'kaviagraduation')->exists()) {
            $this->command?->info('Tenant Kaviagraduation sudah ada, dilewati.');
            return;
        }

        $tenant = Tenant::create([
            'name'                => 'Kaviagraduation',
            'slug'                => 'kaviagraduation',
            'email'               => 'halo@kaviagraduation.com',
            'phone'               => '081234567890',
            'address'             => 'Yogyakarta, Indonesia',
            'subscription_plan'   => 'free',
            'subscription_status' => 'active',
            // Akun partner: gratis selamanya, tidak pernah ditagih, bebas dari gate langganan
            'is_partner'          => true,
            'trial_ends_at'       => null,
            'next_payment_at'     => null,
            'plan_expires_at'     => null,
            'operational_start'   => '09:00:00',
            'operational_end'     => '21:00:00',
            'booking_policy'      => 'Wajib membayar DP minimal 30%. Pembatalan H-2 hangus.',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Admin Kaviagraduation',
            'email'     => 'admin@kaviagraduation.com',
            'password'  => Hash::make('password123'),
            'role'      => 'tenant_admin',
            'phone'     => '081234567890',
        ]);

        $this->seedPackages($tenant);
        $this->seedSlots($tenant);

        $this->command?->info('Tenant Kaviagraduation dibuat (plan free, partner).');
    }

    /**
     * 3. Katalog paket foto.
     */
    private function seedPackages(Tenant $tenant): void
    {
        Package::insert([
            [
                'tenant_id'        => $tenant->id,
                'name'             => 'Graduation Basic',
                'category'         => 'graduation',
                'description'      => 'Sesi wisuda studio 30 menit, 1 kostum, 5 file diedit.',
                'price'            => 250000,
                'down_payment_min' => 75000,
                'duration_minutes' => 30,
                'max_persons'      => 3,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'tenant_id'        => $tenant->id,
                'name'             => 'Self Photo Session',
                'category'         => 'self-photo',
                'description'      => 'Sesi self photo 30 menit, semua softfile via Google Drive.',
                'price'            => 150000,
                'down_payment_min' => 50000,
                'duration_minutes' => 30,
                'max_persons'      => 2,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    /**
     * 4. Slot jadwal 7 hari ke depan (per paket).
     *
     * Memakai command studio:generate-slots supaya logikanya sama
     * dengan generate manual dari dashboard admin (aman duplikat).
     */
    private function seedSlots(Tenant $tenant): void
    {
        \Illuminate\Support\Facades\Artisan::call(
            'studio:generate-slots',
            [
                'tenant' => $tenant->slug,
                '--days' => 7,
            ]
        );
    }
}
