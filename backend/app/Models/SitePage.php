<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    protected $fillable = [
        'locale', 'slug', 'title', 'intro', 'content', 'contact_info', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'contact_info' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
