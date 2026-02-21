<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommandController extends Controller
{
    // ── ALLOWED COMMANDS WHITELIST (security) ─────────────────────────────────
    // Only these command prefixes are permitted through the terminal
    private const ALLOWED_PREFIXES = [
        '/ip hotspot',
        '/ip dhcp-server',
        '/ip dhcp-client',
        '/ip address',
        '/ip route',
        '/queue',
        '/interface',
        '/system identity',
        '/system resource',
        '/system routerboard',
        '/system clock',
        '/system package',
        '/system license',
        '/ping',
    ];

    // ── DANGEROUS COMMANDS — always blocked ───────────────────────────────────
    private const BLOCKED_KEYWORDS = [
        '/system reboot',
        '/system reset',
        '/system shutdown',
        '/user remove',
        '/user set',
        '/ip firewall filter remove',
        '/ip firewall nat remove',
        'factory-reset',
    ];

    public function execute(Request $request)
    {
        $request->validate(['command' => 'required|string|max:500']);

        $command = trim($request->command);

        // Security check — blocked first, then allowed
        if ($this->isBlocked($command)) {
            Log::warning('Blocked MikroTik command attempt', ['user' => auth()->id(), 'command' => $command]);
            return response()->json(['output' => '⛔ This command is blocked for security reasons.'], 403);
        }

        if (!$this->isAllowed($command)) {
            return response()->json(['output' => '⛔ Command not permitted. Only read-only and hotspot commands are allowed.'], 403);
        }

        try {
            $mikrotik = MikroTikService::fromSession();
            $output   = $mikrotik->runCommand($command);

            Log::info('MikroTik terminal command', ['user_id' => auth()->id(), 'command' => $command]);

            return response()->json(['output' => $output ?: 'OK']);
        } catch (\Throwable $e) {
            return response()->json(['output' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function isBlocked(string $command): bool
    {
        $lower = strtolower($command);
        foreach (self::BLOCKED_KEYWORDS as $keyword) {
            if (str_contains($lower, strtolower($keyword))) return true;
        }
        return false;
    }

    private function isAllowed(string $command): bool
    {
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with(strtolower($command), strtolower($prefix))) return true;
        }
        return false;
    }
}
