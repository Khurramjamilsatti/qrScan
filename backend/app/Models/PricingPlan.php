<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'billing_period',
        'features', 'limits', 'is_popular', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'limits' => 'array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
