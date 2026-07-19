<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HuggingFaceImageService;
use App\Support\StorageUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiImageController extends Controller
{
    public function __construct(private HuggingFaceImageService $ai) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'configured' => $this->ai->isConfigured(),
            'provider' => 'huggingface',
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        if (! $this->ai->isConfigured()) {
            return response()->json([
                'message' => __('messages.ai_token_required'),
            ], 503);
        }

        $validated = $request->validate([
            'prompt' => 'required|string|max:500',
            'context' => 'nullable|in:qr-background,qr-logo,card-photo,card-background,form-header,form-logo,form-background,page-background,portfolio,gallery,prize-gift,campaign-logo,giveaway-background,event-logo,event-background,badge-logo,achievement-badge,certificate-background,restaurant-logo,restaurant-interior,food-photo',
        ]);

        try {
            $url = $this->ai->generate(
                $validated['prompt'],
                $validated['context'] ?? 'qr-background'
            );

            return response()->json(['url' => $url]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'folder' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/'],
        ]);

        $folder = $request->input('folder', 'uploads');
        $path = $request->file('image')->store($folder, 'public');

        return response()->json([
            'url' => StorageUrl::publicUrl($path),
            'path' => $path,
        ]);
    }
}
