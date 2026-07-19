<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'is_admin', 'plan', 'scans_this_month', 'scans_reset_at', 'stripe_customer_id', 'stripe_subscription_id', 'stripe_subscription_status'])]
#[Hidden(['password', 'remember_token', 'stripe_customer_id', 'stripe_subscription_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const PLAN_LIMITS = [
        'free' => ['qr_codes' => 1, 'short_links' => 3, 'business_cards' => 1, 'digital_pages' => 1, 'digital_menus' => 1, 'digital_badges' => 1, 'digital_certificates' => 1, 'digital_events' => 1, 'digital_tickets' => 1, 'scan_to_win' => 1, 'forms' => 1, 'scans' => 100, 'analytics' => false],
        'starter' => ['qr_codes' => 10, 'short_links' => -1, 'business_cards' => 5, 'digital_pages' => 5, 'digital_menus' => 3, 'digital_badges' => 5, 'digital_certificates' => 5, 'digital_events' => 5, 'digital_tickets' => 5, 'scan_to_win' => 3, 'forms' => 5, 'scans' => 5000, 'analytics' => 'basic'],
        'pro' => ['qr_codes' => -1, 'short_links' => -1, 'business_cards' => -1, 'digital_pages' => -1, 'digital_menus' => -1, 'digital_badges' => -1, 'digital_certificates' => -1, 'digital_events' => -1, 'digital_tickets' => -1, 'scan_to_win' => -1, 'forms' => -1, 'scans' => 50000, 'analytics' => 'full', 'custom_domains' => 1],
        'business' => ['qr_codes' => -1, 'short_links' => -1, 'business_cards' => -1, 'digital_pages' => -1, 'digital_menus' => -1, 'digital_badges' => -1, 'digital_certificates' => -1, 'digital_events' => -1, 'digital_tickets' => -1, 'scan_to_win' => -1, 'forms' => -1, 'scans' => -1, 'analytics' => 'full', 'custom_domains' => -1],
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'scans_reset_at' => 'datetime',
        ];
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function shortLinks(): HasMany
    {
        return $this->hasMany(ShortLink::class);
    }

    public function businessCards(): HasMany
    {
        return $this->hasMany(BusinessCard::class);
    }

    public function digitalPages(): HasMany
    {
        return $this->hasMany(DigitalPage::class);
    }

    public function digitalMenus(): HasMany
    {
        return $this->hasMany(DigitalMenu::class);
    }

    public function digitalBadges(): HasMany
    {
        return $this->hasMany(DigitalBadge::class);
    }

    public function digitalCertificates(): HasMany
    {
        return $this->hasMany(DigitalCertificate::class);
    }

    public function digitalEvents(): HasMany
    {
        return $this->hasMany(DigitalEvent::class);
    }

    public function digitalTickets(): HasMany
    {
        return $this->hasMany(DigitalTicket::class);
    }

    public function scanToWinCampaigns(): HasMany
    {
        return $this->hasMany(ScanToWinCampaign::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function qrFunnels(): HasMany
    {
        return $this->hasMany(QrFunnel::class);
    }

    public function customDomains(): HasMany
    {
        return $this->hasMany(CustomDomain::class);
    }

    public function planLimits(): array
    {
        return self::PLAN_LIMITS[$this->plan] ?? self::PLAN_LIMITS['free'];
    }

    public function canCreate(string $resource): bool
    {
        $limits = $this->planLimits();
        $limit = $limits[$resource] ?? 0;

        if ($limit === -1) {
            return true;
        }

        return match ($resource) {
            'qr_codes' => $this->qrCodes()->count() < $limit,
            'short_links' => $this->shortLinks()->count() < $limit,
            'business_cards' => $this->businessCards()->count() < $limit,
            'digital_pages' => $this->digitalPages()->count() < $limit,
            'digital_menus' => $this->digitalMenus()->count() < $limit,
            'digital_badges' => $this->digitalBadges()->count() < $limit,
            'digital_certificates' => $this->digitalCertificates()->count() < $limit,
            'digital_events' => $this->digitalEvents()->count() < $limit,
            'digital_tickets' => $this->digitalTickets()->count() < $limit,
            'scan_to_win' => $this->scanToWinCampaigns()->count() < $limit,
            'forms' => $this->forms()->count() < $limit,
            default => false,
        };
    }

    public function canUsePremiumEventTemplates(): bool
    {
        return $this->plan !== 'free';
    }

    public function canScan(): bool
    {
        $limit = $this->planLimits()['scans'];

        if ($limit === -1) {
            return true;
        }

        $this->resetMonthlyScansIfNeeded();

        return $this->scans_this_month < $limit;
    }

    public function incrementScans(): void
    {
        $this->resetMonthlyScansIfNeeded();
        $this->increment('scans_this_month');
    }

    public function resetMonthlyScansIfNeeded(): void
    {
        if (! $this->scans_reset_at || $this->scans_reset_at->isPast()) {
            $this->update([
                'scans_this_month' => 0,
                'scans_reset_at' => now()->addMonth(),
            ]);
        }
    }
}
