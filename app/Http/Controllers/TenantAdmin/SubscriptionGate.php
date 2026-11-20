<?php

namespace App\Http\Controllers\TenantAdmin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionGate
{
    /**
     * Pastikan langganan tenant dalam kondisi baik.
     *
     * - canceled  -> blokir, arahkan ke halaman billing
     * - past_due  -> izinkan (read-only), tampilan handled per fitur
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Superadmin & user tanpa tenant: bebas akses
        if (! $user || ! $user->tenant_id) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            return $next($request);
        }

        // Studio dinonaktifkan superadmin: paksa keluar walau sesi masih hidup
        if ($tenant->isDeactivated()) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->with('error', 'Studio Anda sedang dinonaktifkan oleh pengelola platform. Silakan hubungi superadmin.');
        }

        // Partner: bebas
        if ($tenant->isPartner()) {
            return $next($request);
        }

        // Trial aktif: bebas
        if ($tenant->isTrialActive()) {
            return $next($request);
        }

        // Plan Pro aktif: bebas
        if ($tenant->isPaidPlan()) {
            return $next($request);
        }

        // Nonaktif: blokir total
        if ($tenant->isCanceled()) {
            return redirect()
                ->route('tenant.billing.index')
                ->with('error', 'Langganan Anda sudah berhenti. Aktifkan kembali untuk mengakses dashboard.');
        }

        // Past due: izinkan, tapi tandai agar view bisa menampilkan banner
        $request->attributes->set('subscription_past_due', true);

        return $next($request);
    }
}
