<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Services\StripeBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;

class BillingController extends Controller
{
    public function __construct(
        protected StripeBillingService $stripe
    ) {}

    public function config(): JsonResponse
    {
        $user = request()->user();

        return response()->json([
            'enabled' => $this->stripe->isConfigured(),
            'publishable_key' => $this->stripe->publishableKey(),
            'has_subscription' => filled($user?->stripe_subscription_id),
            'subscription_status' => $user?->stripe_subscription_status,
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        if (! $this->stripe->isConfigured()) {
            return response()->json(['message' => __('messages.billing_not_configured')], 503);
        }

        $validated = $request->validate([
            'plan' => 'required|string|in:starter,pro,business',
        ]);

        $plan = PricingPlan::query()
            ->where('slug', $validated['plan'])
            ->where('locale', 'en')
            ->where('is_active', true)
            ->first();

        if (! $plan) {
            $plan = PricingPlan::query()
                ->where('slug', $validated['plan'])
                ->where('is_active', true)
                ->orderBy('id')
                ->firstOrFail();
        }

        try {
            $session = $this->stripe->createCheckoutSession($request->user(), $plan);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (ApiErrorException $e) {
            report($e);

            return response()->json([
                'message' => $this->stripeErrorMessage($e),
            ], 502);
        }

        return response()->json([
            'checkout_url' => $session->url,
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        if (! $this->stripe->isConfigured()) {
            return response()->json(['message' => __('messages.billing_not_configured')], 503);
        }

        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $user = $this->stripe->confirmCheckoutSession($request->user(), $validated['session_id']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (ApiErrorException $e) {
            report($e);

            return response()->json([
                'message' => $this->stripeErrorMessage($e),
            ], 502);
        }

        return response()->json([
            'message' => __('messages.billing_plan_updated'),
            'plan' => $user->plan,
        ]);
    }

    public function portal(Request $request): JsonResponse
    {
        if (! $this->stripe->isConfigured()) {
            return response()->json(['message' => __('messages.billing_not_configured')], 503);
        }

        try {
            $session = $this->stripe->createPortalSession($request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (ApiErrorException $e) {
            report($e);

            return response()->json([
                'message' => $this->stripeErrorMessage($e),
            ], 502);
        }

        return response()->json([
            'portal_url' => $session->url,
        ]);
    }

    public function webhook(Request $request): Response
    {
        try {
            $this->stripe->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (\InvalidArgumentException $e) {
            return response($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            return response($e->getMessage(), 503);
        }

        return response('OK', 200);
    }

    protected function stripeErrorMessage(ApiErrorException $e): string
    {
        if ($e instanceof ApiConnectionException) {
            return __('messages.billing_stripe_network_error');
        }

        return $e->getMessage();
    }
}
