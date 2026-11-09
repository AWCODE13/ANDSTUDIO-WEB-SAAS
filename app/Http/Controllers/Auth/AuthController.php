<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 3;
    private const LOCKOUT_MINUTES = 60;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('tenant.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $ip = $request->ip();

        // Kunci cache berdasarkan email dan IP
        $emailLockoutKey = 'login_lockout:email:' . $email;
        $ipLockoutKey = 'login_lockout:ip:' . $ip;

        // Cek apakah terkena lockout (bandingkan timestamp integer)
        $emailLockout = (int) Cache::get($emailLockoutKey, 0);
        $ipLockout = (int) Cache::get($ipLockoutKey, 0);
        $lockoutUntil = max($emailLockout, $ipLockout);

        if ($lockoutUntil > 0 && now()->lt(now()->createFromTimestamp($lockoutUntil))) {
            $remainingMinutes = (int) now()->diffInMinutes(now()->createFromTimestamp($lockoutUntil));
            return back()->withErrors([
                'email' => 'Akun Anda telah dikunci sementara karena ' . self::MAX_ATTEMPTS . ' kali percobaan login gagal. Silakan coba lagi dalam ' . $remainingMinutes . ' menit lagi demi keamanan.',
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Hapus rate limit setelah login berhasil
            $this->clearRateLimit($email, $ip);

            // Catat aktivitas login berhasil
            LoginActivity::create([
                'user_id'    => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
                'login_at'   => now(),
            ]);

            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            return redirect()->route('tenant.dashboard');
        }

        // Catat aktivitas login gagal (jika email terdaftar)
        $user = User::where('email', $request->email)->first();
        if ($user) {
            LoginActivity::create([
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status'     => 'failed',
                'login_at'   => now(),
            ]);
        }

        // Rate limiting: increment counter untuk email dan IP (manual untuk kompatibilitas semua driver cache)
        $emailAttemptsKey = 'login_attempts:email:' . $email;
        $ipAttemptsKey = 'login_attempts:ip:' . $ip;

        $emailAttempts = (int) Cache::get($emailAttemptsKey, 0) + 1;
        $ipAttempts = (int) Cache::get($ipAttemptsKey, 0) + 1;

        Cache::put($emailAttemptsKey, $emailAttempts, now()->addMinutes(self::LOCKOUT_MINUTES));
        Cache::put($ipAttemptsKey, $ipAttempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        // Hitung sisa percobaan (berdasarkan email atau IP yang lebih rendah)
        $remainingAttempts = max(0, self::MAX_ATTEMPTS - max($emailAttempts, $ipAttempts));

        // Jika mencapai batas maximal, kunci email dan IP (simpan timestamp integer)
        if ($emailAttempts >= self::MAX_ATTEMPTS || $ipAttempts >= self::MAX_ATTEMPTS) {
            $lockoutUntil = now()->addMinutes(self::LOCKOUT_MINUTES)->timestamp;
            Cache::put($emailLockoutKey, $lockoutUntil, now()->addMinutes(self::LOCKOUT_MINUTES));
            Cache::put($ipLockoutKey, $lockoutUntil, now()->addMinutes(self::LOCKOUT_MINUTES));

            return back()->withErrors([
                'email' => 'Akun Anda telah dikunci sementara karena ' . self::MAX_ATTEMPTS . ' kali percobaan login gagal. Silakan coba lagi dalam ' . self::LOCKOUT_MINUTES . ' menit lagi demi keamanan.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah. Sisa percobaan: ' . $remainingAttempts . ' kali lagi sebelum akun dikunci sementara.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Hapus semua data rate limit setelah login berhasil.
     */
    private function clearRateLimit(string $email, string $ip): void
    {
        Cache::forget('login_attempts:email:' . $email);
        Cache::forget('login_attempts:ip:' . $ip);
        Cache::forget('login_lockout:email:' . $email);
        Cache::forget('login_lockout:ip:' . $ip);
    }
}
