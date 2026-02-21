@extends('admin.layout')
@section('title', 'All ISPs')
@section('page-title', 'All ISP Accounts')

@section('content')

{{-- Filters --}}
<div class="admin-table mb-3 p-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Search by name, email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="active"    {{ request('status')=='active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status')=='suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="plan" class="form-select form-select-sm">
                <option value="">All Plans</option>
                <option value="trial"      {{ request('plan')=='trial'      ? 'selected' : '' }}>Trial</option>
                <option value="starter"    {{ request('plan')=='starter'    ? 'selected' : '' }}>Starter</option>
                <option value="pro"        {{ request('plan')=='pro'        ? 'selected' : '' }}>Pro</option>
                <option value="enterprise" {{ request('plan')=='enterprise' ? 'selected' : '' }}>Enterprise</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">
                <i class="bi bi-search me-1"></i> Filter
            </button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted" style="font-size:13px">{{ $tenants->total() }} ISPs found</span>
    <a href="{{ route('admin.tenants.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add New ISP
    </a>
</div>

<div class="admin-table">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>ISP Name</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Routers</th>
                <th>Users</th>
                <th>Vouchers</th>
                <th>Trial / Expires</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
            <tr>
                <td class="text-muted" style="font-size:12px">{{ $tenant->id }}</td>
                <td>
                    <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-decoration-none fw-semibold text-dark">
                        {{ $tenant->name }}
                    </a>
                    <div class="text-muted" style="font-size:11px">{{ $tenant->email }}</div>
                </td>
                <td><span class="plan-badge plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span></td>
                <td><span class="plan-badge status-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span></td>
                <td><span class="fw-semibold">{{ $tenant->routers_count }}</span> / {{ $tenant->max_routers }}</td>
                <td><span class="fw-semibold">{{ $tenant->users_count }}</span> / {{ $tenant->max_users }}</td>
                <td><span class="fw-semibold">{{ number_format($tenant->vouchers_count) }}</span> / {{ number_format($tenant->max_vouchers) }}</td>
                <td style="font-size:11px;color:#64748b">
                    @if($tenant->plan === 'trial' && $tenant->trial_ends_at)
                        @if($tenant->trial_ends_at->isFuture())
                            <span class="text-warning fw-semibold">{{ $tenant->trialDaysLeft() }}d left</span>
                        @else
                            <span class="text-danger fw-semibold">Expired</span>
                        @endif
                    @elseif($tenant->subscription_ends_at)
                        {{ $tenant->subscription_ends_at->format('d M Y') }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.tenants.show', $tenant) }}"
                           class="btn btn-xs btn-outline-primary" style="font-size:11px;padding:3px 8px"
                           title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.tenants.edit', $tenant) }}"
                           class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:3px 8px"
                           title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($tenant->status === 'active')
                        <form action="{{ route('admin.tenants.suspend', $tenant) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-outline-warning"
                                    style="font-size:11px;padding:3px 8px" title="Suspend"
                                    data-confirm="Suspend {{ $tenant->name }}?"
                                    data-confirm-text="They will be logged out and blocked."
                                    data-confirm-btn="Suspend">
                                <i class="bi bi-slash-circle"></i>
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.tenants.activate', $tenant) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-outline-success"
                                    style="font-size:11px;padding:3px 8px" title="Activate">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-5">
                <i class="bi bi-building" style="font-size:32px;display:block;margin-bottom:8px"></i>
                No ISPs found
            </td></tr>
            @endforelse
        </tbody>
    </table>

    @if($tenants->hasPages())
    <div class="p-3 border-top">
        {{ $tenants->links() }}
    </div>
    @endif
</div>

@endsection
