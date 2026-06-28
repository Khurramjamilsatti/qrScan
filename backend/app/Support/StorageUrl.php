<?php

namespace App\Support;

class StorageUrl
{
    public static function publicUrl(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }

    public static function normalize(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, '/storage/')) {
            return $url;
        }

        if (preg_match('#/storage/.+#', $url, $m)) {
            return $m[0];
        }

        return $url;
    }
}
