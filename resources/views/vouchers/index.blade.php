@extends('layouts.app')

@section('content')

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="pagetitle">
    <h1>Voucher Management</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Vouchers</li>
        </ol>
    </nav>
</div>

<section class="section">

    {{-- ── Flash Messages via SweetAlert ── --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({ icon: 'success', title: 'Success', html: @json(session('success')), timer: 3500, timerProgressBar: true, showConfirmButton: false, toast: false, position: 'top' });
        });
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({ icon: 'error', title: 'Error', html: @json(session('error')), confirmButtonColor: '#d33' });
        });
    </script>
    @endif

    {{-- ── Stats Cards ── --}}
    <div class="row mb-2">
        <div class="col-xxl col-xl-4 col-md-4 col-sm-6 mb-3">
            <div class="card info-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="ps-3">
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Total</h6>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted small">All vouchers</span>
                    </div>
                    <div class="ms-auto">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:rgba(13,110,253,0.12)">
                            <i class="bi bi-ticket-perforated fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-4 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="ps-3">
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Active</h6>
                        <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['active']) }}</h4>
                        <span class="text-muted small">Ready to use</span>
                    </div>
                    <div class="ms-auto">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:rgba(25,135,84,0.12)">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-4 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="ps-3">
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Used</h6>
                        <h4 class="mb-0 fw-bold text-secondary">{{ number_format($stats['used']) }}</h4>
                        <span class="text-muted small">Consumed</span>
                    </div>
                    <div class="ms-auto">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:rgba(108,117,125,0.12)">
                            <i class="bi bi-person-check fs-4 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-4 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="ps-3">
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Expired</h6>
                        <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['expired']) }}</h4>
                        <span class="text-muted small">Timed out</span>
                    </div>
                    <div class="ms-auto">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:rgba(220,53,69,0.12)">
                            <i class="bi bi-clock-history fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-4 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="ps-3">
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Disabled</h6>
                        <h4 class="mb-0 fw-bold text-warning">{{ number_format($stats['disabled']) }}</h4>
                        <span class="text-muted small">Suspended</span>
                    </div>
                    <div class="ms-auto">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:rgba(255,193,7,0.12)">
                            <i class="bi bi-pause-circle fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Card ── --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- Toolbar --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 mb-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-ticket-perforated me-2 text-primary"></i>Vouchers
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    {{-- Generate --}}
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                        <i class="bi bi-lightning-charge me-1"></i> Bulk Generate
                    </button>
                    <button class="btn btn-info btn-sm" onclick="syncFromMikrotik()" id="syncBtn">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Status
                    </button>
                    <a href="{{ route('reports.vouchers') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-graph-up me-1"></i> Reports
                    </a>
                    {{-- Create --}}
                    <a href="{{ route('vouchers.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> New Voucher
                    </a>
                    {{-- Export --}}
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="{{ route('vouchers.export.csv', request()->query()) }}">
                                    <i class="bi bi-filetype-csv me-2 text-success"></i> Export CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('vouchers.export.pdf', request()->query()) }}">
                                    <i class="bi bi-filetype-pdf me-2 text-danger"></i> Export PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('vouchers.print', request()->query()) }}" target="_blank">
                                    <i class="bi bi-printer me-2 text-secondary"></i> Print
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ── Filters ── --}}
            <form method="GET" action="{{ route('vouchers.index') }}" id="filterForm">
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Search username, batch, note…"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active"   @selected(request('status') == 'active')>Active</option>
                            <option value="used"     @selected(request('status') == 'used')>Used</option>
                            <option value="expired"  @selected(request('status') == 'expired')>Expired</option>
                            <option value="disabled" @selected(request('status') == 'disabled')>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="profile" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Profiles</option>
                            @foreach($profiles as $p)
                                <option value="{{ $p }}" @selected(request('profile') == $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="batch" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Batches</option>
                            @foreach($batches as $b)
                                <option value="{{ $b }}" @selected(request('batch') == $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>

            {{-- ── Bulk Action Bar ── --}}
            <form method="POST" action="{{ route('vouchers.bulk') }}" id="bulkForm">
                @csrf
                <div id="bulkBar" class="alert alert-light border d-none mb-3 py-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small me-2">
                            <span id="selectedCount">0</span> selected
                        </span>
                        <select name="action" class="form-select form-select-sm" style="width:auto">
                            <option value="">Choose action…</option>
                            <option value="enable">Enable</option>
                            <option value="disable">Disable</option>
                            <option value="sync">Sync to MikroTik</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmBulk()">
                            <i class="bi bi-lightning me-1"></i> Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="clearSelection()">
                            Clear
                        </button>
                    </div>
                </div>

                {{-- ── Table ── --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="vouchersTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>#</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Profile</th>
                                <th>Status</th>
                                <th>Limits</th>
                                <th>Batch</th>
                                <th>Synced</th>
                                <th>Price</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $voucher)
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $voucher->id }}"
                                           class="form-check-input row-check">
                                </td>
                                <td class="text-muted small">{{ $voucher->id }}</td>
                                <td>
                                    <a href="{{ route('vouchers.show', $voucher) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $voucher->username }}
                                    </a>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded small">{{ $voucher->password }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-normal">
                                        {{ $voucher->profile }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $voucher->status_badge }}">
                                        {{ ucfirst($voucher->status) }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <div><i class="bi bi-clock me-1"></i>{{ $voucher->time_limit_formatted }}</div>
                                    <div><i class="bi bi-database me-1"></i>{{ $voucher->data_limit_formatted }}</div>
                                </td>
                                <td>
                                    @if($voucher->batch)
                                        <span class="badge bg-light text-dark border">{{ $voucher->batch }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($voucher->mikrotik_synced)
                                        <i class="bi bi-cloud-check-fill text-success fs-5" title="Synced"></i>
                                    @else
                                        <i class="bi bi-cloud-slash text-danger fs-5" title="Not synced"></i>
                                    @endif
                                </td>
                                <td class="fw-semibold">
                                    {{ $voucher->price ? '$'.number_format($voucher->price, 2) : '—' }}
                                </td>
                                <td class="small text-muted">{{ $voucher->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('vouchers.show', $voucher) }}"
                                           class="btn btn-sm btn-outline-info px-2" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('vouchers.edit', $voucher) }}"
                                           class="btn btn-sm btn-outline-warning px-2" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger px-2"
                                                title="Delete"
                                                onclick="confirmDelete({{ $voucher->id }}, '{{ $voucher->username }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No vouchers found.
                                    <a href="{{ route('vouchers.create') }}">Create one</a> or
                                    <button type="button" class="btn btn-link p-0 align-baseline"
                                            data-bs-toggle="modal" data-bs-target="#generateModal">
                                        bulk generate
                                    </button>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </form>{{-- end bulkForm --}}

            {{-- ── Individual delete forms OUTSIDE bulkForm (nested forms don't work in HTML) --}}
            @foreach($vouchers as $voucher)
            <form id="del-{{ $voucher->id }}"
                  action="{{ route('vouchers.destroy', $voucher) }}"
                  method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
            @endforeach

            {{-- Pagination --}}
            @if($vouchers->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <p class="text-muted small mb-0">
                    Showing {{ $vouchers->firstItem() }}–{{ $vouchers->lastItem() }}
                    of {{ $vouchers->total() }} vouchers
                </p>
                {{ $vouchers->links() }}
            </div>
            @endif

        </div>
    </div>

</section>

{{-- ══════════════════════════════════════════
     BULK GENERATE MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-lightning-charge me-2"></i>Bulk Generate Vouchers
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('vouchers.generate') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Row 1: Quantity, Batch, Prefix --}}
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="count" class="form-control" value="10" min="1" max="500" required>
                            <div class="form-text">Max 500 at once</div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Batch Name <span class="text-danger">*</span></label>
                            <input type="text" name="batch" class="form-control"
                                   placeholder="e.g. PROMO-JAN" required
                                   value="{{ now()->format('Ymd') }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Prefix <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="prefix" class="form-control" placeholder="e.g. VC-" maxlength="10">
                        </div>

                        {{-- Row 2: Profile --}}
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Profile <span class="text-danger">*</span></label>
                            <select name="profile" class="form-select" required>
                                <option value="">Select profile…</option>
                                @foreach($profiles as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                            <div class="form-text"><i class="bi bi-info-circle"></i> Time & data limits come from the profile</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Price per Voucher</label>
                            <div class="input-group">
                                <span class="input-group-text">TZS</span>
                                <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01">
                            </div>
                        </div>

                        {{-- Row 3: Code settings --}}
                        <div class="col-12">
                            <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0">
                                <div class="fw-bold mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#64748b">
                                    <i class="bi bi-code-square me-1"></i> Code Settings
                                </div>
                                <div class="row g-2">
                                    <div class="col-sm-4">
                                        <label class="form-label" style="font-size:11px;font-weight:600">Code Length</label>
                                        <select name="code_length" class="form-select form-select-sm">
                                            @foreach([4=>'4 chars',5=>'5 chars',6=>'6 chars (recommended)',7=>'7 chars',8=>'8 chars',10=>'10 chars',12=>'12 chars'] as $val=>$label)
                                                <option value="{{ $val }}" {{ $val==6?'selected':'' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-8">
                                        <label class="form-label" style="font-size:11px;font-weight:600">Code Type</label>
                                        <select name="code_type" class="form-select form-select-sm">
                                            <option value="numbers">Numbers only — 482901</option>
                                            <option value="letters_upper">UPPERCASE only — XKMPQR</option>
                                            <option value="letters_lower">lowercase only — xkmpqr</option>
                                            <option value="mixed_upper" selected>Numbers + UPPERCASE ⭐ — X4K2P9</option>
                                            <option value="mixed_lower">Numbers + lowercase — x4k2p9</option>
                                            <option value="mixed_both">Numbers + UPPER + lower — xK4p9R</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Row 4: Password mode --}}
                        <div class="col-12">
                            <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bfdbfe">
                                <div class="fw-bold mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8">
                                    <i class="bi bi-key me-1"></i> Username / Password
                                </div>
                                <div class="d-flex gap-4 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pw_mode" id="genPwSame" value="same" checked>
                                        <label class="form-check-label" for="genPwSame" style="font-size:13px">
                                            <strong>Username = Password</strong>
                                            <span class="text-muted d-block" style="font-size:11px">User types the same code for login & password</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pw_mode" id="genPwDiff" value="different">
                                        <label class="form-check-label" for="genPwDiff" style="font-size:13px">
                                            <strong>Username ≠ Password</strong>
                                            <span class="text-muted d-block" style="font-size:11px">Separate codes generated for each</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Push now --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="push_now" value="1" id="genPushNow" checked>
                                <label class="form-check-label fw-semibold" for="genPushNow" style="font-size:13px">
                                    <i class="bi bi-cloud-upload me-1 text-primary"></i>
                                    Push to MikroTik immediately
                                </label>
                                <div class="text-muted" style="font-size:11px">Uncheck to save to database only</div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-lightning-charge me-1"></i> Generate Vouchers
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════ --}}
<script>
// ── Select All ──────────────────────────────
const selectAll  = document.getElementById('selectAll');
const bulkBar    = document.getElementById('bulkBar');
const countSpan  = document.getElementById('selectedCount');

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    countSpan.textContent = checked.length;
    bulkBar.classList.toggle('d-none', checked.length === 0);
}

selectAll.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function clearSelection() {
    document.querySelectorAll('.row-check, #selectAll').forEach(cb => cb.checked = false);
    updateBulkBar();
}

// ── Confirm Bulk Action ──────────────────────
function confirmBulk() {
    const action = document.querySelector('#bulkForm select[name="action"]').value;
    const count  = document.querySelectorAll('.row-check:checked').length;

    if (!action) { Swal.fire('Select Action', 'Please choose a bulk action.', 'warning'); return; }
    if (!count)  { Swal.fire('No Selection', 'Please select at least one voucher.', 'warning'); return; }

    const isDelete = action === 'delete';

    Swal.fire({
        title: isDelete ? 'Delete Vouchers?' : `${action.charAt(0).toUpperCase()+action.slice(1)} Vouchers?`,
        html:  `This will <strong>${action}</strong> <strong>${count}</strong> selected voucher(s).`,
        icon:  isDelete ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: isDelete ? '#d33' : '#0d6efd',
        confirmButtonText: `Yes, ${action}`,
    }).then(r => { if (r.isConfirmed) document.getElementById('bulkForm').submit(); });
}

// ── Confirm Single Delete ────────────────────
function confirmDelete(id, username) {
    Swal.fire({
        title: 'Delete Voucher?',
        html: `Delete voucher <strong>${username}</strong>? This will also remove it from MikroTik if synced.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Delete',
    }).then(r => { if (r.isConfirmed) document.getElementById('del-'+id).submit(); });
}
</script>


@push('scripts')
<script>
function syncFromMikrotik() {
    const btn = document.getElementById('syncBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';
    
    fetch('{{ route("vouchers.sync.all") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Sync Complete',
            html: `Updated <strong>${data.synced}</strong> voucher(s) from MikroTik.`,
            timer: 3000,
        });
        setTimeout(() => location.reload(), 1500);
    })
    .catch(err => {
        Swal.fire('Error', 'Failed to sync: ' + err.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sync Status';
    });
}
</script>
@endpush

@endsection
