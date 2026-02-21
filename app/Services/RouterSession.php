<?php

namespace App\Services;

use App\Models\MikroTikRouter;

/**
 * RouterSession
 * ─────────────────────────────────────────────────────────────────────────────
 * Manages which MikroTik router the authenticated user is currently connected
 * to. Works like Laravel's Auth facade but for the router connection.
 *
 * Usage:
 *   RouterSession::login($router)  — store router in session after connecting
 *   RouterSession::router()        — get the active MikroTikRouter model
 *   RouterSession::id()            — get the active router's database ID
 *   RouterSession::check()         — returns true if router session is active
 *   RouterSession::logout()        — clear router session (keep user logged in)
 */
class RouterSession
{
    const SESSION_KEY = 'mikrotik_router_id';

    /**
     * Store the connected router in the session.
     */
    public static function login(MikroTikRouter $router): void
    {
        session([self::SESSION_KEY => $router->id]);
        session()->save();
    }

    /**
     * Get the currently active MikroTikRouter model.
     * Returns null if no router session is active.
     */
    public static function router(): ?MikroTikRouter
    {
        $id = session(self::SESSION_KEY);

        if (!$id) {
            return null;
        }

        // Also verify the router still belongs to the logged-in user
        return MikroTikRouter::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
    }

    /**
     * Get the active router's ID.
     */
    public static function id(): ?int
    {
        return session(self::SESSION_KEY);
    }

    /**
     * Check if a router session is active and valid.
     */
    public static function check(): bool
    {
        return session()->has(self::SESSION_KEY)
            && static::router() !== null;
    }

    /**
     * Clear the router session (user stays logged in).
     */
    public static function logout(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
