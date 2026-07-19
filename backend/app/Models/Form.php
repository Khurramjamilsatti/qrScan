<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Form extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'title', 'description',
        'fields', 'settings', 'theme_color', 'background_color',
        'header_image_path', 'logo_path', 'background_image_path',
        'qr_shape', 'dot_style', 'corner_style', 'frame_style',
        'is_active', 'view_count', 'submission_count',
        'closes_at', 'max_submissions', 'max_submissions_per_respondent',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'closes_at' => 'datetime',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function analyticsEvents(): MorphMany
    {
        return $this->morphMany(AnalyticsEvent::class, 'eventable');
    }

    public function isAcceptingSubmissions(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->closes_at && now()->gt($this->closes_at)) {
            return false;
        }

        if ($this->max_submissions > 0 && $this->submission_count >= $this->max_submissions) {
            return false;
        }

        return true;
    }

    public function inputFields(): array
    {
        return collect($this->fields ?? [])
            ->filter(fn ($f) => ! in_array($f['type'] ?? '', ['section_header', 'description_text'], true))
            ->values()
            ->all();
    }
}
