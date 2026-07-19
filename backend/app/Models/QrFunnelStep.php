<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrFunnelStep extends Model
{
    protected $fillable = [
        'funnel_id', 'sort_order', 'step_type', 'title', 'description',
        'target_type', 'target_slug', 'target_url', 'cta_text',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(QrFunnel::class, 'funnel_id');
    }
}
