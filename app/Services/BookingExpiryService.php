<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Payment;

class BookingExpiryService
{
    /**
     * Batalkan booking yang melewati batas waktu pembayaran.
     *
     * - Slot dikembalikan ke "available" supaya bisa dipesan user lain
     * - Payment yang masih pending ditandai "rejected"
     * - Booking ditandai "canceled"
     *
     * @param  int|null  $tenantId   Batasi per tenant (null = semua)
     * @param  int|null  $packageId  Batasi per paket (null = semua)
     * @return int Jumlah booking yang dibatalkan
     */
    public static function releaseExpired(?int $tenantId = null, ?int $packageId = null): int
    {
        $query = Booking::where('booking_status', 'pending')
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<=', now());

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        if ($packageId !== null) {
            $query->where('package_id', $packageId);
        }

        $count = 0;

        foreach ($query->get() as $booking) {
            // Kembalikan slot ke available
            BookingSlot::where('id', $booking->booking_slot_id)
                ->where('status', 'reserved')
                ->update(['status' => 'available']);

            // Payment yang menggantung ditolak
            Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            $booking->update([
                'booking_status' => 'canceled',
                'payment_status' => 'unpaid',
                'paid_amount'    => $booking->verifiedTotal(),
            ]);

            $count++;
        }

        return $count;
    }
}
