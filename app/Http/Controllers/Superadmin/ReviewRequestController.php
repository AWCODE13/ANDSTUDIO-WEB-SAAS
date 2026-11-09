<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ReviewDeletionRequest;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewRequestController extends Controller
{
    /**
     * Antrean permintaan penghapusan ulasan dari studio.
     */
    public function index()
    {
        $requests = ReviewDeletionRequest::withoutGlobalScopes()
            ->with(['tenant:id,name,slug,phone', 'review:id,customer_name,rating,comment,tenant_id', 'requester:id,name'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('superadmin.review_requests.index', compact('requests'));
    }

    /**
     * Setujui permintaan: ulasan disembunyikan (tidak dihapus permanen).
     */
    public function approve(ReviewDeletionRequest $reviewRequest)
    {
        $review = $reviewRequest->review;

        if ($review) {
            $review->update([
                'status'        => 'hidden',
                'hidden_at'     => now(),
                'hidden_by'     => Auth::id(),
                'hidden_reason' => $reviewRequest->reason,
            ]);
        }

        $reviewRequest->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyTenant(
            $reviewRequest,
            "Permintaan penghapusan ulasan pelanggan di studio Anda telah DISETUJUI oleh ANDstudio.\n\n"
            ."Ulasan tersebut kini disembunyikan dari profil publik dan tidak lagi dihitung dalam rating studio.\n\n"
            ."Alasan dari studio: {$reviewRequest->reason}\n\n"
            ."Terima kasih."
        );

        return back()->with('success', 'Permintaan disetujui. Ulasan disembunyikan dari profil publik.');
    }

    /**
     * Tolak permintaan: ulasan tetap tampil.
     */
    public function reject(Request $request, ReviewDeletionRequest $reviewRequest)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $note = $validated['admin_note'] ?? null;

        $reviewRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_note'  => $note,
        ]);

        $this->notifyTenant(
            $reviewRequest,
            "Permintaan penghapusan ulasan pelanggan di studio Anda DITOLAK oleh ANDstudio.\n\n"
            ."Ulasan tetap tampil di profil studio.\n\n"
            .($note ? "Catatan: {$note}\n\n" : '')
            ."Terima kasih."
        );

        return back()->with('success', 'Permintaan ditolak. Ulasan tetap tampil di profil studio.');
    }

    /**
     * Kabari studio lewat WhatsApp.
     */
    private function notifyTenant(ReviewDeletionRequest $reviewRequest, string $message): void
    {
        $tenant = $reviewRequest->tenant;

        if (! $tenant || ! $tenant->phone) {
            return;
        }

        FonnteService::send($tenant->phone, "Halo {$tenant->name},\n\n".$message);
    }
}
