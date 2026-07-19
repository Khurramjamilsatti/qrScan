<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrAiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrAssistantController extends Controller
{
    public function __construct(private QrAiAssistantService $assistant) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'llm_configured' => $this->assistant->isLlmConfigured(),
            'provider' => $this->assistant->isLlmConfigured() ? 'openai' : 'rules',
        ]);
    }

    public function advise(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'context' => 'nullable|array',
        ]);

        $result = $this->assistant->advise(
            $request->user(),
            $validated['prompt'],
            $validated['context'] ?? []
        );

        return response()->json($result);
    }
}
