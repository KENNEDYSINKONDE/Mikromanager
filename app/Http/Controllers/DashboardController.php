<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use App\Services\RouterSession;

class DashboardController extends Controller
{
    public function index()
    {
        $router    = RouterSession::router();
        $connected = false;
        $routerboard = $resources = $identity = $clock = $license = null;

        try {
            $mikrotik = new MikroTikService($router);

            $routerboard = json_decode($mikrotik->runCommand('/system/routerboard/print'), true);
            $resources   = json_decode($mikrotik->runCommand('/system/resource/print'), true);
            $identity    = json_decode($mikrotik->runCommand('/system/identity/print'), true);
            $clock       = json_decode($mikrotik->runCommand('/system/clock/print'), true);
            $license     = json_decode($mikrotik->runCommand('/system/license/print'), true);

            $connected = true;
            $router->markOnline($identity[0]['name'] ?? null);

        } catch (\Exception $e) {
            $router->markOffline($e->getMessage());
        }

        return view('layouts.dashboard', compact(
            'router', 'connected',
            'routerboard', 'resources', 'identity', 'clock', 'license'
        ));
    }
}