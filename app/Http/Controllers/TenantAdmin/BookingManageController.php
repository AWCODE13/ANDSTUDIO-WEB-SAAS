<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Package;
use App\Models\Payment;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingManageController extends Controller
{
    // 1. Tampilkan daftar reservasi
    public function index()
    {
        $bookings = Booking::with(['package', 'bookingSlot', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenant.bookings.index', compact('bookings'));
    }

    // 1b. Form tambah reservasi manual (offline/walk-in/WhatsApp)
    public function create()
    {
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        return view('tenant.bookings.create', compact('packages'));
    }

    // 1c. Ambil slot jadwal tersedia untuk paket & tanggal tertentu (AJAX)
    public function availableSlots(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'date'       => 'required|date|after_or_equal:today',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Release slot yang expired (payment deadline lewat) agar kembali available
        \App\Services\BookingExpiryService::releaseExpired($tenantId, $request->package_id);

        $slots = BookingSlot::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('package_id', $request->package_id)
            ->where('date', $request->date)
            ->where('status', 'available')
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time'])
            ->map(function ($s) {
                return [
                    'id'         => $s->id,
                    'start_time' => substr((string) $s->start_time, 0, 5),
                    'end_time'   => substr((string) $s->end_time, 0, 5),
                    'label'      => substr((string) $s->start_time, 0, 5) . ' - ' . substr((string) $s->end_time, 0, 5),
                ];
            });

        // Deteksi slot yang terpakai oleh booking manual (tanpa booking_slot_id)
        // pada tanggal & paket yang sama, agar tidak ditampilkan sebagai available.
        $manualBookedTimes = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('package_id', $request->package_id)
            ->where('booking_date', $request->date)
            ->whereIn('booking_status', ['pending', 'confirmed', 'completed'])
            ->whereNull('booking_slot_id')
            ->pluck('start_time');

        $slots = $slots->filter(function ($slot) use ($manualBookedTimes) {
            return ! $manualBookedTimes->contains(substr((string) $slot['start_time'], 0, 5));
        })->values();

        return response()->json($slots);
    }

    // 1d. Simpan reservasi manual
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name'      => 'required|string|max:100',
            'client_whatsapp'  => 'required|string|max:25',
            'client_email'     => 'nullable|email|max:100',
            'package_id'       => 'required|exists:packages,id',
            'booking_date'     => 'required|date',
            'booking_slot_id'  => 'nullable|integer',
            'manual_time'      => 'nullable|date_format:H:i',
            'payment_status'   => 'required|in:paid,dp_paid',
            'payment_method'   => 'nullable|in:cash,manual_transfer',
            'dp_amount'        => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        if (! $request->filled('booking_slot_id') && ! $request->filled('manual_time')) {
            throw ValidationException::withMessages([
                'manual_time' => 'Pilih salah satu slot waktu, atau isi jam manual (HH:MM).',
            ]);
        }

        $tenantId = Auth::user()->tenant_id;
        $package = Package::where('id', $validated['package_id'])->firstOrFail();

        try {
            $booking = DB::transaction(function () use ($validated, $tenantId, $package, $request) {
            // 1. Tentukan slot & jam
            $slot = null;
            $startTime = null;
            $endTime = null;

            if ($request->filled('booking_slot_id')) {
                $slot = BookingSlot::where('id', $validated['booking_slot_id'])
                    ->where('tenant_id', $tenantId)
                    ->lockForUpdate()
                    ->first();

                if (! $slot || $slot->status !== 'available') {
                    throw new \RuntimeException('Slot waktu yang dipilih sudah tidak tersedia.');
                }

                $startTime = $slot->start_time;
                $endTime = $slot->end_time;
                $slot->update(['status' => 'reserved']);
            } else {
                // Jam manual (HH:MM)
                $startTime = Carbon::parse($validated['manual_time'])->format('H:i:s');
                $duration = $package->duration_minutes ?: 60;
                $endTime = Carbon::parse($startTime)->addMinutes($duration)->format('H:i:s');

                // Pakai slot yang sudah ada bila jamnya sama, agar tidak dobel.
                $slot = BookingSlot::where('tenant_id', $tenantId)
                    ->where('package_id', $package->id)
                    ->where('date', $validated['booking_date'])
                    ->where('start_time', $startTime)
                    ->lockForUpdate()
                    ->first();

                if ($slot) {
                    if ($slot->status !== 'available') {
                        throw new \RuntimeException('Jadwal jam tersebut sudah terisi / tidak tersedia.');
                    }
                    $slot->update(['status' => 'reserved']);
                } else {
                    $slot = BookingSlot::create([
                        'tenant_id'  => $tenantId,
                        'package_id' => $package->id,
                        'date'       => $validated['booking_date'],
                        'start_time' => $startTime,
                        'end_time'   => $endTime,
                        'status'     => 'reserved',
                    ]);
                }
            }

            // 2. Generate Booking Code Unik Awalan MNL-
            do {
                $bookingCode = 'MNL-' . strtoupper(Str::random(4)) . '-' . date('dmy');
            } while (Booking::withoutGlobalScopes()->where('booking_code', $bookingCode)->exists());

            // 3. Hitung Pembayaran
            $totalPrice = (float) $package->price;
            $isFull = $validated['payment_status'] === 'paid';
            $paymentMethod = $validated['payment_method'] ?? 'cash';

            if ($isFull) {
                $paidAmount = $totalPrice;
                $paymentType = 'full';
                $dbPaymentStatus = 'paid';
            } else {
                $minDp = (float) ($package->down_payment_min ?: round($totalPrice * 0.30, 2));
                $inputDp = (float) $request->input('dp_amount');
                $paidAmount = ($inputDp > 0) ? $inputDp : $minDp;

                if ($paidAmount >= $totalPrice) {
                    $paidAmount = $totalPrice;
                    $paymentType = 'full';
                    $dbPaymentStatus = 'paid';
                } else {
                    $paymentType = 'dp';
                    $dbPaymentStatus = 'partially_paid';
                }
            }

            $email = ($validated['client_email'] ?? null) ?: (Str::slug($validated['client_name']) . '.' . Str::random(3) . '@manual.local');

            // 4. Simpan Data Booking
            $newBooking = Booking::create([
                'tenant_id'        => $tenantId,
                'package_id'       => $package->id,
                'booking_slot_id'  => $slot?->id,
                'booking_code'     => $bookingCode,
                'customer_name'    => $validated['client_name'],
                'customer_phone'   => $validated['client_whatsapp'],
                'customer_email'   => $email,
                'booking_date'     => $validated['booking_date'],
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'total_amount'     => $totalPrice,
                'total_price'      => (int) $totalPrice,
                'paid_amount'      => $paidAmount,
                'payment_status'   => $dbPaymentStatus,
                'booking_status'   => 'confirmed', // Reservasi offline langsung confirmed
                'notes'            => $validated['notes'] ?? null,
            ]);

            // 5. Catat Payment Terverifikasi (Masuk ke Saldo/Laporan Keuangan)
            Payment::create([
                'tenant_id'      => $tenantId,
                'booking_id'     => $newBooking->id,
                'payment_type'   => $paymentType,
                'payment_method' => $paymentMethod,
                'amount'         => $paidAmount,
                'status'         => 'verified',
                'paid_at'        => now(),
                'verified_at'    => now(),
                'verified_by'    => Auth::id(),
            ]);

            return $newBooking;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['booking_slot_id' => $e->getMessage()]);
        }

        return redirect()->route('tenant.bookings.index')
            ->with('success', "Reservasi manual #{$booking->booking_code} ({$booking->customer_name}) berhasil dibuat dan jadwal telah diblokir.");
    }

    // 2. Verifikasi pembayaran DP manual (Jika transfer manual, bukan Midtrans)
    public function verifyPayment(Booking $booking)
    {
        $payment = Payment::where('booking_id', $booking->id)->where('status', 'pending')->first();
        
        if ($payment) {
            $payment->update([
                'status'      => 'verified',
                'paid_at'     => now(),
                'verified_at' => now(),
            ]);

            // Hitung ulang paid_amount & payment_status booking
            $booking->recalculatePayment();
            
            return back()->with('success', 'Pembayaran berhasil diverifikasi!');
        }
        
        return back()->withErrors(['Data pembayaran tidak ditemukan.']);
    }

    // 3. Pelunasan Manual (Cash / Transfer Manual di Studio)
    public function payBalanceCash(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,transfer_manual',
        ]);

        $remaining = $booking->remainingAmount();

        if ($remaining <= 0) {
            return back()->with('error', 'Tagihan sudah lunas.');
        }

        // Pelunasan cash dicatat sebagai "settlement" (bukan "full")
        Payment::create([
            'tenant_id'  => $booking->tenant_id,
            'booking_id' => $booking->id,
            'amount'     => $remaining,

            'payment_type'   => 'settlement',
            'payment_method' => $request->payment_method,
            'status'         => 'verified',
            'paid_at'        => now(),
            'verified_at'    => now(),
        ]);

        // Hitung ulang paid_amount & payment_status booking
        $booking->recalculatePayment();

        return back()->with('success', 'Pelunasan berhasil! Form pengiriman link Google Drive kini terbuka.');
    }

    public function updateArchive(Request $request, Booking $booking)
    {
        $request->validate(['archive_link' => 'required|url']);

        // Guard: foto hanya boleh dikirim jika tagihan sudah lunas
        if (! $booking->isFullyPaid()) {
            return back()->with('error', 'Foto belum bisa dikirim karena tagihan belum lunas.');
        }

        // 1. Simpan link secara eksplisit (Pasti Masuk DB!)
        $booking->archive_link = $request->archive_link;
        $booking->save();

        // 2. Format nomor HP
        $phone = FonnteService::normalizePhone($booking->customer_phone);

        // 3. Format Pesan
        $orderUrl = rtrim((string) env('APP_PUBLIC_URL', ''), '/').'/pesanan/'.$booking->booking_code;

        $message = "Halo *{$booking->customer_name}*,\n\n"
                 . "Terima kasih telah mempercayakan momen Anda bersama kami!\n"
                 . "Berikut adalah link Google Drive untuk mengunduh seluruh hasil foto Anda:\n\n"
                 . "🔗 {$booking->archive_link}\n\n"
                 . "⭐ Sudah selesai? Beri rating studio kami di:\n"
                 . "🔗 {$orderUrl}\n\n"
                 . "Semoga suka dengan hasilnya! 📸";

        // 4. Kirim WA
        if (! FonnteService::send($phone, $message)) {
            return back()
                ->with('wa_error_id', $booking->id)
                ->with('wa_error_msg', 'Gagal: Nomor tidak valid / tidak terkirim.');
        }

        // 5. JIKA WA SUKSES, BARU UBAH STATUS JADI SELESAI
        $booking->booking_status = 'completed';
        $booking->save();

        return back()->with('success', 'File sudah dikirim ke WhatsApp klien!');
    }

    // 4. Resend konfirmasi pembayaran ke customer (fallback jika WA otomatis gagal)
    public function resendCustomerConfirmation(Booking $booking)
    {
        if (!in_array($booking->payment_status, ['partially_paid', 'paid'])) {
            return back()->with('error', 'Tidak bisa kirim konfirmasi — pembayaran belum diterima.');
        }

        try {
            $sent = \App\Services\BookingWANotificationService::sendCustomerConfirmation($booking);
            if ($sent) {
                return back()->with('success', 'Konfirmasi pembayaran berhasil dikirim ke WhatsApp customer!');
            } else {
                return back()->with('error', 'Gagal mengirim konfirmasi — nomor tidak valid atau Fonnte offline.');
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}