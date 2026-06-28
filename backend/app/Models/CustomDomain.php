<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomDomain extends Model
{
    protected $fillable = [
        'user_id', 'domain', 'verification_token',
        'is_verified', 'is_primary', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CustomDomain $domain) {
            if (empty($domain->verification_token)) {
                $domain->verification_token = 'qrscan-verify='.Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function baseUrl(): string
    {
        return 'https://'.$this->domain;
    }
}
