<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingWANotificationService
{
    /**
     * Kirim WA konfirmasi pembayaran ke customer (DP / Lunas).
     */
    public static function sendCustomerConfirmation(Booking $booking): bool
    {
        $cleanPhone = FonnteService::normalizePhone((string) $booking->customer_phone);
        if (!$cleanPhone) {
            Log::warning("Customer phone invalid untuk booking #{$booking->id}");
            return false;
        }

        // Pastikan relasi termuat
        if (!$booking->relationLoaded('package')) {
            $booking->load('package');
        }
        if (!$booking->relationLoaded('tenant')) {
            $booking->load('tenant');
        }

        $message = self::formatCustomerConfirmationMessage($booking);

        try {
            $sent = FonnteService::send($cleanPhone, $message);
            if ($sent) {
                $booking->update(['customer_wa_sent_at' => now()]);
                Log::info("WA konfirmasi pembayaran booking #{$booking->id} ({$booking->payment_status}) terkirim ke customer {$cleanPhone}");
                return true;
            } else {
                Log::warning("Gagal kirim WA konfirmasi ke customer {$cleanPhone} untuk booking #{$booking->id}");
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Error saat kirim WA konfirmasi booking #{$booking->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim WA notifikasi ke semua admin tenant saat DP atau Pelunasan verified.
     */
    public static function sendPaymentNotification(Booking $booking): bool
    {
        $tenant = $booking->tenant;

        if (!$tenant || !$tenant->whatsapp_notification_enabled) {
            return false;
        }

        $admins = $tenant->users;
        if ($admins->isEmpty()) {
            return false;
        }

        // Pastikan relasi paket & tenant termuat
        if (!$booking->relationLoaded('package')) {
            $booking->load('package');
        }
        if (!$booking->relationLoaded('tenant')) {
            $booking->load('tenant');
        }

        $message = self::formatPaymentMessage($booking);
        $success = false;

        foreach ($admins as $admin) {
            if (!$admin->phone) {
                continue;
            }

            try {
                $sent = FonnteService::send($admin->phone, $message);
                if ($sent) {
                    $success = true;
                    Log::info("WA notifikasi payment booking #{$booking->id} ({$booking->payment_status}) terkirim ke {$admin->phone}");
                } else {
                    Log::warning("Gagal kirim WA payment ke {$admin->phone} untuk booking #{$booking->id}");
                }
            } catch (\Throwable $e) {
                Log::error("Error saat kirim WA payment booking #{$booking->id}: " . $e->getMessage());
            }
        }

        return $success;
    }

    /**
     * Alias backward-compatible.
     */
    public static function sendDPConfirmedNotification(Booking $booking): bool
    {
        return self::sendPaymentNotification($booking);
    }

    /**
     * Format pesan WA dinamis (DP atau Lunas).
     */
    private static function formatPaymentMessage(Booking $booking): string
    {
        $isPaid = $booking->payment_status === 'paid';
        $paidAmount = (float) $booking->paid_amount;
        $totalAmount = (float) $booking->total_amount;
        $sisaBayar = max(0, $totalAmount - $paidAmount);

        // Format tanggal & jam sinkron
        $tanggalStr = '-';
        if ($booking->booking_date) {
            $tanggalCarbon = $booking->booking_date instanceof Carbon
                ? $booking->booking_date
                : Carbon::parse($booking->booking_date);
            $tanggalStr = $tanggalCarbon->translatedFormat('d M Y');
        }

        $jamMulai = $booking->start_time ? substr((string) $booking->start_time, 0, 5) : null;
        $jamSelesai = $booking->end_time ? substr((string) $booking->end_time, 0, 5) : null;
        $jadwalStr = $tanggalStr;
        if ($jamMulai && $jamSelesai) {
            $jadwalStr .= " ({$jamMulai} - {$jamSelesai} WIB)";
        }

        // Generate Draf Sapaan Otomatis untuk link wa.me
        $cleanPhone = FonnteService::normalizePhone((string) $booking->customer_phone);
        $waLink = '-';
        if ($cleanPhone) {
            $greetingText = self::buildCustomerGreetingText($booking, $jadwalStr, $isPaid, $paidAmount);
            $waLink = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($greetingText);
        }

        // Catatan customer
        $catatan = !empty(trim((string) $booking->notes)) ? trim((string) $booking->notes) : '-';

        if ($isPaid) {
            $header = "🎉 *Pembayaran Booking LUNAS!*";
        } else {
            $percent = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100) : 0;
            $header = "✅ *DP Booking Dikonfirmasi!*";
        }

        $message = "{$header}\n\n";
        $message .= "📌 *Kode:* #{$booking->booking_code}\n";
        $message .= "👤 *Customer:* {$booking->customer_name}\n";
        $message .= "📱 *Kontak:* {$waLink}\n";
        $message .= "📦 *Paket:* " . ($booking->package?->name ?? 'Paket Foto') . "\n";
        $message .= "📅 *Jadwal:* {$jadwalStr}\n";
        $message .= "📝 *Catatan:* {$catatan}\n\n";

        $message .= "💰 *Rincian Pembayaran:*\n";
        if ($isPaid) {
            $message .= "• Total Lunas: Rp " . number_format($paidAmount, 0, ',', '.') . " (100%)\n";
            $message .= "• Sisa Tagihan: Rp 0\n";
            $message .= "• Status: Lunas ✓\n\n";
        } else {
            $message .= "• DP Diterima: Rp " . number_format($paidAmount, 0, ',', '.') . " ({$percent}%)\n";
            $message .= "• Sisa Tagihan: Rp " . number_format($sisaBayar, 0, ',', '.') . "\n";
            $message .= "• Total Tagihan: Rp " . number_format($totalAmount, 0, ',', '.') . "\n";
            $message .= "• Status: Dikonfirmasi (DP) ✓\n\n";
        }

        $message .= "Klik link kontak di atas untuk langsung membuka chat WhatsApp customer dengan pesan sapaan otomatis.";

        return $message;
    }

    /**
     * Format pesan konfirmasi pembayaran untuk dikirim ke customer.
     */
    private static function formatCustomerConfirmationMessage(Booking $booking): string
    {
        $isPaid = $booking->payment_status === 'paid';
        $paidAmount = (float) $booking->paid_amount;
        $totalAmount = (float) $booking->total_amount;
        $sisaBayar = max(0, $totalAmount - $paidAmount);

        // Format tanggal & jam
        $tanggalStr = '-';
        if ($booking->booking_date) {
            $tanggalCarbon = $booking->booking_date instanceof Carbon
                ? $booking->booking_date
                : Carbon::parse($booking->booking_date);
            $tanggalStr = $tanggalCarbon->translatedFormat('d M Y');
        }

        $jamMulai = $booking->start_time ? substr((string) $booking->start_time, 0, 5) : null;
        $jamSelesai = $booking->end_time ? substr((string) $booking->end_time, 0, 5) : null;
        $jadwalStr = $tanggalStr;
        if ($jamMulai && $jamSelesai) {
            $jadwalStr .= " ({$jamMulai} - {$jamSelesai} WIB)";
        }

        $namaStudio = $booking->tenant?->name ?? 'Studio Foto';
        $namaPaket = $booking->package?->name ?? 'Paket Foto';
        $kodeBooking = $booking->booking_code ?? '';
        $nominalDpStr = 'Rp ' . number_format($paidAmount, 0, ',', '.');

        // URL detail booking (public link — sesuaikan dengan route Anda)
        $bookingUrl = url("/bookings/{$booking->booking_code}");

        if ($isPaid) {
            $header = "Halo {$booking->customer_name}! 👋";
            $body = "Pembayaran Anda di *{$namaStudio}* telah *LUNAS*. Terima kasih banyak! ✨\n\n"
                  . "📋 *Detail Reservasi:*\n"
                  . "• Kode Booking: *#{$kodeBooking}*\n"
                  . "• Paket: *{$namaPaket}*\n"
                  . "• Jadwal: *{$jadwalStr}*\n"
                  . "• Status: *Lunas* (Rp " . number_format($totalAmount, 0, ',', '.') . ")\n\n"
                  . "Link detail pesanan Anda:\n"
                  . "{$bookingUrl}\n\n"
                  . "Sampai jumpa di studio ya kak! 🙏";
        } else {
            $header = "Halo {$booking->customer_name}! 👋";
            $body = "Pembayaran DP untuk booking Anda di *{$namaStudio}* telah berhasil diverifikasi.\n\n"
                  . "📋 *Detail Reservasi:*\n"
                  . "• Kode Booking: *#{$kodeBooking}*\n"
                  . "• Paket: *{$namaPaket}*\n"
                  . "• Jadwal: *{$jadwalStr}*\n"
                  . "• Lokasi: *{$namaStudio}*\n\n"
                  . "💰 *Status Pembayaran:*\n"
                  . "• DP Diterima: *{$nominalDpStr}*\n"
                  . "• Sisa Pelunasan: *Rp " . number_format($sisaBayar, 0, ',', '.') . "* (dibayar saat sesi foto)\n\n"
                  . "Link detail pesanan Anda:\n"
                  . "{$bookingUrl}\n\n"
                  . "Sampai jumpa di studio ya kak! Jika ada pertanyaan atau request khusus, silakan balas pesan ini. Terima kasih! 🙏";
        }

        return $header . "\n\n" . $body;
    }

    /**
     * Susun teks sapaan otomatis untuk dikirim admin ke customer.
     */
    private static function buildCustomerGreetingText(Booking $booking, string $jadwalStr, bool $isPaid, float $paidAmount): string
    {
        $namaStudio = $booking->tenant?->name ?? 'Studio Foto';
        $namaCustomer = $booking->customer_name ?? 'Kak';
        $namaPaket = $booking->package?->name ?? 'Paket Foto';
        $kodeBooking = $booking->booking_code ?? '';

        if ($isPaid) {
            return "Halo {$namaCustomer}, salam dari {$namaStudio}! 👋\n\n"
                 . "Terima kasih banyak, pembayaran untuk pemesanan paket *{$namaPaket}* dengan kode *#{$kodeBooking}* pada jadwal *{$jadwalStr}* sudah kami terima dan *LUNAS*. ✨\n\n"
                 . "Sampai jumpa di studio pada hari H! Jika ada referensi foto, konsep, atau request khusus, silakan infokan ke kami ya kak. 🙏";
        }

        $nominalDpStr = 'Rp ' . number_format($paidAmount, 0, ',', '.');
        return "Halo {$namaCustomer}, salam dari {$namaStudio}! 👋\n\n"
             . "Terima kasih sudah melakukan booking paket *{$namaPaket}* dengan kode *#{$kodeBooking}* pada jadwal *{$jadwalStr}*. DP sebesar *{$nominalDpStr}* sudah kami terima dan terkonfirmasi. ✨\n\n"
             . "Apakah ada yang ingin ditanyakan atau dikonsultasikan terkait persiapan fotonya kak? 🙏";
    }
}
