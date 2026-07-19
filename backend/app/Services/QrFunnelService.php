<?php

namespace App\Services;

use App\Models\QrFunnel;
use App\Models\User;

class QrFunnelService
{
    public function __construct(private DomainUrlService $domains) {}

    public function entryUrl(QrFunnel $funnel, User $user): ?string
    {
        $step = $funnel->steps()->orderBy('sort_order')->first();
        if (! $step) {
            return null;
        }

        return $this->stepUrl($step, $user);
    }

    public function stepUrl($step, User $user): ?string
    {
        $base = $this->domains->resolveBaseUrl($user);

        return match ($step->target_type) {
            'form' => $step->target_slug ? $base.'/form/'.$step->target_slug : $step->target_url,
            'page' => $step->target_slug ? $base.'/page/'.$step->target_slug : $step->target_url,
            'menu' => $step->target_slug ? $base.'/menu/'.$step->target_slug : $step->target_url,
            'card' => $step->target_slug ? $base.'/card/'.$step->target_slug : $step->target_url,
            'ticket' => $step->target_slug ? $base.'/ticket/'.$step->target_slug : $step->target_url,
            'win' => $step->target_slug ? $base.'/win/'.$step->target_slug : $step->target_url,
            default => $step->target_url,
        };
    }

    public function templates(): array
    {
        return [
            [
                'id' => 'lead_capture',
                'goal' => 'lead',
                'name' => 'Lead capture funnel',
                'description' => 'Landing → form → thank you',
                'steps' => [
                    ['step_type' => 'landing', 'title' => 'Welcome', 'target_type' => 'page', 'cta_text' => 'Get started'],
                    ['step_type' => 'form', 'title' => 'Contact details', 'target_type' => 'form', 'cta_text' => 'Submit'],
                    ['step_type' => 'thank_you', 'title' => 'Confirmation', 'target_type' => 'url', 'cta_text' => 'Done'],
                ],
            ],
            [
                'id' => 'booking',
                'goal' => 'booking',
                'name' => 'Booking funnel',
                'description' => 'Info page → booking form → calendar',
                'steps' => [
                    ['step_type' => 'landing', 'title' => 'Service overview', 'target_type' => 'page', 'cta_text' => 'Book now'],
                    ['step_type' => 'form', 'title' => 'Booking request', 'target_type' => 'form', 'cta_text' => 'Request slot'],
                    ['step_type' => 'booking', 'title' => 'Schedule', 'target_type' => 'url', 'cta_text' => 'Confirm'],
                ],
            ],
            [
                'id' => 'purchase',
                'goal' => 'purchase',
                'name' => 'Purchase funnel',
                'description' => 'Product page → checkout link',
                'steps' => [
                    ['step_type' => 'landing', 'title' => 'Product showcase', 'target_type' => 'page', 'cta_text' => 'View offer'],
                    ['step_type' => 'purchase', 'title' => 'Checkout', 'target_type' => 'url', 'cta_text' => 'Buy now'],
                ],
            ],
            [
                'id' => 'download',
                'goal' => 'download',
                'name' => 'Download funnel',
                'description' => 'Teaser → lead form → file download',
                'steps' => [
                    ['step_type' => 'landing', 'title' => 'Preview content', 'target_type' => 'page', 'cta_text' => 'Learn more'],
                    ['step_type' => 'form', 'title' => 'Unlock download', 'target_type' => 'form', 'cta_text' => 'Get file'],
                    ['step_type' => 'download', 'title' => 'Download', 'target_type' => 'url', 'cta_text' => 'Download'],
                ],
            ],
        ];
    }
}
