<?php

use App\Console\Commands\ExpirePendingBookings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: jalankan setiap menit
Schedule::command('bookings:expire')->everyMinute();

// Schedule: proses langganan setiap hari jam 00:15
Schedule::command('subscriptions:process')->dailyAt('00:15');
