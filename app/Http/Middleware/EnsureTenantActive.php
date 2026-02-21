<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EnsureTenantActive
 * ─────────────────────────────────────────────────────────────────────────────
 * Blocks access if the tenant's account is suspended or trial has expired.
 * Always allows: login, logout, register, and the subscription/billing pages.
 */
class EnsureTenantActive
{
    // Routes that are always accessible regardless of subscription status
    private const ALWAYS_ALLOW = [
        'login', 'login.post', 'logout', 'register', 'register.post',
        'subscription.index', 'subscription.plans',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $tenant = Auth::user()->tenant;

        // Superadmin has no tenant restriction
        if (!$tenant || Auth::user()->isSuperAdmin()) {
            return $next($request);
        }

        // Always allow billing/logout pages even when expired
        $routeName = $request->route()?->getName();
        if (in_array($routeName, self::ALWAYS_ALLOW)) {
            return $next($request);
        }

        // Suspended account — hard block
        if ($tenant->status === 'suspended') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        // Trial expired — redirect to plans page (not yet built, just flash for now)
        if ($tenant->isTrialExpired()) {
            return redirect()->route('login')
                ->with('error', 'Your 14-day trial has expired. Please upgrade to continue.');
        }

        // Cancelled with past subscription end date
        if ($tenant->status === 'cancelled' &&
            $tenant->subscription_ends_at &&
            $tenant->subscription_ends_at->isPast()) {
            return redirect()->route('login')
                ->with('error', 'Your subscription has ended. Please renew to continue.');
        }

        return $next($request);
    }
}
