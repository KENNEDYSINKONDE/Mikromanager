<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Services\MikroTikService;

class RouterboardController extends Controller
{
    public function index()
    {
        try {
            $mikrotik    = MikroTikService::fromSession();
            $routerboard = json_decode($mikrotik->runCommand('/system/routerboard/print'), true);
            $resources   = json_decode($mikrotik->runCommand('/system/resource/print'), true);
            $license     = json_decode($mikrotik->runCommand('/system/license/print'), true);
            $identity    = json_decode($mikrotik->runCommand('/system/identity/print'), true);
            $clock       = json_decode($mikrotik->runCommand('/system/clock/print'), true);
            $packages    = json_decode($mikrotik->runCommand('/system/package/print'), true);
        } catch (\Exception $e) {
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
            $routerboard = $resources = $license = $identity = $clock = $packages = null;
        }

        return view('mikrotik.routerboard', compact('routerboard', 'resources', 'license', 'identity', 'clock', 'packages'));
    }
}
