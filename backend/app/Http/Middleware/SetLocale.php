<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-Locale')
            ?? $request->query('locale')
            ?? $this->parseAcceptLanguage($request->header('Accept-Language'));

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function parseAcceptLanguage(?string $header): ?string
    {
        if (! $header) {
            return null;
        }

        foreach (explode(',', $header) as $part) {
            $code = strtolower(trim(explode(';', $part)[0]));
            $lang = explode('-', $code)[0];
            if (in_array($lang, self::SUPPORTED, true)) {
                return $lang;
            }
        }

        return null;
    }
}
