<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

class LocalizedContent
{
    public const SUPPORTED = ['en', 'ar'];

    public static function locale(): string
    {
        $locale = App::getLocale();

        return in_array($locale, self::SUPPORTED, true) ? $locale : 'en';
    }

    /** Resolve bilingual { en, ar } maps and nested structures. */
    public static function resolve(mixed $value, ?string $locale = null): mixed
    {
        $locale ??= self::locale();

        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        if (self::isLocaleMap($value)) {
            return $value[$locale] ?? $value['en'] ?? reset($value);
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => self::resolve($item, $locale), $value);
        }

        $resolved = [];
        foreach ($value as $key => $item) {
            $resolved[$key] = self::resolve($item, $locale);
        }

        return $resolved;
    }

    private static function isLocaleMap(array $value): bool
    {
        $keys = array_keys($value);

        if ($keys === ['en', 'ar'] || $keys === ['ar', 'en']) {
            return true;
        }

        if (count($keys) === 1 && in_array($keys[0], self::SUPPORTED, true)) {
            return true;
        }

        return count(array_intersect($keys, self::SUPPORTED)) >= 1
            && count($keys) <= 2
            && count(array_diff($keys, self::SUPPORTED)) === 0;
    }
}
