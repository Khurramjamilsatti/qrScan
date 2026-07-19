<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalEvent;
use App\Services\DomainUrlService;
use App\Support\EventTemplateCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DigitalEventController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->digitalEvents()->with('customDomain')->latest()->get()
            ->map(fn ($e) => $this->enrich($e));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('digital_events')) {
            return response()->json(['message' => __('messages.digital_event_limit_reached')], 403);
        }

        $validated = $this->validated($request, $user);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $event = $user->digitalEvents()->create($validated);

        return response()->json($this->enrich($event->load('customDomain')), 201);
    }

    public function show(Request $request, DigitalEvent $digitalEvent): JsonResponse
    {
        $this->authorizeOwner($request, $digitalEvent);

        return response()->json($this->enrich($digitalEvent->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, DigitalEvent $digitalEvent): JsonResponse
    {
        $this->authorizeOwner($request, $digitalEvent);

        $digitalEvent->update($this->validated($request, $request->user(), $digitalEvent->id));

        return response()->json($this->enrich($digitalEvent->fresh('customDomain')));
    }

    public function destroy(Request $request, DigitalEvent $digitalEvent): JsonResponse
    {
        $this->authorizeOwner($request, $digitalEvent);
        $digitalEvent->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, DigitalEvent $digitalEvent): JsonResponse
    {
        $this->authorizeOwner($request, $digitalEvent);
        $digitalEvent->update(['is_active' => ! $digitalEvent->is_active]);

        return response()->json($this->enrich($digitalEvent->fresh('customDomain')));
    }

    private function validated(Request $request, $user, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('digital_events')->ignore($ignoreId)],
            'template' => ['nullable', 'string', Rule::in(EventTemplateCatalog::ALL_TEMPLATES)],
            'event_type' => 'nullable|string|max:50',
            'title' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'hosts' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'venue_name' => 'nullable|string|max:255',
            'dress_code' => 'nullable|string|max:255',
            'cover_image_path' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:7',
            'content' => 'nullable|array',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'is_active' => 'sometimes|boolean',
            'qr_shape' => 'nullable|string|max:20',
            'dot_style' => 'nullable|string|max:20',
            'corner_style' => 'nullable|string|max:20',
            'frame_style' => 'nullable|string|max:20',
        ]);

        $template = $validated['template'] ?? 'simple-invite';

        if (EventTemplateCatalog::isPremium($template) && ! $user->canUsePremiumEventTemplates()) {
            abort(403, __('messages.premium_event_template_required'));
        }

        return $validated;
    }

    private function enrich(DigitalEvent $event): DigitalEvent
    {
        $event->setAttribute('invite_url', $this->domains->inviteUrl(
            $event->user,
            $event->slug,
            $event->custom_domain_id
        ));
        $event->setAttribute('domain_label', $event->customDomain?->domain);

        return $event;
    }

    private function authorizeOwner(Request $request, DigitalEvent $event): void
    {
        if ($event->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
