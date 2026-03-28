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
            abort(403, 'Accès non autorisé. Votre rôle ne vous permet pas d\'accéder à cette section.');
        }

        return $next($request);
    }
}
