<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan import ini

class BookingSlot extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    // Relasi ke tabel bookings (sudah ada sebelumnya)
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // TAMBAHKAN INI: Relasi ke tabel packages
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}