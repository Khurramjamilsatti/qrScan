<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DigitalMenu extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'name', 'description',
        'logo_path', 'background_image_path', 'theme_color', 'currency',
        'location', 'phone', 'hours', 'sections', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
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
