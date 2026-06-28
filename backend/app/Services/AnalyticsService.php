<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AnalyticsService
{
    public function track(Model $model, string $eventType, Request $request, int $userId): void
    {
        AnalyticsEvent::create([
            'user_id' => $userId,
            'eventable_type' => $model::class,
            'eventable_id' => $model->id,
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'country' => $request->header('CF-IPCountry', 'Unknown'),
            'city' => null,
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('Referer'),
            'created_at' => now(),
        ]);
    }
}
