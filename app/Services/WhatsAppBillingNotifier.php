<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantInvoice;

class WhatsAppBillingNotifier
{
    /**
     * Kirim pengingat bahwa masa trial akan segera berakhir (H-3).
     */
    public static function sendTrialEndingReminder(Tenant $tenant): bool
    {
        if (! $tenant->phone) {
            return false;
        }

        $daysLeft = $tenant->trial_days_left();
        $message = "Halo *{$tenant->name}*! 👋\n\n"
            . "Masa uji coba gratis (Trial) Anda di ANDstudio tersisa *{$daysLeft} hari lagi*.\n"
            . "Agar operasional studio tidak terganggu, yuk upgrade ke *Plan Pro* sekarang.\n\n"
            . "Kunjungi dashboard untuk upgrade:\n"
            . url('/billing');

        return FonnteService::send($tenant->phone, $message);
    }

    /**
     * Kirim pemberitahuan masa trial telah habis dan akun diubah ke status past_due.
     */
    public static function sendTrialExpired(Tenant $tenant): bool
    {
        if (! $tenant->phone) {
            return false;
        }

        $message = "Halo *{$tenant->name}*,\n\n"
            . "Masa uji coba gratis 14 hari Anda telah berakhir.\n"
            . "Fitur operasional studio Anda saat ini beralih ke mode terbatas (Read-Only).\n\n"
            . "Segera aktifkan Plan Pro untuk membuka kembali seluruh fitur unlimited:\n"
            . url('/billing');

        return FonnteService::send($tenant->phone, $message);
    }

    /**
     * Kirim invoice bulanan baru ke tenant Pro.
     */
    public static function sendInvoiceCreated(TenantInvoice $invoice): bool
    {
        $tenant = $invoice->tenant;
        if (! $tenant || ! $tenant->phone) {
            return false;
        }

        $amountFormatted = 'Rp ' . number_format($invoice->amount, 0, ',', '.');
        $message = "Halo *{$tenant->name}*,\n\n"
            . "Tagihan langganan ANDstudio untuk periode *{$invoice->period_month}* telah terbit.\n\n"
            . "📄 No. Invoice: *{$invoice->invoice_number}*\n"
            . "💰 Total: *{$amountFormatted}*\n"
            . "⏳ Jatuh Tempo: 7 Hari dari sekarang\n\n"
            . "Selesaikan pembayaran dengan mudah via Midtrans melalui tautan berikut:\n"
            . url('/billing');

        return FonnteService::send($tenant->phone, $message);
    }

    /**
     * Kirim peringatan bahwa invoice sudah menunggak dan status langganan jadi past_due.
     */
    public static function sendSubscriptionPastDue(Tenant $tenant, TenantInvoice $invoice): bool
    {
        if (! $tenant->phone) {
            return false;
        }

        $amountFormatted = 'Rp ' . number_format($invoice->amount, 0, ',', '.');
        $message = "⚠️ PENTING: Tagihan Tertunggak\n\n"
            . "Halo *{$tenant->name}*,\n"
            . "Tagihan *{$invoice->invoice_number}* sebesar *{$amountFormatted}* telah melewati batas waktu pembayaran.\n\n"
            . "Akses dashboard studio Anda saat ini dibatasi. Mohon segera selesaikan tagihan untuk memulihkan akses penuh:\n"
            . url('/billing');

        return FonnteService::send($tenant->phone, $message);
    }
}
