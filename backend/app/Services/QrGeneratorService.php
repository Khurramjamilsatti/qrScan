<?php

namespace App\Services;

use App\Models\QrCode;
use App\Support\StorageUrl;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrGenerator;

class QrGeneratorService
{
    public function __construct(private DomainUrlService $domains) {}

    public function generate(QrCode $qrCode, string $format = 'png'): string
    {
        $url = $this->domains->qrScanUrl(
            $qrCode->user,
            $qrCode->code,
            $qrCode->custom_domain_id
        );

        $generator = QrGenerator::format($format === 'svg' ? 'svg' : 'png')
            ->size($qrCode->size ?? 400)
            ->margin($qrCode->margin ?? 2)
            ->errorCorrection($qrCode->error_correction ?? 'M')
            ->color(...$this->hexToRgb($qrCode->foreground_color))
            ->backgroundColor(...$this->hexToRgb($qrCode->background_color));

        $hasBackground = $format === 'png' && $qrCode->background_image_path;

        if ($qrCode->logo_path && $format === 'png' && ! $hasBackground) {
            $logoPath = $this->resolveLocalPath($qrCode->logo_path);
            if ($logoPath) {
                $generator->merge($logoPath, 0.25, true);
            }
        }

        $output = $generator->generate($url);

        if ($format === 'png' && $qrCode->background_image_path) {
            $composed = $this->compositeBackground($output, $qrCode->background_image_path, $qrCode->logo_path);
            if ($composed) {
                return $composed;
            }
        }

        return $output;
    }

    private function compositeBackground(string $qrPng, string $backgroundUrl, ?string $logoUrl): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $bgPath = $this->resolveLocalPath($backgroundUrl);
        if (! $bgPath) {
            return null;
        }

        $qrImg = @imagecreatefromstring($qrPng);
        $bgImg = @imagecreatefromstring(file_get_contents($bgPath));
        if (! $qrImg || ! $bgImg) {
            return null;
        }

        $size = imagesx($qrImg);
        $canvas = imagecreatetruecolor($size, $size);
        imagecopyresampled($canvas, $bgImg, 0, 0, 0, 0, $size, $size, imagesx($bgImg), imagesy($bgImg));
        imagecopy($canvas, $qrImg, 0, 0, 0, 0, $size, $size);

        if ($logoUrl) {
            $logoPath = $this->resolveLocalPath($logoUrl);
            if ($logoPath) {
                $logoImg = @imagecreatefromstring(file_get_contents($logoPath));
                if ($logoImg) {
                    $logoSize = (int) ($size * 0.22);
                    $x = (int) (($size - $logoSize) / 2);
                    $y = (int) (($size - $logoSize) / 2);
                    $white = imagecolorallocate($canvas, 255, 255, 255);
                    imagefilledrectangle($canvas, $x - 4, $y - 4, $x + $logoSize + 4, $y + $logoSize + 4, $white);
                    imagecopyresampled($canvas, $logoImg, $x, $y, 0, 0, $logoSize, $logoSize, imagesx($logoImg), imagesy($logoImg));
                }
            }
        }

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();
        imagedestroy($qrImg);
        imagedestroy($bgImg);
        imagedestroy($canvas);

        return $result ?: null;
    }

    private function resolveLocalPath(string $url): ?string
    {
        $normalized = StorageUrl::normalize($url) ?? $url;

        if (str_starts_with($normalized, '/storage/')) {
            $path = storage_path('app/public/'.ltrim(str_replace('/storage/', '', $normalized), '/'));

            return file_exists($path) ? $path : null;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL)) {
            try {
                $contents = Http::timeout(10)->get($normalized)->body();
                $tmp = storage_path('app/tmp-'.md5($normalized).'.png');
                file_put_contents($tmp, $contents);

                return $tmp;
            } catch (\Throwable) {
                return null;
            }
        }

        return file_exists($normalized) ? $normalized : null;
    }

    private function hexToRgb(string $hex): array
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
}
