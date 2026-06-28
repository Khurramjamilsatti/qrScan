<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShortLinkController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->shortLinks()->with('customDomain')->latest()->get()
            ->map(fn ($l) => $this->enrich($l));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('short_links')) {
            return response()->json(['message' => __('messages.short_link_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $link = $user->shortLinks()->create($validated);

        return response()->json($this->enrich($link->load('customDomain')), 201);
    }

    public function show(Request $request, ShortLink $shortLink): JsonResponse
    {
        $this->authorizeOwner($request, $shortLink);

        return response()->json($this->enrich($shortLink->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, ShortLink $shortLink): JsonResponse
    {
        $this->authorizeOwner($request, $shortLink);

        $shortLink->update($this->validated($request, $shortLink->id, true));

        return response()->json($this->enrich($shortLink->fresh('customDomain')));
    }

    public function destroy(Request $request, ShortLink $shortLink): JsonResponse
    {
        $this->authorizeOwner($request, $shortLink);
        $shortLink->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function toggleActive(Request $request, ShortLink $shortLink): JsonResponse
    {
        $this->authorizeOwner($request, $shortLink);
        $shortLink->update(['is_active' => ! $shortLink->is_active]);

        return response()->json($this->enrich($shortLink->fresh('customDomain')));
    }

    private function validated(Request $request, ?int $ignoreId = null, bool $partial = false): array
    {
        return $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'slug' => [($partial ? 'sometimes' : 'required'), 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('short_links')->ignore($ignoreId)],
            'destination_url' => ($partial ? 'sometimes|' : '').'required|url',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_term' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'foreground_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'logo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'qr_size' => 'nullable|integer|min:200|max:600',
            'error_correction' => 'nullable|in:L,M,Q,H',
            'margin' => 'nullable|integer|min:0|max:8',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
        ]);
    }

    private function enrich(ShortLink $link): ShortLink
    {
        $link->setAttribute('short_url', $this->domains->shortLinkUrl(
            $link->user,
            $link->slug,
            $link->custom_domain_id
        ));
        $link->setAttribute('scan_url', $this->domains->shortLinkScanUrl(
            $link->user,
            $link->slug,
            $link->custom_domain_id
        ));
        $link->setAttribute('domain_label', $link->customDomain?->domain);

        return $link;
    }

    private function authorizeOwner(Request $request, ShortLink $shortLink): void
    {
        if ($shortLink->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
