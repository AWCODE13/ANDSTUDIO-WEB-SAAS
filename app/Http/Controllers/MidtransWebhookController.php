<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\TenantInvoice;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    /**
     * Callback notifikasi dari Midtrans.
     */
    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');

        // 1. Validasi keamanan: pastikan yang mengirim ini benar-benar Midtrans
        $hashed = hash(
            'sha512',
            $request->order_id . $request->status_code . $request->gross_amount . $serverKey
        );

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Signature tidak valid'], 403);
        }

        $status = $request->transaction_status;

        // 2. Hanya tangani transaksi yang sukses / berhasil
        if (! in_array($status, ['capture', 'settlement'], true)) {
            if (in_array($status, ['deny', 'cancel', 'expire', 'pending'], true)) {
                // Tandai payment ditolak agar tidak menggantung
                $payment = MidtransService::findPaymentByOrderId((string) $request->order_id);
                if ($payment && $payment->status === 'pending' && $status !== 'pending') {
                    $payment->update(['status' => 'rejected']);
                }
            }

            return response()->json(['message' => 'Callback diterima']);
        }

        // 3. Order berawalan INV- = tagihan langganan (bukan pembayaran booking)
        if (str_starts_with((string) $request->order_id, 'INV-')) {
            return $this->handleSubscriptionInvoice((string) $request->order_id);
        }

        // 4. Temukan payment berdasarkan order_id
        $payment = MidtransService::findPaymentByOrderId((string) $request->order_id);

        if (! $payment) {
            return response()->json(['message' => 'Payment tidak ditemukan'], 404);
        }

        // 4. Idempoten: sudah verified tidak diproses lagi
        if ($payment->status !== 'verified') {
            $payment->update([
                'status'      => 'verified',
                'paid_at'     => now(),
                'verified_at' => now(),
            ]);

            // 5. Hitung ulang status booking (paid_amount & payment_status)
            $booking = Booking::with('package')->find($payment->booking_id);

            if ($booking) {
                // recalculatePayment() men-trigger BookingObserver untuk kirim notif WA (DP maupun Lunas)
                $booking->recalculatePayment();
            }
        }

        return response()->json(['message' => 'Callback diterima']);
    }

    /**
     * Handle callback untuk tagihan langganan (order_id berawalan INV-).
     */
    private function handleSubscriptionInvoice(string $orderId): \Illuminate\Http\JsonResponse
    {
        $invoice = TenantInvoice::where('midtrans_order_id', $orderId)->first();

        // Fallback: parse id dari format INV-{invoice_id}-{random}
        if (! $invoice) {
            $parts = explode('-', $orderId);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $invoice = TenantInvoice::find((int) $parts[1]);
            }
        }

        if (! $invoice) {
            return response()->json(['message' => 'Invoice tidak ditemukan'], 404);
        }

        // Idempoten
        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice sudah lunas']);
        }

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        $tenant = $invoice->tenant;

        if ($tenant) {
            $tenant->update([
                'subscription_plan'   => 'pro',
                'subscription_status' => 'active',
                'trial_ends_at'       => null,
                'next_payment_at'     => now()->addMonth(),
                'plan_expires_at'     => now()->addMonth(),
            ]);
        }

        return response()->json(['message' => 'Tagihan langganan berhasil diproses']);
    }
}
