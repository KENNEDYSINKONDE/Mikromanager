<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) return redirect()->route('router.select');
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:100',
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'phone'         => 'nullable|string|max:20',
        ]);

        // ── Create tenant (ISP account) ───────────────────────────────────────
        $tenant = Tenant::create([
            'name'          => $validated['business_name'],
            'email'         => $validated['email'],
            'slug'          => Tenant::generateSlug($validated['business_name']),
            'phone'         => $validated['phone'] ?? null,
            'plan'          => 'trial',
            'status'        => 'active',
            'trial_ends_at' => now()->addDays(14),
            ...Tenant::planDefaults('trial'),
        ]);

        // ── Create owner user ─────────────────────────────────────────────────
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        // ── Log them in immediately ───────────────────────────────────────────
        Auth::login($user);
        $request->session()->regenerate();
        $user->recordLogin($request->ip());

        return redirect()->route('router.select')
            ->with('success',
                "Welcome to MikroTik Manager, <strong>{$tenant->name}</strong>! " .
                "Your 14-day free trial has started."
            );
    }
}
