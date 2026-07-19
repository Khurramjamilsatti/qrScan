<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrFunnel;
use App\Services\QrFunnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QrFunnelController extends Controller
{
    public function __construct(private QrFunnelService $funnels) {}

    public function templates(): JsonResponse
    {
        return response()->json(['templates' => $this->funnels->templates()]);
    }

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->qrFunnels()->with('steps')->latest()->get();

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);
        $steps = $validated['steps'] ?? [];
        unset($validated['steps']);

        $funnel = $request->user()->qrFunnels()->create($validated);
        $this->syncSteps($funnel, $steps);

        return response()->json($funnel->load('steps'), 201);
    }

    public function show(Request $request, QrFunnel $qrFunnel): JsonResponse
    {
        $this->authorizeOwner($request, $qrFunnel);

        return response()->json($qrFunnel->load('steps'));
    }

    public function update(Request $request, QrFunnel $qrFunnel): JsonResponse
    {
        $this->authorizeOwner($request, $qrFunnel);

        $validated = $this->validated($request, $qrFunnel->id);
        $steps = $validated['steps'] ?? null;
        unset($validated['steps']);

        $qrFunnel->update($validated);
        if (is_array($steps)) {
            $this->syncSteps($qrFunnel, $steps);
        }

        return response()->json($qrFunnel->fresh('steps'));
    }

    public function destroy(Request $request, QrFunnel $qrFunnel): JsonResponse
    {
        $this->authorizeOwner($request, $qrFunnel);
        $qrFunnel->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, QrFunnel $qrFunnel): JsonResponse
    {
        $this->authorizeOwner($request, $qrFunnel);
        $qrFunnel->update(['is_active' => ! $qrFunnel->is_active]);

        return response()->json($qrFunnel->fresh('steps'));
    }

    private function syncSteps(QrFunnel $funnel, array $steps): void
    {
        $funnel->steps()->delete();
        foreach ($steps as $i => $step) {
            $funnel->steps()->create([
                'sort_order' => $step['sort_order'] ?? $i,
                'step_type' => $step['step_type'] ?? 'custom',
                'title' => $step['title'] ?? 'Step '.($i + 1),
                'description' => $step['description'] ?? null,
                'target_type' => $step['target_type'] ?? 'url',
                'target_slug' => $step['target_slug'] ?? null,
                'target_url' => $step['target_url'] ?? null,
                'cta_text' => $step['cta_text'] ?? null,
            ]);
        }
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('qr_funnels')->ignore($ignoreId)],
            'goal' => 'nullable|in:lead,booking,purchase,download,engagement',
            'description' => 'nullable|string|max:2000',
            'theme_color' => 'nullable|string|max:7',
            'is_active' => 'sometimes|boolean',
            'steps' => 'nullable|array',
            'steps.*.step_type' => 'required_with:steps|string|max:50',
            'steps.*.title' => 'required_with:steps|string|max:255',
            'steps.*.description' => 'nullable|string|max:1000',
            'steps.*.target_type' => 'nullable|string|max:50',
            'steps.*.target_slug' => 'nullable|string|max:100',
            'steps.*.target_url' => 'nullable|string|max:2048',
            'steps.*.cta_text' => 'nullable|string|max:100',
            'steps.*.sort_order' => 'nullable|integer|min:0',
        ]);
    }

    private function authorizeOwner(Request $request, QrFunnel $funnel): void
    {
        if ($funnel->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
