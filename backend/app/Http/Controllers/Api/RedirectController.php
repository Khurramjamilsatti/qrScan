<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use App\Models\DigitalBadge;
use App\Models\DigitalMenu;
use App\Models\DigitalPage;
use App\Models\DigitalTicket;
use App\Models\QrCode;
use App\Models\ScanToWinCampaign;
use App\Models\ShortLink;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function shortLink(string $slug): RedirectResponse|JsonResponse
    {
        $link = ShortLink::where('slug', $slug)->where('is_active', true)->firstOrFail();

        if ($link->user->canScan()) {
            $link->increment('click_count');
            $link->user->incrementScans();
            $this->analytics->track($link, 'click', request(), $link->user_id);
        }

        return redirect()->away($link->fullDestinationUrl());
    }

    public function qrCode(string $code): RedirectResponse|JsonResponse
    {
        $qr = QrCode::where('code', $code)->where('is_active', true)->firstOrFail();

        if ($qr->user->canScan()) {
            $qr->increment('scan_count');
            $qr->user->incrementScans();
            $this->analytics->track($qr, 'scan', request(), $qr->user_id);
        }

        return redirect()->away($qr->destination_url);
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
