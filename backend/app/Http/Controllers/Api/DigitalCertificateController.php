<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalCertificate;
use App\Services\CertificatePdfService;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DigitalCertificateController extends Controller
{
    public function __construct(
        private DomainUrlService $domains,
        private CertificatePdfService $pdf,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->digitalCertificates()->with('customDomain')->latest()->get()
            ->map(fn ($c) => $this->enrich($c));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('digital_certificates')) {
            return response()->json(['message' => __('messages.digital_certificate_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $cert = $user->digitalCertificates()->create($validated);
        $this->pdf->generateAndStore($cert);

        return response()->json($this->enrich($cert->fresh('customDomain')), 201);
    }

    public function show(Request $request, DigitalCertificate $digitalCertificate): JsonResponse
    {
        $this->authorizeOwner($request, $digitalCertificate);

        return response()->json($this->enrich($digitalCertificate->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, DigitalCertificate $digitalCertificate): JsonResponse
    {
        $this->authorizeOwner($request, $digitalCertificate);

        $digitalCertificate->update($this->validated($request, $digitalCertificate->id));
        $this->pdf->generateAndStore($digitalCertificate->fresh());

        return response()->json($this->enrich($digitalCertificate->fresh('customDomain')));
    }

    public function destroy(Request $request, DigitalCertificate $digitalCertificate): JsonResponse
    {
        $this->authorizeOwner($request, $digitalCertificate);
        $digitalCertificate->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, DigitalCertificate $digitalCertificate): JsonResponse
    {
        $this->authorizeOwner($request, $digitalCertificate);
        $digitalCertificate->update(['is_active' => ! $digitalCertificate->is_active]);

        return response()->json($this->enrich($digitalCertificate->fresh('customDomain')));
    }

    public function revoke(Request $request, DigitalCertificate $digitalCertificate): JsonResponse
    {
        $this->authorizeOwner($request, $digitalCertificate);
        $digitalCertificate->update(['status' => 'revoked']);

        return response()->json($this->enrich($digitalCertificate->fresh('customDomain')));
    }

    public function downloadPdf(Request $request, DigitalCertificate $digitalCertificate): Response
    {
        $this->authorizeOwner($request, $digitalCertificate);

        return $this->pdf->download($digitalCertificate);
    }

    public function bulkImport(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'template' => 'required|array',
            'recipients' => 'required|array|min:1|max:500',
            'recipients.*.recipient_name' => 'required|string|max:255',
            'recipients.*.recipient_email' => 'nullable|email|max:255',
            'recipients.*.award_title' => 'nullable|string|max:255',
            'recipients.*.completion_date' => 'nullable|date',
        ]);

        $created = [];
        $template = $validated['template'];

        foreach ($validated['recipients'] as $row) {
            if (! $user->canCreate('digital_certificates')) {
                break;
            }

            $slug = \Illuminate\Support\Str::slug($row['recipient_name']).'-'.\Illuminate\Support\Str::random(5);

            $cert = $user->digitalCertificates()->create(array_merge($template, [
                'recipient_name' => $row['recipient_name'],
                'recipient_email' => $row['recipient_email'] ?? null,
                'award_title' => $row['award_title'] ?? ($template['award_title'] ?? null),
                'completion_date' => $row['completion_date'] ?? ($template['completion_date'] ?? null),
                'slug' => $slug,
                'is_active' => $template['is_active'] ?? true,
            ]));

            $this->pdf->generateAndStore($cert);
            $created[] = $this->enrich($cert->fresh('customDomain'));
        }

        return response()->json([
            'created' => count($created),
            'certificates' => $created,
        ], 201);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('digital_certificates')->ignore($ignoreId)],
            'certificate_id' => ['nullable', 'string', 'max:50', Rule::unique('digital_certificates')->ignore($ignoreId)],
            'title' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'template' => 'nullable|in:classic,formal,modern,elegant',
            'recipient_name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'recipient_email' => 'nullable|email|max:255',
            'award_title' => 'nullable|string|max:255',
            'issuer_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'completion_date' => 'nullable|date',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|in:valid,revoked',
            'settings' => 'nullable|array',
            'settings.font_family' => 'nullable|in:instrument-serif,georgia,times,dm-sans,palatino,arabic',
            'settings.text_color' => 'nullable|string|max:7',
            'settings.background_color' => 'nullable|string|max:7',
            'settings.show_dates' => 'nullable|boolean',
            'settings.show_certificate_id' => 'nullable|boolean',
            'settings.show_qr' => 'nullable|boolean',
            'theme_color' => 'nullable|string|max:7',
            'logo_path' => 'nullable|string|max:500',
            'seal_path' => 'nullable|string|max:500',
            'instructor_signature_path' => 'nullable|string|max:500',
            'organization_signature_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function enrich(DigitalCertificate $cert): DigitalCertificate
    {
        $cert->setAttribute('certificate_url', $this->domains->certificateUrl(
            $cert->user,
            $cert->slug,
            $cert->custom_domain_id
        ));
        $cert->setAttribute('verify_url', $this->domains->verifyUrl(
            $cert->user,
            $cert->certificate_id,
            $cert->custom_domain_id
        ));
        $cert->setAttribute('domain_label', $cert->customDomain?->domain);

        return $cert;
    }

    private function authorizeOwner(Request $request, DigitalCertificate $cert): void
    {
        if ($cert->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
