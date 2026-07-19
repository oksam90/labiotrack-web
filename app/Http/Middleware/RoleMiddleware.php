<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if (! in_array($user->role, $roles)) {
            abort(403, __('common.access_denied'));
        }

        return $next($request);
    }
}
