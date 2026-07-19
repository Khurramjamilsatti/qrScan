<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Services\QrAiAnalyticsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private QrAiAnalyticsService $aiAnalytics) {}

    public function show(Request $request, string $type, int $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $this->authorizeOwner($request, $model);

        $events = AnalyticsEvent::where('eventable_type', $model::class)
            ->where('eventable_id', $model->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $byCountry = $events->groupBy('country')->map->count()->sortDesc()->take(10);
        $byDay = $events->groupBy(fn ($e) => $e->created_at->format('Y-m-d'))->map->count()->sortKeys();
        $byReferrer = $events->whereNotNull('referrer')->groupBy('referrer')->map->count()->sortDesc()->take(5);

        return response()->json([
            'total' => $events->count(),
            'by_country' => $byCountry,
            'by_day' => $byDay,
            'by_referrer' => $byReferrer,
            'recent' => $events->take(20),
        ]);
    }

    public function insights(Request $request, string $type, int $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $this->authorizeOwner($request, $model);

        $eventType = in_array($type, ['short-links'], true) ? 'click' : 'scan';

        return response()->json($this->aiAnalytics->insights($model, $eventType));
    }

    private function resolveModel(string $type, int $id): Model
    {
        return match ($type) {
            'qr-codes' => \App\Models\QrCode::findOrFail($id),
            'short-links' => \App\Models\ShortLink::findOrFail($id),
            'business-cards' => \App\Models\BusinessCard::findOrFail($id),
            'digital-pages' => \App\Models\DigitalPage::findOrFail($id),
            'digital-menus' => \App\Models\DigitalMenu::findOrFail($id),
            'digital-events' => \App\Models\DigitalEvent::findOrFail($id),
            'digital-badges' => \App\Models\DigitalBadge::findOrFail($id),
            'digital-certificates' => \App\Models\DigitalCertificate::findOrFail($id),
            'digital-tickets' => \App\Models\DigitalTicket::findOrFail($id),
            'scan-to-win' => \App\Models\ScanToWinCampaign::findOrFail($id),
            'forms' => \App\Models\Form::findOrFail($id),
            default => abort(404),
        };
    }

    private function authorizeOwner(Request $request, Model $model): void
    {
        if ($model->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
