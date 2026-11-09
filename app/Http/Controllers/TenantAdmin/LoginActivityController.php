<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Superadmin melihat semua aktivitas login, tenant admin hanya miliknya
        $query = LoginActivity::with('user')
            ->when($user->isSuperAdmin(), fn ($q) => $q)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('login_at', 'desc');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter rentang tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(20)->withQueryString();

        $stats = [
            'total'      => LoginActivity::when(! $user->isSuperAdmin(), fn ($q) => $q->where('user_id', $user->id))->count(),
            'success'    => LoginActivity::when(! $user->isSuperAdmin(), fn ($q) => $q->where('user_id', $user->id))->where('status', 'success')->count(),
            'failed'     => LoginActivity::when(! $user->isSuperAdmin(), fn ($q) => $q->where('user_id', $user->id))->where('status', 'failed')->count(),
            'last_login' => LoginActivity::when(! $user->isSuperAdmin(), fn ($q) => $q->where('user_id', $user->id))->where('status', 'success')->orderBy('login_at', 'desc')->value('login_at'),
        ];

        return view('tenant.login-activity', compact('activities', 'stats'));
    }
}
