<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_admin) {
            abort(403, __('messages.admin_must_use_portal'));
        }

        return $next($request);
    }
}
