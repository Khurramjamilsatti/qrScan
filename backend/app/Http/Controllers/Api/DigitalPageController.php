<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalPage;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DigitalPageController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->digitalPages()->with('customDomain')->latest()->get()
            ->map(fn ($p) => $this->enrich($p));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('digital_pages')) {
            return response()->json(['message' => __('messages.digital_page_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $page = $user->digitalPages()->create($validated);

        return response()->json($this->enrich($page->load('customDomain')), 201);
    }

    public function show(Request $request, DigitalPage $digitalPage): JsonResponse
    {
        $this->authorizeOwner($request, $digitalPage);

        return response()->json($this->enrich($digitalPage->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, DigitalPage $digitalPage): JsonResponse
    {
        $this->authorizeOwner($request, $digitalPage);

        $digitalPage->update($this->validated($request, $digitalPage->id));

        return response()->json($this->enrich($digitalPage->fresh('customDomain')));
    }

    public function destroy(Request $request, DigitalPage $digitalPage): JsonResponse
    {
        $this->authorizeOwner($request, $digitalPage);
        $digitalPage->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, DigitalPage $digitalPage): JsonResponse
    {
        $this->authorizeOwner($request, $digitalPage);
        $digitalPage->update(['is_active' => ! $digitalPage->is_active]);

        return response()->json($this->enrich($digitalPage->fresh('customDomain')));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('digital_pages')->ignore($ignoreId)],
            'title' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'template' => 'nullable|in:landing,portfolio,event,simple',
            'content' => 'nullable|array',
            'theme_color' => 'nullable|string|max:7',
            'logo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function enrich(DigitalPage $page): DigitalPage
    {
        $page->setAttribute('page_url', $this->domains->pageUrl(
            $page->user,
            $page->slug,
            $page->custom_domain_id
        ));
        $page->setAttribute('domain_label', $page->customDomain?->domain);

        return $page;
    }

    private function authorizeOwner(Request $request, DigitalPage $page): void
    {
        if ($page->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
