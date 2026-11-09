<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->user->tenant;
    }

    /**
     * Parsing user agent menjadi nama browser yang readable.
     */
    public function parseUserAgent(): ?string
    {
        if (! $this->user_agent) {
            return null;
        }

        $ua = $this->user_agent;

        if (preg_match('/Edge\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome\/([\d.]+)/i', $ua, $m) && !preg_match('/Edge\/([\d.]+)/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari\/([\d.]+)/i', $ua, $m) && !preg_match('/Chrome\/([\d.]+)/i', $ua)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Firefox\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Mozilla Firefox';
        } else {
            $browser = 'Browser Lainnya';
        }

        if (preg_match('/Windows NT ([\d.]+)/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh.*Mac OS X ([\d_]+)/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $os = 'iOS';
        } else {
            $os = 'OS Lainnya';
        }

        return "{$browser} on {$os}";
    }

    /**
     * Mendeteksi tipe perangkat (desktop, mobile, tablet).
     */
    public function parseDeviceType(): ?string
    {
        if (! $this->user_agent) {
            return null;
        }

        $ua = $this->user_agent;

        if (preg_match('/iPad/i', $ua) || (preg_match('/Android/i', $ua) && !preg_match('/Mobile/i', $ua))) {
            return 'Tablet';
        }

        if (preg_match('/Mobile|Android|iPhone|iPod/i', $ua)) {
            return 'Mobile';
        }

        return 'Desktop';
    }
}
