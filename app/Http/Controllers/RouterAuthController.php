<?php

namespace App\Http\Controllers;

use App\Models\MikroTikRouter;
use App\Services\RouterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RouterOS\Client;
use RouterOS\Query;

class RouterAuthController extends Controller
{
    public function connect(Request $request)
    {
        $validated = $request->validate([
            'host'     => 'required|string|max:255',
            'port'     => 'nullable|integer|between:1,65535',
            'username' => 'required|string|max:64',
            'password' => 'required|string|max:128',
        ]);

        $host     = trim($validated['host']);
        $port     = (int) ($validated['port'] ?? 8728);
        $username = $validated['username'];
        $password = $validated['password'];

        // ── Check tenant router limit BEFORE connecting ───────────────────────
        $tenant = Auth::user()->tenant;
        if ($tenant && !$tenant->canAddRouter()) {
            // Only block if this is a NEW router (not reconnecting to existing)
            $exists = MikroTikRouter::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('host', $host)
                ->where('port', $port)
                ->exists();

            if (!$exists) {
                return back()->withInput()->with('error',
                    "Your plan allows a maximum of {$tenant->max_routers} router(s). " .
                    "Please upgrade to add more."
                );
            }
        }

        // ── Step 1: Test connection with plain credentials BEFORE touching DB ──
        try {
            $client   = new Client([
                'host' => $host, 'user' => $username,
                'pass' => $password, 'port' => $port, 'timeout' => 10,
            ]);
            $result   = $client->query(new Query('/system/identity/print'))->read();
            $identity = $result[0]['name'] ?? $host;

        } catch (\Exception $e) {
            return back()->withInput()->with('error',
                'Could not connect to MikroTik — ' . $this->cleanError($e->getMessage())
            );
        }

        // ── Step 2: Save router (tenant_id set automatically by BelongsToTenant) ─
        $router = MikroTikRouter::updateOrCreate(
            ['tenant_id' => $tenant?->id, 'host' => $host, 'port' => $port],
            [
                'user_id'           => Auth::id(),
                'username'          => $username,
                'password'          => $password,
                'name'              => $identity,
                'identity'          => $identity,
                'status'            => 'online',
                'last_connected_at' => now(),
                'last_error'        => null,
            ]
        );

        // ── Step 3: Store in session ──────────────────────────────────────────
        RouterSession::login($router);

        return redirect()->route('layout.dashboard')
            ->with('success', "Connected to <strong>{$identity}</strong> successfully.");
    }

    public function disconnect()
    {
        $router = RouterSession::router();
        $name   = $router?->name ?? 'router';

        $router?->update(['status' => 'offline']);
        RouterSession::logout();

        return redirect()->route('router.select')
            ->with('success', "Disconnected from <strong>{$name}</strong>. Select a router to continue.");
    }

    private function cleanError(string $message): string
    {
        if (str_contains($message, 'Connection refused')) return 'Connection refused. Check the IP and port.';
        if (str_contains($message, 'timed out'))          return 'Connection timed out. Is the router reachable?';
        if (str_contains($message, 'wrong login'))        return 'Wrong username or password.';
        if (str_contains($message, 'NULL'))               return 'Invalid IP address or port.';
        return $message;
    }
}
