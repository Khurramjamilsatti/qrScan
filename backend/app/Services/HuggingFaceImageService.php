<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\StorageUrl;

class HuggingFaceImageService
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.huggingface.token'));
    }

    public function generate(string $prompt, string $context = 'qr-background'): ?string
    {
        $token = config('services.huggingface.token');

        if (! $token) {
            return null;
        }

        $model = config('services.huggingface.model', 'stabilityai/stable-diffusion-xl-base-1.0');

        $response = Http::withToken($token)
            ->timeout(120)
            ->post("https://api-inference.huggingface.co/models/{$model}", [
                'inputs' => $this->enhancePrompt($prompt, $context),
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException($response->json('error') ?? 'Hugging Face generation failed');
        }

        $filename = 'ai/'.Str::uuid().'.png';
        Storage::disk('public')->put($filename, $response->body());

        return StorageUrl::publicUrl($filename);
    }

    private function enhancePrompt(string $prompt, string $context): string
    {
        return match ($context) {
            'qr-logo' => "minimal logo icon, {$prompt}, flat vector, white background, centered, professional",
            'card-photo' => "professional headshot portrait, {$prompt}, business photo, studio lighting, neutral background",
            'card-background' => "abstract gradient background, {$prompt}, soft professional, minimal, no text",
            default => "abstract pattern background, {$prompt}, minimal, clean, no text, suitable for QR code",
        };
    }
}
