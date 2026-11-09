<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Tenant;
use App\Notifications\BookingStatusChangedNotification;
use App\Services\BookingWANotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingObserver
{
    /**
     * Mencegah pembuatan booking baru jika kuota max_bookings_per_month sudah habis.
     *
     * Kuota tidak berlaku untuk: Partner, Trial aktif, Plan Pro aktif.
     * Hanya berlaku untuk Plan Free.
     */
    public function creating(Booking $booking): void
    {
        // Muat tenant tanpa global scope (soft delete, tenant scope)
        $tenant = Tenant::withoutGlobalScopes()->find($booking->tenant_id);

        if (! $tenant instanceof Tenant) {
            return;
        }

        // Kuota tidak berlaku untuk partner, trial aktif, atau plan pro
        if ($tenant->isPartner() || $tenant->isTrialActive() || $tenant->isPaidPlan()) {
            return;
        }

        $quota = $tenant->quota();
        $maxBookings = $quota['max_bookings_per_month'] ?? null;

        if ($maxBookings === null) {
            return; // unlimited
        }

        $currentMonthStart = now()->startOfMonth();
        $currentCount = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $currentMonthStart)
            ->count();

        if ($currentCount >= $maxBookings) {
            throw ValidationException::withMessages([
                'bookings' => 'Kuota booking per bulan sudah habis. Maksimal ' . $maxBookings . ' booking untuk plan Free. Upgrade ke Pro untuk booking tidak terbatas.',
            ]);
        }
    }

    public function updated(Booking $booking): void
    {
        // 1. Notifikasi internal saat booking_status berubah
        if ($booking->isDirty('booking_status')) {
            $oldStatus = $booking->getOriginal('booking_status');
            $newStatus = $booking->booking_status;

            try {
                $tenantUsers = \App\Models\User::where('tenant_id', $booking->tenant_id)->get();
                foreach ($tenantUsers as $admin) {
                    $admin->notify(new BookingStatusChangedNotification($booking, $oldStatus, $newStatus));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed sending BookingStatusChangedNotification: ' . $e->getMessage());
            }
        }

        // 2. Notifikasi WhatsApp ke Admin Tenant saat payment status berubah jadi DP (partially_paid) atau Lunas (paid)
        if ($booking->isDirty('payment_status')) {
            $oldPayment = $booking->getOriginal('payment_status');
            $newPayment = $booking->payment_status;

            if (in_array($newPayment, ['partially_paid', 'paid'], true) && $oldPayment !== $newPayment) {
                // Kirim notif ke admin
                try {
                    BookingWANotificationService::sendPaymentNotification($booking);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed sending WA payment notification via Observer: ' . $e->getMessage());
                }

                // Kirim konfirmasi otomatis ke customer
                try {
                    BookingWANotificationService::sendCustomerConfirmation($booking);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed sending WA customer confirmation via Observer: ' . $e->getMessage());
                }
            }
        }
    }
}
