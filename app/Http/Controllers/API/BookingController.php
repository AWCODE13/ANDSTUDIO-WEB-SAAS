<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Package;
use App\Models\BookingSlot;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Lama waktu (menit) user punya untuk menyelesaikan pembayaran awal.
     */
    public const PAYMENT_WINDOW_MINUTES = 15;

    /**
     * Buat booking baru (pembayaran via Midtrans).
     */
    public function store(Request $request, ?string $slug = null, $packageId = null)
    {
        $packageId = (int) $packageId;
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        // Jika request datang via subdomain (middleware DetectTenant), gunakan tenant dari context
        $subdomainTenant = $request->attributes->get('tenant');
        if ($subdomainTenant instanceof \App\Models\Tenant) {
            $tenant = $subdomainTenant;
        }
        $package = Package::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('id', $packageId)->firstOrFail();

        $validated = $request->validate([
            'booking_slot_id' => 'required|exists:booking_slots,id',
            'customer_name'   => 'required|string|max:100',
            'customer_email'  => 'required|email|max:100',
            'customer_phone'  => 'required|string|max:20',
            'payment_option'  => 'required|in:dp,full',
            'notes'           => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $tenant, $package) {
            $slot = BookingSlot::withoutGlobalScopes()->where('id', $validated['booking_slot_id'])
                ->where('tenant_id', $tenant->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, slot waktu ini baru saja dipesan.',
                ], 422);
            }

            $bookingCode = 'FS-' . strtoupper(Str::random(4)) . '-' . date('dmy');
            $totalPrice = (float) $package->price;
            $isFull = $validated['payment_option'] === 'full';
            $amountToPay = $isFull ? $totalPrice : round($totalPrice * 0.30, 2);

            $booking = Booking::create([
                'tenant_id'        => $tenant->id,
                'package_id'       => $package->id,
                'booking_slot_id'  => $slot->id,
                'booking_code'     => $bookingCode,
                'customer_name'    => $validated['customer_name'],
                'customer_email'   => $validated['customer_email'],
                'customer_phone'   => $validated['customer_phone'],
                'booking_date'     => $slot->date,
                'start_time'       => $slot->start_time,
                'end_time'         => $slot->end_time,

                'total_amount'     => $totalPrice,
                'total_price'      => $totalPrice,

                // BELUM ada pembayaran terverifikasi saat booking dibuat
                'paid_amount'      => 0,
                'payment_status'   => 'unpaid',
                'booking_status'   => 'pending',

                // Batas waktu pembayaran awal (auto-cancel)
                'payment_deadline' => now()->addMinutes(self::PAYMENT_WINDOW_MINUTES),

                'notes'            => $validated['notes'] ?? null,
            ]);

            // Payment awal: DP 30% atau Lunas 100%
            Payment::create([
                'tenant_id'             => $booking->tenant_id,
                'booking_id'            => $booking->id,
                'payment_type'          => $isFull ? 'full' : 'dp',
                'payment_method'        => 'midtrans',
                'amount'                => $amountToPay,
                'proof_attachment_path' => null,
                'status'                => 'pending',
            ]);

            $slot->update(['status' => 'reserved']);

            // Notify Tenant Admins
            try {
                $tenantUsers = \App\Models\User::where('tenant_id', $tenant->id)->get();
                foreach ($tenantUsers as $admin) {
                    $admin->notify(new \App\Notifications\NewBookingNotification($booking));
                }
            } catch (\Throwable $e) {
                // Jangan gagalkan booking jika notif gagal
                \Illuminate\Support\Facades\Log::warning('Failed sending NewBookingNotification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_code'     => $booking->booking_code,
                    'booking_status'   => $booking->booking_status,
                    'payment_status'   => $booking->payment_status,
                    'amount_to_pay'    => $amountToPay,
                    'total_amount'     => $totalPrice,
                    'payment_deadline' => $booking->payment_deadline?->toIso8601String(),
                    'detail_url'       => route('api.order.show', ['code' => $booking->booking_code]),
                ],
            ]);
        });
    }

    /**
     * Detail booking + generate Snap Token Midtrans.
     */
    public function success(string $code)
    {
        $booking = Booking::withoutGlobalScopes()->with(['package', 'payments'])->where('booking_code', $code)->firstOrFail();

        $snapToken = null;
        $pendingPayment = $booking->pendingPayment();

        if ($pendingPayment) {
            $snapToken = MidtransService::snapTokenFor($pendingPayment, $booking);
        }

        $total = $booking->totalTagihan();
        $paid = $booking->verifiedTotal();
        $remaining = max(0, $total - $paid);

        return response()->json([
            'success' => true,
            'data' => [
                'booking_code'    => $booking->booking_code,
                'package'         => $booking->package,
                'booking_date'    => $booking->booking_date?->format('Y-m-d'),
                'booking_date_label' => $booking->booking_date?->translatedFormat('d M Y'),
                'start_time'      => substr($booking->start_time, 0, 5),
                'customer_name'   => $booking->customer_name,
                'customer_email'  => $booking->customer_email,
                'customer_phone'  => $booking->customer_phone,
                'booking_status'  => $booking->booking_status,
                'payment_status'  => $booking->payment_status,
                'total_amount'    => $total,
                'total_paid'      => $paid,
                'remaining'       => $remaining,
                'is_fully_paid'   => $remaining <= 0,
                'snap_token'      => $snapToken,
                'payments'        => $booking->payments,
                'payment_deadline'=> $booking->payment_deadline?->toIso8601String(),
            ],
        ]);
    }
}
