@extends('admin.layout')
@section('title', $tenant->name)
@section('page-title', $tenant->name)

@section('content')

<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form action="{{ route('admin.tenants.impersonate', $tenant) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-purple"
                style="border-color:#8b5cf6;color:#8b5cf6"
                data-confirm="Login as {{ $tenant->name }}?"
                data-confirm-text="You will be viewing the app as this ISP's owner."
                data-confirm-btn="Impersonate">
            <i class="bi bi-person-fill-gear me-1"></i> Impersonate
        </button>
    </form>
    @if($tenant->status === 'active')
    <form action="{{ route('admin.tenants.suspend', $tenant) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-warning"
                data-confirm="Suspend {{ $tenant->name }}?"
                data-confirm-text="This will block their access immediately."
                data-confirm-btn="Suspend">
            <i class="bi bi-slash-circle me-1"></i> Suspend
        </button>
    </form>
    @else
    <form action="{{ route('admin.tenants.activate', $tenant) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="bi bi-check-circle me-1"></i> Activate
        </button>
    </form>
    @endif
    <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST" class="ms-auto">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger"
                data-confirm="DELETE {{ $tenant->name }}?"
                data-confirm-text="This will permanently delete ALL their data including vouchers, routers, and users. This cannot be undone."
                data-confirm-btn="Yes, Delete Everything">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
</div>

<div class="row g-3">

    {{-- ISP Info --}}
    <div class="col-lg-4">
        <div class="stat-card mb-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon" style="background:#eff6ff;color:#3b82f6;font-size:24px;width:56px;height:56px;border-radius:14px">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6">{{ $tenant->name }}</div>
                    <div class="text-muted" style="font-size:12px">{{ $tenant->email }}</div>
                </div>
            </div>
            <table class="table table-sm table-borderless mb-0" style="font-size:13px">
                <tr>
                    <td class="text-muted ps-0">Status</td>
                    <td><span class="plan-badge status-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Plan</td>
                    <td><span class="plan-badge plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Phone</td>
                    <td>{{ $tenant->phone ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Registered</td>
                    <td>{{ $tenant->created_at->format('d M Y') }}</td>
                </tr>
                @if($tenant->trial_ends_at)
                <tr>
                    <td class="text-muted ps-0">Trial ends</td>
                    <td class="{{ $tenant->trial_ends_at->isPast() ? 'text-danger' : 'text-warning' }} fw-semibold">
                        {{ $tenant->trial_ends_at->format('d M Y') }}
                        @if($tenant->trial_ends_at->isFuture())
                            ({{ $tenant->trialDaysLeft() }}d left)
                        @else
                            (Expired)
                        @endif
                    </td>
                </tr>
                @endif
                @if($tenant->subscription_ends_at)
                <tr>
                    <td class="text-muted ps-0">Sub. ends</td>
                    <td>{{ $tenant->subscription_ends_at->format('d M Y') }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Usage limits --}}
        <div class="stat-card mb-3">
            <h6 class="fw-bold mb-3">Plan Usage</h6>
            @php
            $usages = [
                ['label'=>'Routers',  'used'=>$tenant->routers_count,  'max'=>$tenant->max_routers,  'color'=>'#3b82f6'],
                ['label'=>'Users',    'used'=>$tenant->users_count,    'max'=>$tenant->max_users,    'color'=>'#8b5cf6'],
                ['label'=>'Vouchers', 'used'=>$tenant->vouchers_count, 'max'=>$tenant->max_vouchers, 'color'=>'#22c55e'],
            ];
            @endphp
            @foreach($usages as $u)
            @php $pct = $u['max'] > 0 ? min(100, round(($u['used']/$u['max'])*100)) : 0; @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:12px">
                    <span class="fw-semibold">{{ $u['label'] }}</span>
                    <span class="text-muted">{{ number_format($u['used']) }} / {{ number_format($u['max']) }}</span>
                </div>
                <div class="progress" style="height:5px;background:#f1f5f9;border-radius:3px">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $u['color'] }};border-radius:3px"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-8">

        {{-- Change plan --}}
        <div class="stat-card mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-arrow-up-circle me-2 text-primary"></i>Change Plan</h6>
            <form action="{{ route('admin.tenants.changePlan', $tenant) }}" method="POST" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <select name="plan" class="form-select form-select-sm">
                        @foreach(['trial','starter','pro','enterprise'] as $plan)
                        <option value="{{ $plan }}" {{ $tenant->plan === $plan ? 'selected' : '' }}>
                            {{ ucfirst($plan) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="months" class="form-select form-select-sm">
                        @foreach([1,3,6,12,24,36] as $m)
                        <option value="{{ $m }}" {{ $m==12?'selected':'' }}>{{ $m }} month{{ $m>1?'s':'' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-check me-1"></i> Update Plan
                    </button>
                </div>
            </form>
        </div>

        {{-- Extend trial --}}
        @if($tenant->plan === 'trial')
        <div class="stat-card mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-warning"></i>Extend Trial</h6>
            <form action="{{ route('admin.tenants.extendTrial', $tenant) }}" method="POST" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <select name="days" class="form-select form-select-sm">
                        @foreach([7,14,30,60,90] as $d)
                        <option value="{{ $d }}">{{ $d }} days</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-warning w-100">
                        <i class="bi bi-plus me-1"></i> Extend
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Routers --}}
        <div class="admin-table mb-3">
            <div class="p-3 border-bottom fw-bold" style="font-size:13px">
                <i class="bi bi-router me-2 text-primary"></i>Routers ({{ count($routers) }})
            </div>
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Host</th><th>Status</th><th>Last Connected</th></tr></thead>
                <tbody>
                    @forelse($routers as $router)
                    <tr>
                        <td class="fw-semibold">{{ $router->name }}</td>
                        <td style="font-family:monospace;font-size:12px">{{ $router->host }}:{{ $router->port }}</td>
                        <td>
                            <span class="plan-badge {{ $router->status === 'online' ? 'status-active' : 'status-suspended' }}">
                                {{ ucfirst($router->status) }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:12px">
                            {{ $router->last_connected_at?->diffForHumans() ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No routers connected</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Users --}}
        <div class="admin-table mb-3">
            <div class="p-3 border-bottom fw-bold" style="font-size:13px">
                <i class="bi bi-people me-2 text-primary"></i>Users ({{ count($users) }})
            </div>
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td style="font-size:12px">{{ $user->email }}</td>
                        <td><span class="badge bg-{{ $user->role_badge }}">{{ ucfirst($user->role) }}</span></td>
                        <td class="text-muted" style="font-size:12px">
                            {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No users</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Voucher stats --}}
        <div class="row g-2">
            @php
            $vstats = [
                ['label'=>'Total',   'value'=>$voucherStats['total'],   'color'=>'#0f172a'],
                ['label'=>'Active',  'value'=>$voucherStats['active'],  'color'=>'#22c55e'],
                ['label'=>'Used',    'value'=>$voucherStats['used'],    'color'=>'#64748b'],
                ['label'=>'Expired', 'value'=>$voucherStats['expired'], 'color'=>'#ef4444'],
            ];
            @endphp
            @foreach($vstats as $vs)
            <div class="col-3">
                <div class="stat-card text-center p-3">
                    <div style="font-size:22px;font-weight:800;color:{{ $vs['color'] }}">{{ number_format($vs['value']) }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $vs['label'] }} Vouchers</div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>

@endsection
