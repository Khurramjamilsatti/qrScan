<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingFeature extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'description', 'items',
        'icon', 'color', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
