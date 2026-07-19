<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'accessable_type', 'accessable_id', 'ip_hash', 'access_type', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
