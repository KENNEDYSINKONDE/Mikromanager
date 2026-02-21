@extends('layouts.app')

@section('title', 'Dashboard — MikroTik Manager')

@section('content')

<div class="pagetitle">
  <h1>Dashboard</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('layout.dashboard') }}">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

{{-- ── Router offline warning ─────────────────────────────────────────── --}}
@if(!$connected)
<div class="alert alert-warning d-flex align-items-center gap-2">
  <i class="bi bi-exclamation-triangle-fill fs-5"></i>
  <div>
    <strong>Router unreachable.</strong> Could not fetch live data.
    <a href="{{ route('router.disconnect') }}" class="alert-link ms-2"
       onclick="event.preventDefault(); document.getElementById('disconnect-form').submit();">
      Reconnect
    </a>
    <form id="disconnect-form" action="{{ route('router.disconnect') }}" method="POST" class="d-none">@csrf</form>
  </div>
</div>
@endif

{{-- ── Top stat cards ──────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

  {{-- Router Identity --}}
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
          <i class="bi bi-router text-primary fs-4"></i>
        </div>
        <div>
          <p class="text-muted small mb-0">Router</p>
          <h6 class="mb-0 fw-bold">{{ $identity[0]['name'] ?? ($activeRouter->identity ?? $activeRouter->name) }}</h6>
          <span class="badge bg-success-subtle text-success mt-1">
            <i class="bi bi-circle-fill" style="font-size:7px"></i> Online
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- CPU --}}
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10">
          <i class="bi bi-cpu text-success fs-4"></i>
        </div>
        <div class="flex-fill">
          <p class="text-muted small mb-0">CPU Load</p>
          @php $cpu = (int)($resources[0]['cpu-load'] ?? 0); @endphp
          <h6 class="mb-1 fw-bold">{{ $cpu }}%</h6>
          <div class="progress" style="height:4px">
            <div class="progress-bar {{ $cpu > 80 ? 'bg-danger' : ($cpu > 50 ? 'bg-warning' : 'bg-success') }}"
                 style="width:{{ $cpu }}%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Memory --}}
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10">
          <i class="bi bi-memory text-warning fs-4"></i>
        </div>
        <div class="flex-fill">
          <p class="text-muted small mb-0">Memory</p>
          @php
            $memFree  = (int)($resources[0]['free-memory']  ?? 0);
            $memTotal = (int)($resources[0]['total-memory'] ?? 1);
            $memUsed  = $memTotal - $memFree;
            $memPct   = $memTotal > 0 ? round(($memUsed / $memTotal) * 100) : 0;
            $memUsedMb  = round($memUsed  / 1048576, 1);
            $memTotalMb = round($memTotal / 1048576, 1);
          @endphp
          <h6 class="mb-1 fw-bold">{{ $memUsedMb }} / {{ $memTotalMb }} MB</h6>
          <div class="progress" style="height:4px">
            <div class="progress-bar {{ $memPct > 80 ? 'bg-danger' : ($memPct > 60 ? 'bg-warning' : 'bg-info') }}"
                 style="width:{{ $memPct }}%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Uptime --}}
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10">
          <i class="bi bi-clock-history text-info fs-4"></i>
        </div>
        <div>
          <p class="text-muted small mb-0">Uptime</p>
          <h6 class="mb-0 fw-bold">{{ $resources[0]['uptime'] ?? '—' }}</h6>
          <small class="text-muted">{{ $resources[0]['version'] ?? '' }}</small>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ── Voucher stats + Router info row ─────────────────────────────────── --}}
<div class="row g-3 mb-4">

  {{-- Voucher stats --}}
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="card-title mb-0">Hotspot Vouchers</h5>
          <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-ticket-perforated me-1"></i> Manage
          </a>
        </div>
        @php
          $routerId     = $activeRouter->id;
          $totalV    = \App\Models\Voucher::where('router_id', $routerId)->count();
          $activeV   = \App\Models\Voucher::where('router_id', $routerId)->where('status','active')->count();
          $usedV     = \App\Models\Voucher::where('router_id', $routerId)->where('status','used')->count();
          $expiredV  = \App\Models\Voucher::where('router_id', $routerId)->where('status','expired')->count();
          $unsyncedV = \App\Models\Voucher::where('router_id', $routerId)->where('mikrotik_synced', false)->count();
        @endphp
        <div class="row g-3 text-center">
          <div class="col">
            <div class="border rounded p-3">
              <div class="fs-4 fw-bold text-dark">{{ $totalV }}</div>
              <small class="text-muted">Total</small>
            </div>
          </div>
          <div class="col">
            <div class="border rounded p-3 border-success">
              <div class="fs-4 fw-bold text-success">{{ $activeV }}</div>
              <small class="text-muted">Active</small>
            </div>
          </div>
          <div class="col">
            <div class="border rounded p-3 border-secondary">
              <div class="fs-4 fw-bold text-secondary">{{ $usedV }}</div>
              <small class="text-muted">Used</small>
            </div>
          </div>
          <div class="col">
            <div class="border rounded p-3 border-danger">
              <div class="fs-4 fw-bold text-danger">{{ $expiredV }}</div>
              <small class="text-muted">Expired</small>
            </div>
          </div>
          <div class="col">
            <div class="border rounded p-3 border-warning">
              <div class="fs-4 fw-bold text-warning">{{ $unsyncedV }}</div>
              <small class="text-muted">Unsynced</small>
            </div>
          </div>
        </div>

        {{-- Quick actions --}}
        <div class="d-flex gap-2 mt-3">
          <a href="{{ route('vouchers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> New Voucher
          </a>
          <a href="{{ route('vouchers.create') }}?bulk=1" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-layers me-1"></i> Bulk Generate
          </a>
          <a href="{{ route('vouchers.export.csv') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Router info panel --}}
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title">Router Info</h5>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td class="text-muted small">Identity</td>
            <td class="fw-semibold small">{{ $identity[0]['name'] ?? '—' }}</td>
          </tr>
          <tr>
            <td class="text-muted small">Model</td>
            <td class="fw-semibold small">{{ $routerboard[0]['model'] ?? '—' }}</td>
          </tr>
          <tr>
            <td class="text-muted small">RouterOS</td>
            <td class="fw-semibold small">{{ $resources[0]['version'] ?? '—' }}</td>
          </tr>
          <tr>
            <td class="text-muted small">Architecture</td>
            <td class="fw-semibold small">{{ $resources[0]['architecture-name'] ?? '—' }}</td>
          </tr>
          <tr>
            <td class="text-muted small">CPU</td>
            <td class="fw-semibold small">{{ $resources[0]['cpu'] ?? '—' }}
              ({{ $resources[0]['cpu-count'] ?? '?' }} core)</td>
          </tr>
          <tr>
            <td class="text-muted small">Uptime</td>
            <td class="fw-semibold small">{{ $resources[0]['uptime'] ?? '—' }}</td>
          </tr>
          <tr>
            <td class="text-muted small">Date / Time</td>
            <td class="fw-semibold small">
              {{ $clock[0]['date'] ?? '' }} {{ $clock[0]['time'] ?? '' }}
            </td>
          </tr>
          <tr>
            <td class="text-muted small">License</td>
            <td class="fw-semibold small">Level {{ $license[0]['nlevel'] ?? '—' }}</td>
          </tr>
          <tr>
            <td class="text-muted small">Host</td>
            <td class="fw-semibold small" style="font-family:monospace">
              {{ $activeRouter->host }}:{{ $activeRouter->port }}
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

</div>

{{-- ── Quick navigation cards ───────────────────────────────────────────── --}}
<div class="row g-3">
  <div class="col-12">
    <h6 class="text-muted text-uppercase" style="font-size:11px;letter-spacing:1px">Quick Access</h6>
  </div>

  @php
  $quickLinks = [
    ['route' => 'profiles.index',          'icon' => 'bi-person-badge',    'color' => 'primary',   'label' => 'User Profiles',    'desc' => 'Manage hotspot profiles'],
    ['route' => 'vouchers.index',          'icon' => 'bi-ticket-perforated','color' => 'success',  'label' => 'Vouchers',         'desc' => 'View & generate vouchers'],
    ['route' => 'hotspot.servers',         'icon' => 'bi-wifi',             'color' => 'info',     'label' => 'Hotspot Servers',  'desc' => 'Active hotspot servers'],
    ['route' => 'mikrotik.interfaces',     'icon' => 'bi-diagram-3',        'color' => 'warning',  'label' => 'Interfaces',       'desc' => 'Network interfaces'],
    ['route' => 'mikrotik.routerboard',    'icon' => 'bi-motherboard',      'color' => 'secondary','label' => 'RouterBOARD',      'desc' => 'Hardware details'],
    ['route' => 'mikrotik.terminal',       'icon' => 'bi-terminal',         'color' => 'dark',     'label' => 'Terminal',         'desc' => 'Run CLI commands'],
  ];
  @endphp

  @foreach($quickLinks as $link)
  <div class="col-xxl-2 col-lg-3 col-md-4 col-6">
    <a href="{{ route($link['route']) }}" class="text-decoration-none">
      <div class="card text-center p-3 h-100 quick-link-card">
        <div class="mb-2">
          <span class="rounded-circle d-inline-flex align-items-center justify-content-center
                       bg-{{ $link['color'] }} bg-opacity-10"
                style="width:48px;height:48px">
            <i class="bi {{ $link['icon'] }} text-{{ $link['color'] }} fs-5"></i>
          </span>
        </div>
        <div class="fw-semibold small">{{ $link['label'] }}</div>
        <div class="text-muted" style="font-size:11px">{{ $link['desc'] }}</div>
      </div>
    </a>
  </div>
  @endforeach

</div>

@endsection

@push('styles')
<style>
  .quick-link-card {
    border: 1px solid #e9ecef;
    transition: transform .15s, box-shadow .15s, border-color .15s;
    cursor: pointer;
  }
  .quick-link-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border-color: #4154f1;
  }
  .card-icon {
    width: 48px;
    height: 48px;
    font-size: 20px;
    flex-shrink: 0;
  }
</style>
@endpush
