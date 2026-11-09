<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Package;
use App\Models\BookingSlot;
use App\Models\Booking;
use App\Models\StudioReview;
use App\Services\BookingExpiryService;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    // Daftar semua studio aktif (untuk halaman beranda)
    public function index()
    {
        // Select semua kolom: kolom profil baru (logo_path, instagram_url)
        // otomatis ikut tanpa harus hardcode daftar kolom.
        // Pakai withoutGlobalScopes agar daftar studio Publik tidak tertangkap
        // global scope BelongsToTenant (tenant admin lihat hanya paketnya sendiri).
        $tenants = Tenant::whereHas('packages', function ($q) {
            $q->withoutGlobalScopes()->where('is_active', true);
        })->get();

        // Rata-rata rating dari ulasan pelanggan (1 query untuk semua studio).
        $aggregates = StudioReview::aggregateByTenant();

        $studios = $tenants->map(function ($tenant) use ($aggregates) {
            $aggregate = $aggregates[$tenant->id] ?? ['rating' => null, 'reviews_count' => 0];

            return [
                'id'                => $tenant->id,
                'name'              => $tenant->name,
                'slug'              => $tenant->slug,
                'logo_url'          => $tenant->logo_url,
                'rating'            => $aggregate['rating'],
                'reviews_count'     => $aggregate['reviews_count'],
                'instagram_url'     => $tenant->instagram_url,
                'operational_start' => $tenant->operational_start,
                'operational_end'   => $tenant->operational_end,
                'address'           => $tenant->address,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'studios' => $studios,
            ],
        ]);
    }

    /**
     * Resolve tenant dari request: prioritas subdomain (middleware DetectTenant),
     * fallback ke slug dari URL path.
     */
    protected function resolveTenant(?string $slug = null): \App\Models\Tenant
    {
        $tenantFromRequest = request()->attributes->get('tenant');

        if ($tenantFromRequest instanceof \App\Models\Tenant) {
            return $tenantFromRequest;
        }

        if ($slug) {
            return Tenant::where('slug', $slug)->firstOrFail();
        }

        abort(404, 'Studio tidak ditemukan.');
    }

    // Katalog studio: daftar paket aktif
    public function show(?string $slug = null)
    {
        $tenant = $this->resolveTenant($slug);

        $packages = Package::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with('photos')
            ->get();

        // Transform photos URL di setiap paket
        $packages->each(function ($pkg) {
            $pkg->photos->transform(function ($photo) {
                return [
                    'id' => $photo->id,
                    'photo_url' => $photo->url,
                    'sort_order' => $photo->sort_order,
                ];
            });
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $this->tenantPayload($tenant),
                'packages' => $packages,
            ],
        ]);
    }

    /**
     * Data profil studio yang dipakai frontend (katalog + halaman profil).
     */
    private function tenantPayload(Tenant $tenant): array
    {
        $aggregate = StudioReview::aggregateByTenant()[$tenant->id] ?? ['rating' => null, 'reviews_count' => 0];

        return [
            'id'                => $tenant->id,
            'name'              => $tenant->name,
            'slug'              => $tenant->slug,
            'email'             => $tenant->email,
            'phone'             => $tenant->phone,
            'address'           => $tenant->address,
            'logo_url'          => $tenant->logo_url,
            'rating'            => $aggregate['rating'],
            'reviews_count'     => $aggregate['reviews_count'],
            'instagram_url'     => $tenant->instagram_url,
            'operational_start' => $tenant->operational_start,
            'operational_end'   => $tenant->operational_end,
            'booking_policy'    => $tenant->booking_policy,
        ];
    }

    /**
     * Statistik platform publik (halaman beranda).
     * Data dinamis: jumlah studio aktif, total booking, rata-rata rating, dan uptime.
     */
    public function stats()
    {
        // Studio yang memiliki paket aktif (partner platform)
        $studioCount = Tenant::withoutGlobalScopes()
            ->whereHas('packages', function ($q) {
                $q->withoutGlobalScopes()->where('is_active', true);
            })
            ->count();

        // Total booking semua tenant (all-time)
        $totalBookings = Booking::withoutGlobalScopes()->count();

        // Rata-rata rating dari semua ulasan (client satisfaction)
        $avgRating = StudioReview::withoutGlobalScopes()
            ->visible()
            ->avg('rating');

        // Uptime dalam bulan (dari tenant terlama yang masih aktif)
        $oldestTenant = Tenant::withoutGlobalScopes()
            ->orderBy('created_at', 'asc')
            ->first();
        $uptimeMonths = $oldestTenant
            ? max(1, (int) ceil(now()->diffInMonths($oldestTenant->created_at)))
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'studio_partners'   => $studioCount,
                'total_bookings'    => $totalBookings,
                'client_satisfaction' => $avgRating ? round((float) $avgRating, 1) : 0,
                'uptime_months'     => $uptimeMonths,
            ],
        ]);
    }

    // Detail paket + slot tersedia
    // URUTAN PARAMETER: Laravel route binding mengikuti urutan di route pattern
    // (/studio/{slug}/package/{packageId}), jadi $slug harus dulu, $packageId keduanya.
    public function package(?string $slug = null, $packageId = null)
    {
        $packageId = (int) $packageId;
        $tenant = $this->resolveTenant($slug);

        $package = Package::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('id', $packageId)
            ->where('is_active', true)
            ->with('photos')
            ->firstOrFail();

        // Transform photos ke include URL accessor
        $package->photos->transform(function ($photo) {
            return [
                'id' => $photo->id,
                'photo_url' => $photo->url,
                'sort_order' => $photo->sort_order,
            ];
        });

        BookingExpiryService::releaseExpired($tenant->id, $package->id);

        $availableSlots = BookingSlot::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('package_id', $package->id)
            ->where('date', '>=', now()->toDateString())
            ->where('status', 'available')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('date');

        return response()->json([
            'success' => true,
            'data' => [
                'package' => $package,
                'availableSlots' => $availableSlots,
            ],
        ]);
    }
}
