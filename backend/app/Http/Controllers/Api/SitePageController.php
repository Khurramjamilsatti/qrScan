<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use App\Support\LocalizedContent;
use Illuminate\Http\JsonResponse;

class SitePageController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $locale = LocalizedContent::locale();

        $page = SitePage::where('slug', $slug)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        if (! $page && $locale !== 'en') {
            $page = SitePage::where('slug', $slug)
                ->where('locale', 'en')
                ->where('is_active', true)
                ->first();
        }

        if (! $page) {
            abort(404, __('messages.page_not_found'));
        }

        return response()->json($page);
    }
}
