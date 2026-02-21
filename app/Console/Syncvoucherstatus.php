<?php

namespace App\Console\Commands;

use App\Models\MikroTikRouter;
use App\Models\Voucher;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class SyncVoucherStatus extends Command
{
    protected $signature = 'vouchers:sync {router_id?}';
    protected $description = 'Sync voucher status from MikroTik hotspot users';

    public function handle()
    {
        $routerId = $this->argument('router_id');

        $routers = $routerId 
            ? MikroTikRouter::withoutTenantScope()->where('id', $routerId)->get()
            : MikroTikRouter::withoutTenantScope()->where('status', 'online')->get();

        if ($routers->isEmpty()) {
            $this->error('No routers found.');
            return 1;
        }

        foreach ($routers as $router) {
            $this->info("Syncing router: {$router->identity} ({$router->host})");
            $this->syncRouter($router);
        }

        $this->info('✓ Sync completed.');
        return 0;
    }

    private function syncRouter(MikroTikRouter $router)
    {
        try {
            $mikrotik = new MikroTikService($router);
            
            // Get all hotspot users from MikroTik
            $usersJson = $mikrotik->runCommand('/ip/hotspot/user/print');
            $users = json_decode($usersJson, true);

            if (!is_array($users)) {
                $this->warn("  ⚠ Failed to fetch users from {$router->identity}");
                return;
            }

            // Get active sessions
            $activeJson = $mikrotik->runCommand('/ip/hotspot/active/print');
            $activeSessions = json_decode($activeJson, true) ?: [];

            // Create lookup for active sessions by username
            $activeByUsername = collect($activeSessions)->keyBy('user');

            $synced = 0;
            foreach ($users as $user) {
                $username = $user['name'] ?? null;
                if (!$username) continue;

                // Find voucher in our database
                $voucher = Voucher::withoutTenantScope()
                    ->where('router_id', $router->id)
                    ->where('username', $username)
                    ->first();

                if (!$voucher) continue;

                // Check if user has active session
                $activeSession = $activeByUsername->get($username);

                // Update voucher with MikroTik data
                $this->updateVoucherFromMikrotik($voucher, $user, $activeSession);
                $synced++;
            }

            $this->info("  ✓ Synced {$synced} vouchers from {$router->identity}");

        } catch (\Exception $e) {
            $this->error("  ✗ Error syncing {$router->identity}: " . $e->getMessage());
        }
    }

    private function updateVoucherFromMikrotik(Voucher $voucher, array $user, ?array $activeSession)
    {
        $data = [];

        // Status determination
        $disabled = isset($user['disabled']) && $user['disabled'] === 'true';
        $bytesIn  = (int) ($user['bytes-in'] ?? 0);
        $bytesOut = (int) ($user['bytes-out'] ?? 0);
        $uptime   = $this->parseUptime($user['uptime'] ?? '0s');

        // Check if voucher has been used at all
        if ($uptime > 0 || $bytesIn > 0 || $bytesOut > 0) {
            // Mark first use
            if (!$voucher->first_used_at) {
                $data['first_used_at'] = now();
            }
            $data['last_used_at'] = now();

            // Update usage stats
            $data['bytes_in']     = $bytesIn;
            $data['bytes_out']    = $bytesOut;
            $data['session_time'] = $uptime;

            // If there's an active session, get more details
            if ($activeSession) {
                $data['last_caller_id'] = $activeSession['mac-address'] ?? null;
                $data['last_ip']        = $activeSession['address'] ?? null;
            }
        }

        // Determine status
        if ($disabled) {
            $data['status'] = 'disabled';
        } elseif ($this->isExpired($voucher, $user, $uptime)) {
            $data['status'] = 'expired';
        } elseif ($uptime > 0 || $bytesIn > 0) {
            // Has been used at least once
            if ($activeSession) {
                $data['status'] = 'used'; // Currently active
            } else {
                // Used before but not currently active
                // Check if completely consumed
                if ($this->isFullyConsumed($voucher, $user, $uptime, $bytesIn, $bytesOut)) {
                    $data['status'] = 'expired';
                } else {
                    $data['status'] = 'used';
                }
            }
        } else {
            $data['status'] = 'active'; // Never used yet
        }

        if (!empty($data)) {
            $voucher->update($data);
        }
    }

    private function isExpired(Voucher $voucher, array $user, int $uptime): bool
    {
        // Check database expiry
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return true;
        }

        // Check time limit from profile (if set in MikroTik user)
        $limitUptime = $this->parseUptime($user['limit-uptime'] ?? '0s');
        if ($limitUptime > 0 && $uptime >= $limitUptime) {
            return true;
        }

        return false;
    }

    private function isFullyConsumed(Voucher $voucher, array $user, int $uptime, int $bytesIn, int $bytesOut): bool
    {
        // Check if time is fully consumed
        $limitUptime = $this->parseUptime($user['limit-uptime'] ?? '0s');
        if ($limitUptime > 0 && $uptime >= $limitUptime) {
            return true;
        }

        // Check if bytes are fully consumed
        $limitBytes = $this->parseBytes($user['limit-bytes-total'] ?? '0');
        if ($limitBytes > 0 && ($bytesIn + $bytesOut) >= $limitBytes) {
            return true;
        }

        return false;
    }

    private function parseUptime(string $uptime): int
    {
        // Parse MikroTik uptime format: "1d2h3m4s" → seconds
        preg_match_all('/(\d+)([dhms])/', $uptime, $matches, PREG_SET_ORDER);
        
        $seconds = 0;
        foreach ($matches as $match) {
            $value = (int) $match[1];
            $unit  = $match[2];
            
            $seconds += match($unit) {
                'd' => $value * 86400,
                'h' => $value * 3600,
                'm' => $value * 60,
                's' => $value,
                default => 0,
            };
        }
        
        return $seconds;
    }

    private function parseBytes(string $bytes): int
    {
        // Parse MikroTik bytes format: "100M", "1G" → bytes
        if (!preg_match('/^(\d+(?:\.\d+)?)\s*([KMGT]?)$/', $bytes, $match)) {
            return (int) $bytes;
        }

        $value = (float) $match[1];
        $unit  = $match[2] ?? '';

        $multiplier = match($unit) {
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
            'T' => 1024 * 1024 * 1024 * 1024,
            default => 1,
        };

        return (int) ($value * $multiplier);
    }
}
