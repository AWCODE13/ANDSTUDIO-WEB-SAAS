<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\ReviewDeletionRequest;
use App\Models\StudioReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Daftar ulasan pelanggan untuk studio ini.
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;

        $reviews = StudioReview::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with(['package:id,name', 'deletionRequests' => fn ($q) => $q->latest()])
            ->orderByDesc('created_at')
            ->paginate(15);

        $base = StudioReview::withoutGlobalScopes()->where('tenant_id', $tenant->id);

        $stats = [
            'total'   => (clone $base)->count(),
            'visible' => (clone $base)->visible()->count(),
            'hidden'  => (clone $base)->where('status', 'hidden')->count(),
            'pending' => ReviewDeletionRequest::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->pending()
                ->count(),
            'rating'  => (float) ((clone $base)->visible()->avg('rating') ?? 0),
        ];

        return view('tenant.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Ajukan penghapusan ulasan ke super admin.
     * Tenant tidak boleh menghapus sendiri.
     */
    public function requestDeletion(Request $request, StudioReview $review)
    {
        $tenant = Auth::user()->tenant;

        if ((int) $review->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        if ($review->isHidden()) {
            return back()->with('error', 'Ulasan ini sudah disembunyikan.');
        }

        if ($review->deletionRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', 'Sudah ada permintaan hapus yang menunggu persetujuan super admin.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        ReviewDeletionRequest::create([
            'tenant_id'     => $tenant->id,
            'review_id'     => $review->id,
            'reason'        => $validated['reason'],
            'status'        => 'pending',
            'requested_by'  => Auth::id(),
        ]);

        try {
            $superAdmins = \App\Models\User::where('role', 'superadmin')->get();
            foreach ($superAdmins as $admin) {
                $admin->notify(new \App\Notifications\ReviewDeletionRequestedNotification(
                    \App\Models\ReviewDeletionRequest::latest()->first()
                ));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending ReviewDeletionRequestedNotification: ' . $e->getMessage());
        }

        return back()->with('success', 'Permintaan penghapusan ulasan dikirim ke super admin.');
    }
}
