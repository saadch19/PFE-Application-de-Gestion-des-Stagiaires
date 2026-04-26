<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (empty($roles) || $user->hasRole(...$roles)) {
            return $next($request);
        }

        abort(403, 'Acces refuse pour ce role.');
    }
}
