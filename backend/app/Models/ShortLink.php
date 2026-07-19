<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ShortLink extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'title', 'description', 'slug', 'destination_url',
        'funnel_id', 'routing_rules', 'security', 'max_scans', 'signing_secret',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'expires_at', 'is_active',
        'foreground_color', 'background_color', 'logo_path', 'background_image_path',
        'qr_size', 'error_correction', 'margin',
        'qr_shape', 'dot_style', 'corner_style', 'frame_style',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'routing_rules' => 'array',
            'security' => 'array',
        ];
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(QrFunnel::class, 'funnel_id');
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

    public function fullDestinationUrl(): string
    {
        $url = $this->destination_url;
        $params = array_filter([
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_term' => $this->utm_term,
            'utm_content' => $this->utm_content,
        ]);

        if (empty($params)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query($params);
    }
}
