<?php

namespace App\Services;

use App\Models\CustomDomain;
use App\Models\User;

class DomainUrlService
{
    public function canUseCustomDomains(User $user): bool
    {
        return in_array($user->plan, ['pro', 'business'], true);
    }

    public function domainLimit(User $user): int
    {
        return match ($user->plan) {
            'pro' => 1,
            'business' => -1,
            default => 0,
        };
    }

    public function canAddDomain(User $user): bool
    {
        if (! $this->canUseCustomDomains($user)) {
            return false;
        }

        $limit = $this->domainLimit($user);

        if ($limit === -1) {
            return true;
        }

        return $user->customDomains()->count() < $limit;
    }

    public function resolveBaseUrl(User $user, ?int $customDomainId = null): string
    {
        if ($customDomainId) {
            $domain = CustomDomain::query()
                ->where('user_id', $user->id)
                ->where('id', $customDomainId)
                ->where('is_verified', true)
                ->first();

            if ($domain) {
                return $domain->baseUrl();
            }
        }

        $primary = $user->customDomains()
            ->where('is_verified', true)
            ->where('is_primary', true)
            ->first();

        if ($primary && $this->canUseCustomDomains($user)) {
            return $primary->baseUrl();
        }

        return rtrim(config('app.frontend_url', config('app.url')), '/');
    }

    public function shortLinkUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/r/'.$slug;
    }

    public function shortLinkScanUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/api/r/'.$slug;
    }

    public function qrScanUrl(User $user, string $code, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/api/qr/'.$code;
    }

    public function cardUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/card/'.$slug;
    }

    public function pageUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/page/'.$slug;
    }

    public function menuUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/menu/'.$slug;
    }

    public function badgeUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/badge/'.$slug;
    }

    public function certificateUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/certificate/'.$slug;
    }

    public function verifyUrl(User $user, string $certificateId, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/verify/'.$certificateId;
    }

    public function ticketUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/ticket/'.$slug;
    }

    public function scanToWinUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/win/'.$slug;
    }

    public function formUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/form/'.$slug;
    }

    public function inviteUrl(User $user, string $slug, ?int $customDomainId = null): string
    {
        $base = $this->resolveBaseUrl($user, $customDomainId);

        return $base.'/invite/'.$slug;
    }

    public function dnsInstructions(CustomDomain $domain): array
    {
        $cnameTarget = config('app.cname_target', 'app.qrscan.digital');

        return [
            'type' => 'CNAME',
            'host' => $domain->domain,
            'cname_host' => $domain->domain,
            'value' => $cnameTarget,
            'txt_host' => '_qrscan.'.$domain->domain,
            'txt_value' => $domain->verification_token,
        ];
    }
}
