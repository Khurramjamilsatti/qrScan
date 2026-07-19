<?php

namespace App\Services;

use App\Models\DigitalCertificate;
use App\Support\StorageUrl;
use App\Support\CertificateAppearance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificatePdfService
{
    public function __construct(private DomainUrlService $domains) {}

    public function generateAndStore(DigitalCertificate $cert): string
    {
        $pdf = $this->buildPdf($cert);
        $filename = 'certificates/'.$cert->certificate_id.'.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        $cert->update(['pdf_path' => StorageUrl::publicUrl($filename)]);

        return $cert->pdf_path;
    }

    public function buildPdf(DigitalCertificate $cert): \Barryvdh\DomPDF\PDF
    {
        $cert->loadMissing('user');

        $verifyUrl = $this->domains->verifyUrl($cert->user, $cert->certificate_id, $cert->custom_domain_id);
        $settings = $cert->settings ?? [];
        $themeColor = $cert->theme_color ?? '#1a1333';
        $textColor = CertificateAppearance::textColor($settings);
        $qrPath = $this->qrSvgPath($verifyUrl, $themeColor, $cert->certificate_id);

        return Pdf::loadView('certificates.pdf', [
            'cert' => $cert,
            'verifyUrl' => $verifyUrl,
            'logo' => $this->circularAsset($this->absoluteAsset($cert->logo_path), $cert->certificate_id.'-logo'),
            'seal' => $this->circularAsset($this->absoluteAsset($cert->seal_path), $cert->certificate_id.'-seal'),
            'instructorSig' => $this->absoluteAsset($cert->instructor_signature_path),
            'orgSig' => $this->absoluteAsset($cert->organization_signature_path),
            'background' => $this->absoluteAsset($cert->background_image_path),
            'qrPath' => $qrPath,
            'textColor' => $textColor,
            'backgroundColor' => CertificateAppearance::backgroundColor($settings),
            'pdfFontFamily' => CertificateAppearance::pdfFontFamily($settings),
            'themeColor' => $themeColor,
            'accentColor' => CertificateAppearance::accentColor($themeColor),
            'goldColor' => CertificateAppearance::goldColor(),
            'mutedColor' => CertificateAppearance::mutedTextColor($textColor),
            'borderColor' => CertificateAppearance::mixHex($themeColor, '#d9d2c4', 0.18),
            'elegantOuterBorder' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), $themeColor, 0.4),
            'elegantFrameBorder' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#d4cfc0', 0.4),
            'ruleLineColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#cccccc', 0.5),
            'awardColor' => CertificateAppearance::mixHex($textColor, '#4a4460', 0.15),
            'chipBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#e8e4f0', 0.25),
            'footerBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#e0dcd3', 0.3),
            'sigBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#c4bdb0', 0.35),
            'qrBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#e8e4f0', 0.2),
            'idBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#d4cfc0', 0.3),
            'logoBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#d4cfc0', 0.4),
            'sealBorderColor' => CertificateAppearance::mixHex(CertificateAppearance::goldColor(), '#ffffff', 0.35),
            'watermarkColor' => CertificateAppearance::mixHex($textColor, '#ffffff', 0.95),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('chroot', base_path());
    }

    public function download(DigitalCertificate $cert): \Symfony\Component\HttpFoundation\Response
    {
        return $this->buildPdf($cert)->download($cert->certificate_id.'.pdf');
    }

    private function qrSvgPath(string $url, string $foreground, string $certificateId): ?string
    {
        $pngPath = $this->qrPngPath($url, $foreground, $certificateId);
        if ($pngPath) {
            return $pngPath;
        }

        $svg = $this->qrSvg($url, $foreground);
        if (! $svg) {
            return null;
        }

        $dir = storage_path('app/certificates/tmp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir.'/'.$certificateId.'-qr.svg';
        file_put_contents($path, $svg);

        return $path;
    }

    private function qrPngPath(string $url, string $foreground, string $certificateId): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        try {
            [$r, $g, $b] = $this->hexToRgb($foreground);
            $matrix = \BaconQrCode\Encoder\Encoder::encode(
                $url,
                \BaconQrCode\Common\ErrorCorrectionLevel::M(),
            )->getMatrix();

            $moduleCount = $matrix->getWidth();
            $scale = 4;
            $margin = 2;
            $size = ($moduleCount + ($margin * 2)) * $scale;
            $image = imagecreatetruecolor($size, $size);
            if ($image === false) {
                return null;
            }

            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, $r, $g, $b);
            imagefill($image, 0, 0, $white);

            for ($y = 0; $y < $moduleCount; $y++) {
                for ($x = 0; $x < $moduleCount; $x++) {
                    if ($matrix->get($x, $y) === 1) {
                        imagefilledrectangle(
                            $image,
                            ($x + $margin) * $scale,
                            ($y + $margin) * $scale,
                            (($x + $margin + 1) * $scale) - 1,
                            (($y + $margin + 1) * $scale) - 1,
                            $black,
                        );
                    }
                }
            }

            $dir = storage_path('app/certificates/tmp');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $path = $dir.'/'.$certificateId.'-qr.png';
            imagepng($image, $path);
            imagedestroy($image);

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    private function qrSvg(string $url, string $foreground = '#1a1333'): ?string
    {
        try {
            [$r, $g, $b] = $this->hexToRgb($foreground);

            return QrCode::format('svg')
                ->size(96)
                ->margin(1)
                ->color($r, $g, $b)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('M')
                ->generate($url);
        } catch (\Throwable) {
            return null;
        }
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

    private function circularAsset(?string $path, string $cacheKey, int $size = 240): ?string
    {
        if (! $path || ! extension_loaded('gd')) {
            return $path;
        }

        $dir = storage_path('app/certificates/tmp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $out = $dir.'/'.$cacheKey.'-circle.png';
        if (file_exists($out) && filemtime($out) >= filemtime($path)) {
            return $out;
        }

        try {
            $source = $this->loadGdImage($path);
            if ($source === null) {
                return $path;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $side = min($width, $height);
            $srcX = (int) floor(($width - $side) / 2);
            $srcY = (int) floor(($height - $side) / 2);

            $square = imagecreatetruecolor($size, $size);
            imagealphablending($square, false);
            imagesavealpha($square, true);
            $transparent = imagecolorallocatealpha($square, 0, 0, 0, 127);
            imagefill($square, 0, 0, $transparent);

            imagecopyresampled($square, $source, 0, 0, $srcX, $srcY, $size, $size, $side, $side);
            imagedestroy($source);

            $circle = imagecreatetruecolor($size, $size);
            imagealphablending($circle, false);
            imagesavealpha($circle, true);
            imagefill($circle, 0, 0, $transparent);

            $radius = $size / 2;
            for ($x = 0; $x < $size; $x++) {
                for ($y = 0; $y < $size; $y++) {
                    $dx = $x - $radius + 0.5;
                    $dy = $y - $radius + 0.5;
                    if (($dx * $dx + $dy * $dy) <= ($radius * $radius)) {
                        $color = imagecolorat($square, $x, $y);
                        imagesetpixel($circle, $x, $y, $color);
                    }
                }
            }
            imagedestroy($square);

            imagepng($circle, $out);
            imagedestroy($circle);

            return $out;
        } catch (\Throwable) {
            return $path;
        }
    }

    private function loadGdImage(string $path): ?\GdImage
    {
        $type = exif_imagetype($path);
        if ($type === IMAGETYPE_JPEG) {
            return imagecreatefromjpeg($path) ?: null;
        }
        if ($type === IMAGETYPE_PNG) {
            return imagecreatefrompng($path) ?: null;
        }
        if ($type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            return imagecreatefromwebp($path) ?: null;
        }

        return null;
    }

    private function absoluteAsset(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = StorageUrl::normalize($path);
        if (! $normalized) {
            return null;
        }

        $relative = ltrim(str_replace('/storage/', '', $normalized), '/');
        $full = Storage::disk('public')->path($relative);

        return file_exists($full) ? $full : null;
    }

    private function storagePath(string $publicPath): string
    {
        return ltrim(str_replace('/storage/', '', $publicPath), '/');
    }
}
