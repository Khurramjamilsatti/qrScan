<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->is_admin || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('messages.invalid_admin_credentials')],
            ]);
        }

        $token = $user->createToken('admin')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->is_admin) {
            abort(403, __('messages.admin_access_required'));
        }

        return response()->json(['user' => $user]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('messages.logged_out')]);
    }
}
