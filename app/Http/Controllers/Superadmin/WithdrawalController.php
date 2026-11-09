<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Tenant;
use App\Notifications\WithdrawalStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WithdrawalController extends Controller
{
    /**
     * Daftar semua pengajuan penarikan, filterable, paginated.
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with('tenant')
            ->withoutGlobalScopes()
            ->latest()
            ->when($request->get('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->when($request->get('q'), function ($q) use ($request) {
                $q->whereHas('tenant', function ($w) use ($request) {
                    $w->where('name', 'like', "%{$request->get('q')}%")
                        ->orWhere('email', 'like', "%{$request->get('q')}%");
                });
            });

        $withdrawals = $query->paginate(20)->withQueryString();

        $stats = [
            'total_pending'     => Withdrawal::withoutGlobalScopes()->where('status', 'pending')->sum('amount'),
            'total_completed'   => Withdrawal::withoutGlobalScopes()->where('status', 'completed')->sum('amount'),
            'count_pending'     => Withdrawal::withoutGlobalScopes()->where('status', 'pending')->count(),
            'count_completed'   => Withdrawal::withoutGlobalScopes()->where('status', 'completed')->count(),
            'count_rejected'    => Withdrawal::withoutGlobalScopes()->where('status', 'rejected')->count(),
        ];

        return view('superadmin.withdrawals.index', compact('withdrawals', 'stats'));
    }

    /**
     * Detail satu pengajuan + aksi approve/reject.
     */
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load('tenant');
        return view('superadmin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Tandai Selesai (admin sudah transfer) — upload bukti transfer opsional.
     */
    public function complete(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'transfer_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes'          => 'nullable|string|max:500',
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan pending yang bisa diproses.');
        }

        $data = [
            'status'       => 'completed',
            'processed_at' => now(),
        ];

        if ($request->hasFile('transfer_proof')) {
            $path = $request->file('transfer_proof')->store('withdrawals/proofs', 'public');
            $data['transfer_proof_path'] = $path;
        }

        $withdrawal->update($data);

        foreach ($withdrawal->tenant->users as $user) {
            $user->notify(new WithdrawalStatusUpdatedNotification($withdrawal));
        }

        return back()->with('success', 'Penarikan ditandai selesai. Bukti transfer disimpan.');
    }

    /**
     * Tolak pengajuan (w/ alasan).
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan pending yang bisa ditolak.');
        }

        $withdrawal->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'processed_at'     => now(),
        ]);

        foreach ($withdrawal->tenant->users as $user) {
            $user->notify(new WithdrawalStatusUpdatedNotification($withdrawal));
        }

        return back()->with('success', 'Penarikan ditolak. Alasan telah dicatat.');
    }
}