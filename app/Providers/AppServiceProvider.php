<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Package;
use App\Observers\BookingObserver;
use App\Observers\PackageObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register Observers
        Booking::observe(BookingObserver::class);
        Package::observe(PackageObserver::class);

        // Paksa URL menjadi HTTPS hanya jika diaktifkan lewat .env (FORCE_HTTPS=true).
        // Ini dibutuhkan saat tunneling via ngrok atau saat deploy di belakang SSL terminator.
        // Di development lokal (http://localhost) harus FALSE, jika tidak:
        // - CSS/JS tidak termuat (browser mencoba https ke server http)
        // - Form login & redirect mengarah ke https dan gagal
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
