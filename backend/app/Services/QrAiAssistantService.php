<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QrAiAssistantService
{
    public function __construct(
        private QrFunnelService $funnels,
        private DomainUrlService $domains,
    ) {}

    public function isLlmConfigured(): bool
    {
        return ! empty(config('services.openai.api_key'));
    }

    public function advise(User $user, string $prompt, ?array $context = []): array
    {
        if ($this->isLlmConfigured()) {
            try {
                return $this->llmAdvise($user, $prompt, $context);
            } catch (\Throwable) {
                // fall through to heuristics
            }
        }

        return $this->heuristicAdvise($user, $prompt, $context);
    }

    public function heuristicAdvise(User $user, string $prompt, ?array $context = []): array
    {
        $text = strtolower($prompt);
        $intent = $this->detectIntent($text, $user);
        $template = collect($this->funnels->templates())->firstWhere('goal', $intent['goal'])
            ?? $this->funnels->templates()[0];

        $routing = $this->suggestRouting($intent);
        $security = $this->suggestSecurity($intent);

        return [
            'provider' => 'rules',
            'intent' => $intent,
            'summary' => $intent['summary'],
            'recommended_solution' => $intent['solution'],
            'suggested_name' => Str::title($intent['solution']).' QR',
            'destination_url' => $intent['destination_hint'],
            'funnel_template' => $template,
            'routing_rules' => $routing,
            'security' => $security,
            'next_steps' => [
                __('messages.ai_assistant_step_create', ['type' => $intent['solution']]),
                __('messages.ai_assistant_step_funnel'),
                __('messages.ai_assistant_step_routing'),
                __('messages.ai_assistant_step_publish'),
            ],
            'reply' => $intent['reply'],
        ];
    }

    private function detectIntent(string $text, User $user): array
    {
        $base = $this->domains->resolveBaseUrl($user);

        if (preg_match('/menu|restaurant|food|dining/', $text)) {
            return [
                'goal' => 'engagement',
                'solution' => 'digital_menu',
                'summary' => 'Use a digital menu QR so diners scan and browse on mobile.',
                'destination_hint' => $base.'/menu/your-menu',
                'reply' => 'I recommend a Digital Menu with a mobile-first funnel: menu page → optional feedback form.',
            ];
        }
        if (preg_match('/book|appointment|schedule|reservation/', $text)) {
            return [
                'goal' => 'booking',
                'solution' => 'form_funnel',
                'summary' => 'Use a booking funnel: info page → booking form → confirmation.',
                'destination_hint' => 'https://calendly.com/your-link',
                'reply' => 'Set up a booking funnel with a form step to capture date/time preferences, then route mobile users to your scheduler.',
            ];
        }
        if (preg_match('/buy|purchase|shop|product|checkout|pay/', $text)) {
            return [
                'goal' => 'purchase',
                'solution' => 'purchase_funnel',
                'summary' => 'Use a purchase funnel that showcases the offer then sends users to checkout.',
                'destination_hint' => 'https://your-store.com/checkout',
                'reply' => 'Create a purchase funnel with a product landing step and a checkout URL. Add routing to send mobile traffic to your mobile-optimized store.',
            ];
        }
        if (preg_match('/download|pdf|file|app|install/', $text)) {
            return [
                'goal' => 'download',
                'solution' => 'download_funnel',
                'summary' => 'Gate downloads behind a short lead form for better conversion tracking.',
                'destination_hint' => 'https://your-site.com/download',
                'reply' => 'Use a download funnel: teaser page → email capture form → secure download link with one-time access.',
            ];
        }
        if (preg_match('/lead|contact|email|signup|register|newsletter/', $text)) {
            return [
                'goal' => 'lead',
                'solution' => 'form',
                'summary' => 'A form-backed QR is ideal for capturing leads at events and print materials.',
                'destination_hint' => $base.'/form/contact',
                'reply' => 'Start with a lead capture form. Add a funnel with a welcome page, form step, and thank-you redirect.',
            ];
        }
        if (preg_match('/event|ticket|conference/', $text)) {
            return [
                'goal' => 'engagement',
                'solution' => 'digital_ticket',
                'summary' => 'Digital tickets work well for events with scan-to-enter flows.',
                'destination_hint' => $base.'/ticket/your-event',
                'reply' => 'Use a digital ticket QR with expiring links and signed URLs for secure entry.',
            ];
        }
        if (preg_match('/card|business|vcard|contact card/', $text)) {
            return [
                'goal' => 'lead',
                'solution' => 'business_card',
                'summary' => 'Digital business cards are perfect for networking and conferences.',
                'destination_hint' => $base.'/card/your-name',
                'reply' => 'Create a digital business card QR. Route mobile scanners directly to your card and desktop users to your website.',
            ];
        }

        return [
            'goal' => 'lead',
            'solution' => 'short_link',
            'summary' => 'A tracked short link or QR with routing rules fits most campaigns.',
            'destination_hint' => 'https://your-website.com',
            'reply' => 'Tell me your goal (leads, bookings, sales, downloads) and I\'ll recommend the best QR setup with funnel steps and routing rules.',
        ];
    }

    private function suggestRouting(array $intent): array
    {
        return [
            [
                'id' => 'mobile',
                'name' => 'Mobile visitors',
                'priority' => 1,
                'enabled' => true,
                'conditions' => ['device' => ['mobile']],
                'destination_url' => $intent['destination_hint'],
            ],
            [
                'id' => 'desktop',
                'name' => 'Desktop visitors',
                'priority' => 2,
                'enabled' => true,
                'conditions' => ['device' => ['desktop']],
                'destination_url' => $intent['destination_hint'],
            ],
        ];
    }

    private function suggestSecurity(array $intent): array
    {
        $secure = in_array($intent['goal'], ['download', 'purchase', 'booking'], true);

        return [
            'signed' => $secure,
            'one_time_access' => $intent['goal'] === 'download',
            'password_enabled' => false,
            'max_scans' => 0,
            'expires_at' => null,
        ];
    }

    private function llmAdvise(User $user, string $prompt, ?array $context): array
    {
        $key = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');

        $system = 'You are a QR marketing assistant. Respond ONLY with valid JSON: {"summary","recommended_solution","destination_url","goal","reply","routing_rules":[{"name","priority","enabled","conditions":{"device":[],"country":[],"language":[]},"destination_url"}],"security":{"signed":bool,"one_time_access":bool}}';

        $response = Http::withToken($key)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.4,
            ])
            ->throw()
            ->json();

        $content = $response['choices'][0]['message']['content'] ?? '{}';
        $parsed = json_decode($content, true) ?? [];

        $heuristic = $this->heuristicAdvise($user, $prompt, $context);

        return array_merge($heuristic, $parsed, [
            'provider' => 'openai',
            'funnel_template' => $heuristic['funnel_template'],
            'next_steps' => $heuristic['next_steps'],
        ]);
    }
}
