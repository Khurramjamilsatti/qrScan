<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DigitalEvent extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'template', 'event_type',
        'title', 'subtitle', 'hosts', 'event_date', 'event_end_date',
        'venue_name', 'dress_code', 'cover_image_path', 'theme_color',
        'content', 'is_active', 'qr_shape', 'dot_style', 'corner_style', 'frame_style',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'event_date' => 'datetime',
            'event_end_date' => 'datetime',
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

    public function analyticsEvents(): MorphMany
    {
        return $this->morphMany(AnalyticsEvent::class, 'eventable');
    }
}
