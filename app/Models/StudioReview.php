<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudioReview extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    protected $casts = [
        'rating'    => 'integer',
        'hidden_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function deletionRequests(): HasMany
    {
        // FK eksplisit: nama kolomnya review_id (bukan studio_review_id default Eloquent)
        return $this->hasMany(ReviewDeletionRequest::class, 'review_id');
    }

    /**
     * Hanya ulasan yang masih terlihat publik.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'visible');
    }

    public function isHidden(): bool
    {
        return $this->status === 'hidden';
    }

    /**
     * Request penghapusan yang masih menunggu keputusan super admin.
     */
    public function pendingDeletionRequest(): ?ReviewDeletionRequest
    {
        return $this->deletionRequests()
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    /**
     * Rata-rata rating + jumlah ulasan untuk banyak studio sekaligus (1 query).
     *
     * Hanya ulasan visible yang dihitung: ulasan yang disembunyikan tidak
     * boleh memengaruhi skor rating studio.
     *
     * Global scope tenant di-bypass: data ini untuk endpoint publik dan tetap
     * harus lengkap walau request bawa cookie admin yang sedang login.
     *
     * @return array<int, array{rating: float|null, reviews_count: int}>
     */
    public static function aggregateByTenant(): array
    {
        return static::withoutGlobalScopes()
            ->visible()
            ->selectRaw('tenant_id, COUNT(*) as reviews_count, AVG(rating) as avg_rating')
            ->groupBy('tenant_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->tenant_id => [
                    'rating'        => (int) $row->reviews_count > 0 ? round((float) $row->avg_rating, 1) : null,
                    'reviews_count' => (int) $row->reviews_count,
                ],
            ])
            ->all();
    }

    /**
     * Nama pelanggan yang ditampilkan ke publik (dimasking).
     * "Ahmad Fauzi" -> "Ahmad F."
     */
    public function maskedName(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->customer_name)) ?: [];

        if ($parts === []) {
            return 'Pelanggan';
        }

        $first = array_shift($parts);

        if ($parts === []) {
            return $first;
        }

        return $first.' '.mb_substr((string) end($parts), 0, 1).'.';
    }
}
