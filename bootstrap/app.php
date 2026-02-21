<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'auth'          => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest'         => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'router.session'=> \App\Http\Middleware\RequireRouterSession::class,
            'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
            'superadmin'    => \App\Http\Middleware\RequireSuperAdmin::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetTenantContext::class,
        ]);

        $middleware->redirectGuestsTo(fn() => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
