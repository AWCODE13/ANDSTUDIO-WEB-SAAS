<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'trial_ends_at'   => 'datetime',
        'next_payment_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'is_partner'      => 'boolean',
        'whatsapp_notification_enabled' => 'boolean',
    ];

    protected $appends = ['logo_url', 'available_balance'];

    /**
     * URL absolut logo studio.
     *
     * url() mengikuti root request aktif sehingga otomatis ikut port
     * (localhost:8001) maupun domain tunneling (ngrok), sama seperti
     * PackagePhoto::getUrlAttribute().
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return url('storage/' . ltrim((string) $this->logo_path, '/'));
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingSlots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    // ------------------------------------------------------------------
    // Helper Langganan
    // ------------------------------------------------------------------

    /**
     * Partner = akun Hasan (gratis selamanya, tidak pernah ditagih).
     */
    public function isPartner(): bool
    {
        return (bool) $this->is_partner;
    }

    /**
     * Masih dalam masa uji coba?
     */
    public function isTrialActive(): bool
    {
        if ($this->isPartner()) {
            return false;
        }

        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Berapa hari lagi trial berakhir (0 = tidak ada trial).
     */
    public function trialDaysLeft(): int
    {
        if (! $this->isTrialActive()) {
            return 0;
        }

        return (int) ceil(now()->diffInHours($this->trial_ends_at) / 24);
    }

    /**
     * Paket berbayar yang aktif?
     */
    public function isPaidPlan(): bool
    {
        if ($this->isPartner()) {
            return false;
        }

        if ($this->subscription_plan === 'pro' && $this->subscription_status === 'active') {
            return $this->plan_expires_at === null || $this->plan_expires_at->isFuture();
        }

        return false;
    }

    /**
     * Langganan telat bayar (harus read-only)?
     */
    public function isPastDue(): bool
    {
        if ($this->isPartner() || $this->isTrialActive() || $this->isPaidPlan()) {
            return false;
        }

        return $this->subscription_status === 'past_due';
    }

    /**
     * Langganan berhenti (harus diblokir)?
     */
    public function isCanceled(): bool
    {
        if ($this->isPartner() || $this->isTrialActive() || $this->isPaidPlan()) {
            return false;
        }

        return $this->subscription_status === 'canceled';
    }

    /**
     * Label singkat untuk badge di UI.
     */
    public function subscriptionLabel(): array
    {
        if ($this->isPartner()) {
            return ['text' => 'Partner', 'color' => 'bg-violet-100 text-violet-700'];
        }

        if ($this->isTrialActive()) {
            return ['text' => 'Trial ' . $this->trialDaysLeft() . ' hari', 'color' => 'bg-amber-100 text-amber-700'];
        }

        if ($this->isPaidPlan()) {
            return ['text' => 'Plan Pro', 'color' => 'bg-emerald-100 text-emerald-700'];
        }

        if ($this->isPastDue()) {
            return ['text' => 'Tagihan Terlambat', 'color' => 'bg-rose-100 text-rose-700'];
        }

        if ($this->isCanceled()) {
            return ['text' => 'Nonaktif', 'color' => 'bg-slate-200 text-slate-600'];
        }

        return ['text' => 'Plan Free', 'color' => 'bg-slate-100 text-slate-600'];
    }

    /**
     * BatasKuota per plan.
     */
    public function quota(): array
    {
        if ($this->isPartner() || $this->isPaidPlan() || $this->isTrialActive()) {
            return ['max_packages' => null, 'max_bookings_per_month' => null, 'max_staff' => null];
        }

        return ['max_packages' => 3, 'max_bookings_per_month' => 50, 'max_staff' => 1];
    }

    /**
     * Cek apakah tenant boleh memakai sebuah fitur.
     */
    public function canUse(string $feature): bool
    {
        if ($this->isPartner() || $this->isPaidPlan() || $this->isTrialActive()) {
            return true;
        }

        return ! in_array($feature, ['wa_blast', 'custom_domain', 'unlimited_bookings'], true);
    }

    /**
     * Saldo tersedia yang bisa ditarik (Total verified payments dari booking non-cancelled dikurangi penarikan pending & completed).
     */
    public function getAvailableBalanceAttribute(): float
    {
        // Total pembayaran terverifikasi dari booking milik tenant ini (kecuali yang canceled)
        $totalEarned = \App\Models\Payment::withoutGlobalScopes()
            ->whereHas('booking', function ($q) {
                $q->withoutGlobalScopes()
                    ->where('tenant_id', $this->id)
                    ->where('booking_status', '!=', 'canceled');
            })
            ->where('status', 'verified')
            ->sum('amount');

        // Total withdrawal pending dan completed
        $totalWithdrawn = $this->withdrawals()
            ->withoutGlobalScopes()
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');

        return max(0.0, (float) ($totalEarned - $totalWithdrawn));
    }

    public function getTotalWithdrawnAttribute(): float
    {
        return (float) $this->withdrawals()
            ->withoutGlobalScopes()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function hasBankConfigured(): bool
    {
        return !empty($this->bank_name) && !empty($this->account_number) && !empty($this->account_name);
    }

    /**
     * Daftar fitur plan untuk ditampilkan di halaman billing.
     */
    public static function planFeatures(string $plan): array
    {
        $pro = [
            ['label' => 'Jumlah paket foto', 'free' => '3 paket', 'pro' => 'Unlimited'],
            ['label' => 'Booking per bulan', 'free' => '50 booking', 'pro' => 'Unlimited'],
            ['label' => 'Kirim link foto via WA', 'free' => true, 'pro' => true],
            ['label' => 'Laporan keuangan', 'free' => true, 'pro' => true],
            ['label' => 'WhatsApp blast', 'free' => false, 'pro' => true],
            ['label' => 'Custom domain', 'free' => false, 'pro' => true],
            ['label' => 'Multi-staff', 'free' => '1 admin', 'pro' => 'Unlimited'],
        ];

        return $pro;
    }
}
