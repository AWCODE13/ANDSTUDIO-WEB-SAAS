<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ApplyUserPreferences
{
    /**
     * Set locale & tema dari preferensi user login.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = config('app.locale');
        $theme = 'dark';
        $country = 'ID';

        if (Auth::check()) {
            $user = Auth::user();
            $locale = $user->locale ?? config('app.locale');
            $theme = $user->theme ?? 'dark';
            $country = $user->country ?? 'ID';
        }

        App::setLocale($locale);

        view()->share('theme', $theme);
        view()->share('userLocale', $locale);
        view()->share('userCountry', $country);

        return $next($request);
    }
}
