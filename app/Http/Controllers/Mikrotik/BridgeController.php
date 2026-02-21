<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Services\BridgeService;
use App\Services\RouterSession;

class BridgeController extends Controller
{
    private function bridge(): BridgeService
    {
        return BridgeService::fromSession();
    }

    public function bridges()
    {
        try {
            $bridges = $this->bridge()->listBridges();
        } catch (\Exception $e) {
            $bridges = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('mikrotik.Bridge.bridges', compact('bridges'));
    }

    public function ports()
    {
        try {
            $ports = $this->bridge()->listBridgePorts();
        } catch (\Exception $e) {
            $ports = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('mikrotik.Bridge.ports', compact('ports'));
    }

    public function hosts()
    {
        try {
            $hosts = $this->bridge()->listBridgeHosts();
        } catch (\Exception $e) {
            $hosts = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('mikrotik.Bridge.hosts', compact('hosts'));
    }
}
