<?php

namespace App\Http\Middleware;

use App\Services\RouterSession;
use Closure;
use Illuminate\Http\Request;

class RequireRouterSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!RouterSession::check()) {
            return redirect()->route('router.select')
                ->with('error', 'Please connect to your MikroTik router first.');
        }

        // Make active router available in ALL views automatically
        view()->share('activeRouter', RouterSession::router());

        return $next($request);
    }
}
