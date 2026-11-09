<?php

// Nonaktifkan pencetakan deprecation notice PHP 8.5 ke layar browser
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // ... baris konfigurasi bawaan ke bawah biarkan tetap sama
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CSRF protection enabled for all API mutating routes.
        // Exception: only external webhook (no browser session).
        $middleware->validateCsrfTokens(except: [
            '/midtrans/callback',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ApplyUserPreferences::class,
            \App\Http\Middleware\DetectTenant::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\DetectTenant::class,
        ]);

        // API butuh session agar auto-login (setelah register) & tenant admin API bisa jalan
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // Wajib untuk SPA CSRF: set cookie XSRF-TOKEN (GET) & validasi
            // header X-XSRF-TOKEN pada POST/PUT/PATCH/DELETE.
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

    
