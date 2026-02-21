<?php

namespace App\Services;

use App\Models\MikroTikRouter;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Query;

class VoucherMikroTikService
{
    protected Client $client;
    protected MikroTikRouter $router;

    public function __construct(MikroTikRouter $router)
    {
        $this->router = $router;
        try {
            $this->client = new Client($router->getConnectionConfig());
        } catch (\Exception $e) {
            $router->markOffline($e->getMessage());
            // Show the REAL error, not a wrapper
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function fromSession(): static
    {
        $router = RouterSession::router();
        if (!$router) {
            throw new \RuntimeException('No router session active.');
        }
        return new static($router);
    }

    public function pushVoucher(string $username, string $password, string $profile = 'default', ?int $timeLimit = null, ?int $dataLimit = null, string $comment = ''): bool
    {
        try {
            $query = new Query('/ip/hotspot/user/add');
            $query->equal('name', $username)->equal('password', $password)->equal('profile', $profile);
            if ($comment)   $query->equal('comment',           $comment);
            if ($timeLimit) $query->equal('limit-uptime',      $this->secondsToMikrotik($timeLimit));
            if ($dataLimit) $query->equal('limit-bytes-total', (string) $dataLimit);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            Log::error('pushVoucher failed', ['username' => $username, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function pushVoucherBatch(array $vouchers): int
    {
        $synced = 0;
        foreach ($vouchers as $v) {
            $ok = $this->pushVoucher($v['username'], $v['password'], $v['profile'] ?? 'default', $v['time_limit'] ?? null, $v['data_limit'] ?? null, $v['comment'] ?? '');
            if ($ok) $synced++;
        }
        return $synced;
    }

    public function findUser(string $username): ?array
    {
        $results = $this->client->query(new Query('/ip/hotspot/user/print'))->read();
        foreach ($results as $user) {
            if (($user['name'] ?? '') === $username) return $user;
        }
        return null;
    }

    public function enableUser(string $username): bool
    {
        $user = $this->findUser($username);
        if (!$user || empty($user['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/enable'))->equal('.id', $user['.id']))->read();
        return true;
    }

    public function disableUser(string $username): bool
    {
        $user = $this->findUser($username);
        if (!$user || empty($user['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/disable'))->equal('.id', $user['.id']))->read();
        return true;
    }

    public function removeUser(string $username): bool
    {
        $user = $this->findUser($username);
        if (!$user || empty($user['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/remove'))->equal('.id', $user['.id']))->read();
        return true;
    }

    public function getActiveSessions(): array
    {
        return $this->client->query(new Query('/ip/hotspot/active/print'))->read();
    }

    public function listProfiles(): array
    {
        $results = $this->client->query(new Query('/ip/hotspot/user/profile/print'))->read();
        return array_column($results, 'name');
    }

    public function secondsToMikrotik(int $seconds): string
    {
        $d = floor($seconds / 86400);
        $h = floor(($seconds % 86400) / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        $parts = [];
        if ($d) $parts[] = "{$d}d";
        if ($h) $parts[] = "{$h}h";
        if ($m) $parts[] = "{$m}m";
        if ($s) $parts[] = "{$s}s";
        return implode('', $parts) ?: '0s';
    }

    public function mikrotikToSeconds(string $time): int
    {
        $seconds = 0;
        preg_match_all('/(\d+)([dhms])/', $time, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $seconds += match ($m[2]) {
                'd' => $m[1] * 86400, 'h' => $m[1] * 3600,
                'm' => $m[1] * 60,    's' => (int) $m[1],
            };
        }
        return $seconds;
    }
}
