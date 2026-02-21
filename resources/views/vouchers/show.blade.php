@extends('layouts.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon:'success', title:'Success', html: @json(session('success')), timer:3000, timerProgressBar:true, showConfirmButton:false, position:'top' });
    });
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon:'error', title:'Error', html: @json(session('error')) });
    });
</script>
@endif

<div class="pagetitle">
    <h1>Voucher Details</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
            <li class="breadcrumb-item active">{{ $voucher->username }}</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">

        {{-- ── Voucher Card (visual) ── --}}
        <div class="col-lg-4 mb-4">

            {{-- Visual Voucher --}}
            <div class="card border-0 shadow mb-3"
                 style="background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);border-radius:16px">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="small opacity-75 text-uppercase fw-semibold">Hotspot Voucher</div>
                            <div class="fs-4 fw-bold font-monospace mt-1">{{ $voucher->username }}</div>
                        </div>
                        <i class="bi bi-wifi fs-1 opacity-40"></i>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="small opacity-75">Password</div>
                            <div class="fw-bold font-monospace fs-5">{{ $voucher->password }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Profile</div>
                            <div class="fw-bold">{{ $voucher->profile }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Time</div>
                            <div class="fw-bold">{{ $voucher->time_limit_formatted }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Data</div>
                            <div class="fw-bold">{{ $voucher->data_limit_formatted }}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-white border-opacity-25">
                        <div>
                            <div class="small opacity-75">Price</div>
                            <div class="fw-bold fs-5">{{ $voucher->price ? '$'.number_format($voucher->price,2) : 'Free' }}</div>
                        </div>
                        <span class="badge bg-{{ $voucher->status_badge }} px-3 py-2 text-uppercase fs-6">
                            {{ $voucher->status }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- MikroTik Sync Status --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    @if($voucher->mikrotik_synced)
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                             style="width:44px;height:44px">
                            <i class="bi bi-cloud-check-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-success">Synced to MikroTik</div>
                            <div class="small text-muted">User exists on router</div>
                        </div>
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10"
                             style="width:44px;height:44px">
                            <i class="bi bi-cloud-slash text-danger fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-danger">Not Synced</div>
                            <div class="small text-muted">Saved locally only</div>
                        </div>
                        <form action="{{ route('vouchers.sync', $voucher) }}" method="POST" class="ms-auto">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-cloud-upload me-1"></i> Sync Now
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted fw-semibold mb-3">Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i> Edit Voucher
                        </a>
                        <a href="{{ route('vouchers.export.pdf', ['ids' => $voucher->id]) }}" class="btn btn-outline-danger">
                            <i class="bi bi-filetype-pdf me-1"></i> Export PDF
                        </a>
                        <button type="button" class="btn btn-outline-danger"
                                onclick="confirmDelete({{ $voucher->id }}, '{{ $voucher->username }}')">
                            <i class="bi bi-trash me-1"></i> Delete Voucher
                        </button>
                        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Details Panel ── --}}
        <div class="col-lg-8">

            {{-- Info card --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Voucher Information</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4" style="width:35%">ID</td>
                                <td class="pe-4">#{{ $voucher->id }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Username</td>
                                <td class="pe-4 font-monospace fw-bold">{{ $voucher->username }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Password</td>
                                <td class="pe-4 font-monospace">{{ $voucher->password }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Profile</td>
                                <td class="pe-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $voucher->profile }}</span>
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Status</td>
                                <td class="pe-4">
                                    <span class="badge bg-{{ $voucher->status_badge }} px-3">{{ ucfirst($voucher->status) }}</span>
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Time Limit</td>
                                <td class="pe-4">{{ $voucher->time_limit_formatted }}
                                    @if($voucher->time_limit)
                                        <span class="text-muted small">({{ number_format($voucher->time_limit) }}s)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Data Limit</td>
                                <td class="pe-4">{{ $voucher->data_limit_formatted }}
                                    @if($voucher->data_limit)
                                        <span class="text-muted small">({{ number_format($voucher->data_limit) }} bytes)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Price</td>
                                <td class="pe-4 fw-bold">{{ $voucher->price ? '$'.number_format($voucher->price,2) : '—' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Batch</td>
                                <td class="pe-4">
                                    @if($voucher->batch)
                                        <a href="{{ route('vouchers.index', ['batch' => $voucher->batch]) }}"
                                           class="badge bg-light text-dark border text-decoration-none">
                                            {{ $voucher->batch }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Note</td>
                                <td class="pe-4">{{ $voucher->note ?: '—' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Expires At</td>
                                <td class="pe-4">{{ $voucher->expires_at ? $voucher->expires_at->format('d M Y H:i') : 'Never' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">MikroTik Synced</td>
                                <td class="pe-4">
                                    @if($voucher->mikrotik_synced)
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Yes</span>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-4">Created At</td>
                                <td class="pe-4">{{ $voucher->created_at->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold ps-4">Updated At</td>
                                <td class="pe-4">{{ $voucher->updated_at->format('d M Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Hidden delete form --}}
<form id="deleteForm" action="{{ route('vouchers.destroy', $voucher) }}" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, username) {
    Swal.fire({
        title: 'Delete Voucher?',
        html: `Delete <strong>${username}</strong>? This will also remove it from MikroTik if synced.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Delete',
    }).then(r => { if (r.isConfirmed) document.getElementById('deleteForm').submit(); });
}
</script>

@endsection
