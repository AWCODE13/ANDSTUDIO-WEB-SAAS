<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    /**
     * Casting kolom tanggal/numerik.
     */
    protected $casts = [
        'booking_date'      => 'date',
        'payment_deadline' => 'datetime',
        'total_amount'     => 'float',
        'total_price'      => 'float',
        'paid_amount'      => 'float',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function bookingSlot(): BelongsTo
    {
        return $this->belongsTo(BookingSlot::class);
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_photographer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StudioReview::class);
    }

    /**
     * Total tagihan booking (fallback ke harga paket bila kolom kosong).
     * CATATAN: pakai method, bukan accessor, karena "total_price" sudah jadi kolom DB.
     */
    public function totalTagihan(): float
    {
        if ($this->total_price > 0) {
            return (float) $this->total_price;
        }

        return (float) ($this->package->price ?? 0);
    }

    /**
     * Total yang sudah terverifikasi (sudah dibayar).
     */
    public function verifiedTotal(): float
    {
        return (float) $this->payments()->where('status', 'verified')->sum('amount');
    }

    /**
     * Sisa tagihan yang belum dibayar.
     */
    public function remainingAmount(): float
    {
        return max(0, $this->totalTagihan() - $this->verifiedTotal());
    }

    /**
     * Apakah sudah lunas.
     */
    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0;
    }

    /**
     * Payment pending yang masih aktif (untuk bayar DP/lunas).
     */
    public function pendingPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', 'pending')
            ->where('payment_method', 'midtrans')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Sudah ada pembayaran terverifikasi (DP sudah dibayar).
     */
    public function hasVerifiedPayment(): bool
    {
        return $this->payments()->where('status', 'verified')->exists();
    }

    /**
     * Sudah lewat batas waktu pembayaran?
     */
    public function isExpired(): bool
    {
        return $this->payment_deadline !== null && $this->payment_deadline->isPast();
    }

    /**
     * Boleh bayar pembayaran awal (DP / Lunas)?
     */
    public function canPayInitial(): bool
    {
        return $this->pendingPayment() !== null
            && ! $this->isFullyPaid()
            && $this->booking_status !== 'canceled'
            && ! $this->isExpired();
    }

    /**
     * Boleh bayar pelunasan sisa?
     * Syarat: DP/sudah ada pembayaran terverifikasi & tidak ada payment pending.
     */
    public function canPayBalance(): bool
    {
        return $this->hasVerifiedPayment()
            && ! $this->isFullyPaid()
            && $this->pendingPayment() === null
            && $this->booking_status !== 'canceled';
    }

    /**
     * Ringkasan singkat untuk daftar pesanan.
     */
    public function summary(): array
    {
        $total = $this->totalTagihan();
        $paid = $this->verifiedTotal();
        $remaining = max(0, $total - $paid);
        $pending = $this->pendingPayment();

        return [
            'booking_code'     => $this->booking_code,
            'tenant_name'      => $this->tenant?->name ?? 'Studio',
            'studio_name'      => $this->tenant?->name ?? 'Studio',
            'package_name'     => $this->package->name ?? 'Paket Foto',
            'package_category' => $this->package->category ?? null,
            'booking_date'     => $this->booking_date?->format('Y-m-d'),
            'booking_date_label' => $this->booking_date?->translatedFormat('d M Y'),
            'start_time'       => substr((string) $this->start_time, 0, 5),
            'booking_status'   => $this->booking_status,
            'payment_status'   => $this->payment_status,
            'total_amount'     => $total,
            'total_paid'       => $paid,
            'remaining'        => $remaining,
            'is_fully_paid'    => $remaining <= 0,
            'is_expired'       => $this->isExpired(),
            'can_pay_initial'  => $this->canPayInitial(),
            'can_pay_balance'  => $this->canPayBalance(),
            'pending_amount'   => $pending ? (float) $pending->amount : null,
            'pending_type'     => $pending?->payment_type,
            'result_link'      => $this->drive_archive_url ?? null,
            'has_reviewed'     => $this->relationLoaded('review') ? ($this->review !== null) : $this->review()->exists(),
        ];
    }

    /**
     * Hitung ulang paid_amount & payment_status berdasarkan payment terverifikasi.
     */
    public function recalculatePayment(): void
    {
        $paid = $this->verifiedTotal();
        $total = $this->totalTagihan();

        $status = 'unpaid';
        if ($paid >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        }

        $this->paid_amount = $paid;
        $this->payment_status = $status;

        if ($this->booking_status !== 'canceled' && $this->booking_status !== 'completed') {
            $this->booking_status = $paid > 0 ? 'confirmed' : 'pending';
        }

        $this->save();
    }
}
