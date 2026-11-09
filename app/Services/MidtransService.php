<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    /**
     * Konfigurasi global Midtrans.
     */
    public static function config(): void
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Generate Snap Token untuk sebuah payment.
     *
     * Catatan: order_id di-generate ulang per percobaan (Midtrans menolak
     * order_id duplikat), TAPI record Payment yang sama tetap dipakai.
     * Ini mencegah user refresh halaman tidak menghasilkan data payment sampah.
     */
    public static function snapTokenFor(Payment $payment, Booking $booking): string
    {
        self::config();

        $orderId = 'PAY-' . $payment->id . '-' . strtoupper(Str::random(8));

        // Simpan order_id terbaru untuk audit & pencocokan webhook
        $payment->update(['midtrans_order_id' => $orderId]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'email' => $booking->customer_email,
                'phone' => $booking->customer_phone,
            ],
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Cari payment berdasarkan order_id dari Midtrans.
     *
     * Format order_id: PAY-{payment_id}-{random}
     */
    public static function findPaymentByOrderId(string $orderId): ?Payment
    {
        // 1. Coba parse payment id dari order_id (paling andal)
        $parts = explode('-', $orderId);
        if (isset($parts[1]) && is_numeric($parts[1])) {
            $payment = Payment::find((int) $parts[1]);
            if ($payment) {
                return $payment;
            }
        }

        // 2. Fallback: cari via kolom midtrans_order_id
        return Payment::where('midtrans_order_id', $orderId)->first();
    }
}
