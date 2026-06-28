<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use App\Models\SitePage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SitePageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'pages' => SitePage::orderBy('slug')->get(),
            'footer' => LandingSetting::getValue('footer', []),
        ]);
    }

    public function update(Request $request, SitePage $sitePage): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'intro' => 'nullable|string|max:500',
            'content' => 'sometimes|string',
            'contact_info' => 'nullable|array',
            'contact_info.email' => 'nullable|email|max:255',
            'contact_info.phone' => 'nullable|string|max:50',
            'contact_info.address' => 'nullable|string|max:500',
            'contact_info.hours' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $sitePage->update($validated);

        return response()->json($sitePage);
    }

    public function updateFooter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tagline' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
        ]);

        LandingSetting::setValue('footer', $validated);

        return response()->json(['footer' => $validated]);
    }
}
