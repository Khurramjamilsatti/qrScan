<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingFeature;
use App\Models\LandingSetting;
use App\Models\PricingPlan;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingContentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'hero' => LandingSetting::getValue('hero'),
            'stats' => LandingSetting::getValue('stats'),
            'features' => LandingFeature::orderBy('sort_order')->get(),
            'pricing' => PricingPlan::orderBy('sort_order')->get(),
            'testimonials' => Testimonial::orderBy('sort_order')->get(),
            'cta' => LandingSetting::getValue('cta'),
            'site' => LandingSetting::getValue('site'),
            'sections' => LandingSetting::getValue('sections'),
        ]);
    }

    public function updateHero(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'badge' => 'nullable|string',
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'cta_primary' => 'nullable|string',
            'cta_secondary' => 'nullable|string',
        ]);

        LandingSetting::setValue('hero', $validated);

        return response()->json(['hero' => $validated]);
    }

    public function updateStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stats' => 'required|array',
            'stats.*.label' => 'required|string',
            'stats.*.value' => 'required|string',
        ]);

        LandingSetting::setValue('stats', $validated['stats']);

        return response()->json(['stats' => $validated['stats']]);
    }

    public function updateCta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string',
        ]);

        LandingSetting::setValue('cta', $validated);

        return response()->json(['cta' => $validated]);
    }

    public function updateSite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'tagline' => 'nullable|string',
            'logo_text' => 'nullable|string',
        ]);

        LandingSetting::setValue('site', $validated);

        return response()->json(['site' => $validated]);
    }

    public function updateSections(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'features_eyebrow' => 'nullable|string|max:255',
            'features_title' => 'nullable|string|max:500',
            'pricing_eyebrow' => 'nullable|string|max:255',
            'pricing_title' => 'nullable|string|max:255',
            'pricing_subtitle' => 'nullable|string|max:500',
            'testimonials_title' => 'nullable|string|max:255',
        ]);

        LandingSetting::setValue('sections', $validated);

        return response()->json(['sections' => $validated]);
    }

    public function storeFeature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'description' => 'required|string',
            'items' => 'required|array',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $feature = LandingFeature::create($validated);

        return response()->json($feature, 201);
    }

    public function updateFeature(Request $request, LandingFeature $feature): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'subtitle' => 'nullable|string',
            'description' => 'sometimes|string',
            'items' => 'sometimes|array',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $feature->update($validated);

        return response()->json($feature);
    }

    public function destroyFeature(LandingFeature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function storePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:pricing_plans,slug',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'nullable|string',
            'features' => 'required|array',
            'limits' => 'required|array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan = PricingPlan::create($validated);

        return response()->json($plan, 201);
    }

    public function updatePlan(Request $request, PricingPlan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'slug' => 'sometimes|string|unique:pricing_plans,slug,'.$plan->id,
            'price' => 'sometimes|numeric|min:0',
            'billing_period' => 'nullable|string',
            'features' => 'sometimes|array',
            'limits' => 'sometimes|array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan->update($validated);

        return response()->json($plan);
    }

    public function destroyPlan(PricingPlan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function storeTestimonial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'role' => 'nullable|string',
            'company' => 'nullable|string',
            'content' => 'required|string',
            'avatar' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $testimonial = Testimonial::create($validated);

        return response()->json($testimonial, 201);
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'role' => 'nullable|string',
            'company' => 'nullable|string',
            'content' => 'sometimes|string',
            'avatar' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $testimonial->update($validated);

        return response()->json($testimonial);
    }

    public function destroyTestimonial(Testimonial $testimonial): JsonResponse
    {
        $testimonial->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }
}
