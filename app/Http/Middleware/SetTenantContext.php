<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SetTenantContext
 * ─────────────────────────────────────────────────────────────────────────────
 * Runs on every authenticated request.
 * Binds the logged-in user's tenant to the service container so that
 * BelongsToTenant global scopes can automatically filter queries.
 *
 * Also shares the tenant with all Blade views.
 */
class SetTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $tenant = Auth::user()->tenant;

            if ($tenant) {
                // Bind to container — used by BelongsToTenant trait
                app()->instance('current.tenant', $tenant);

                // Share with every Blade view
                view()->share('currentTenant', $tenant);
            }
        }

        return $next($request);
    }
}
