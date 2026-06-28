<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->withCount([
                'qrCodes', 'shortLinks', 'businessCards', 'digitalPages', 'digitalMenus',
                'digitalBadges', 'digitalTickets', 'scanToWinCampaigns',
            ])
            ->latest();

        if ($request->boolean('app_only', true)) {
            $query->where('is_admin', false);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function show(User $user): JsonResponse
    {
        if ($user->is_admin) {
            abort(404);
        }

        return response()->json(AdminAnalyticsService::forUser($user));
    }

    public function updatePlan(Request $request, User $user): JsonResponse
    {
        if ($user->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'plan' => 'required|in:free,starter,pro,business',
        ]);

        $user->update(['plan' => $validated['plan']]);

        return response()->json($user);
    }
}
