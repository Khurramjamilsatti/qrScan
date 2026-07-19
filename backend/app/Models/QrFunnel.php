<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QrFunnel extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'goal', 'description',
        'theme_color', 'is_active', 'conversion_count', 'entry_count',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (QrFunnel $funnel) {
            if (empty($funnel->slug)) {
                $funnel->slug = Str::slug($funnel->name).'-'.Str::random(4);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(QrFunnelStep::class, 'funnel_id')->orderBy('sort_order');
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'funnel_id');
    }

    public function shortLinks(): HasMany
    {
        return $this->hasMany(ShortLink::class, 'funnel_id');
    }
}
