@extends('admin.layout')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['label'=>'Total ISPs',       'value'=>$stats['total_tenants'],  'icon'=>'bi-building',      'color'=>'#3b82f6', 'bg'=>'#eff6ff'],
        ['label'=>'Active ISPs',      'value'=>$stats['active_tenants'], 'icon'=>'bi-check-circle',  'color'=>'#22c55e', 'bg'=>'#f0fdf4'],
        ['label'=>'On Trial',         'value'=>$stats['trial_tenants'],  'icon'=>'bi-clock-history', 'color'=>'#f59e0b', 'bg'=>'#fffbeb'],
        ['label'=>'Suspended',        'value'=>$stats['suspended'],      'icon'=>'bi-slash-circle',  'color'=>'#ef4444', 'bg'=>'#fef2f2'],
        ['label'=>'Total Users',      'value'=>$stats['total_users'],    'icon'=>'bi-people',        'color'=>'#8b5cf6', 'bg'=>'#f5f3ff'],
        ['label'=>'Total Routers',    'value'=>$stats['total_routers'],  'icon'=>'bi-router',        'color'=>'#06b6d4', 'bg'=>'#ecfeff'],
        ['label'=>'Online Routers',   'value'=>$stats['online_routers'], 'icon'=>'bi-wifi',          'color'=>'#22c55e', 'bg'=>'#f0fdf4'],
        ['label'=>'Total Vouchers',   'value'=>number_format($stats['total_vouchers']), 'icon'=>'bi-ticket-perforated', 'color'=>'#f97316', 'bg'=>'#fff7ed'],
    ];
    @endphp

    @foreach($cards as $card)
    <div class="col-xxl-3 col-lg-4 col-md-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="stat-value">{{ $card['value'] }}</div>
                <div class="stat-label">{{ $card['label'] }}</div>
            </div>
            <div class="stat-icon" style="background:{{ $card['bg'] }};color:{{ $card['color'] }}">
                <i class="bi {{ $card['icon'] }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Recent ISPs --}}
    <div class="col-lg-8">
        <div class="admin-table">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <h6 class="mb-0 fw-bold">Recently Registered ISPs</h6>
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>ISP Name</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Routers</th>
                        <th>Registered</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTenants as $tenant)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $tenant->name }}</div>
                            <div class="text-muted" style="font-size:11px">{{ $tenant->email }}</div>
                        </td>
                        <td>
                            <span class="plan-badge plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span>
                        </td>
                        <td>
                            <span class="plan-badge status-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span>
                        </td>
                        <td>{{ $tenant->routers_count ?? 0 }}</td>
                        <td class="text-muted" style="font-size:12px">{{ $tenant->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:3px 10px">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No ISPs registered yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Plan distribution --}}
    <div class="col-lg-4">
        <div class="stat-card h-100">
            <h6 class="fw-bold mb-3">ISPs by Plan</h6>
            @php
            $plans = ['trial'=>'#f59e0b','starter'=>'#3b82f6','pro'=>'#8b5cf6','enterprise'=>'#22c55e'];
            @endphp
            @foreach($plans as $plan => $color)
            @php $count = $planStats[$plan] ?? 0; $total = $stats['total_tenants'] ?: 1; @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:13px;font-weight:600">{{ ucfirst($plan) }}</span>
                    <span style="font-size:13px;color:#64748b">{{ $count }}</span>
                </div>
                <div class="progress" style="height:6px;border-radius:3px;background:#f1f5f9">
                    <div class="progress-bar" style="width:{{ round(($count/$total)*100) }}%;background:{{ $color }};border-radius:3px"></div>
                </div>
            </div>
            @endforeach

            <hr class="my-3">
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary w-100">
                <i class="bi bi-plus-circle me-2"></i> Add New ISP
            </a>
        </div>
    </div>
</div>

@endsection
