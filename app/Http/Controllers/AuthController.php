<?php

namespace App\Http\Controllers;

use App\Models\MikroTikRouter;
use App\Services\RouterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // ── Show Login ───────────────────────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('router.select');
        }
        return view('login');
    }

    // ── Process Login ────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::withoutTenantScope()->where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withInput()
                ->with('error', 'Your account has been deactivated. Contact your administrator.');
        }

        // Block suspended or cancelled tenants from logging in
        if ($user && $user->tenant) {
            if ($user->tenant->status === 'suspended') {
                return back()->withInput()
                    ->with('error', 'Your account has been suspended. Please contact support.');
            }
            if ($user->tenant->status === 'cancelled') {
                return back()->withInput()
                    ->with('error', 'Your account has been cancelled. Please contact support.');
            }
            if ($user->tenant->isTrialExpired()) {
                return back()->withInput()
                    ->with('error', 'Your 14-day trial has expired. Please upgrade to continue.');
            }
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput()
                ->with('error', 'Invalid email or password. Please try again.');
        }

        Auth::user()->recordLogin($request->ip());
        $request->session()->regenerate();

        $user = Auth::user();

        // Superadmin goes straight to admin panel
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, <strong>' . $user->name . '</strong>!');
        }

        return redirect()->route('router.select')
            ->with('success', 'Welcome back, <strong>' . $user->name . '</strong>!');
    }

    // ── Logout ───────────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $name = Auth::user()->name;

        RouterSession::logout();   // clear router session first
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', "Goodbye, <strong>{$name}</strong>. You have been logged out safely.");
    }

    // ── Router Select ────────────────────────────────────────────────────────
    public function showRouterSelect()
    {
        if (RouterSession::check()) {
            return redirect()->route('dashboard');
        }

        // Only show routers belonging to the authenticated user
        $recentRouters = MikroTikRouter::where('user_id', Auth::id())
            ->latest('last_connected_at')
            ->take(10)
            ->get();

        return view('router-select', compact('recentRouters'));
    }

    // ── Profile ──────────────────────────────────────────────────────────────
    public function showProfile()
    {
        return view('auth.profile');
    }

    // ── Avatar upload ──────────────────────────────────────────────────────────
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    // ── Remove avatar ──────────────────────────────────────────────────────────
    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Profile photo removed.');
    }

    public function updateProfile(Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        $user->update($validated);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }
}
