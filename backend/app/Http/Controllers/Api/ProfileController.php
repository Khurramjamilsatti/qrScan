<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            abort(403, __('messages.admin_use_portal'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($validated);

        return response()->json(['user' => $user->fresh()]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            abort(403, __('messages.admin_use_portal'));
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('messages.current_password_incorrect')],
            ]);
        }

        $user->update(['password' => $validated['password']]);

        return response()->json(['message' => __('messages.password_updated')]);
    }
}
