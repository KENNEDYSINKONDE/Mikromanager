<?php

namespace App\Services;

use App\Models\MikroTikRouter;
use RouterOS\Client;
use RouterOS\Query;

/**
 * MikroTikService
 * ─────────────────────────────────────────────────────────────────────────────
 * Core MikroTik RouterOS API service.
 * NOW accepts a MikroTikRouter model — reads from session, NOT from .env
 */
class MikroTikService
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
            throw new \RuntimeException('MikroTik connection failed: ' . $e->getMessage());
        }
    }

    /** Build from the current session router. Throws if none active. */
    public static function fromSession(): static
    {
        $router = RouterSession::router();
        if (!$router) {
            throw new \RuntimeException('No router session active. Please connect to a router first.');
        }
        return new static($router);
    }

    /** Test connection — returns identity string or null on failure. */
    public function testConnection(): ?string
    {
        try {
            $result   = $this->client->query(new Query('/system/identity/print'))->read();
            $identity = $result[0]['name'] ?? 'Unknown';
            $this->router->markOnline($identity);
            return $identity;
        } catch (\Exception $e) {
            $this->router->markOffline($e->getMessage());
            return null;
        }
    }

    // ── SYSTEM ────────────────────────────────────────────────────────────────
    public function getIdentity(): array
    {
        return $this->client->query(new Query('/system/identity/print'))->read();
    }

    public function getInterfaces(): array
    {
        $results = $this->client->query(new Query('/interface/print'))->read();
        $interfaces = [];
        foreach ($results as $row) {
            $interfaces[] = [
                'name'        => $row['name']        ?? '-',
                'type'        => $row['type']        ?? '-',
                'actual-mtu'  => $row['actual-mtu']  ?? '-',
                'l2mtu'       => $row['l2mtu']       ?? '-',
                'max-l2mtu'   => $row['max-l2mtu']   ?? '-',
                'mac-address' => $row['mac-address'] ?? '-',
                'running'     => ($row['running']    ?? '') === 'true',
                'slave'       => ($row['slave']      ?? '') === 'true',
                'tx'          => $row['tx-byte']     ?? 0,
                'rx'          => $row['rx-byte']     ?? 0,
                'tx-packet'   => $row['tx-packet']   ?? 0,
                'rx-packet'   => $row['rx-packet']   ?? 0,
                'arp'         => $row['arp']         ?? 'enabled',
                'switch'      => $row['switch']      ?? '-',
            ];
        }
        return $interfaces;
    }

    // ── HOTSPOT USERS ─────────────────────────────────────────────────────────
    public function createHotspotUser(string $username, string $password, string $profile = 'default', ?int $limitUptime = null, ?int $limitBytes = null, string $comment = ''): array
    {
        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)->equal('password', $password)->equal('profile', $profile);
        if ($comment)     $query->equal('comment',           $comment);
        if ($limitUptime) $query->equal('limit-uptime',      $limitUptime . 's');
        if ($limitBytes)  $query->equal('limit-bytes-total', (string) $limitBytes);
        return $this->client->query($query)->read();
    }

    public function createVoucher(array $data): array
    {
        return $this->createHotspotUser($data['username'], $data['password'], $data['profile'] ?? 'default', $data['time_limit'] ?? null, $data['data_limit'] ?? null, 'Voucher ID: ' . ($data['id'] ?? 'N/A'));
    }

    /** PHP-side filter — avoids ->where() library compatibility issues */
    public function findHotspotUser(string $username): ?array
    {
        $users = $this->client->query(new Query('/ip/hotspot/user/print'))->read();
        foreach ($users as $user) {
            if (($user['name'] ?? '') === $username) return $user;
        }
        return null;
    }

    public function disableUser(string $username): bool
    {
        $user = $this->findHotspotUser($username);
        if (!$user || empty($user['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/disable'))->equal('.id', $user['.id']))->read();
        return true;
    }

    public function enableUser(string $username): bool
    {
        $user = $this->findHotspotUser($username);
        if (!$user || empty($user['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/enable'))->equal('.id', $user['.id']))->read();
        return true;
    }

    public function removeHotspotUser(string $username): bool
    {
        $user = $this->findHotspotUser($username);
        if (!$user || empty($user['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/remove'))->equal('.id', $user['.id']))->read();
        return true;
    }

    public function getActiveSessions(): array
    {
        return $this->client->query(new Query('/ip/hotspot/active/print'))->read();
    }

    // ── HOTSPOT PROFILES ──────────────────────────────────────────────────────
    public function listProfiles(): array
    {
        return $this->client->query(new Query('/ip/hotspot/user/profile/print'))->read();
    }

    public function getProfile(string $name): ?array
    {
        foreach ($this->listProfiles() as $profile) {
            if (($profile['name'] ?? '') === $name) return $profile;
        }
        return null;
    }

    public function createProfile(string $name, ?string $rateLimit = null, ?string $sessionTime = null, int $sharedUsers = 1): array
    {
        $query = new Query('/ip/hotspot/user/profile/add');
        $query->equal('name', $name)->equal('shared-users', (string) $sharedUsers);
        if ($rateLimit   !== null && $rateLimit   !== '') $query->equal('rate-limit',      $rateLimit);
        if ($sessionTime !== null && $sessionTime !== '') $query->equal('session-timeout', $sessionTime);
        return $this->client->query($query)->read();
    }

    public function updateProfile(string $name, ?string $rateLimit = null, ?string $sessionTime = null, int $sharedUsers = 1): array
    {
        $profile = $this->getProfile($name);
        if (!$profile || empty($profile['.id'])) throw new \RuntimeException("Profile \"{$name}\" not found on router.");
        $query = new Query('/ip/hotspot/user/profile/set');
        $query->equal('.id', $profile['.id'])->equal('shared-users', (string) $sharedUsers)
              ->equal('rate-limit', $rateLimit ?? '')->equal('session-timeout', $sessionTime ?? '');
        return $this->client->query($query)->read();
    }

    public function removeProfile(string $name): bool
    {
        $profile = $this->getProfile($name);
        if (!$profile || empty($profile['.id'])) return false;
        $this->client->query((new Query('/ip/hotspot/user/profile/remove'))->equal('.id', $profile['.id']))->read();
        return true;
    }

    public function listServerProfiles(): array
    {
        return $this->client->query(new Query('/ip/hotspot/profile/print'))->read();
    }

    public function listHotspotServers(): array
    {
        return $this->client->query(new Query('/ip/hotspot/print'))->read();
    }

    // ── CLI ───────────────────────────────────────────────────────────────────
    public function runCommand(string $command): string
    {
        $response = $this->client->query(new Query($command))->read();
        return empty($response) ? 'OK' : json_encode($response, JSON_PRETTY_PRINT);
    }
}
