<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Pastikan user yang login punya role superadmin.
     *
     * Tanpa middleware ini, siapa pun yang punya akun tenant bisa
     * membuka halaman /superadmin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya untuk Superadmin.');
        }

        return $next($request);
    }
}
