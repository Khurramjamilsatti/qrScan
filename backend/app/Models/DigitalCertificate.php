<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class DigitalCertificate extends Model
{
    protected $fillable = [
        'user_id', 'custom_domain_id', 'slug', 'certificate_id', 'template',
        'title', 'recipient_name', 'recipient_email', 'award_title', 'issuer_name',
        'description', 'completion_date', 'issue_date', 'expiry_date', 'status',
        'theme_color', 'logo_path', 'seal_path', 'instructor_signature_path',
        'organization_signature_path', 'background_image_path', 'pdf_path',
        'settings', 'qr_shape', 'dot_style', 'corner_style', 'frame_style', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'completion_date' => 'date',
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DigitalCertificate $cert) {
            if (empty($cert->certificate_id)) {
                $cert->certificate_id = static::generateCertificateId();
            }
            if (empty($cert->slug)) {
                $cert->slug = Str::slug($cert->recipient_name.'-'.Str::random(4));
            }
            if (empty($cert->issue_date)) {
                $cert->issue_date = now()->toDateString();
            }
        });
    }

    public static function generateCertificateId(): string
    {
        $year = now()->format('Y');
        $prefix = "CERT-{$year}-";

        do {
            $suffix = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $id = $prefix.$suffix;
        } while (static::where('certificate_id', $id)->exists());

        return $id;
    }

    public function isValid(): bool
    {
        if ($this->status === 'revoked') {
            return false;
        }

        if ($this->expiry_date && now()->gt($this->expiry_date)) {
            return false;
        }

        return true;
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
