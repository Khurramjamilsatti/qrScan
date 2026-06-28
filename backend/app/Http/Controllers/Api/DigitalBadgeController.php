<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalBadge;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DigitalBadgeController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->digitalBadges()->with('customDomain')->latest()->get()
            ->map(fn ($b) => $this->enrich($b));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('digital_badges')) {
            return response()->json(['message' => __('messages.digital_badge_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $badge = $user->digitalBadges()->create($validated);

        return response()->json($this->enrich($badge->load('customDomain')), 201);
    }

    public function show(Request $request, DigitalBadge $digitalBadge): JsonResponse
    {
        $this->authorizeOwner($request, $digitalBadge);

        return response()->json($this->enrich($digitalBadge->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, DigitalBadge $digitalBadge): JsonResponse
    {
        $this->authorizeOwner($request, $digitalBadge);

        $digitalBadge->update($this->validated($request, $digitalBadge->id));

        return response()->json($this->enrich($digitalBadge->fresh('customDomain')));
    }

    public function destroy(Request $request, DigitalBadge $digitalBadge): JsonResponse
    {
        $this->authorizeOwner($request, $digitalBadge);
        $digitalBadge->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, DigitalBadge $digitalBadge): JsonResponse
    {
        $this->authorizeOwner($request, $digitalBadge);
        $digitalBadge->update(['is_active' => ! $digitalBadge->is_active]);

        return response()->json($this->enrich($digitalBadge->fresh('customDomain')));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('digital_badges')->ignore($ignoreId)],
            'title' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'template' => 'nullable|in:classic,modern,certificate',
            'recipient_name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'recipient_email' => 'nullable|email|max:255',
            'issuer_name' => 'nullable|string|max:255',
            'badge_id' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'verify_url' => 'nullable|url|max:500',
            'settings' => 'nullable|array',
            'theme_color' => 'nullable|string|max:7',
            'logo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'badge_image_path' => 'nullable|string|max:500',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function enrich(DigitalBadge $badge): DigitalBadge
    {
        $badge->setAttribute('badge_url', $this->domains->badgeUrl(
            $badge->user,
            $badge->slug,
            $badge->custom_domain_id
        ));
        $badge->setAttribute('domain_label', $badge->customDomain?->domain);

        return $badge;
    }

    private function authorizeOwner(Request $request, DigitalBadge $badge): void
    {
        if ($badge->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
