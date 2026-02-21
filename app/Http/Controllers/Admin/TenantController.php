<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Voucher;
use App\Models\MikroTikRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    // ── Dashboard — overview stats ────────────────────────────────────────────
    public function dashboard()
    {
        $stats = [
            'total_tenants'   => Tenant::count(),
            'active_tenants'  => Tenant::where('status', 'active')->count(),
            'trial_tenants'   => Tenant::where('plan', 'trial')->count(),
            'suspended'       => Tenant::where('status', 'suspended')->count(),
            'total_users'     => User::withoutTenantScope()->count(),
            'total_routers'   => MikroTikRouter::withoutTenantScope()->count(),
            'total_vouchers'  => Voucher::withoutTenantScope()->count(),
            'online_routers'  => MikroTikRouter::withoutTenantScope()->where('status', 'online')->count(),
        ];

        $recentTenants = Tenant::latest()->take(5)->get();

        $planStats = Tenant::selectRaw('plan, count(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        return view('admin.dashboard', compact('stats', 'recentTenants', 'planStats'));
    }

    // ── List all tenants ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Tenant::withCount(['users', 'routers', 'vouchers']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('slug',  'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $tenants = $query->latest()->paginate(20)->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    // ── View single tenant ────────────────────────────────────────────────────
    public function show(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'routers', 'vouchers']);

        $users   = User::withoutTenantScope()->where('tenant_id', $tenant->id)->get();
        $routers = MikroTikRouter::withoutTenantScope()->where('tenant_id', $tenant->id)->get();
        $voucherStats = [
            'total'   => Voucher::withoutTenantScope()->where('tenant_id', $tenant->id)->count(),
            'active'  => Voucher::withoutTenantScope()->where('tenant_id', $tenant->id)->where('status', 'active')->count(),
            'used'    => Voucher::withoutTenantScope()->where('tenant_id', $tenant->id)->where('status', 'used')->count(),
            'expired' => Voucher::withoutTenantScope()->where('tenant_id', $tenant->id)->where('status', 'expired')->count(),
        ];

        return view('admin.tenants.show', compact('tenant', 'users', 'routers', 'voucherStats'));
    }

    // ── Create tenant form ────────────────────────────────────────────────────
    public function create()
    {
        return view('admin.tenants.create');
    }

    // ── Store new tenant ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'owner_name'    => 'required|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'plan'          => 'required|in:trial,starter,pro,enterprise',
            'password'      => 'required|string|min:8',
        ]);

        $tenant = Tenant::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'slug'                 => Tenant::generateSlug($validated['name']),
            'phone'                => $validated['phone'] ?? null,
            'plan'                 => $validated['plan'],
            'status'               => 'active',
            'trial_ends_at'        => $validated['plan'] === 'trial' ? now()->addDays(14) : null,
            'subscription_ends_at' => $validated['plan'] !== 'trial' ? now()->addYear() : null,
            ...Tenant::planDefaults($validated['plan']),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => $validated['owner_name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', "ISP account <strong>{$tenant->name}</strong> created successfully.");
    }

    // ── Edit tenant ───────────────────────────────────────────────────────────
    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    // ── Update tenant ─────────────────────────────────────────────────────────
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'email'                => 'required|email|unique:tenants,email,' . $tenant->id,
            'phone'                => 'nullable|string|max:20',
            'plan'                 => 'required|in:trial,starter,pro,enterprise',
            'status'               => 'required|in:active,suspended,cancelled',
            'max_routers'          => 'required|integer|min:1',
            'max_vouchers'         => 'required|integer|min:1',
            'max_users'            => 'required|integer|min:1',
            'trial_ends_at'        => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    // ── Suspend tenant ────────────────────────────────────────────────────────
    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);
        return back()->with('success', "<strong>{$tenant->name}</strong> has been suspended.");
    }

    // ── Activate tenant ───────────────────────────────────────────────────────
    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        return back()->with('success', "<strong>{$tenant->name}</strong> has been activated.");
    }

    // ── Change plan ───────────────────────────────────────────────────────────
    public function changePlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan'   => 'required|in:trial,starter,pro,enterprise',
            'months' => 'nullable|integer|min:1|max:36',
        ]);

        $months  = (int) ($validated['months'] ?? 12);
        $limits  = Tenant::planDefaults($validated['plan']);

        $tenant->update([
            'plan'                 => $validated['plan'],
            'status'               => 'active',
            'subscription_ends_at' => now()->addMonths($months),
            'trial_ends_at'        => null,
            ...$limits,
        ]);

        return back()->with('success',
            "Plan changed to <strong>{$validated['plan']}</strong> for {$months} months."
        );
    }

    // ── Extend trial ──────────────────────────────────────────────────────────
    public function extendTrial(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:90',
        ]);

        $newEnd = ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
            ? $tenant->trial_ends_at->addDays($validated['days'])
            : now()->addDays($validated['days']);

        $tenant->update([
            'trial_ends_at' => $newEnd,
            'status'        => 'active',
        ]);

        return back()->with('success',
            "Trial extended by {$validated['days']} days. New end: {$newEnd->format('d M Y')}."
        );
    }

    // ── Delete tenant (and all their data) ────────────────────────────────────
    public function destroy(Tenant $tenant)
    {
        $name = $tenant->name;

        // Delete all related data — cascade handles DB, but be explicit
        Voucher::withoutTenantScope()->where('tenant_id', $tenant->id)->delete();
        MikroTikRouter::withoutTenantScope()->where('tenant_id', $tenant->id)->delete();
        User::withoutTenantScope()->where('tenant_id', $tenant->id)->delete();
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', "ISP account <strong>{$name}</strong> and all data permanently deleted.");
    }

    // ── Impersonate tenant owner ──────────────────────────────────────────────
    public function impersonate(Tenant $tenant)
    {
        $owner = User::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('role', 'owner')
            ->first();

        if (!$owner) {
            return back()->with('error', 'No owner user found for this tenant.');
        }

        // Store original admin ID so we can return later
        session(['impersonating_as' => $owner->id, 'impersonator_id' => Auth::id()]);

        Auth::login($owner);

        return redirect()->route('router.select')
            ->with('success', "Now viewing as <strong>{$tenant->name}</strong>. <a href='" . route('admin.impersonate.stop') . "'>Stop impersonating</a>");
    }

    // ── Stop impersonating ────────────────────────────────────────────────────
    public function stopImpersonate()
    {
        $adminId = session('impersonator_id');
        session()->forget(['impersonating_as', 'impersonator_id', 'mikrotik_router_id']);

        if ($adminId) {
            $admin = User::withoutTenantScope()->find($adminId);
            if ($admin) Auth::login($admin);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Returned to admin panel.');
    }
}
