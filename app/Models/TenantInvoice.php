<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoice extends Model
{
    use BelongsToTenant;
    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
        'amount'       => 'float',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Generate nomor invoice unik, contoh: INV-202610-0001
     */
    public static function generateNumber(int $tenantId, string $periodMonth): string
    {
        $count = self::where('tenant_id', $tenantId)
            ->where('period_month', $periodMonth)
            ->count() + 1;

        return sprintf('INV-%s-%04d', str_replace('-', '', $periodMonth), $count);
    }
}
