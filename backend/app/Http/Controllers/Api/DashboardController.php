<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadCount(['qrCodes', 'shortLinks', 'businessCards', 'digitalPages', 'digitalMenus', 'digitalBadges', 'digitalCertificates', 'digitalEvents', 'digitalTickets', 'scanToWinCampaigns', 'forms']);

        $recentEvents = AnalyticsEvent::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalScans = $user->qrCodes()->sum('scan_count');
        $totalClicks = $user->shortLinks()->sum('click_count');
        $totalViews = $user->businessCards()->sum('view_count')
            + $user->digitalPages()->sum('view_count')
            + $user->digitalMenus()->sum('view_count')
            + $user->digitalEvents()->sum('view_count')
            + $user->digitalBadges()->sum('view_count')
            + $user->digitalCertificates()->sum('view_count')
            + $user->digitalTickets()->sum('view_count')
            + $user->scanToWinCampaigns()->sum('view_count')
            + $user->forms()->sum('view_count');

        return response()->json([
            'stats' => [
                'qr_codes' => $user->qr_codes_count,
                'short_links' => $user->short_links_count,
                'business_cards' => $user->business_cards_count,
                'digital_pages' => $user->digital_pages_count,
                'digital_menus' => $user->digital_menus_count,
                'digital_events' => $user->digital_events_count,
                'digital_badges' => $user->digital_badges_count,
                'digital_certificates' => $user->digital_certificates_count,
                'digital_tickets' => $user->digital_tickets_count,
                'scan_to_win' => $user->scan_to_win_campaigns_count,
                'forms' => $user->forms_count,
                'total_scans' => $totalScans,
                'total_clicks' => $totalClicks,
                'total_card_views' => $totalViews,
                'scans_this_month' => $user->scans_this_month,
            ],
            'limits' => $user->planLimits(),
            'plan' => $user->plan,
            'recent_activity' => $recentEvents,
        ]);
    }
}
