<?php

namespace App\Support;

class CertificateAppearance
{
    public static function textColor(array $settings): string
    {
        return self::hexColor($settings['text_color'] ?? null, '#1a1333');
    }

    public static function backgroundColor(array $settings): string
    {
        return self::hexColor($settings['background_color'] ?? null, '#fdfbf7');
    }

    public static function pdfFontFamily(array $settings): string
    {
        $fontId = $settings['font_family'] ?? 'instrument-serif';

        return in_array($fontId, ['dm-sans', 'arabic'], true)
            ? 'DejaVu Sans, sans-serif'
            : 'DejaVu Serif, serif';
    }

    public static function accentColor(string $themeColor): string
    {
        return self::mixHex($themeColor, '#c9a227', 0.45);
    }

    public static function mutedTextColor(string $textColor): string
    {
        return self::mixHex($textColor, '#8b839c', 0.55);
    }

    public static function goldColor(): string
    {
        return '#b8952e';
    }

    public static function mixHex(string $hex1, string $hex2, float $ratio): string
    {
        [$r1, $g1, $b1] = self::hexToRgb($hex1);
        [$r2, $g2, $b2] = self::hexToRgb($hex2);
        $r = (int) round($r1 + ($r2 - $r1) * $ratio);
        $g = (int) round($g1 + ($g2 - $g1) * $ratio);
        $b = (int) round($b1 + ($b2 - $b1) * $ratio);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /** @return array{0:int,1:int,2:int} */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function hexColor(?string $value, string $fallback): string
    {
        if ($value && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return $value;
        }

        return $fallback;
    }
}
