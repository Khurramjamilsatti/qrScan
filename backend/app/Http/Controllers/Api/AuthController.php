<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'plan' => 'free',
            'scans_reset_at' => now()->addMonth(),
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('messages.invalid_credentials')],
            ]);
        }

        if ($user->is_admin) {
            throw ValidationException::withMessages([
                'email' => [__('messages.admin_sign_in_portal')],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('messages.logged_out')]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            abort(403, __('messages.admin_must_use_portal'));
        }

        $user->loadCount(['qrCodes', 'shortLinks', 'businessCards', 'digitalPages', 'digitalMenus', 'digitalBadges', 'digitalEvents', 'digitalTickets', 'scanToWinCampaigns']);

        return response()->json([
            'user' => $user,
            'limits' => $user->planLimits(),
            'usage' => [
                'qr_codes' => $user->qr_codes_count,
                'short_links' => $user->short_links_count,
                'business_cards' => $user->business_cards_count,
                'digital_pages' => $user->digital_pages_count,
                'digital_menus' => $user->digital_menus_count,
                'digital_events' => $user->digital_events_count,
                'scans_this_month' => $user->scans_this_month,
            ],
        ]);
    }
}
