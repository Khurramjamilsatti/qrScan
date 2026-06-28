<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanToWinPlay extends Model
{
    protected $fillable = [
        'scan_to_win_campaign_id', 'ip_hash', 'won', 'prize_name',
    ];

    protected function casts(): array
    {
        return [
            'won' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ScanToWinCampaign::class, 'scan_to_win_campaign_id');
    }
}
