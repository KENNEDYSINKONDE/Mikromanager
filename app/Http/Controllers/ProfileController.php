<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use App\Services\RouterSession;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private function mikrotik(): MikroTikService
    {
        return MikroTikService::fromSession();
    }

    public function index()
    {
        try {
            $profiles = $this->mikrotik()->listProfiles();
        } catch (\Exception $e) {
            $profiles = [];
            session()->flash('error', 'Could not connect to MikroTik: ' . $e->getMessage());
        }
        return view('profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:64',
            'rate_limit'   => ['nullable', 'string', 'regex:/^\d+[KMG]?\/\d+[KMG]?$/i'],
            'session_time' => ['nullable', 'string', 'regex:/^\d+[smhd]$/i'],
            'shared_users' => 'nullable|integer|min:1|max:9999',
        ]);

        try {
            $this->mikrotik()->createProfile(
                name:        $validated['name'],
                rateLimit:   $validated['rate_limit']   ?? null,
                sessionTime: $validated['session_time'] ?? null,
                sharedUsers: (int) ($validated['shared_users'] ?? 1),
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create profile: ' . $e->getMessage());
        }

        return redirect()->route('profiles.index')
            ->with('success', "Profile <strong>{$validated['name']}</strong> created successfully.");
    }

    public function show(string $profile)
    {
        try {
            $profileData = $this->mikrotik()->getProfile($profile);
        } catch (\Exception $e) {
            return redirect()->route('profiles.index')->with('error', 'MikroTik error: ' . $e->getMessage());
        }

        if (!$profileData) {
            return redirect()->route('profiles.index')->with('error', "Profile \"{$profile}\" not found.");
        }

        return view('profiles.show', ['profile' => $profileData]);
    }

    public function edit(string $profile)
    {
        try {
            $profileData = $this->mikrotik()->getProfile($profile);
        } catch (\Exception $e) {
            return redirect()->route('profiles.index')->with('error', 'MikroTik error: ' . $e->getMessage());
        }

        if (!$profileData) {
            return redirect()->route('profiles.index')->with('error', "Profile \"{$profile}\" not found.");
        }

        return view('profiles.edit', ['profile' => $profileData]);
    }

    public function update(Request $request, string $profile)
    {
        $validated = $request->validate([
            'rate_limit'   => ['nullable', 'string', 'regex:/^\d+[KMG]?\/\d+[KMG]?$/i'],
            'session_time' => ['nullable', 'string', 'regex:/^\d+[smhd]$/i'],
            'shared_users' => 'nullable|integer|min:1|max:9999',
        ]);

        try {
            $this->mikrotik()->updateProfile(
                name:        $profile,
                rateLimit:   $validated['rate_limit']   ?? null,
                sessionTime: $validated['session_time'] ?? null,
                sharedUsers: (int) ($validated['shared_users'] ?? 1),
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }

        return redirect()->route('profiles.index')
            ->with('success', "Profile <strong>{$profile}</strong> updated successfully.");
    }

    public function destroy(string $profile)
    {
        try {
            $this->mikrotik()->removeProfile($profile);
        } catch (\Exception $e) {
            return redirect()->route('profiles.index')->with('error', 'Failed to delete profile: ' . $e->getMessage());
        }

        return redirect()->route('profiles.index')
            ->with('success', "Profile <strong>{$profile}</strong> deleted successfully.");
    }
}
