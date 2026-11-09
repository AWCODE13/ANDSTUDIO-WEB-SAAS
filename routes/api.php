<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use App\Http\Controllers\API\StudioController;
use App\Http\Controllers\API\StudioReviewController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\TenantBillingController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\TenantAdmin\BookingManageController;
use App\Http\Controllers\TenantAdmin\DashboardController;

// ============================================
// PUBLIC API - Tanpa login
// ============================================

// CSRF Cookie for SPA (Sanctum-compatible & API endpoint)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});
Route::get('/api/csrf-cookie', function () {
    return response()->noContent();
});

// Daftar studio baru (trial 14 hari) - dibatasi 5x per menit
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('api.register');

// Daftar studio aktif (untuk halaman beranda)
Route::get('/studios', [StudioController::class, 'index'])->name('api.studios.index');

// Statistik platform publik (untuk halaman beranda)
Route::get('/stats', [StudioController::class, 'stats'])->name('api.stats');

// Katalog studio & paket — via slug (path-based) maupun via subdomain
Route::get('/studio/{slug}', [StudioController::class, 'show'])->name('api.studio.show');
Route::get('/studio', [StudioController::class, 'show'])->name('api.studio.show-by-subdomain');

// Detail paket — via slug maupun via subdomain
Route::get('/studio/{slug}/package/{packageId}', [StudioController::class, 'package'])->name('api.studio.package');
Route::get('/studio/package/{packageId}', [StudioController::class, 'package'])->name('api.studio.package-by-subdomain');

// Ulasan & rating studio — via slug maupun via subdomain
Route::get('/studio/{slug}/reviews', [StudioReviewController::class, 'index'])->name('api.studio.reviews');
Route::get('/studio/reviews', [StudioReviewController::class, 'index'])->name('api.studio.reviews-by-subdomain');
Route::post('/reviews', [StudioReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.review.store');

// Buat booking — via slug maupun via subdomain
Route::post('/studio/{slug}/package/{packageId}/book', [BookingController::class, 'store'])->name('api.booking.store');
Route::post('/studio/package/{packageId}/book', [BookingController::class, 'store'])->name('api.booking.store-by-subdomain');

// Detail booking + snap token (untuk pembayaran DP atau sisa)
Route::get('/booking/success/{code}', [BookingController::class, 'success'])->name('api.booking.success');

// Cari order (form pencarian)
Route::get('/pesanan', [OrderController::class, 'lookup'])->name('api.order.lookup');

// Detail order
Route::get('/pesanan/{code}', [OrderController::class, 'show'])->name('api.order.show');

// Bayar sisa via Midtrans (user FE)
Route::post('/pesanan/{code}/bayar-sisa', [OrderController::class, 'payBalance'])->name('api.order.pay-balance');

// Perpanjang batas waktu pembayaran
Route::post('/pesanan/{code}/perpanjang', [OrderController::class, 'extendDeadline'])->name('api.order.extend');

// Halaman snap Midtrans untuk pelunasan sisa
Route::get('/pesanan/{code}/bayar-sisa/success', [OrderController::class, 'paySuccess'])->name('api.order.pay-success');

// Webhook Midtrans
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'callback'])->name('api.midtrans.callback');

// ============================================
// TENANT ADMIN API - Butuh auth
// ============================================
Route::middleware(['auth'])->prefix('tenant')->group(function () {
    Route::get('/bookings', [BookingManageController::class, 'index'])->name('api.tenant.bookings.index');
    Route::patch('/payments/{payment}/verify', [DashboardController::class, 'verifyPayment'])->name('api.tenant.payments.verify');
    Route::post('/bookings/{booking}/pay-cash', [BookingManageController::class, 'payBalanceCash'])->name('api.tenant.bookings.pay-cash');
    Route::patch('/bookings/{booking}/archive', [BookingManageController::class, 'updateArchive'])->name('api.tenant.bookings.archive');

    // Billing & Subscription
    Route::get('/billing', [TenantBillingController::class, 'show'])->name('api.tenant.billing.show');
    Route::post('/billing/checkout', [TenantBillingController::class, 'checkout'])->name('api.tenant.billing.checkout');
    Route::post('/billing/cancel/{invoice}', [TenantBillingController::class, 'cancel'])->name('api.tenant.billing.cancel');
});
