<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenu;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DigitalMenuController extends Controller
{
    private const MENU_TEMPLATES = [
        'classic', 'restaurant', 'bistro', 'elegant', 'modern', 'cafe', 'brunch', 'minimal', 'compact', 'grid',
    ];
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->digitalMenus()->with('customDomain')->latest()->get()
            ->map(fn ($m) => $this->enrich($m));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('digital_menus')) {
            return response()->json(['message' => __('messages.digital_menu_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $menu = $user->digitalMenus()->create($validated);

        return response()->json($this->enrich($menu->load('customDomain')), 201);
    }

    public function show(Request $request, DigitalMenu $digitalMenu): JsonResponse
    {
        $this->authorizeOwner($request, $digitalMenu);

        return response()->json($this->enrich($digitalMenu->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, DigitalMenu $digitalMenu): JsonResponse
    {
        $this->authorizeOwner($request, $digitalMenu);

        $digitalMenu->update($this->validated($request, $digitalMenu->id));

        return response()->json($this->enrich($digitalMenu->fresh('customDomain')));
    }

    public function destroy(Request $request, DigitalMenu $digitalMenu): JsonResponse
    {
        $this->authorizeOwner($request, $digitalMenu);
        $digitalMenu->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, DigitalMenu $digitalMenu): JsonResponse
    {
        $this->authorizeOwner($request, $digitalMenu);
        $digitalMenu->update(['is_active' => ! $digitalMenu->is_active]);

        return response()->json($this->enrich($digitalMenu->fresh('customDomain')));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('digital_menus')->ignore($ignoreId)],
            'template' => ['nullable', 'string', Rule::in(self::MENU_TEMPLATES)],
            'name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'logo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:7',
            'currency' => 'nullable|string|max:3',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'hours' => 'nullable|string|max:255',
            'sections' => 'nullable|array',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'is_active' => 'sometimes|boolean',
            'qr_shape' => 'nullable|string|max:20',
            'dot_style' => 'nullable|string|max:20',
            'corner_style' => 'nullable|string|max:20',
            'frame_style' => 'nullable|string|max:20',
        ]);
    }

    private function enrich(DigitalMenu $menu): DigitalMenu
    {
        $menu->setAttribute('menu_url', $this->domains->menuUrl(
            $menu->user,
            $menu->slug,
            $menu->custom_domain_id
        ));
        $menu->setAttribute('domain_label', $menu->customDomain?->domain);

        return $menu;
    }

    private function authorizeOwner(Request $request, DigitalMenu $menu): void
    {
        if ($menu->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
