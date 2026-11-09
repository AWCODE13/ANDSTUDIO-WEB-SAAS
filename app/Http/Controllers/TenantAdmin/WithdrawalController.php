<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;

        $withdrawals = $tenant->withdrawals()
            ->withoutGlobalScopes()
            ->latest()
            ->paginate(15);

        return view('tenant.withdrawals.index', [
            'tenant'         => $tenant,
            'withdrawals'    => $withdrawals,
            'availableBalance' => $tenant->available_balance,
            'totalWithdrawn'   => $tenant->total_withdrawn,
            'hasBank'        => $tenant->hasBankConfigured(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50000',
        ]);

        $tenant = Auth::user()->tenant;

        if (! $tenant->hasBankConfigured()) {
            return back()->withErrors([
                'amount' => 'Lengkapi data rekening bank di halaman Pengaturan terlebih dahulu.',
            ]);
        }

        $amount = (int) $validated['amount'];
        $error = null;

        DB::transaction(function () use ($tenant, $amount, &$error) {
            // Kunci baris tenant agar dua request bersamaan tidak lolos validasi saldo.
            $lockedTenant = Tenant::withoutGlobalScopes()->lockForUpdate()->find($tenant->id);

            $hasPending = $lockedTenant->withdrawals()
                ->withoutGlobalScopes()
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                $error = 'Masih ada pengajuan penarikan yang sedang diproses. Tunggu hingga selesai.';
                return;
            }

            if ($amount > (int) $lockedTenant->available_balance) {
                $error = 'Nominal penarikan melebihi saldo tersedia.';
                return;
            }

            $lockedTenant->withdrawals()->create([
                'amount'         => $amount,
                'status'         => 'pending',
                'bank_name'      => $lockedTenant->bank_name,
                'account_number' => $lockedTenant->account_number,
                'account_name'   => $lockedTenant->account_name,
            ]);
        });

        if ($error) {
            return back()->withErrors(['amount' => $error])->withInput();
        }

        return back()->with('success', 'Pengajuan penarikan berhasil dikirim. Menunggu proses admin.');
    }
}
