<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BusinessCard extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'template', 'full_name', 'job_title', 'company',
        'bio', 'address', 'tagline', 'email', 'phone', 'website',
        'photo_path', 'background_image_path', 'logo_path', 'social_links',
        'theme_color', 'qr_shape', 'dot_style', 'corner_style', 'frame_style', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
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
