<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalTicket;
use App\Services\DomainUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DigitalTicketController extends Controller
{
    public function __construct(private DomainUrlService $domains) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->digitalTickets()->with('customDomain')->latest()->get()
            ->map(fn ($t) => $this->enrich($t));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('digital_tickets')) {
            return response()->json(['message' => __('messages.digital_ticket_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $ticket = $user->digitalTickets()->create($validated);

        return response()->json($this->enrich($ticket->load('customDomain')), 201);
    }

    public function show(Request $request, DigitalTicket $digitalTicket): JsonResponse
    {
        $this->authorizeOwner($request, $digitalTicket);

        return response()->json($this->enrich($digitalTicket->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, DigitalTicket $digitalTicket): JsonResponse
    {
        $this->authorizeOwner($request, $digitalTicket);

        $digitalTicket->update($this->validated($request, $digitalTicket->id));

        return response()->json($this->enrich($digitalTicket->fresh('customDomain')));
    }

    public function destroy(Request $request, DigitalTicket $digitalTicket): JsonResponse
    {
        $this->authorizeOwner($request, $digitalTicket);
        $digitalTicket->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, DigitalTicket $digitalTicket): JsonResponse
    {
        $this->authorizeOwner($request, $digitalTicket);
        $digitalTicket->update(['is_active' => ! $digitalTicket->is_active]);

        return response()->json($this->enrich($digitalTicket->fresh('customDomain')));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('digital_tickets')->ignore($ignoreId)],
            'event_name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'event_date' => 'nullable|string|max:100',
            'event_time' => 'nullable|string|max:100',
            'venue' => 'nullable|string|max:255',
            'holder_name' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'holder_email' => 'nullable|email|max:255',
            'ticket_type' => 'nullable|in:general,vip,early-bird,backstage,student,other',
            'seat_section' => 'nullable|string|max:100',
            'seat_row' => 'nullable|string|max:50',
            'seat_number' => 'nullable|string|max:50',
            'order_id' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'template' => 'nullable|in:concert,conference,transit,sports',
            'terms' => 'nullable|string|max:5000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'status' => 'nullable|in:valid,used,expired,cancelled',
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

    private function enrich(DigitalTicket $ticket): DigitalTicket
    {
        $ticket->setAttribute('ticket_url', $this->domains->ticketUrl(
            $ticket->user,
            $ticket->slug,
            $ticket->custom_domain_id
        ));
        $ticket->setAttribute('domain_label', $ticket->customDomain?->domain);

        return $ticket;
    }

    private function authorizeOwner(Request $request, DigitalTicket $ticket): void
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
