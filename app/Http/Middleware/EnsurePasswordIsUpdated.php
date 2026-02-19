<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsUpdated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_update_password) {
            $allowedRoutes = [
                'password.force.show',
                'password.force.update',
                'logout',
            ];

            if (! in_array($request->route()?->getName(), $allowedRoutes, true)) {
                return redirect()->route('password.force.show');
            }
        }

        return $next($request);
    }
}
