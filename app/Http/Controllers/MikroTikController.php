<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use App\Services\RouterSession;

class MikroTikController extends Controller
{
    private function mikrotik(): MikroTikService
    {
        return MikroTikService::fromSession();
    }

    public function status()
    {
        try {
            $identity = $this->mikrotik()->getIdentity();
        } catch (\Exception $e) {
            $identity = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('mikrotik.status', compact('identity'));
    }

    public function interfaces()
    {
        try {
            $interfaces = $this->mikrotik()->getInterfaces();
        } catch (\Exception $e) {
            $interfaces = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('mikrotik.interfaces', compact('interfaces'));
    }

    public function hotspotServers()
    {
        try {
            $servers = $this->mikrotik()->listHotspotServers();
        } catch (\Exception $e) {
            $servers = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('hotspot.servers', compact('servers'));
    }

    public function ServerProfiles()
    {
        try {
            $profiles = $this->mikrotik()->listServerProfiles();
        } catch (\Exception $e) {
            $profiles = [];
            session()->flash('error', 'MikroTik error: ' . $e->getMessage());
        }
        return view('hotspot.serverProfile', compact('profiles'));
    }
}
