@extends('layouts.app')

@section('title', 'Create Voucher')

@section('content')

<div class="pagetitle">
    <h1>New Voucher</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('layout.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
    
    {{-- Quick navigation tabs --}}
    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i> All Vouchers
        </a>
        <a href="{{ route('vouchers.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Single
        </a>
        <a href="{{ route('reports.vouchers') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i> Reports & Analytics
        </a>
    </div>
</div>

<section class="section">
<div class="row">

{{-- ── LEFT: Form ── --}}
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center gap-2">
        <i class="bi bi-ticket-perforated text-primary fs-5"></i>
        <h5 class="mb-0">Voucher Generator</h5>
    </div>
    <div class="card-body p-4">
    <form action="{{ route('vouchers.store') }}" method="POST" id="createForm">
        @csrf

        {{-- ── STEP 1: Profile ── --}}
        <div class="step-section mb-4">
            <div class="step-label">
                <span class="step-num">1</span> Select Profile
                <small class="text-muted ms-2" style="font-size:11px;font-weight:400">
                    Time limit & data limit come from the profile
                </small>
            </div>
            <select name="profile" id="profile" class="form-select @error('profile') is-invalid @enderror"
                    onchange="updatePreview()" required>
                <option value="">Choose a profile…</option>
                @foreach($profiles as $p)
                    <option value="{{ $p }}" @selected(old('profile') == $p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if(empty($profiles))
                <div class="alert alert-warning py-2 mt-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    No profiles found. <a href="{{ route('profiles.create') }}">Create a profile first</a>.
                </div>
            @endif
        </div>

        {{-- ── STEP 2: Code Generator ── --}}
        <div class="step-section mb-4">
            <div class="step-label">
                <span class="step-num">2</span> Voucher Code Settings
            </div>

            <div class="row g-3 mb-3">
                {{-- Code Length --}}
                <div class="col-sm-4">
                    <label class="form-label fw-semibold" style="font-size:12px">Code Length</label>
                    <select id="code_length" class="form-select form-select-sm" onchange="generateCode()">
                        @foreach([4,5,6,7,8,10,12] as $len)
                            <option value="{{ $len }}" {{ $len == 6 ? 'selected' : '' }}>{{ $len }} characters</option>
                        @endforeach
                    </select>
                </div>

                {{-- Code Type --}}
                <div class="col-sm-8">
                    <label class="form-label fw-semibold" style="font-size:12px">Code Type</label>
                    <select id="code_type" class="form-select form-select-sm" onchange="generateCode()">
                        <option value="numbers">Numbers only (0–9)</option>
                        <option value="letters_upper">UPPERCASE letters only (A–Z)</option>
                        <option value="letters_lower">lowercase letters only (a–z)</option>
                        <option value="mixed_upper" selected>Numbers + UPPERCASE (recommended)</option>
                        <option value="mixed_lower">Numbers + lowercase</option>
                        <option value="mixed_both">Numbers + UPPERCASE + lowercase</option>
                    </select>
                </div>
            </div>

            {{-- Prefix --}}
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:12px">
                    Prefix <span class="text-muted fw-normal">(optional — added before the code)</span>
                </label>
                <div class="input-group input-group-sm">
                    <input type="text" id="code_prefix" class="form-control" style="max-width:120px"
                           placeholder="e.g. VC-" maxlength="10" oninput="updatePreview()">
                    <span class="input-group-text text-muted" style="font-size:12px">
                        Preview: <strong id="prefix-preview" class="ms-1 font-monospace">—</strong>
                    </span>
                </div>
            </div>

            {{-- Username = Password toggle --}}
            <div class="p-3 rounded mb-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                <div class="fw-semibold mb-2" style="font-size:13px">
                    <i class="bi bi-key me-1 text-primary"></i> Username / Password Relationship
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pw_mode" id="pw_same"
                               value="same" checked onchange="handlePwMode()">
                        <label class="form-check-label" for="pw_same" style="font-size:13px">
                            Username = Password
                            <span class="text-muted" style="font-size:11px">(user types same code for both)</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pw_mode" id="pw_diff"
                               value="different" onchange="handlePwMode()">
                        <label class="form-check-label" for="pw_diff" style="font-size:13px">
                            Username ≠ Password
                            <span class="text-muted" style="font-size:11px">(separate codes generated)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Hidden username/password fields --}}
            <input type="hidden" name="username" id="username">
            <input type="hidden" name="password" id="password">

            {{-- Generated Code Preview --}}
            <div class="p-3 rounded text-center" style="background:#0f172a;border:1px solid #1e293b">
                <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:1px">Generated Code</div>
                <div id="code-display" class="fw-bold font-monospace"
                     style="font-size:22px;color:#22c55e;letter-spacing:3px">——————</div>
                <div id="pw-display" class="font-monospace mt-1" style="font-size:13px;color:#64748b;display:none">
                    Password: <span id="pw-text" style="color:#f59e0b"></span>
                </div>
                <button type="button" class="btn btn-sm mt-2"
                        style="background:rgba(255,255,255,0.08);color:#94a3b8;font-size:11px"
                        onclick="generateCode()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Regenerate
                </button>
            </div>
        </div>

        {{-- ── STEP 3: Details ── --}}
        <div class="step-section mb-4">
            <div class="step-label">
                <span class="step-num">3</span> Optional Details
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <label class="form-label fw-semibold" style="font-size:12px">Price</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">TZS</span>
                        <input type="number" name="price" id="price" class="form-control"
                               value="{{ old('price') }}" step="0.01" placeholder="0.00"
                               oninput="updatePreview()">
                    </div>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-semibold" style="font-size:12px">Batch / Group</label>
                    <input type="text" name="batch" class="form-control form-control-sm"
                           value="{{ old('batch') }}" placeholder="e.g. PROMO-JAN">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-semibold" style="font-size:12px">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="form-control form-control-sm"
                           value="{{ old('expires_at') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:12px">Note</label>
                <textarea name="note" class="form-control form-control-sm" rows="2"
                          placeholder="Optional note…">{{ old('note') }}</textarea>
            </div>
        </div>

        {{-- ── STEP 4: Push ── --}}
        <div class="p-3 rounded mb-4" style="background:#eff6ff;border:1px solid #bfdbfe">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="push_now" value="1"
                       id="pushNow" checked>
                <label class="form-check-label fw-semibold" for="pushNow" style="font-size:13px">
                    <i class="bi bi-cloud-upload me-1 text-primary"></i>
                    Push to MikroTik immediately
                </label>
                <div class="text-muted" style="font-size:11px">Uncheck to save to database only</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bi bi-plus-circle me-1"></i> Create Voucher
            </button>
            <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>

    </form>
    </div>
</div>
</div>

{{-- ── RIGHT: Live Preview ── --}}
<div class="col-lg-5">

    {{-- Voucher Card Preview --}}
    <div class="card border-0 shadow-sm mb-3" style="background:linear-gradient(135deg,#1d4ed8,#0d9488)">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div style="font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:1px">Hotspot Voucher</div>
                    <div class="fw-bold font-monospace mt-1" id="prev-code" style="font-size:20px;letter-spacing:2px">——————</div>
                </div>
                <i class="bi bi-wifi" style="font-size:28px;opacity:.5"></i>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div style="font-size:10px;opacity:.7">Password</div>
                    <div class="fw-bold font-monospace" id="prev-password-show" style="font-size:14px">same as username</div>
                </div>
                <div class="col-6">
                    <div style="font-size:10px;opacity:.7">Profile</div>
                    <div class="fw-bold" id="prev-profile" style="font-size:14px">—</div>
                </div>
                <div class="col-6">
                    <div style="font-size:10px;opacity:.7">Time Limit</div>
                    <div class="fw-bold" id="prev-time" style="font-size:14px">From profile</div>
                </div>
                <div class="col-6">
                    <div style="font-size:10px;opacity:.7">Data Limit</div>
                    <div class="fw-bold" id="prev-data" style="font-size:14px">From profile</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2"
                 style="border-top:1px solid rgba(255,255,255,.2)">
                <div>
                    <div style="font-size:10px;opacity:.7">Price</div>
                    <div class="fw-bold" id="prev-price" style="font-size:18px">—</div>
                </div>
                <span style="background:rgba(255,255,255,.2);border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700">
                    ACTIVE
                </span>
            </div>
        </div>
    </div>

    {{-- Code type legend --}}
    <div class="card border-0 bg-light">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-2" style="font-size:12px">
                <i class="bi bi-info-circle text-primary me-1"></i> Code Type Guide
            </h6>
            <table class="table table-sm table-borderless mb-0" style="font-size:11px">
                <tr><td class="text-muted">Numbers only</td><td><code>482901</code></td></tr>
                <tr><td class="text-muted">UPPERCASE only</td><td><code>XKMPQR</code></td></tr>
                <tr><td class="text-muted">lowercase only</td><td><code>xkmpqr</code></td></tr>
                <tr><td class="text-muted">Numbers + UPPER ⭐</td><td><code>X4K2P9</code></td></tr>
                <tr><td class="text-muted">Numbers + lower</td><td><code>x4k2p9</code></td></tr>
                <tr><td class="text-muted">Numbers + UPPER + lower</td><td><code>xK4p9R</code></td></tr>
            </table>
            <div class="alert alert-info py-2 mt-2 mb-0" style="font-size:11px">
                <i class="bi bi-shield-check me-1"></i>
                <strong>Tip:</strong> Avoid ambiguous chars like 0/O, 1/l/I — they are automatically excluded.
            </div>
        </div>
    </div>

</div>

</div>
</section>

@push('styles')
<style>
.step-section { }
.step-label {
    display: flex; align-items: center; gap: 10px;
    font-size: 14px; font-weight: 700; color: #0f172a;
    margin-bottom: 14px;
}
.step-num {
    width: 26px; height: 26px; border-radius: 50%;
    background: #3b82f6; color: #fff; font-size: 12px; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
</style>
@endpush

@push('scripts')
<script>
// ── Character sets (no ambiguous chars: 0,O,1,l,I) ──────────────────────────
const CHARSETS = {
    numbers:      '23456789',
    letters_upper:'ABCDEFGHJKLMNPQRSTUVWXYZ',
    letters_lower:'abcdefghjkmnpqrstuvwxyz',
    mixed_upper:  'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
    mixed_lower:  'abcdefghjkmnpqrstuvwxyz23456789',
    mixed_both:   'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789',
};

function randomCode(charset, length) {
    let code = '';
    for (let i = 0; i < length; i++) {
        code += charset[Math.floor(Math.random() * charset.length)];
    }
    return code;
}

function generateCode() {
    const length  = parseInt(document.getElementById('code_length').value);
    const type    = document.getElementById('code_type').value;
    const prefix  = document.getElementById('code_prefix').value.trim();
    const charset = CHARSETS[type];
    const pwMode  = document.querySelector('input[name="pw_mode"]:checked').value;

    const code    = randomCode(charset, length);
    const fullUsername = prefix + code;

    // Set hidden inputs
    document.getElementById('username').value = fullUsername;

    if (pwMode === 'same') {
        document.getElementById('password').value = fullUsername;
    } else {
        const pwCode = randomCode(charset, length);
        document.getElementById('password').value = prefix + pwCode;
    }

    updatePreview();
}

function handlePwMode() {
    generateCode(); // regenerate with new mode
}

function updatePreview() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const profile  = document.getElementById('profile').value;
    const price    = parseFloat(document.getElementById('price').value);
    const prefix   = document.getElementById('code_prefix').value.trim();
    const pwMode   = document.querySelector('input[name="pw_mode"]:checked').value;

    // Update voucher card
    document.getElementById('prev-code').textContent    = username || '——————';
    document.getElementById('prev-profile').textContent = profile  || '—';
    document.getElementById('prev-price').textContent   = isNaN(price) ? '—' : 'TZS ' + price.toLocaleString();

    // Password display
    if (pwMode === 'same') {
        document.getElementById('prev-password-show').textContent = 'same as username';
    } else {
        document.getElementById('prev-password-show').textContent = password || '—';
    }

    // Code display in dark box
    const codeDisplay = document.getElementById('code-display');
    const pwDisplay   = document.getElementById('pw-display');
    const pwText      = document.getElementById('pw-text');
    const prefixPrev  = document.getElementById('prefix-preview');

    codeDisplay.textContent = username || '——————';
    prefixPrev.textContent  = prefix || '—';

    if (pwMode === 'different' && password) {
        pwDisplay.style.display = 'block';
        pwText.textContent = password;
    } else {
        pwDisplay.style.display = 'none';
    }
}

// Validate before submit
document.getElementById('createForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const profile  = document.getElementById('profile').value;

    if (!username || !password) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning', title: 'Generate a code first',
            text: 'Please wait for a code to be generated before submitting.',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    if (!profile) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning', title: 'Select a profile',
            text: 'You must select a hotspot profile.',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
});

// Generate on page load
document.addEventListener('DOMContentLoaded', generateCode);

// Regenerate when prefix changes
document.getElementById('code_prefix').addEventListener('input', function() {
    updatePreview();
});
</script>
@endpush

@endsection
