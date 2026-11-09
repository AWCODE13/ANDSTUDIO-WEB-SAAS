<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\StudioReview;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Cari order berdasarkan kode, email, atau telepon.
     *
     * - Pencarian dengan kode booking menghasilkan tepat 1 order (kode unik)
     * - Pencarian dengan email/telepon menghasilkan daftar semua order
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:100',
        ]);

        // Catatan: $request->query adalah InputBag, gunakan input() untuk ambil nilainya
        $query = $request->input('query');

        // 1. Exact match kode booking (unik, 1 hasil)
        $byCode = Booking::withoutGlobalScopes()->with(['package', 'tenant', 'review'])->where('booking_code', $query)->first();

        if ($byCode) {
            return response()->json([
                'success' => true,
                'data' => [
                    'found'        => true,
                    'orders_count' => 1,
                    'orders'       => [$byCode->summary()],
                ],
            ]);
        }

        // 2. Pencarian via email / telepon (bisa banyak hasil)
        $bookings = Booking::withoutGlobalScopes()->with(['package', 'tenant', 'review'])
            ->where(function ($q) use ($query) {
                $q->where('customer_email', $query)
                    ->orWhere('customer_phone', $query);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        if ($bookings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan. Periksa kembali kode booking, email, atau nomor telepon Anda.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'found'        => true,
                'orders_count' => $bookings->count(),
                'orders'       => $bookings->map(fn ($b) => $b->summary())->values(),
            ],
        ]);
    }

    /**
     * Detail order + status pembayaran + snap token untuk pembayaran yang pending.
     */
    public function show(string $code)
    {
        $booking = Booking::withoutGlobalScopes()->with(['package', 'bookingSlot', 'payments'])->where('booking_code', $code)->firstOrFail();

        $total = $booking->totalTagihan();
        $paid = $booking->verifiedTotal();
        $remaining = max(0, $total - $paid);
        $isFullyPaid = $remaining <= 0;

        $pendingPayment = $booking->pendingPayment();

        // Snap token hanya relevan kalau ada pembayaran awal yang masih pending (DP / Lunas)
        $snapToken = null;
        if ($pendingPayment && ! $isFullyPaid) {
            $snapToken = MidtransService::snapTokenFor($pendingPayment, $booking);
        }

        $isCanceled = $booking->booking_status === 'canceled';
        $isExpired = $booking->isExpired();

        // Ulasan pelanggan (1 per booking)
        $review = StudioReview::where('booking_id', $booking->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'booking_code'    => $booking->booking_code,
                'package'         => $booking->package,
                'booking_date'    => $booking->booking_date?->format('Y-m-d'),
                'booking_date_label' => $booking->booking_date?->translatedFormat('d M Y'),
                'start_time'      => substr($booking->start_time, 0, 5),
                'end_time'        => substr($booking->end_time, 0, 5),
                'customer_name'   => $booking->customer_name,
                // Email & WA dimasking: halaman status pesanan bisa dibuka siapa pun
                // yang punya kode booking, jadi data kontak tidak boleh bocor.
                'customer_email'  => $this->maskEmail($booking->customer_email),
                'customer_phone'  => $this->maskPhone($booking->customer_phone),
                'notes'           => $booking->notes,
                'result_link'     => $booking->drive_archive_url ?? null,
                'booking_status'  => $booking->booking_status,
                'payment_status'  => $booking->payment_status,
                'total_amount'    => $total,
                'total_paid'      => $paid,
                'remaining'       => $remaining,
                'is_fully_paid'   => $isFullyPaid,
                'payments'        => $booking->payments,

                // Tombol bayar pembayaran awal (DP / Lunas)
                'pending_payment' => $pendingPayment ? [
                    'id'           => $pendingPayment->id,
                    'amount'       => (float) $pendingPayment->amount,
                    'payment_type' => $pendingPayment->payment_type,
                ] : null,
                'snap_token'      => $snapToken,
                'can_pay_initial' => $booking->canPayInitial(),

                // Tombol bayar pelunasan sisa
                'can_pay_balance' => $booking->canPayBalance(),

                // Batas waktu pembayaran
                'payment_deadline'    => $booking->payment_deadline?->toIso8601String(),
                'is_expired'          => $isExpired,
                'can_extend_deadline' => ! $isFullyPaid && ! $isCanceled && ! $isExpired && $pendingPayment !== null,

                // Rating: hanya boleh setelah booking selesai & belum pernah dirating
                'review'     => $review ? [
                    'id'            => $review->id,
                    'customer_name' => $review->maskedName(),
                    'rating'        => $review->rating,
                    'comment'       => $review->comment,
                    'created_at'    => $review->created_at?->toIso8601String(),
                ] : null,
                'can_review' => ($booking->booking_status === 'completed' || ($isFullyPaid && !empty($booking->drive_archive_url))) && ! $review,
            ],
        ]);
    }

    /**
     * Bayar pelunasan sisa via Midtrans (user FE).
     *
     * Syarat:
     * - Sudah ada minimal satu pembayaran terverifikasi (DP sudah dibayar)
     * - Belum ada pembayaran pending yang menggantung
     * - Sisa tagihan > 0
     */
    public function payBalance(string $code)
    {
        $booking = Booking::withoutGlobalScopes()->with('package')->where('booking_code', $code)->firstOrFail();

        if ($booking->booking_status === 'canceled') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibatalkan.',
            ], 422);
        }

        if ($booking->pendingPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada pembayaran yang belum diselesaikan. Selesaikan dulu pembayaran sebelumnya.',
            ], 422);
        }

        if (! $booking->hasVerifiedPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran awal (DP) belum lunas. Selesaikan DP terlebih dahulu.',
            ], 422);
        }

        $remaining = $booking->remainingAmount();

        if ($remaining <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan sudah lunas.',
            ], 422);
        }

        // Payment pelunasan (settlement), bukan "full"
        $payment = Payment::create([
            'tenant_id'             => $booking->tenant_id,
            'booking_id'            => $booking->id,
            'payment_type'          => 'settlement',
            'payment_method'        => 'midtrans',
            'amount'                => $remaining,
            'proof_attachment_path' => null,
            'status'                => 'pending',
        ]);

        $snapToken = MidtransService::snapTokenFor($payment, $booking);

        return response()->json([
            'success' => true,
            'data' => [
                'snap_token' => $snapToken,
                'amount'     => $remaining,
                'pay_url'    => route('api.order.pay-success', ['code' => $booking->booking_code]),
            ],
        ]);
    }

    /**
     * Perpanjang batas waktu pembayaran (maks 1x per booking pending).
     */
    public function extendDeadline(string $code)
    {
        $booking = Booking::withoutGlobalScopes()->where('booking_code', $code)->firstOrFail();

        if ($booking->booking_status === 'canceled') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibatalkan.',
            ], 422);
        }

        if ($booking->isFullyPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan sudah lunas.',
            ], 422);
        }

        if (! $booking->pendingPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pembayaran yang menunggu.',
            ], 422);
        }

        $booking->update([
            'payment_deadline' => now()->addMinutes(15),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_deadline' => $booking->payment_deadline->toIso8601String(),
                'message'          => 'Waktu pembayaran diperpanjang 15 menit.',
            ],
        ]);
    }

    /**
     * Email dimasking supaya tidak bocor lewat halaman status pesanan.
     */
    private function maskEmail(?string $email): ?string
    {
        if (! $email) {
            return $email;
        }

        [$user, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return $user;
        }

        $hidden = max(3, min(mb_strlen($user) - 1, 6));

        return mb_substr($user, 0, 1).str_repeat('*', $hidden).'@'.$domain;
    }

    /**
     * Nomor WA dimasking (4 digit depan + 2 digit belakang).
     */
    private function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return $phone;
        }

        $digits = (string) preg_replace('/\D/', '', $phone);

        if (mb_strlen($digits) <= 6) {
            return $digits;
        }

        return mb_substr($digits, 0, 4).'****'.mb_substr($digits, -2);
    }

    /**
     * Halaman pembayaran Midtrans (return snap token untuk Next.js).
     */
    public function paySuccess(string $code, Request $request)
    {
        $booking = Booking::withoutGlobalScopes()->with(['package', 'bookingSlot', 'payments'])->where('booking_code', $code)->firstOrFail();
        $snapToken = $request->query('snapToken');

        return response()->json([
            'success' => true,
            'data' => [
                'booking_code' => $booking->booking_code,
                'snap_token'   => $snapToken,
                'remaining'    => $booking->remainingAmount(),
                'redirect_url' => route('api.order.show', ['code' => $booking->booking_code]),
            ],
        ]);
    }
}
