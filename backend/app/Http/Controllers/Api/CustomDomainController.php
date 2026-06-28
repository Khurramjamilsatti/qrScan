<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Services\CustomDomainVerificationService;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomDomainController extends Controller
{
    public function __construct(
        private DomainUrlService $domains,
        private CustomDomainVerificationService $verification,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $domains = $user->customDomains()
            ->orderByDesc('is_primary')
            ->get()
            ->map(function (CustomDomain $domain) {
                $payload = $domain->toArray();
                if (! $domain->is_verified) {
                    $payload['dns'] = $this->domains->dnsInstructions($domain);
                }

                return $payload;
            });

        return response()->json([
            'domains' => $domains,
            'can_use' => $this->domains->canUseCustomDomains($user),
            'can_add' => $this->domains->canAddDomain($user),
            'limit' => $this->domains->domainLimit($user),
            'default_base_url' => rtrim(config('app.frontend_url', config('app.url')), '/'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->domains->canAddDomain($user)) {
            return response()->json([
                'message' => __('messages.custom_domains_pro_required'),
            ], 403);
        }

        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:custom_domains,domain|regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i',
            'is_primary' => 'boolean',
        ]);

        $domain = $user->customDomains()->create([
            'domain' => strtolower($validated['domain']),
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        if ($domain->is_primary || $user->customDomains()->verified()->count() === 0) {
            $user->customDomains()->where('id', '!=', $domain->id)->update(['is_primary' => false]);
            $domain->update(['is_primary' => true]);
        }

        return response()->json([
            'domain' => $domain->fresh(),
            'dns' => $this->domains->dnsInstructions($domain),
        ], 201);
    }

    public function verify(Request $request, CustomDomain $customDomain): JsonResponse
    {
        $this->authorizeOwner($request, $customDomain);

        if ($customDomain->is_verified) {
            return response()->json([
                'domain' => $customDomain,
                'message' => __('messages.domain_already_verified'),
                'checks' => ['txt' => true, 'cname' => true],
            ]);
        }

        $result = $this->verification->check($customDomain);

        if (! $result['verified']) {
            return response()->json([
                'message' => $result['message'],
                'checks' => $result['checks'],
                'warnings' => $result['warnings'] ?? [],
                'dns' => $result['dns'] ?? $this->domains->dnsInstructions($customDomain),
            ], 422);
        }

        $customDomain->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'domain' => $customDomain->fresh(),
            'message' => $result['message'],
            'checks' => $result['checks'],
            'warnings' => $result['warnings'] ?? [],
        ]);
    }

    public function verificationStatus(Request $request, CustomDomain $customDomain): JsonResponse
    {
        $this->authorizeOwner($request, $customDomain);

        if ($customDomain->is_verified) {
            return response()->json([
                'verified' => true,
                'checks' => ['txt' => true, 'cname' => true],
                'warnings' => [],
            ]);
        }

        $result = $this->verification->check($customDomain);

        return response()->json([
            'verified' => $result['verified'],
            'checks' => $result['checks'],
            'warnings' => $result['warnings'] ?? [],
            'message' => $result['message'],
            'dns' => $this->domains->dnsInstructions($customDomain),
        ]);
    }

    public function setPrimary(Request $request, CustomDomain $customDomain): JsonResponse
    {
        $this->authorizeOwner($request, $customDomain);

        if (! $customDomain->is_verified) {
            return response()->json(['message' => __('messages.verify_before_primary')], 422);
        }

        $request->user()->customDomains()->update(['is_primary' => false]);
        $customDomain->update(['is_primary' => true]);

        return response()->json($customDomain);
    }

    public function destroy(Request $request, CustomDomain $customDomain): JsonResponse
    {
        $this->authorizeOwner($request, $customDomain);
        $customDomain->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function dns(Request $request, CustomDomain $customDomain): JsonResponse
    {
        $this->authorizeOwner($request, $customDomain);

        return response()->json($this->domains->dnsInstructions($customDomain));
    }

    private function authorizeOwner(Request $request, CustomDomain $domain): void
    {
        if ($domain->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
