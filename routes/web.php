<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\TenantAdmin\DashboardController;
use App\Http\Controllers\TenantAdmin\PackageController;
use App\Http\Controllers\TenantAdmin\PhotoController;
use App\Http\Controllers\TenantAdmin\BookingSlotController;
use App\Http\Controllers\TenantAdmin\ExpenseController;
use App\Http\Controllers\TenantAdmin\BookingManageController;
use App\Http\Controllers\TenantAdmin\CustomerController;
use App\Http\Controllers\TenantAdmin\BillingController;
use App\Http\Controllers\TenantAdmin\WithdrawalController as TenantWithdrawalController;
use App\Http\Controllers\TenantAdmin\ProfileController;
use App\Http\Controllers\TenantAdmin\LoginActivityController;
use App\Http\Controllers\TenantAdmin\ReviewController as TenantReviewController;
use App\Http\Controllers\TenantAdmin\SubscriptionGate;
use App\Http\Controllers\Superadmin\TenantController as SuperadminTenantController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\ReviewController as SuperadminReviewController;
use App\Http\Controllers\Superadmin\ReviewRequestController as SuperadminReviewRequestController;
use App\Http\Controllers\Superadmin\WithdrawalController as SuperadminWithdrawalController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Middleware\EnsureSuperAdmin;

// CSRF Cookie endpoint for SPA (Sanctum convention)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});

// 1. Landing Page Platform
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rute Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 2.1 Registrasi Studio Baru (Trial 14 Hari)
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// 2.2 Lupa / Reset Kata Sandi
Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// 3. Portal Publik Klien
// CATATAN: Halaman publik (katalog, booking, riwayat pesanan) dibangun di
// frontend Next.js. Admin diarahkan ke sana lewat link "Lihat Web Publik".
// Route Blade lama (/studio/{slug}) sudah dihapus agar tidak ada halaman mati.

// Notifications (Shared Auth)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
});

// 4. Modul Tenant Admin (Studio Area)
Route::prefix('app')->middleware(['auth', SubscriptionGate::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

    // Katalog Paket
    Route::resource('packages', PackageController::class)->names('tenant.packages');
    Route::post('packages/{package}/photos', [PhotoController::class, 'store'])->name('tenant.packages.photos.store');
    Route::delete('photos/{photo}', [PhotoController::class, 'destroy'])->name('tenant.photos.destroy');
    Route::post('packages/{package}/photos/reorder', [PhotoController::class, 'reorder'])->name('tenant.packages.photos.reorder');

    // Kalender & Jadwal
    Route::get('/booking-slots', [BookingSlotController::class, 'index'])->name('tenant.booking-slots.index');
    Route::post('/booking-slots/generate', [BookingSlotController::class, 'generate'])->name('tenant.booking-slots.generate');
    Route::delete('/booking-slots/{bookingSlot}', [BookingSlotController::class, 'destroy'])->name('tenant.booking-slots.destroy');
    Route::patch('/booking-slots/{bookingSlot}/status', [BookingSlotController::class, 'updateStatus'])->name('tenant.booking-slots.update-status');
    Route::delete('/booking-slots/date/{date}/package/{packageId}', [BookingSlotController::class, 'destroyByDate'])->name('tenant.booking-slots.destroy-date');
    Route::get('/booking-slots/check-available', [BookingSlotController::class, 'checkAvailable'])->name('tenant.booking-slots.check-available');

    // Daftar Reservasi
    Route::get('/bookings', [BookingManageController::class, 'index'])->name('tenant.bookings.index');
    Route::get('/bookings/create', [BookingManageController::class, 'create'])->name('tenant.bookings.create');
    Route::post('/bookings', [BookingManageController::class, 'store'])->name('tenant.bookings.store');
    Route::get('/bookings/available-slots', [BookingManageController::class, 'availableSlots'])->name('tenant.bookings.available-slots');
    Route::patch('/bookings/{booking}/verify', [BookingManageController::class, 'verifyPayment'])->name('tenant.bookings.verify-payment');
    Route::post('/bookings/{booking}/pay-cash', [BookingManageController::class, 'payBalanceCash'])->name('tenant.bookings.pay-cash');
    Route::patch('/bookings/{booking}/archive', [BookingManageController::class, 'updateArchive'])->name('tenant.bookings.archive');
    Route::post('/bookings/{booking}/resend-confirmation', [BookingManageController::class, 'resendCustomerConfirmation'])->name('tenant.bookings.resend-confirmation');
    // Simpan link GDrive + status dari dashboard (tanpa kirim WA)
    Route::patch('/bookings/{booking}/archive-link', [DashboardController::class, 'updateBookingArchive'])->name('tenant.bookings.archive-link');

    // Verifikasi Pembayaran Manual
    Route::patch('/payments/{payment}/verify', [DashboardController::class, 'verifyPayment'])->name('tenant.payments.verify');

    // Laporan Keuangan
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy'])->names('tenant.expenses');

    // Plan & Tagihan
    Route::get('/billing', [BillingController::class, 'index'])->name('tenant.billing.index');
    Route::match(['get', 'post'], '/billing/checkout', [BillingController::class, 'checkout'])->name('tenant.billing.checkout');
    Route::delete('/billing/invoice/{invoice}', [BillingController::class, 'cancelInvoice'])->name('tenant.billing.cancel');

    // Profil Studio & Akun Admin
    Route::get('/profile', [ProfileController::class, 'edit'])->name('tenant.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('tenant.profile.update');
    Route::put('/profile/account', [ProfileController::class, 'updateAccount'])->name('tenant.profile.account');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('tenant.profile.password');

    // Pengaturan Tampilan & Bahasa
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'show'])->name('tenant.settings');
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('tenant.settings.update');
    Route::put('/settings/bank', [\App\Http\Controllers\SettingsController::class, 'bank'])->name('tenant.settings.bank');

    // Penarikan Saldo (Withdrawals)
    Route::get('/withdrawals', [TenantWithdrawalController::class, 'index'])->name('tenant.withdrawals.index');
    Route::post('/withdrawals', [TenantWithdrawalController::class, 'store'])->name('tenant.withdrawals.store');

    // Data & Riwayat Pelanggan
    Route::get('/pelanggan', [CustomerController::class, 'index'])->name('tenant.customers.index');

    // Ulasan Pelanggan
    Route::get('/ulasan', [TenantReviewController::class, 'index'])->name('tenant.reviews.index');
    Route::post('/ulasan/{review}/hapus', [TenantReviewController::class, 'requestDeletion'])->name('tenant.reviews.request-deletion');

    // Riwayat Login (Aktivitas Masuk)
    Route::get('/login-activity', [LoginActivityController::class, 'index'])->name('tenant.login-activity');

    // Analytics & Laporan (Tenant)
    Route::get('/analytics', [\App\Http\Controllers\TenantAdmin\AnalyticsController::class, 'index'])->name('tenant.analytics');
    Route::post('/analytics/export', [\App\Http\Controllers\TenantAdmin\AnalyticsController::class, 'export'])->name('tenant.analytics.export');
});

// 5. Webhook Midtrans (tidak perlu auth)
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'callback']);

// 6. Modul Superadmin Platform Andcode
Route::prefix('superadmin')->middleware(['auth', EnsureSuperAdmin::class])->group(function () {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('superadmin.dashboard');

    // A. Daftar semua studio
    Route::get('/tenants', [SuperadminTenantController::class, 'index'])->name('superadmin.tenants.index');

    // C. Buat studio manual
    Route::get('/tenants/create', [SuperadminTenantController::class, 'create'])->name('superadmin.tenants.create');
    Route::post('/tenants', [SuperadminTenantController::class, 'store'])->name('superadmin.tenants.store');

    // B. Detail & kelola studio
    Route::get('/tenants/{id}', [SuperadminTenantController::class, 'show'])->name('superadmin.tenants.show');
    Route::put('/tenants/{id}', [SuperadminTenantController::class, 'update'])->name('superadmin.tenants.update');
    Route::put('/tenants/{id}/subscription', [SuperadminTenantController::class, 'updateSubscription'])->name('superadmin.tenants.subscription');
    Route::post('/tenants/{id}/extend-trial', [SuperadminTenantController::class, 'extendTrial'])->name('superadmin.tenants.extend-trial');
    Route::delete('/tenants/{id}', [SuperadminTenantController::class, 'destroy'])->name('superadmin.tenants.destroy');

    // D. Ulasan pelanggan (moderasi)
    Route::get('/ulasan', [SuperadminReviewController::class, 'index'])->name('superadmin.reviews.index');
    Route::put('/ulasan/{review}/sembunyikan', [SuperadminReviewController::class, 'hide'])->name('superadmin.reviews.hide');
    Route::put('/ulasan/{review}/tampilkan', [SuperadminReviewController::class, 'restore'])->name('superadmin.reviews.restore');

    // E. Permintaan hapus ulasan dari studio
    Route::get('/permintaan-hapus', [SuperadminReviewRequestController::class, 'index'])->name('superadmin.review-requests.index');
    Route::put('/permintaan-hapus/{reviewRequest}/setujui', [SuperadminReviewRequestController::class, 'approve'])->name('superadmin.review-requests.approve');
    Route::put('/permintaan-hapus/{reviewRequest}/tolak', [SuperadminReviewRequestController::class, 'reject'])->name('superadmin.review-requests.reject');

    // F. Pengaturan Tampilan & Bahasa (Superadmin)
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'show'])->name('superadmin.settings');
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('superadmin.settings.update');

    // G. Analytics & Laporan
    Route::get('/analytics', [\App\Http\Controllers\Superadmin\AnalyticsController::class, 'index'])->name('superadmin.analytics');
    Route::post('/analytics/export', [\App\Http\Controllers\Superadmin\AnalyticsController::class, 'export'])->name('superadmin.analytics.export');

    // H. Manajemen Penarikan Saldo (Withdrawals)
    Route::get('/withdrawals', [SuperadminWithdrawalController::class, 'index'])->name('superadmin.withdrawals.index');
    Route::get('/withdrawals/{withdrawal}', [SuperadminWithdrawalController::class, 'show'])->name('superadmin.withdrawals.show');
    Route::post('/withdrawals/{withdrawal}/complete', [SuperadminWithdrawalController::class, 'complete'])->name('superadmin.withdrawals.complete');
    Route::post('/withdrawals/{withdrawal}/reject', [SuperadminWithdrawalController::class, 'reject'])->name('superadmin.withdrawals.reject');
});
