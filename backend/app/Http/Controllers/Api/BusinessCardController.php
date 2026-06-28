<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class BusinessCardController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->businessCards()->with('customDomain')->latest()->get()
            ->map(fn ($c) => $this->enrich($c));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('business_cards')) {
            return response()->json(['message' => __('messages.business_card_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $card = $user->businessCards()->create($validated);

        return response()->json($this->enrich($card->load('customDomain')), 201);
    }

    public function show(Request $request, BusinessCard $businessCard): JsonResponse
    {
        $this->authorizeOwner($request, $businessCard);

        return response()->json($this->enrich($businessCard->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, BusinessCard $businessCard): JsonResponse
    {
        $this->authorizeOwner($request, $businessCard);

        $businessCard->update($this->validated($request, $businessCard->id));

        return response()->json($this->enrich($businessCard->fresh('customDomain')));
    }

    public function destroy(Request $request, BusinessCard $businessCard): JsonResponse
    {
        $this->authorizeOwner($request, $businessCard);
        $businessCard->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, BusinessCard $businessCard): JsonResponse
    {
        $this->authorizeOwner($request, $businessCard);
        $businessCard->update(['is_active' => ! $businessCard->is_active]);

        return response()->json($this->enrich($businessCard->fresh('customDomain')));
    }

    public function vcard(string $slug): Response
    {
        $businessCard = BusinessCard::where('slug', $slug)->firstOrFail();

        if (! $businessCard->is_active) {
            abort(403, __('messages.card_not_published'));
        }

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:'.$this->escapeVcard($businessCard->full_name ?? ''),
            'N:'.$this->escapeVcard($businessCard->full_name ?? '').';;;;',
        ];

        if ($businessCard->job_title) {
            $lines[] = 'TITLE:'.$this->escapeVcard($businessCard->job_title);
        }
        if ($businessCard->company) {
            $lines[] = 'ORG:'.$this->escapeVcard($businessCard->company);
        }
        if ($businessCard->email) {
            $lines[] = 'EMAIL;TYPE=INTERNET:'.$this->escapeVcard($businessCard->email);
        }
        if ($businessCard->phone) {
            $lines[] = 'TEL;TYPE=CELL:'.$this->escapeVcard($businessCard->phone);
        }
        if ($businessCard->website) {
            $lines[] = 'URL:'.$this->escapeVcard($businessCard->website);
        }
        if ($businessCard->address) {
            $lines[] = 'ADR;TYPE=WORK:;;'.$this->escapeVcard($businessCard->address).';;;;';
        }
        if ($businessCard->bio) {
            $lines[] = 'NOTE:'.$this->escapeVcard($businessCard->bio);
        }
        if ($businessCard->tagline) {
            $lines[] = 'ROLE:'.$this->escapeVcard($businessCard->tagline);
        }

        foreach ($businessCard->social_links ?? [] as $social) {
            if (! empty($social['url'])) {
                $type = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $social['platform'] ?? 'SOCIAL') ?: 'SOCIAL');
                $lines[] = 'URL;TYPE='.$type.':'.$this->escapeVcard($social['url']);
            }
        }

        $lines[] = 'END:VCARD';

        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $businessCard->slug) ?: 'contact';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.vcf"',
        ]);
    }

    private function escapeVcard(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n", "\r"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n', ''],
            trim($value)
        );
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('business_cards')->ignore($ignoreId)],
            'full_name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'photo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'logo_path' => 'nullable|string|max:500',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'social_links' => 'nullable|array',
            'theme_color' => 'nullable|string|max:7',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function enrich(BusinessCard $card): BusinessCard
    {
        $card->setAttribute('card_url', $this->domains->cardUrl(
            $card->user,
            $card->slug,
            $card->custom_domain_id
        ));
        $card->setAttribute('domain_label', $card->customDomain?->domain);

        return $card;
    }

    private function authorizeOwner(Request $request, BusinessCard $businessCard): void
    {
        if ($businessCard->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
