<?php

namespace App\Services;

use App\Models\QrAccessLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class QrSecurityService
{
    public function defaultSettings(): array
    {
        return [
            'signed' => false,
            'one_time_access' => false,
            'password_enabled' => false,
            'password' => null,
        ];
    }

    public function normalizeSettings(?array $settings): array
    {
        return array_merge($this->defaultSettings(), $settings ?? []);
    }

    public function ensureSigningSecret(Model $asset): string
    {
        if (! empty($asset->signing_secret)) {
            return $asset->signing_secret;
        }

        $secret = bin2hex(random_bytes(16));
        $asset->update(['signing_secret' => $secret]);

        return $secret;
    }

    public function signUrl(string $baseUrl, Model $asset): string
    {
        $settings = $this->normalizeSettings($asset->security ?? []);
        if (empty($settings['signed'])) {
            return $baseUrl;
        }

        $secret = $this->ensureSigningSecret($asset);
        $expires = $asset->expires_at?->timestamp ?? (now()->addYear()->timestamp);
        $payload = ($asset->code ?? $asset->slug ?? $asset->id).'|'.$expires;
        $sig = hash_hmac('sha256', $payload, $secret);

        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl.$separator.http_build_query(['sig' => $sig, 'exp' => $expires]);
    }

    public function validateAccess(Model $asset, Request $request): ?array
    {
        $settings = $this->normalizeSettings($asset->security ?? []);

        if ($asset->expires_at && now()->gt($asset->expires_at)) {
            return $this->deny('expired', __('messages.qr_expired'));
        }

        $maxScans = (int) ($asset->max_scans ?? 0);
        $scanCount = (int) ($asset->scan_count ?? $asset->click_count ?? 0);
        if ($maxScans > 0 && $scanCount >= $maxScans) {
            return $this->deny('max_scans', __('messages.qr_max_scans_reached'));
        }

        if (! empty($settings['signed'])) {
            $sig = $request->query('sig');
            $exp = (int) $request->query('exp', 0);
            if (! $sig || ! $exp || $exp < now()->timestamp) {
                return $this->deny('invalid_signature', __('messages.qr_invalid_signature'));
            }
            $secret = $asset->signing_secret;
            if (! $secret) {
                return $this->deny('invalid_signature', __('messages.qr_invalid_signature'));
            }
            $key = $asset->code ?? $asset->slug ?? (string) $asset->id;
            $expected = hash_hmac('sha256', $key.'|'.$exp, $secret);
            if (! hash_equals($expected, $sig)) {
                return $this->deny('invalid_signature', __('messages.qr_invalid_signature'));
            }
        }

        if (! empty($settings['password_enabled'])) {
            $password = $request->query('pw', '');
            $hash = $settings['password_hash'] ?? null;
            if (! $hash || ! Hash::check($password, $hash)) {
                return $this->deny('password_required', __('messages.qr_password_required'));
            }
        }

        if (! empty($settings['one_time_access'])) {
            $ipHash = $this->ipHash($request);
            $exists = QrAccessLog::query()
                ->where('accessable_type', $asset::class)
                ->where('accessable_id', $asset->id)
                ->where('ip_hash', $ipHash)
                ->exists();
            if ($exists) {
                return $this->deny('one_time_used', __('messages.qr_one_time_used'));
            }
        }

        return null;
    }

    private function deny(string $code, string $message): array
    {
        return ['code' => $code, 'message' => $message];
    }

    public function recordAccess(Model $asset, Request $request, string $type = 'scan'): void
    {
        QrAccessLog::create([
            'accessable_type' => $asset::class,
            'accessable_id' => $asset->id,
            'ip_hash' => $this->ipHash($request),
            'access_type' => $type,
            'created_at' => now(),
        ]);
    }

    public function prepareSettingsForSave(array $settings, ?array $existing = null): array
    {
        $merged = array_merge($this->defaultSettings(), $existing ?? [], $settings);
        $out = [
            'signed' => (bool) ($merged['signed'] ?? false),
            'one_time_access' => (bool) ($merged['one_time_access'] ?? false),
            'password_enabled' => (bool) ($merged['password_enabled'] ?? false),
        ];

        if (! empty($merged['password']) && $merged['password'] !== '********') {
            $out['password_hash'] = Hash::make($merged['password']);
        } elseif (! empty($out['password_enabled']) && ! empty($existing['password_hash'])) {
            $out['password_hash'] = $existing['password_hash'];
        }

        return $out;
    }

    public function ipHash(Request $request): string
    {
        return hash('sha256', $request->ip().'|'.$request->userAgent());
    }
}
