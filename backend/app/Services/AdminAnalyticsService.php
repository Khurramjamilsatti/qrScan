<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\BusinessCard;
use App\Models\CustomDomain;
use App\Models\DigitalBadge;
use App\Models\DigitalMenu;
use App\Models\DigitalPage;
use App\Models\DigitalTicket;
use App\Models\Form;
use App\Models\QrCode;
use App\Models\ScanToWinCampaign;
use App\Models\ScanToWinPlay;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminAnalyticsService
{
    public static function platformOverview(): array
    {
        $appUsers = User::where('is_admin', false);

        $stats = [
            'users' => (clone $appUsers)->count(),
            'qr_codes' => QrCode::count(),
            'short_links' => ShortLink::count(),
            'business_cards' => BusinessCard::count(),
            'digital_pages' => DigitalPage::count(),
            'digital_menus' => DigitalMenu::count(),
            'digital_badges' => DigitalBadge::count(),
            'digital_tickets' => DigitalTicket::count(),
            'scan_to_win' => ScanToWinCampaign::count(),
            'forms' => Form::count(),
            'custom_domains' => CustomDomain::count(),
            'total_scans' => QrCode::sum('scan_count'),
            'total_clicks' => ShortLink::sum('click_count'),
            'total_views' => BusinessCard::sum('view_count')
                + DigitalPage::sum('view_count')
                + DigitalMenu::sum('view_count')
                + DigitalBadge::sum('view_count')
                + DigitalTicket::sum('view_count')
                + ScanToWinCampaign::sum('view_count')
                + Form::sum('view_count'),
            'form_submissions' => Form::sum('submission_count'),
            'scan_to_win_plays' => ScanToWinPlay::count(),
            'analytics_events' => AnalyticsEvent::count(),
            'scans_this_month' => (clone $appUsers)->sum('scans_this_month'),
        ];

        $plans = User::where('is_admin', false)
            ->selectRaw('plan, count(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $recentActivity = AnalyticsEvent::with('user:id,name,email')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $eventsByType = AnalyticsEvent::selectRaw('event_type, count(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type');

        $eventsByDay = AnalyticsEvent::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as day, count(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $topCountries = AnalyticsEvent::whereNotNull('country')
            ->selectRaw('country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'country');

        $topUsers = User::where('is_admin', false)
            ->withCount(['qrCodes', 'shortLinks', 'businessCards', 'digitalPages', 'digitalMenus', 'digitalBadges', 'digitalTickets', 'scanToWinCampaigns', 'forms'])
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'plan' => $user->plan,
                'total_assets' => $user->qr_codes_count
                    + $user->short_links_count
                    + $user->business_cards_count
                    + $user->digital_pages_count
                    + $user->digital_menus_count
                    + $user->digital_badges_count
                    + $user->digital_tickets_count
                    + $user->scan_to_win_campaigns_count
                    + $user->forms_count,
            ])
            ->sortByDesc('total_assets')
            ->take(8)
            ->values();

        return [
            'stats' => $stats,
            'plans' => $plans,
            'recent_activity' => $recentActivity,
            'events_by_type' => $eventsByType,
            'events_by_day' => $eventsByDay,
            'top_countries' => $topCountries,
            'top_users' => $topUsers,
        ];
    }

    public static function forUser(User $user): array
    {
        $user->loadCount([
            'qrCodes', 'shortLinks', 'businessCards', 'digitalPages', 'digitalMenus',
            'digitalBadges', 'digitalTickets', 'scanToWinCampaigns', 'forms', 'customDomains',
        ]);

        $events = AnalyticsEvent::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $stats = [
            'qr_codes' => $user->qr_codes_count,
            'short_links' => $user->short_links_count,
            'business_cards' => $user->business_cards_count,
            'digital_pages' => $user->digital_pages_count,
            'digital_menus' => $user->digital_menus_count,
            'digital_badges' => $user->digital_badges_count,
            'digital_tickets' => $user->digital_tickets_count,
            'scan_to_win' => $user->scan_to_win_campaigns_count,
            'forms' => $user->forms_count,
            'custom_domains' => $user->custom_domains_count,
            'total_scans' => $user->qrCodes()->sum('scan_count'),
            'total_clicks' => $user->shortLinks()->sum('click_count'),
            'total_views' => $user->businessCards()->sum('view_count')
                + $user->digitalPages()->sum('view_count')
                + $user->digitalMenus()->sum('view_count')
                + $user->digitalBadges()->sum('view_count')
                + $user->digitalTickets()->sum('view_count')
                + $user->scanToWinCampaigns()->sum('view_count')
                + $user->forms()->sum('view_count'),
            'scans_this_month' => $user->scans_this_month,
            'analytics_events' => $events->count(),
        ];

        return [
            'user' => $user,
            'stats' => $stats,
            'limits' => $user->planLimits(),
            'recent_activity' => $events->take(15),
            'analytics' => [
                'by_type' => self::groupCount($events, 'event_type'),
                'by_country' => self::groupCount($events->whereNotNull('country'), 'country')->take(10),
                'by_day' => $events->groupBy(fn ($e) => $e->created_at->format('Y-m-d'))->map->count()->sortKeys(),
            ],
            'resources' => [
                'qr_codes' => $user->qrCodes()->select('id', 'name', 'code', 'scan_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'short_links' => $user->shortLinks()->select('id', 'title', 'slug', 'click_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'business_cards' => $user->businessCards()->select('id', 'full_name', 'slug', 'view_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'digital_pages' => $user->digitalPages()->select('id', 'title', 'slug', 'view_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'digital_menus' => $user->digitalMenus()->select('id', 'title', 'slug', 'view_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'digital_badges' => $user->digitalBadges()->select('id', 'title', 'slug', 'view_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'digital_tickets' => $user->digitalTickets()->select('id', 'title', 'slug', 'view_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'scan_to_win' => $user->scanToWinCampaigns()->select('id', 'name', 'slug', 'view_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
                'forms' => $user->forms()->select('id', 'title', 'slug', 'view_count', 'submission_count', 'is_active', 'created_at')->latest()->limit(10)->get(),
            ],
        ];
    }

    private static function groupCount(Collection $items, string $key): Collection
    {
        return $items->groupBy($key)->map->count()->sortDesc();
    }
}
