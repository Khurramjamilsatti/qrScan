<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScanToWinCampaign;
use App\Models\ScanToWinPlay;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScanToWinCampaignController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->scanToWinCampaigns()->with('customDomain')->latest()->get()
            ->map(fn ($c) => $this->enrich($c));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('scan_to_win')) {
            return response()->json(['message' => __('messages.scan_to_win_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $campaign = $user->scanToWinCampaigns()->create($validated);

        return response()->json($this->enrich($campaign->load('customDomain')), 201);
    }

    public function show(Request $request, ScanToWinCampaign $scanToWinCampaign): JsonResponse
    {
        $this->authorizeOwner($request, $scanToWinCampaign);

        return response()->json($this->enrich($scanToWinCampaign->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, ScanToWinCampaign $scanToWinCampaign): JsonResponse
    {
        $this->authorizeOwner($request, $scanToWinCampaign);

        $scanToWinCampaign->update($this->validated($request, $scanToWinCampaign->id));

        return response()->json($this->enrich($scanToWinCampaign->fresh('customDomain')));
    }

    public function destroy(Request $request, ScanToWinCampaign $scanToWinCampaign): JsonResponse
    {
        $this->authorizeOwner($request, $scanToWinCampaign);
        $scanToWinCampaign->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, ScanToWinCampaign $scanToWinCampaign): JsonResponse
    {
        $this->authorizeOwner($request, $scanToWinCampaign);
        $scanToWinCampaign->update(['is_active' => ! $scanToWinCampaign->is_active]);

        return response()->json($this->enrich($scanToWinCampaign->fresh('customDomain')));
    }

    public function play(Request $request, string $slug): JsonResponse
    {
        $campaign = ScanToWinCampaign::where('slug', $slug)->first();

        if (! $campaign) {
            abort(404, __('messages.campaign_not_found'));
        }

        if (! $campaign->is_active) {
            abort(403, __('messages.campaign_not_active'));
        }

        if ($campaign->starts_at && now()->lt($campaign->starts_at)) {
            return response()->json(['message' => __('messages.campaign_not_started')], 422);
        }

        if ($campaign->ends_at && now()->gt($campaign->ends_at)) {
            return response()->json(['message' => __('messages.campaign_ended')], 422);
        }

        $ipHash = hash('sha256', $request->ip().'|'.$request->userAgent());

        if ($campaign->max_plays_per_day > 0) {
            $todayPlays = ScanToWinPlay::where('scan_to_win_campaign_id', $campaign->id)
                ->where('ip_hash', $ipHash)
                ->whereDate('created_at', today())
                ->count();

            if ($todayPlays >= $campaign->max_plays_per_day) {
                return response()->json([
                    'message' => __('messages.daily_play_limit'),
                    'won' => false,
                ], 429);
            }
        }

        $prizes = collect($campaign->prizes ?? [])->map(function ($prize) {
            return array_merge([
                'name' => '',
                'description' => '',
                'image_path' => '',
                'quantity' => 0,
                'remaining' => 0,
                'weight' => 1,
            ], $prize);
        });

        $available = $prizes->filter(fn ($p) => ($p['remaining'] ?? 0) > 0 && ($p['weight'] ?? 0) > 0);
        $totalWeight = $available->sum(fn ($p) => (int) ($p['weight'] ?? 1));
        $winThreshold = $totalWeight > 0 ? min(100, max(1, (int) round($totalWeight / max(1, $prizes->count()) * 10))) : 0;
        $won = $totalWeight > 0 && random_int(1, 100) <= $winThreshold;

        $prizeWon = null;
        if ($won && $available->isNotEmpty()) {
            $roll = random_int(1, $totalWeight);
            $cursor = 0;
            foreach ($available as $prize) {
                $cursor += (int) ($prize['weight'] ?? 1);
                if ($roll <= $cursor) {
                    $prizeWon = $prize;
                    break;
                }
            }
        }

        if ($prizeWon) {
            $updatedPrizes = $prizes->map(function ($p) use ($prizeWon) {
                if ($p['name'] === $prizeWon['name']) {
                    $p['remaining'] = max(0, (int) ($p['remaining'] ?? 0) - 1);
                }

                return $p;
            })->values()->all();

            $campaign->update([
                'prizes' => $updatedPrizes,
                'total_wins' => $campaign->total_wins + 1,
            ]);
        }

        $campaign->increment('total_plays');

        ScanToWinPlay::create([
            'scan_to_win_campaign_id' => $campaign->id,
            'ip_hash' => $ipHash,
            'won' => (bool) $prizeWon,
            'prize_name' => $prizeWon['name'] ?? null,
        ]);

        if ($campaign->user->canScan()) {
            $campaign->user->incrementScans();
        }

        return response()->json([
            'won' => (bool) $prizeWon,
            'prize' => $prizeWon ? [
                'name' => $prizeWon['name'],
                'description' => $prizeWon['description'] ?? '',
                'image_path' => $prizeWon['image_path'] ?? '',
            ] : null,
            'message' => $prizeWon
                ? ($campaign->win_message ?: __('messages.default_win_message'))
                : ($campaign->lose_message ?: __('messages.default_lose_message')),
        ]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('scan_to_win_campaigns')->ignore($ignoreId)],
            'name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'template' => 'nullable|in:instant,wheel,scratch',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'max_plays_per_day' => 'nullable|integer|min:0|max:100',
            'win_message' => 'nullable|string|max:500',
            'lose_message' => 'nullable|string|max:500',
            'terms' => 'nullable|string|max:5000',
            'prizes' => 'nullable|array',
            'prizes.*.name' => 'required_with:prizes|string|max:255',
            'prizes.*.description' => 'nullable|string|max:500',
            'prizes.*.image_path' => 'nullable|string|max:500',
            'prizes.*.quantity' => 'nullable|integer|min:0',
            'prizes.*.remaining' => 'nullable|integer|min:0',
            'prizes.*.weight' => 'nullable|integer|min:1|max:100',
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

    private function enrich(ScanToWinCampaign $campaign): ScanToWinCampaign
    {
        $campaign->setAttribute('campaign_url', $this->domains->scanToWinUrl(
            $campaign->user,
            $campaign->slug,
            $campaign->custom_domain_id
        ));
        $campaign->setAttribute('domain_label', $campaign->customDomain?->domain);

        return $campaign;
    }

    private function authorizeOwner(Request $request, ScanToWinCampaign $campaign): void
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
