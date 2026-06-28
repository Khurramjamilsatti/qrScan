<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ScanToWinCampaign extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'name', 'description', 'template',
        'starts_at', 'ends_at', 'max_plays_per_day', 'win_message', 'lose_message',
        'terms', 'prizes', 'theme_color', 'logo_path', 'background_image_path',
        'qr_shape', 'dot_style', 'corner_style', 'frame_style', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'prizes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customDomain(): BelongsTo
    {
        return $this->belongsTo(CustomDomain::class);
    }

    public function plays(): HasMany
    {
        return $this->hasMany(ScanToWinPlay::class);
    }

    public function analyticsEvents(): MorphMany
    {
        return $this->morphMany(AnalyticsEvent::class, 'eventable');
    }
}
