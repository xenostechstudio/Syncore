<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module, ?string $action = null): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Super admin bypasses all checks
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Check module access
        if (!$user->can("access.{$module}")) {
            abort(403, "You don't have access to the {$module} module.");
        }

        // Check specific action if provided
        if ($action && !$user->can("{$module}.{$action}")) {
            abort(403, "You don't have permission to {$action} in the {$module} module.");
        }

        return $next($request);
    }
}
