@extends('layouts.app')

@section('title', 'Voucher Reports')

@section('content')

<div class="pagetitle">
    <h1>Voucher Reports & Analytics</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('layout.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol>
    </nav>
    
    {{-- Quick navigation tabs --}}
    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i> All Vouchers
        </a>
        <a href="{{ route('vouchers.create') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Single
        </a>
        <a href="{{ route('reports.vouchers') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-graph-up me-1"></i> Reports & Analytics
        </a>
    </div>
</div>

<section class="section">

{{-- Date filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:12px">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm"
                       value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:12px">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm"
                       value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('reports.vouchers.pdf', request()->query()) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('reports.vouchers.csv', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Revenue cards --}}
<div class="row g-3 mb-3">
    @php
    $cards = [
        ['label'=>'Total Revenue',     'value'=>'TZS '.number_format($stats['total_revenue'], 2),     'icon'=>'bi-cash-stack',     'color'=>'primary',  'bg'=>'#eff6ff'],
        ['label'=>'Earned (Used)',     'value'=>'TZS '.number_format($stats['earned_revenue'], 2),   'icon'=>'bi-check-circle',   'color'=>'success',  'bg'=>'#f0fdf4'],
        ['label'=>'Pending (Active)',  'value'=>'TZS '.number_format($stats['pending_revenue'], 2),  'icon'=>'bi-clock-history',  'color'=>'warning',  'bg'=>'#fffbeb'],
        ['label'=>'Vouchers Created',  'value'=>number_format($stats['total']),                      'icon'=>'bi-ticket-perforated', 'color'=>'info',  'bg'=>'#f0f9ff'],
    ];
    @endphp

    @foreach($cards as $c)
    <div class="col-xxl-3 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">
                        {{ $c['label'] }}
                    </div>
                    <div style="font-size:22px;font-weight:800;color:#0f172a">
                        {{ $c['value'] }}
                    </div>
                </div>
                <div style="width:50px;height:50px;border-radius:12px;
                            background:{{ $c['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi {{ $c['icon'] }} text-{{ $c['color'] }}" style="font-size:24px"></i>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">

    {{-- Status breakdown --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Status Breakdown
                </h6>
            </div>
            <div class="card-body p-4">
                <canvas id="statusChart" style="max-height:200px"></canvas>

                <div class="mt-4">
                    @php
                    $statusList = [
                        ['label'=>'Active',   'count'=>$stats['active'],   'color'=>'#3b82f6'],
                        ['label'=>'Used',     'count'=>$stats['used'],     'color'=>'#22c55e'],
                        ['label'=>'Expired',  'count'=>$stats['expired'],  'color'=>'#ef4444'],
                        ['label'=>'Disabled', 'count'=>$stats['disabled'], 'color'=>'#64748b'],
                    ];
                    @endphp
                    @foreach($statusList as $s)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:12px;height:12px;border-radius:2px;background:{{ $s['color'] }};display:inline-block"></span>
                            <span style="font-size:13px;color:#64748b">{{ $s['label'] }}</span>
                        </div>
                        <span style="font-size:13px;font-weight:700;color:#0f172a">{{ number_format($s['count']) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Data usage --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-server me-2 text-success"></i>Data Usage
                </h6>
            </div>
            <div class="card-body p-4">
                @php
                function formatBytes($bytes) {
                    if ($bytes == 0) return '0 B';
                    $units = ['B','KB','MB','GB','TB'];
                    $i = floor(log($bytes)/log(1024));
                    return round($bytes/pow(1024,$i),2).' '.$units[$i];
                }
                $totalBytes = $stats['total_bytes_in'] + $stats['total_bytes_out'];
                @endphp

                <div class="text-center mb-4">
                    <div style="font-size:32px;font-weight:800;color:#22c55e">
                        {{ formatBytes($totalBytes) }}
                    </div>
                    <div style="font-size:12px;color:#94a3b8">Total Bandwidth</div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:12px">
                        <span class="text-muted">Download</span>
                        <span class="fw-semibold">{{ formatBytes($stats['total_bytes_in']) }}</span>
                    </div>
                    <div class="progress" style="height:6px;border-radius:3px">
                        @php $dlPct = $totalBytes > 0 ? round(($stats['total_bytes_in']/$totalBytes)*100) : 0; @endphp
                        <div class="progress-bar bg-primary" style="width:{{ $dlPct }}%;border-radius:3px"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:12px">
                        <span class="text-muted">Upload</span>
                        <span class="fw-semibold">{{ formatBytes($stats['total_bytes_out']) }}</span>
                    </div>
                    <div class="progress" style="height:6px;border-radius:3px">
                        @php $ulPct = $totalBytes > 0 ? round(($stats['total_bytes_out']/$totalBytes)*100) : 0; @endphp
                        <div class="progress-bar bg-success" style="width:{{ $ulPct }}%;border-radius:3px"></div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:12px;color:#64748b">Total Session Time</span>
                    <span style="font-size:14px;font-weight:700;color:#0f172a">
                        @php
                        $hours = floor($stats['total_session_time'] / 3600);
                        $mins  = floor(($stats['total_session_time'] % 3600) / 60);
                        @endphp
                        {{ $hours }}h {{ $mins }}m
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Top batches --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-trophy me-2 text-warning"></i>Top Batches by Revenue
                </h6>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0" style="font-size:12px">
                    @forelse($topBatches as $batch)
                    <tr>
                        <td class="ps-0" style="color:#0f172a;font-weight:600">
                            {{ Str::limit($batch->batch, 20) }}
                        </td>
                        <td style="color:#64748b">{{ $batch->used_count }}/{{ $batch->total }}</td>
                        <td class="pe-0 text-end" style="color:#22c55e;font-weight:700">
                            TZS {{ number_format($batch->earned, 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No batches found</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>

</div>

</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Status pie chart
const ctx = document.getElementById('statusChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Used', 'Expired', 'Disabled'],
        datasets: [{
            data: [
                {{ $stats['active'] }},
                {{ $stats['used'] }},
                {{ $stats['expired'] }},
                {{ $stats['disabled'] }}
            ],
            backgroundColor: ['#3b82f6', '#22c55e', '#ef4444', '#64748b'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.label}: ${ctx.formattedValue} vouchers`
                }
            }
        },
        cutout: '65%',
    }
});
</script>
@endpush

@endsection
