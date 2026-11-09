<?php

namespace App\Observers;

use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PackageObserver
{
    /**
     * Mencegah pembuatan paket baru jika kuota max_packages sudah habis.
     *
     * Kuota tidak berlaku untuk: Partner, Trial aktif, Plan Pro aktif.
     * Hanya berlaku untuk Plan Free.
     */
    public function creating(Package $package): void
    {
        // Muat tenant tanpa global scope (soft delete, tenant scope) agar observer
        // tetap bekerja walaupun tenant sedang dalam status soft-deleted.
        $tenant = Tenant::withoutGlobalScopes()->find($package->tenant_id);

        if (! $tenant instanceof Tenant) {
            return;
        }

        // Kuota tidak berlaku untuk partner, trial aktif, atau plan pro
        if ($tenant->isPartner() || $tenant->isTrialActive() || $tenant->isPaidPlan()) {
            return;
        }

        $quota = $tenant->quota();
        $maxPackages = $quota['max_packages'] ?? null;

        if ($maxPackages === null) {
            return; // unlimited
        }

        $currentCount = Package::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->count();

        if ($currentCount >= $maxPackages) {
            throw ValidationException::withMessages([
                'packages' => 'Kuota paket sudah habis. Maksimal ' . $maxPackages . ' paket aktif untuk plan Free. Upgrade ke Pro untuk paket tidak terbatas.',
            ]);
        }
    }
}
