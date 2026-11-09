<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * A. Daftar semua studio.
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        $plan = $request->get('plan');
        $status = $request->get('status');

        $tenants = Tenant::withCount(['packages', 'bookings', 'users'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($w) use ($query) {
                    $w->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when($plan, fn ($q) => $q->where('subscription_plan', $plan))
            ->when($status, fn ($q) => $q->where('subscription_status', $status))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        // Total pendapatan per tenant (pembayaran booking yang terverifikasi)
        $revenues = \App\Models\Payment::where('status', 'verified')
            ->selectRaw('booking_id, SUM(amount) as total')
            ->groupBy('booking_id')
            ->pluck('total', 'booking_id');

        $stats = [
            'total'    => Tenant::count(),
            'pro'      => Tenant::where('subscription_plan', 'pro')->count(),
            'partner'  => Tenant::where('is_partner', true)->count(),
            'active'   => Tenant::where('subscription_status', 'active')->count(),
        ];

        return view('superadmin.tenants.index', compact('tenants', 'revenues', 'stats', 'query', 'plan', 'status'));
    }

    /**
     * C. Form buat studio manual.
     */
    public function create()
    {
        return view('superadmin.tenants.create', ['tenant' => new Tenant()]);
    }

    /**
     * C. Simpan studio baru + akun admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email'               => 'required|email|max:150|unique:tenants,email',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'admin_name'          => 'required|string|max:100',
            'admin_email'         => 'required|email|max:150|unique:users,email',
            'admin_password'      => 'required|min:8',
            'subscription_plan'   => 'required|in:free,pro',
            'subscription_status' => 'required|in:active,past_due,canceled',
            'is_partner'          => 'nullable|boolean',
            'trial_days'          => 'nullable|integer|min:0|max:365',
        ], [
            'email.unique' => 'Email studio sudah dipakai.',
            'admin_email.unique' => 'Email admin sudah dipakai.',
        ]);

        $tenant = DB::transaction(function () use ($validated) {
            $trialDays = (int) ($validated['trial_days'] ?? 0);
            $isPartner = (bool) ($validated['is_partner'] ?? false);

            $tenant = Tenant::create([
                'name'                => $validated['name'],
                'slug'                => $this->uniqueSlug($validated['name']),
                'email'               => $validated['email'],
                'phone'               => $validated['phone'] ?? null,
                'address'             => $validated['address'] ?? null,
                'subscription_plan'   => $validated['subscription_plan'],
                'subscription_status' => $validated['subscription_status'],
                'is_partner'          => $isPartner,
                'trial_ends_at'       => ($isPartner || $trialDays === 0) ? null : now()->addDays($trialDays),
                'operational_start'   => '09:00:00',
                'operational_end'     => '21:00:00',
                'booking_policy'      => 'Wajib membayar DP minimal 30%. Pembatalan H-2 hangus.',
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name'      => $validated['admin_name'],
                'email'     => $validated['admin_email'],
                'password'  => $validated['admin_password'],
                'role'      => 'tenant_admin',
                'phone'     => $validated['phone'] ?? null,
            ]);

            return $tenant;
        });

        return redirect()
            ->route('superadmin.tenants.show', $tenant->id)
            ->with('success', 'Studio ' . $tenant->name . ' berhasil dibuat.');
    }

    /**
     * B. Detail studio.
     */
    public function show(int $id)
    {
        $tenant = Tenant::withCount(['packages', 'bookings', 'users'])
            ->with(['users', 'invoices' => fn ($q) => $q->latest()->limit(10)])
            ->findOrFail($id);

        $revenue = (float) \App\Models\Payment::where('status', 'verified')
            ->whereIn('booking_id', $tenant->bookings()->pluck('id'))
            ->sum('amount');

        $bookingCount = $tenant->bookings()->count();
        $pendingCount = $tenant->bookings()->where('booking_status', 'pending')->count();

        return view('superadmin.tenants.show', compact('tenant', 'revenue', 'bookingCount', 'pendingCount'));
    }

    /**
     * B. Update info dasar studio.
     */
    public function update(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email'               => 'required|email|max:150|unique:tenants,email,' . $tenant->id,
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'subscription_plan'   => 'required|in:free,pro',
            'subscription_status' => 'required|in:active,past_due,canceled',
            'is_partner'          => 'nullable|boolean',
            'operational_start'   => 'required|date_format:H:i',
            'operational_end'     => 'required|date_format:H:i|after:operational_start',
            'booking_policy'      => 'nullable|string|max:1000',
        ]);

        $validated['is_partner'] = $request->boolean('is_partner');

        $tenant->update($validated);

        return back()->with('success', 'Informasi studio berhasil diperbarui.');
    }

    /**
     * B. Update status langganan (plan / status / trial / partner).
     */
    public function updateSubscription(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'subscription_plan'   => 'required|in:free,pro',
            'subscription_status' => 'required|in:active,past_due,canceled',
            'trial_days'          => 'nullable|integer|min:0|max:365',
            'next_payment_at'     => 'nullable|date',
        ]);

        $isPartner = $tenant->isPartner();

        $data = [
            'subscription_plan'   => $validated['subscription_plan'],
            'subscription_status' => $validated['subscription_status'],
        ];

        // Trial hanya relevan untuk tenant non-partner
        if (! $isPartner) {
            $trialDays = (int) ($validated['trial_days'] ?? 0);
            $data['trial_ends_at'] = $trialDays > 0 ? now()->addDays($trialDays) : null;
        }

        if ($validated['next_payment_at'] ?? null) {
            $data['next_payment_at'] = $validated['next_payment_at'];
        }

        $tenant->update($data);

        return back()->with('success', 'Langganan studio berhasil diperbarui.');
    }

    /**
     * B. Ubah status trial manual (perpanjang / reset).
     */
    public function extendTrial(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);

        $days = (int) $request->input('days', 14);
        $tenant->update([
            'trial_ends_at'       => now()->addDays($days),
            'subscription_status' => 'active',
        ]);

        return back()->with('success', "Trial studio diperpanjang $days hari.");
    }

    /**
     * B. Hapus studio (soft delete).
     */
    public function destroy(int $id)
    {
        $tenant = Tenant::findOrFail($id);

        // Superadmin tidak punya tenant_id, jadi tidak mungkin terhapus lewat sini
        if ($tenant->users()->where('role', 'superadmin')->exists()) {
            return back()->with('error', 'Tidak dapat menghapus studio ini.');
        }

        $tenant->delete();

        return redirect()
            ->route('superadmin.tenants.index')
            ->with('success', 'Studio ' . $tenant->name . ' telah dinonaktifkan.');
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

        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
