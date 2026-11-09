<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\StudioReview;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Semua ulasan lintas studio.
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        $tenantId = $request->get('tenant');
        $status = $request->get('status');
        $rating = $request->get('rating');

        $reviews = StudioReview::withoutGlobalScopes()
            ->with(['tenant:id,name,slug', 'package:id,name'])
            ->when($query, fn ($q) => $q->where('customer_name', 'like', "%{$query}%"))
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($rating, fn ($q) => $q->where('rating', $rating))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('superadmin.reviews.index', compact('reviews', 'tenants', 'query', 'tenantId', 'status', 'rating'));
    }

    /**
     * Sembunyikan ulasan (moderasi langsung, tanpa request tenant).
     */
    public function hide(Request $request, StudioReview $review)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $review->update([
            'status'        => 'hidden',
            'hidden_at'     => now(),
            'hidden_by'     => Auth::id(),
            'hidden_reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Ulasan disembunyikan. Rating studio sudah dihitung ulang.');
    }

    /**
     * Kembalikan ulasan ke profil publik.
     */
    public function restore(StudioReview $review)
    {
        $review->update([
            'status'        => 'visible',
            'hidden_at'     => null,
            'hidden_by'     => null,
            'hidden_reason' => null,
        ]);

        return back()->with('success', 'Ulasan dikembalikan ke profil publik.');
    }
}
