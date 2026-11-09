<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Halaman profil studio.
     */
    public function edit()
    {
        $tenant = Auth::user()->tenant;

        return view('tenant.profile.index', compact('tenant'));
    }

    /**
     * Simpan perubahan profil studio.
     */
    public function update(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email'               => ['required', 'email', 'max:150', Rule::unique('tenants', 'email')->ignore($tenant->id)],
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'operational_start'   => 'required|date_format:H:i',
            'operational_end'     => 'required|date_format:H:i|after:operational_start',
            'booking_policy'      => 'nullable|string|max:1000',
            'instagram_url'       => 'nullable|string|max:255',
            'logo'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_logo'         => 'nullable|boolean',
        ]);

        // --- Logo studio -------------------------------------------------
        // 1) Centang "Hapus Foto" -> hapus file lama, logo jadi null.
        if ($request->boolean('remove_logo') && $tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
            $tenant->logo_path = null;
        }

        // 2) Upload baru -> file lama (jika ada) dihapus, lalu simpan yang baru.
        if ($request->hasFile('logo')) {
            try {
                if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
                    Storage::disk('public')->delete($tenant->logo_path);
                }

                $path = $request->file('logo')->store("tenants/{$tenant->id}", 'public');
                
                if (!$path) {
                    throw new \Exception('Gagal menyimpan file ke storage.');
                }

                $tenant->logo_path = $path;
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal mengupload logo: ' . $e->getMessage())->withInput();
            }
        }

        // --- Sosmed ----------------------------------------------------
        if (filled($validated['instagram_url'] ?? null)) {
            $validated['instagram_url'] = $this->normalizeUrl($validated['instagram_url']);
        } else {
            $validated['instagram_url'] = null;
        }

        // Field request yang bukan kolom tabel tidak ikut di-update.
        unset($validated['logo'], $validated['remove_logo']);

        $tenant->update($validated);

        return back()->with('success', 'Profil studio berhasil diperbarui.');
    }

    /**
     * Pastikan URL medsos bisa dibuka.
     *
     * - "@kaviagraduation"  -> https://instagram.com/kaviagraduation
     * - "instagram.com/x"   -> https://instagram.com/x
     * - "https://@handle"   -> https://instagram.com/handle (repair nilai rusak)
     * - "https://..."       -> dipakai apa adanya
     */
    private function normalizeUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        // Nilai rusak: skema + @handle
        if (preg_match('#^https?://@(.+)$#i', $value, $m)) {
            return 'https://instagram.com/'.ltrim($m[1], '@');
        }

        // Handle Instagram
        if (str_starts_with($value, '@')) {
            return 'https://instagram.com/'.ltrim($value, '@');
        }

        // Sudah punya skema
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        // Domain tanpa skema (instagram.com/nama, youtube.com/@nama)
        if (preg_match('#^[a-z0-9.-]+\.[a-z]{2,}(/|$)#i', $value)) {
            return 'https://'.$value;
        }

        // Selain itu anggap sebagai handle Instagram
        return 'https://instagram.com/'.$value;
    }

    /**
     * Update akun admin (nama & email).
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return back()->with('success', 'Akun admin berhasil diperbarui.');
    }

    /**
     * Ganti password akun admin.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', function ($attr, $value, $fail) {
                if (! Hash::check($value, Auth::user()->password)) {
                    $fail('Kata sandi saat ini salah.');
                }
            }],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Auth::user()->update(['password' => $validated['password']]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
