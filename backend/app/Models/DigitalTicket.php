<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DigitalTicket extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'event_name', 'event_date',
        'event_time', 'venue', 'holder_name', 'holder_email', 'ticket_type',
        'seat_section', 'seat_row', 'seat_number', 'order_id', 'barcode',
        'template', 'terms', 'valid_from', 'valid_until', 'status',
        'theme_color', 'logo_path', 'background_image_path',
        'qr_shape', 'dot_style', 'corner_style', 'frame_style', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
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
