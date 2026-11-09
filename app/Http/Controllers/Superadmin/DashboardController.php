<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ReviewDeletionRequest;
use App\Models\StudioReview;
use App\Models\Tenant;

class DashboardController extends Controller
{
    /**
     * Ringkasan seluruh platform.
     */
    public function index()
    {
        $stats = [
            'tenants'          => Tenant::count(),
            'active_tenants'   => Tenant::where('subscription_status', 'active')->count(),
            'pro'              => Tenant::where('subscription_plan', 'pro')->count(),
            'bookings_today'   => Booking::whereDate('booking_date', today())->count(),
            'revenue'          => (float) Payment::where('status', 'verified')->sum('amount'),
            'reviews'          => StudioReview::withoutGlobalScopes()->count(),
            'pending_requests' => ReviewDeletionRequest::withoutGlobalScopes()->pending()->count(),
        ];

        $recentReviews = StudioReview::withoutGlobalScopes()
            ->with('tenant:id,name')
            ->latest()
            ->limit(5)
            ->get();

        $pendingRequests = ReviewDeletionRequest::withoutGlobalScopes()
            ->with(['tenant:id,name', 'review:id,customer_name,rating,comment'])
            ->pending()
            ->latest()
            ->limit(5)
            ->get();

        return view('superadmin.dashboard.index', compact('stats', 'recentReviews', 'pendingRequests'));
    }
}
