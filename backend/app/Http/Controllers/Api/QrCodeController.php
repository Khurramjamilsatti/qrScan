<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Services\DomainUrlService;
use App\Services\QrGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QrCodeController extends Controller
{
    public function __construct(
        private QrGeneratorService $qrGenerator,
        private DomainUrlService $domains,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->qrCodes()->with('customDomain')->latest()->get()
            ->map(fn ($qr) => $this->enrich($qr));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('qr_codes')) {
            return response()->json(['message' => __('messages.qr_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $qrCode = $user->qrCodes()->create($validated);

        return response()->json($this->enrich($qrCode->load('customDomain')), 201);
    }

    public function show(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorizeOwner($request, $qrCode);

        return response()->json($this->enrich($qrCode->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorizeOwner($request, $qrCode);

        $validated = $this->validated($request, true);
        $qrCode->update($validated);

        return response()->json($this->enrich($qrCode->fresh('customDomain')));
    }

    public function destroy(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorizeOwner($request, $qrCode);
        $qrCode->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function toggleActive(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorizeOwner($request, $qrCode);
        $qrCode->update(['is_active' => ! $qrCode->is_active]);

        return response()->json($this->enrich($qrCode->fresh('customDomain')));
    }

    public function download(Request $request, QrCode $qrCode, string $format): Response
    {
        $this->authorizeOwner($request, $qrCode);

        if (! in_array($format, ['png', 'svg'])) {
            abort(400, __('messages.invalid_format'));
        }

        $content = $this->qrGenerator->generate($qrCode, $format);
        $mime = $format === 'svg' ? 'image/svg+xml' : 'image/png';

        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => "attachment; filename=\"{$qrCode->name}.{$format}\"",
        ]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $rules = [
            'name' => ($partial ? 'sometimes|' : '').'required|string|max:255',
            'destination_url' => ($partial ? 'sometimes|' : '').'required|url',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'foreground_color' => 'nullable|string|max:7',
            'background_color' => 'nullable|string|max:7',
            'logo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'size' => 'nullable|integer|min:200|max:800',
            'error_correction' => 'nullable|in:L,M,Q,H',
            'margin' => 'nullable|integer|min:0|max:10',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
            'is_active' => 'sometimes|boolean',
        ];

        return $request->validate($rules);
    }

    private function enrich(QrCode $qr): QrCode
    {
        $qr->setAttribute('scan_url', $this->domains->qrScanUrl(
            $qr->user,
            $qr->code,
            $qr->custom_domain_id
        ));
        $qr->setAttribute('domain_label', $qr->customDomain?->domain);

        return $qr;
    }

    private function authorizeOwner(Request $request, QrCode $qrCode): void
    {
        if ($qrCode->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
