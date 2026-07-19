<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use App\Models\DigitalBadge;
use App\Models\DigitalCertificate;
use App\Models\DigitalEvent;
use App\Models\DigitalMenu;
use App\Models\DigitalPage;
use App\Models\DigitalTicket;
use App\Models\QrCode;
use App\Models\ScanToWinCampaign;
use App\Models\ShortLink;
use App\Services\AnalyticsService;
use App\Services\CertificatePdfService;
use App\Services\DomainUrlService;
use App\Services\QrFunnelService;
use App\Services\QrRoutingEngineService;
use App\Services\QrSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RedirectController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
        private QrSecurityService $security,
        private QrRoutingEngineService $routing,
        private QrFunnelService $funnels,
        private CertificatePdfService $certificatePdf,
    ) {}

    public function shortLink(Request $request, string $slug): RedirectResponse|JsonResponse|Response
    {
        $link = ShortLink::with('funnel.steps', 'user')->where('slug', $slug)->where('is_active', true)->firstOrFail();

        if ($denial = $this->security->validateAccess($link, $request)) {
            return $this->handleDenial($request, $denial, 'short_link', $slug);
        }

        if ($link->expires_at && now()->gt($link->expires_at)) {
            return $this->handleDenial($request, ['code' => 'expired', 'message' => __('messages.qr_expired')], 'short_link', $slug);
        }

        $destination = $this->resolveDestination($link, $request, $link->fullDestinationUrl());

        if ($link->user->canScan()) {
            $link->increment('click_count');
            $link->user->incrementScans();
            $this->analytics->track($link, 'click', $request, $link->user_id);
        }

        $this->security->recordAccess($link, $request, 'click');

        return redirect()->away($destination);
    }

    public function qrCode(Request $request, string $code): RedirectResponse|JsonResponse|Response
    {
        $qr = QrCode::with('funnel.steps', 'user')->where('code', $code)->where('is_active', true)->firstOrFail();

        if ($denial = $this->security->validateAccess($qr, $request)) {
            return $this->handleDenial($request, $denial, 'qr', $code);
        }

        $destination = $this->resolveDestination($qr, $request, $qr->destination_url);

        if ($qr->user->canScan()) {
            $qr->increment('scan_count');
            $qr->user->incrementScans();
            $this->analytics->track($qr, 'scan', $request, $qr->user_id);
        }

        $this->security->recordAccess($qr, $request, 'scan');

        if ($qr->funnel_id && $qr->funnel) {
            $qr->funnel->increment('entry_count');
        }

        return redirect()->away($destination);
    }

    private function resolveDestination($asset, Request $request, string $fallback): string
    {
        if ($asset->funnel_id && $asset->funnel) {
            $entry = $this->funnels->entryUrl($asset->funnel, $asset->user);
            if ($entry) {
                $fallback = $entry;
            }
        }

        return $this->routing->resolveDestination($asset, $request, $fallback);
    }

    private function handleDenial(Request $request, array $denial, string $type, string $identifier): JsonResponse|Response
    {
        if ($denial['code'] === 'password_required' && ! $request->expectsJson()) {
            return $this->passwordChallenge($request, $type, $identifier, $denial['message']);
        }

        return $this->denyAccess($denial['message']);
    }

    private function passwordChallenge(Request $request, string $type, string $identifier, string $message): Response
    {
        $action = $type === 'qr'
            ? url('/api/qr/'.$identifier)
            : url('/api/r/'.$identifier);

        $query = $request->query();
        unset($query['pw']);
        $hiddenFields = '';
        foreach ($query as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $hiddenFields .= '<input type="hidden" name="'.e($key).'" value="'.e((string) $value).'">';
            }
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Password required</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f8f7fc; color: #1a1333; display: grid; place-items: center; min-height: 100vh; margin: 0; }
    .card { background: #fff; border: 1px solid #e8e4f4; border-radius: 1rem; padding: 2rem; width: min(420px, 92vw); box-shadow: 0 8px 30px rgba(26,19,51,.08); }
    h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
    p { color: #6b6280; font-size: .9rem; line-height: 1.5; margin: 0 0 1.25rem; }
    label { display: block; font-size: .8125rem; font-weight: 600; margin-bottom: .375rem; }
    input[type=password] { width: 100%; padding: .75rem .875rem; border: 1px solid #d9d3ea; border-radius: .5rem; font-size: 1rem; box-sizing: border-box; }
    button { margin-top: 1rem; width: 100%; padding: .75rem; border: 0; border-radius: .5rem; background: #5b4bb7; color: #fff; font-weight: 600; cursor: pointer; }
    .error { color: #b91c1c; font-size: .8125rem; margin-bottom: .75rem; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Password required</h1>
    <p>This link is protected. Enter the password to continue.</p>
    <form method="get" action="{$action}">
      {$hiddenFields}
      <label for="pw">Password</label>
      <input id="pw" name="pw" type="password" required autofocus>
      <button type="submit">Continue</button>
    </form>
  </div>
</body>
</html>
HTML;

        $wrongPassword = $request->filled('pw');
        if ($wrongPassword) {
            $html = str_replace(
                '<p>This link is protected.',
                '<p class="error">Incorrect password. Try again.</p><p>This link is protected.',
                $html
            );
        }

        return response($html, $wrongPassword ? 401 : 403)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function denyAccess(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 403);
    }

    public function businessCard(string $slug): JsonResponse
    {
        $card = BusinessCard::where('slug', $slug)->first();

        if (! $card) {
            abort(404, __('messages.card_not_found'));
        }

        if (! $card->is_active) {
            abort(403, __('messages.card_not_published'));
        }

        if ($card->user->canScan()) {
            $card->increment('view_count');
            $card->user->incrementScans();
            $this->analytics->track($card, 'view', request(), $card->user_id);
        }

        return response()->json($card);
    }

    public function digitalPage(string $slug): JsonResponse
    {
        $page = DigitalPage::where('slug', $slug)->first();

        if (! $page) {
            abort(404, __('messages.page_not_found'));
        }

        if (! $page->is_active) {
            abort(403, __('messages.page_not_published'));
        }

        if ($page->user->canScan()) {
            $page->increment('view_count');
            $page->user->incrementScans();
            $this->analytics->track($page, 'view', request(), $page->user_id);
        }

        return response()->json($page);
    }

    public function digitalMenu(string $slug): JsonResponse
    {
        $menu = DigitalMenu::where('slug', $slug)->first();

        if (! $menu) {
            abort(404, __('messages.menu_not_found'));
        }

        if (! $menu->is_active) {
            abort(403, __('messages.menu_not_published'));
        }

        if ($menu->user->canScan()) {
            $menu->increment('view_count');
            $menu->user->incrementScans();
            $this->analytics->track($menu, 'view', request(), $menu->user_id);
        }

        return response()->json($menu);
    }

    public function digitalEvent(string $slug): JsonResponse
    {
        $event = DigitalEvent::where('slug', $slug)->first();

        if (! $event) {
            abort(404, __('messages.event_not_found'));
        }

        if (! $event->is_active) {
            abort(403, __('messages.event_not_published'));
        }

        if ($event->user->canScan()) {
            $event->increment('view_count');
            $event->user->incrementScans();
            $this->analytics->track($event, 'view', request(), $event->user_id);
        }

        return response()->json($event);
    }

    public function digitalBadge(string $slug): JsonResponse
    {
        $badge = DigitalBadge::where('slug', $slug)->first();

        if (! $badge) {
            abort(404, __('messages.badge_not_found'));
        }

        if (! $badge->is_active) {
            abort(403, __('messages.badge_not_published'));
        }

        if ($badge->user->canScan()) {
            $badge->increment('view_count');
            $badge->user->incrementScans();
            $this->analytics->track($badge, 'view', request(), $badge->user_id);
        }

        return response()->json($badge);
    }

    public function digitalCertificate(string $slug): JsonResponse
    {
        $cert = DigitalCertificate::with('user')->where('slug', $slug)->first();

        if (! $cert) {
            abort(404, __('messages.certificate_not_found'));
        }

        if (! $cert->is_active) {
            abort(403, __('messages.certificate_not_published'));
        }

        if ($cert->user->canScan()) {
            $cert->increment('view_count');
            $cert->user->incrementScans();
            $this->analytics->track($cert, 'view', request(), $cert->user_id);
        }

        return response()->json($this->enrichCertificatePublic($cert));
    }

    public function verifyCertificate(string $certificateId): JsonResponse
    {
        $cert = DigitalCertificate::with('user')->where('certificate_id', $certificateId)->first();

        if (! $cert) {
            abort(404, __('messages.certificate_not_found'));
        }

        if (! $cert->is_active) {
            abort(403, __('messages.certificate_not_published'));
        }

        if ($cert->user->canScan()) {
            $cert->increment('view_count');
            $cert->user->incrementScans();
            $this->analytics->track($cert, 'verify', request(), $cert->user_id);
        }

        return response()->json($this->enrichCertificatePublic($cert));
    }

    public function verifyCertificatePdf(string $certificateId): SymfonyResponse
    {
        $cert = DigitalCertificate::where('certificate_id', $certificateId)->firstOrFail();

        if (! $cert->is_active) {
            abort(403, __('messages.certificate_not_published'));
        }

        return $this->certificatePdf->download($cert);
    }

    private function enrichCertificatePublic(DigitalCertificate $cert): array
    {
        $domains = app(DomainUrlService::class);

        return array_merge($cert->toArray(), [
            'verify_url' => $domains->verifyUrl($cert->user, $cert->certificate_id, $cert->custom_domain_id),
            'certificate_url' => $domains->certificateUrl($cert->user, $cert->slug, $cert->custom_domain_id),
            'is_valid' => $cert->isValid(),
            'status_label' => $cert->isValid() ? 'valid' : ($cert->status === 'revoked' ? 'revoked' : 'expired'),
        ]);
    }

    public function digitalTicket(string $slug): JsonResponse
    {
        $ticket = DigitalTicket::where('slug', $slug)->first();

        if (! $ticket) {
            abort(404, __('messages.ticket_not_found'));
        }

        if (! $ticket->is_active) {
            abort(403, __('messages.ticket_not_published'));
        }

        if ($ticket->user->canScan()) {
            $ticket->increment('view_count');
            $ticket->user->incrementScans();
            $this->analytics->track($ticket, 'view', request(), $ticket->user_id);
        }

        return response()->json($ticket);
    }

    public function scanToWin(string $slug): JsonResponse
    {
        $campaign = ScanToWinCampaign::where('slug', $slug)->first();

        if (! $campaign) {
            abort(404, __('messages.campaign_not_found'));
        }

        if (! $campaign->is_active) {
            abort(403, __('messages.campaign_not_published'));
        }

        if ($campaign->user->canScan()) {
            $campaign->increment('view_count');
            $campaign->user->incrementScans();
            $this->analytics->track($campaign, 'view', request(), $campaign->user_id);
        }

        return response()->json($campaign);
    }
}