<?php

namespace App\Services;

use App\Models\QrAccessLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class QrRoutingEngineService
{
    public function __construct(private QrSecurityService $security) {}

    public function resolveDestination(Model $asset, Request $request, string $fallbackUrl): string
    {
        $rules = collect($asset->routing_rules ?? [])
            ->filter(fn ($r) => ! empty($r['enabled']) && ! empty($r['destination_url']))
            ->sortBy(fn ($r) => $r['priority'] ?? 99)
            ->values();

        foreach ($rules as $rule) {
            if ($this->matches($rule, $asset, $request)) {
                return $rule['destination_url'];
            }
        }

        return $fallbackUrl;
    }

    private function matches(array $rule, Model $asset, Request $request): bool
    {
        $conditions = $rule['conditions'] ?? [];

        if (! empty($conditions['device'])) {
            $device = $this->detectDevice($request->userAgent());
            if (! in_array($device, (array) $conditions['device'], true)) {
                return false;
            }
        }

        if (! empty($conditions['country'])) {
            $country = strtoupper($request->header('CF-IPCountry', ''));
            $allowed = array_map('strtoupper', (array) $conditions['country']);
            if ($country && ! in_array($country, $allowed, true)) {
                return false;
            }
        }

        if (! empty($conditions['language'])) {
            $lang = $this->preferredLanguage($request);
            $allowed = array_map('strtolower', (array) $conditions['language']);
            if (! in_array($lang, $allowed, true)) {
                return false;
            }
        }

        if (! empty($conditions['time_start']) || ! empty($conditions['time_end'])) {
            $now = now();
            $start = $conditions['time_start'] ?? '00:00';
            $end = $conditions['time_end'] ?? '23:59';
            $time = $now->format('H:i');
            if ($time < $start || $time > $end) {
                return false;
            }
        }

        if (! empty($conditions['days'])) {
            $day = (int) now()->format('N');
            if (! in_array($day, array_map('intval', (array) $conditions['days']), true)) {
                return false;
            }
        }

        if (! empty($conditions['audience'])) {
            $ipHash = $this->security->ipHash($request);
            $seen = QrAccessLog::query()
                ->where('accessable_type', $asset::class)
                ->where('accessable_id', $asset->id)
                ->where('ip_hash', $ipHash)
                ->exists();

            if ($conditions['audience'] === 'new' && $seen) {
                return false;
            }
            if ($conditions['audience'] === 'returning' && ! $seen) {
                return false;
            }
        }

        return true;
    }

    private function detectDevice(?string $ua): string
    {
        $ua = strtolower($ua ?? '');
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }

    private function preferredLanguage(Request $request): string
    {
        $header = $request->header('Accept-Language', 'en');
        $part = explode(',', $header)[0] ?? 'en';
        $part = explode(';', $part)[0] ?? 'en';

        return strtolower(substr(trim($part), 0, 2));
    }
}
