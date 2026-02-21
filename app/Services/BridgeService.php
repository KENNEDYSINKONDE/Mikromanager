<?php

namespace App\Services;

use App\Models\MikroTikRouter;
use RouterOS\Client;
use RouterOS\Query;

/**
 * BridgeService — reads from session router, NOT .env
 */
class BridgeService
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

    public static function fromSession(): static
    {
        $router = RouterSession::router();
        if (!$router) throw new \RuntimeException('No router session active.');
        return new static($router);
    }

    public function listBridges(): array
    {
        return $this->client->query(new Query('/interface/bridge/print'))->read();
    }

    public function listBridgePorts(): array
    {
        return $this->client->query(new Query('/interface/bridge/port/print'))->read();
    }

    public function listBridgeHosts(): array
    {
        return $this->client->query(new Query('/interface/bridge/host/print'))->read();
    }
}
