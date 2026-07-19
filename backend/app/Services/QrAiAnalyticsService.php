<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Model;

class QrAiAnalyticsService
{
    public function insights(Model $asset, string $eventType = 'scan'): array
    {
        $events = AnalyticsEvent::query()
            ->where('eventable_type', $asset::class)
            ->where('eventable_id', $asset->id)
            ->where('event_type', $eventType)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $total = $events->count();
        $byCountry = $events->groupBy(fn ($e) => $e->country ?: 'Unknown')->map->count()->sortDesc();
        $byDay = $events->groupBy(fn ($e) => $e->created_at->format('Y-m-d'))->map->count()->sortKeys();
        $byReferrer = $events->groupBy(fn ($e) => $e->referrer ?: 'Direct')->map->count()->sortDesc();

        $recommendations = $this->buildRecommendations($asset, $total, $byCountry, $byDay, $byReferrer);
        $score = $this->performanceScore($total, $byDay);

        return [
            'score' => $score,
            'score_label' => $this->scoreLabel($score),
            'summary' => $this->buildSummary($asset, $total, $byCountry, $eventType),
            'recommendations' => $recommendations,
            'highlights' => [
                'top_country' => $byCountry->keys()->first(),
                'top_referrer' => $byReferrer->keys()->first(),
                'peak_day' => $byDay->sortDesc()->keys()->first(),
                'trend' => $this->trend($byDay),
            ],
        ];
    }

    private function buildSummary(Model $asset, int $total, $byCountry, string $eventType): string
    {
        $name = $asset->name ?? $asset->title ?? 'QR asset';
        $top = $byCountry->keys()->first() ?? 'unknown regions';
        $verb = $eventType === 'click' ? 'clicks' : 'scans';

        if ($total === 0) {
            return __('messages.ai_analytics_no_data', ['name' => $name]);
        }

        return __('messages.ai_analytics_summary', [
            'name' => $name,
            'total' => $total,
            'verb' => $verb,
            'country' => $top,
        ]);
    }

    private function buildRecommendations(Model $asset, int $total, $byCountry, $byDay, $byReferrer): array
    {
        $recs = [];

        if ($total < 10) {
            $recs[] = ['type' => 'growth', 'title' => __('messages.ai_rec_low_traffic_title'), 'detail' => __('messages.ai_rec_low_traffic_detail')];
        }

        if ($byCountry->count() === 1 && $total > 20) {
            $recs[] = ['type' => 'routing', 'title' => __('messages.ai_rec_geo_title'), 'detail' => __('messages.ai_rec_geo_detail', ['country' => $byCountry->keys()->first()])];
        }

        if ($this->trend($byDay) === 'declining') {
            $recs[] = ['type' => 'engagement', 'title' => __('messages.ai_rec_decline_title'), 'detail' => __('messages.ai_rec_decline_detail')];
        }

        $mobileHint = $byReferrer->has('Direct') && $total > 15;
        if ($mobileHint) {
            $recs[] = ['type' => 'funnel', 'title' => __('messages.ai_rec_funnel_title'), 'detail' => __('messages.ai_rec_funnel_detail')];
        }

        if (empty($asset->routing_rules)) {
            $recs[] = ['type' => 'routing', 'title' => __('messages.ai_rec_routing_title'), 'detail' => __('messages.ai_rec_routing_detail')];
        }

        $security = $asset->security ?? [];
        if (empty($security['signed']) && $total > 50) {
            $recs[] = ['type' => 'security', 'title' => __('messages.ai_rec_signed_title'), 'detail' => __('messages.ai_rec_signed_detail')];
        }

        if (empty($recs)) {
            $recs[] = ['type' => 'optimize', 'title' => __('messages.ai_rec_performing_title'), 'detail' => __('messages.ai_rec_performing_detail')];
        }

        return $recs;
    }

    private function performanceScore(int $total, $byDay): int
    {
        if ($total === 0) {
            return 0;
        }

        $score = min(40, $total);
        $score += min(30, $byDay->count() * 5);
        $score += $this->trend($byDay) === 'growing' ? 20 : ($this->trend($byDay) === 'stable' ? 10 : 0);
        $score += $total > 100 ? 10 : ($total > 25 ? 5 : 0);

        return min(100, $score);
    }

    private function scoreLabel(int $score): string
    {
        return match (true) {
            $score >= 75 => 'excellent',
            $score >= 50 => 'good',
            $score >= 25 => 'fair',
            default => 'needs_attention',
        };
    }

    private function trend($byDay): string
    {
        $values = $byDay->values()->all();
        if (count($values) < 2) {
            return 'stable';
        }
        $recent = array_slice($values, -3);
        $older = array_slice($values, 0, max(1, count($values) - 3));
        $recentAvg = array_sum($recent) / count($recent);
        $olderAvg = array_sum($older) / count($older);

        if ($recentAvg > $olderAvg * 1.2) {
            return 'growing';
        }
        if ($recentAvg < $olderAvg * 0.8) {
            return 'declining';
        }

        return 'stable';
    }
}
