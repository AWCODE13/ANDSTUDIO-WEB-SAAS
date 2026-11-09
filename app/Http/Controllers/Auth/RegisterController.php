<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Lama trial untuk tenant baru (hari).
     */
    public const TRIAL_DAYS = 14;

    /**
     * Tampilkan form pendaftaran studio.
     */
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('tenant.dashboard');
        }

        return view('auth.register');
    }

    /**
     * Proses pendaftaran studio baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'studio_name' => 'required|string|max:100',
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|max:150|unique:users,email',
            'phone'       => 'required|string|max:20',
            'password'    => 'required|min:8|confirmed',
            'terms'       => 'accepted',
            'plan'        => 'nullable|string|in:free,pro',
            'instagram_url' => 'nullable|url',
            'operational_start' => 'nullable|date_format:H:i',
            'operational_end'   => 'nullable|date_format:H:i',
            'address'           => 'required|string|max:500',
            'logo_file'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'address.required'   => 'Alamat studio wajib diisi.',
            'email.unique'       => 'Email ini sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui syarat & ketentuan.',
        ]);

        $plan = $validated['plan'] ?? 'free';

        $logoPath = null;
        if ($request->hasFile('logo_file')) {
            $logoPath = $request->file('logo_file')->store('logos', 'public');
        }

        $tenant = DB::transaction(function () use ($validated, $plan, $logoPath) {
            // 1. Slug unik dari nama studio
            $slug = $this->uniqueSlug($validated['studio_name']);

            // 2. Buat tenant dengan plan yang dipilih
            $tenant = Tenant::create([
                'name'                => $validated['studio_name'],
                'slug'                => $slug,
                'email'               => $validated['email'],
                'phone'               => $validated['phone'],
                'subscription_plan'   => $plan,
                'subscription_status' => 'active',
                'is_partner'          => false,
                'trial_ends_at'       => now()->addDays(self::TRIAL_DAYS),
                'operational_start'   => $validated['operational_start'] ?? '09:00:00',
                'operational_end'     => $validated['operational_end'] ?? '21:00:00',
                'instagram_url'       => $validated['instagram_url'] ?? null,
                'address'             => $validated['address'],
                'logo_path'           => $logoPath,
                'booking_policy'      => 'Wajib membayar DP minimal 30%. Pembatalan H-2 hangus.',
            ]);

            // 3. Buat user admin studio
            User::create([
                'tenant_id' => $tenant->id,
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => $validated['password'],
                'role'      => 'tenant_admin',
                'phone'     => $validated['phone'],
            ]);

            return $tenant;
        });

        // 4. Auto login
        $user = User::where('email', $validated['email'])->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        $message = 'Selamat datang di ANDstudio! Trial gratis Anda aktif selama ' . self::TRIAL_DAYS . ' hari.';

        $targetUrl = $plan === 'pro' ? url('/app/billing') : url('/app/dashboard');

        // 5a. Response JSON (dipakai Next.js)
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'booking_code'  => null,
                    'tenant_slug'   => $tenant->slug,
                    'tenant_name'   => $tenant->name,
                    'trial_days'    => self::TRIAL_DAYS,
                    'dashboard_url' => $targetUrl,
                    'message'       => $message,
                ],
            ]);
        }

        // 5b. Response Blade (form Laravel)
        return redirect($targetUrl)
            ->with('success', $message);
    }

    /**
     * Buat slug unik dari nama studio.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'studio-' . Str::random(6);
        $slug = $base;
        $i = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
