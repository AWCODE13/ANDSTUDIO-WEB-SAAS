<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePhoto extends Model
{
    use BelongsToTenant;
    protected $guarded = ['id'];
    protected $casts = ['sort_order' => 'integer'];
    protected $appends = ['url'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getUrlAttribute(): string
    {
        // URL absolut yang utuh. url() mengikuti root request aktif sehingga
        // otomatis ikut port (localhost:8001) maupun domain tunneling (ngrok).
        return url('storage/' . ltrim((string) $this->photo_url, '/'));
    }
}
