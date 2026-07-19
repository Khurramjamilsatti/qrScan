<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class QrCode extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'name', 'code', 'destination_url',
        'funnel_id', 'routing_rules', 'security', 'expires_at', 'max_scans', 'signing_secret',
        'foreground_color', 'background_color', 'logo_path', 'background_image_path',
        'size', 'error_correction', 'margin', 'dot_style', 'qr_shape', 'corner_style', 'frame_style', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'routing_rules' => 'array',
            'security' => 'array',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(QrFunnel::class, 'funnel_id');
    }

    public function customDomain(): BelongsTo
    {
        return $this->belongsTo(CustomDomain::class);
    }

    protected static function booted(): void
    {
        static::creating(function (QrCode $qrCode) {
            if (empty($qrCode->code)) {
                $qrCode->code = Str::random(8);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyticsEvents(): MorphMany
    {
        return $this->morphMany(AnalyticsEvent::class, 'eventable');
    }
}
