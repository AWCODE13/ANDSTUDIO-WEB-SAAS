<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DetectTenant
{
    /**
     * Domain utama platform (tanpa subdomain).
     * Contoh: andstudio.com → subdomain "kavia" dari kavia.andstudio.com akan terdeteksi.
     */
    protected string $mainDomain;

    /**
     * URL landing platform untuk redirect saat subdomain tidak ditemukan.
     */
    protected string $fallbackUrl;

    public function __construct()
    {
        $appUrl = config('app.url', 'http://localhost:8000');

        $this->mainDomain = config('app.main_domain')
            ?? parse_url($appUrl, PHP_URL_HOST)
            ?? 'localhost';

        $this->fallbackUrl = rtrim($appUrl, '/');
    }

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Coba dapatkan subdomain dari header X-Tenant-Subdomain (dikirim frontend Next.js)
        $subdomain = $request->header('X-Tenant-Subdomain');

        // 2. Jika tidak ada header, coba ekstrak dari HTTP_HOST
        if (!$subdomain) {
            $host = $request->getHost();

            // Jika host adalah IP address (IPv4 atau IPv6), tidak ada subdomain
            if ($this->isIpAddress($host)) {
                return $next($request);
            }

            $subdomain = $this->extractSubdomain($host);
        }

        // Jika tidak ada subdomain (domain utama / IP address), lanjutkan tanpa tenant context
        if (!$subdomain) {
            return $next($request);
        }

        // Cari tenant berdasarkan subdomain
        $tenant = \App\Models\Tenant::withoutGlobalScopes()
            ->where('subdomain', $subdomain)
            ->first();

        if (!$tenant) {
            // Subdomain tidak terdaftar → redirect ke landing platform
            return redirect()
                ->to($this->fallbackUrl)
                ->with('error', "Studio {$subdomain} tidak ditemukan.");
        }

        // Simpan tenant ke request attribute untuk diakses di controller
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_subdomain', $subdomain);

        // Jika user sudah login dan milik tenant ini, pastikan tenant_id di session konsisten
        if (Auth::check() && Auth::user()->tenant_id === $tenant->id) {
            $request->session()->put('tenant_id', $tenant->id);
        }

        return $next($request);
    }

    /**
     * Cek apakah host adalah IP address (IPv4 atau IPv6).
     * IP address tidak memiliki subdomain.
     */
    protected function isIpAddress(string $host): bool
    {
        // Hilangkan port
        $host = explode(':', $host)[0];

        // IPv4: 4 octet yang semuanya angka
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host)) {
            return true;
        }

        // IPv6: mengandung colon (dan bukan domain dengan port)
        if (strpos($host, ':') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Ekstrak subdomain dari host.
     * Contoh: kavia.andstudio.com → "kavia"
     * Contoh: localhost:8000 → null
     * Contoh: andstudio.com → null
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Hilangkan port
        $host = explode(':', $host)[0];
        $parts = explode('.', $host);

        // Perlu minimal 3 bagian untuk memiliki subdomain (sub.domain.tld)
        if (count($parts) < 3) {
            return null;
        }

        $mainDomainParts = explode('.', $this->mainDomain);
        $mainDomainCount = count($mainDomainParts);

        // Jika host sama dengan main domain atau lebih sedikit bagian, tidak ada subdomain
        if (count($parts) <= $mainDomainCount) {
            return null;
        }

        // Ambil bagian sebelum main domain
        $subdomainParts = array_slice($parts, 0, count($parts) - $mainDomainCount);

        return implode('.', $subdomainParts);
    }
}
