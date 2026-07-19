<?php

namespace App\Services;

use App\Models\PricingPlan;
use App\Models\User;
use App\Support\StripeHttpClientConfigurator;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeBillingService
{
    public function __construct()
    {
        StripeHttpClientConfigurator::configure();
        Stripe::setApiKey(config('stripe.secret'));
    }

    public function isConfigured(): bool
    {
        return filled(config('stripe.key')) && filled(config('stripe.secret'));
    }

    public function publishableKey(): ?string
    {
        return config('stripe.key');
    }

    public function planRank(string $plan): int
    {
        return config('stripe.plan_rank')[$plan] ?? 0;
    }

    public function createCheckoutSession(User $user, PricingPlan $plan): Session
    {
        if ($plan->slug === 'free' || (float) $plan->price <= 0) {
            throw new \InvalidArgumentException(__('messages.billing_free_plan'));
        }

        if ($this->planRank($plan->slug) <= $this->planRank($user->plan)) {
            throw new \InvalidArgumentException(__('messages.billing_use_portal_for_downgrade'));
        }

        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $amount = (int) round((float) $plan->price * 100);

        $params = [
            'mode' => 'subscription',
            'client_reference_id' => (string) $user->id,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => config('app.name').' — '.$plan->name,
                    ],
                    'recurring' => [
                        'interval' => $plan->billing_period === 'year' ? 'year' : 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $frontendUrl.'/app/billing?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $frontendUrl.'/app/billing?checkout=cancel',
            'metadata' => [
                'user_id' => (string) $user->id,
                'plan_slug' => $plan->slug,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan_slug' => $plan->slug,
                ],
            ],
        ];

        if ($user->stripe_customer_id) {
            $params['customer'] = $user->stripe_customer_id;
        } else {
            $params['customer_email'] = $user->email;
        }

        return Session::create($params);
    }

    public function createPortalSession(User $user): PortalSession
    {
        if (! $user->stripe_customer_id) {
            throw new \InvalidArgumentException(__('messages.billing_no_subscription'));
        }

        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        return PortalSession::create([
            'customer' => $user->stripe_customer_id,
            'return_url' => $frontendUrl.'/app/billing',
        ]);
    }

    public function handleWebhook(string $payload, ?string $signature): void
    {
        $secret = config('stripe.webhook_secret');

        if (! $secret) {
            throw new \RuntimeException(__('messages.billing_webhook_not_configured'));
        }

        try {
            $event = Webhook::constructEvent($payload, $signature ?? '', $secret);
        } catch (UnexpectedValueException|SignatureVerificationException) {
            throw new \InvalidArgumentException(__('messages.billing_invalid_webhook'));
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            default => null,
        };
    }

    public function fulfillCheckoutSession(object $session): void
    {
        $this->handleCheckoutCompleted($session);
    }

    public function confirmCheckoutSession(User $user, string $sessionId): User
    {
        $session = Session::retrieve($sessionId);

        if ((string) ($session->client_reference_id ?? '') !== (string) $user->id) {
            throw new \InvalidArgumentException(__('messages.unauthorized'));
        }

        if ($session->status !== 'complete') {
            throw new \InvalidArgumentException(__('messages.billing_payment_incomplete'));
        }

        $this->fulfillCheckoutSession($session);

        return $user->fresh();
    }

    protected function handleCheckoutCompleted(object $session): void
    {
        $userId = $session->metadata->user_id ?? $session->client_reference_id ?? null;
        $planSlug = $session->metadata->plan_slug ?? null;

        if (! $userId || ! $planSlug) {
            return;
        }

        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $previousSubscriptionId = $user->stripe_subscription_id;

        $user->update([
            'stripe_customer_id' => $session->customer,
            'stripe_subscription_id' => $session->subscription,
            'stripe_subscription_status' => 'active',
            'plan' => $planSlug,
        ]);

        if ($previousSubscriptionId && $previousSubscriptionId !== $session->subscription) {
            try {
                Subscription::retrieve($previousSubscriptionId)->cancel();
            } catch (\Throwable) {
                // Old subscription may already be replaced by Stripe.
            }
        }
    }

    protected function handleSubscriptionUpdated(object $subscription): void
    {
        $user = $this->findUserBySubscription($subscription);
        if (! $user) {
            return;
        }

        $planSlug = $subscription->metadata->plan_slug ?? $user->plan;
        $status = $subscription->status;

        $user->update([
            'stripe_subscription_id' => $subscription->id,
            'stripe_subscription_status' => $status,
            'plan' => in_array($status, ['active', 'trialing'], true) ? $planSlug : 'free',
        ]);
    }

    protected function handleSubscriptionDeleted(object $subscription): void
    {
        $user = $this->findUserBySubscription($subscription);
        if (! $user) {
            return;
        }

        $user->update([
            'stripe_subscription_id' => null,
            'stripe_subscription_status' => 'canceled',
            'plan' => 'free',
        ]);
    }

    protected function findUserBySubscription(object $subscription): ?User
    {
        $userId = $subscription->metadata->user_id ?? null;
        if ($userId) {
            return User::find($userId);
        }

        return User::where('stripe_subscription_id', $subscription->id)->first()
            ?? User::where('stripe_customer_id', $subscription->customer)->first();
    }
}
