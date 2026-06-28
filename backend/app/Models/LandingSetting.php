<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function getValue(string $key, mixed $default = []): mixed
    {
        return static::where('key', $key)->first()?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value): self
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
