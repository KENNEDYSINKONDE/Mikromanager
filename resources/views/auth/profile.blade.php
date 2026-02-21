@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<div class="pagetitle">
    <h1>My Profile</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('layout.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>
</div>

@php $user = Auth::user(); $tenant = $currentTenant ?? null; @endphp

<section class="section">
<div class="row">

    {{-- ── LEFT: Profile card ── --}}
    <div class="col-xl-4">

        {{-- Avatar card --}}
        <div class="card border-0 shadow-sm text-center mb-3">
            <div class="card-body pt-4 pb-3">

                {{-- Avatar --}}
                <div class="position-relative d-inline-block mb-3">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}"
                             alt="Avatar" id="avatarPreview"
                             class="rounded-circle"
                             style="width:90px;height:90px;object-fit:cover;
                                    border:3px solid #e2e8f0;box-shadow:0 4px 12px rgba(0,0,0,.1)">
                    @else
                        <div id="avatarPreview"
                             class="rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto"
                             style="width:90px;height:90px;
                                    background:linear-gradient(135deg,#4154f1,#7c3aed);
                                    color:#fff;font-size:28px;
                                    border:3px solid #e2e8f0;box-shadow:0 4px 12px rgba(0,0,0,.1)">
                            {{ $user->initials }}
                        </div>
                    @endif

                    {{-- Upload trigger --}}
                    <label for="avatarInput"
                           class="position-absolute bottom-0 end-0 rounded-circle d-flex
                                  align-items-center justify-content-center"
                           style="width:28px;height:28px;background:#4154f1;
                                  cursor:pointer;border:2px solid #fff"
                           title="Change photo">
                        <i class="bi bi-camera-fill" style="color:#fff;font-size:12px"></i>
                    </label>
                    <input type="file" id="avatarInput" class="d-none"
                           accept="image/*" onchange="previewAvatar(this)">
                </div>

                <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                <p class="text-muted mb-2" style="font-size:13px">{{ $user->email }}</p>

                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <span class="badge bg-{{ $user->role_badge }} px-3 py-1"
                          style="font-size:11px">
                        {{ ucfirst($user->role) }}
                    </span>
                    @if($tenant)
                    <span class="badge bg-light text-dark border px-3 py-1"
                          style="font-size:11px">
                        <i class="bi bi-building me-1"></i>{{ $tenant->name }}
                    </span>
                    @endif
                </div>

                <hr class="my-3">

                {{-- Quick stats --}}
                <div class="row g-0 text-center">
                    <div class="col-4 border-end">
                        <div class="fw-bold" style="font-size:18px;color:#4154f1">
                            {{ $user->created_at->diffInDays(now()) }}
                        </div>
                        <div class="text-muted" style="font-size:10px">Days Active</div>
                    </div>
                    <div class="col-4 border-end">
                        <div class="fw-bold" style="font-size:18px;color:#22c55e">
                            {{ $user->last_login_at ? $user->last_login_at->format('d M') : '—' }}
                        </div>
                        <div class="text-muted" style="font-size:10px">Last Login</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold" style="font-size:18px;color:#f59e0b">
                            {{ $user->is_active ? 'Active' : 'Off' }}
                        </div>
                        <div class="text-muted" style="font-size:10px">Status</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ISP Plan card --}}
        @if($tenant)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3" style="font-size:13px">
                    <i class="bi bi-building me-2 text-primary"></i>ISP Account
                </h6>
                <table class="table table-sm table-borderless mb-0" style="font-size:12px">
                    <tr>
                        <td class="text-muted ps-0">Business</td>
                        <td class="fw-semibold">{{ $tenant->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Plan</td>
                        <td>
                            <span class="badge plan-{{ $tenant->plan }}"
                                  style="font-size:10px">
                                {{ ucfirst($tenant->plan) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Status</td>
                        <td>
                            <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : 'bg-danger' }}"
                                  style="font-size:10px">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($tenant->isOnTrial() && $tenant->trial_ends_at)
                    <tr>
                        <td class="text-muted ps-0">Trial ends</td>
                        <td class="{{ $tenant->trialDaysLeft() <= 3 ? 'text-danger fw-bold' : 'text-warning fw-semibold' }}">
                            {{ $tenant->trial_ends_at->format('d M Y') }}
                            ({{ $tenant->trialDaysLeft() }}d)
                        </td>
                    </tr>
                    @elseif($tenant->subscription_ends_at)
                    <tr>
                        <td class="text-muted ps-0">Renews</td>
                        <td class="fw-semibold">{{ $tenant->subscription_ends_at->format('d M Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted ps-0">Routers</td>
                        <td class="fw-semibold">
                            {{ $tenant->routers()->count() }} / {{ $tenant->max_routers }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        {{-- Security info card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3" style="font-size:13px">
                    <i class="bi bi-shield-check me-2 text-success"></i>Security Info
                </h6>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-clock-history text-success" style="font-size:14px"></i>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#0f172a">Last Login</div>
                        <div style="font-size:11px;color:#64748b">
                            {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Never' }}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-geo-alt text-primary" style="font-size:14px"></i>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#0f172a">Last IP</div>
                        <div style="font-size:11px;color:#64748b;font-family:monospace">
                            {{ $user->last_login_ip ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: Edit forms ── --}}
    <div class="col-xl-8">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-check-circle-fill"></i>
            {!! session('success') !!}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-circle-fill"></i>
            {!! session('error') !!}
        </div>
        @endif

        {{-- ── Tab navigation ── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom bg-transparent px-4 pt-3 pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" id="infoTab"
                           data-bs-toggle="tab" href="#info" style="font-size:13px">
                            <i class="bi bi-person me-1"></i>Personal Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" id="passwordTab"
                           data-bs-toggle="tab" href="#password" style="font-size:13px">
                            <i class="bi bi-key me-1"></i>Change Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" id="avatarTab"
                           data-bs-toggle="tab" href="#avatarUpload" style="font-size:13px">
                            <i class="bi bi-image me-1"></i>Photo
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">

                    {{-- ── TAB 1: Personal Info ── --}}
                    <div class="tab-pane fade show active" id="info">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf @method('PUT')

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px">
                                        Full Name *
                                    </label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px">
                                        Email Address *
                                    </label>
                                    <input type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px">Role</label>
                                    <input type="text" class="form-control bg-light"
                                           value="{{ ucfirst($user->role) }}" readonly>
                                    <div class="form-text">Role is managed by your administrator</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px">
                                        Member Since
                                    </label>
                                    <input type="text" class="form-control bg-light"
                                           value="{{ $user->created_at->format('d M Y') }}" readonly>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Save Changes
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ── TAB 2: Change Password ── --}}
                    <div class="tab-pane fade" id="password">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:12px">
                                    Current Password *
                                </label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="currPw"
                                           class="form-control @error('current_password') is-invalid @enderror"
                                           placeholder="Enter your current password" required>
                                    <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePw('currPw', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                <div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:12px">
                                    New Password *
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="newPw"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min 8 characters" required minlength="8"
                                           oninput="checkStrength(this.value)">
                                    <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePw('newPw', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                {{-- Strength bar --}}
                                <div class="mt-2">
                                    <div class="progress" style="height:4px;border-radius:2px">
                                        <div id="strengthBar" class="progress-bar"
                                             style="width:0%;border-radius:2px;transition:all .3s"></div>
                                    </div>
                                    <div id="strengthText" class="text-muted mt-1"
                                         style="font-size:11px"></div>
                                </div>
                                @error('password')
                                <div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="font-size:12px">
                                    Confirm New Password *
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="confPw"
                                           class="form-control"
                                           placeholder="Repeat new password" required>
                                    <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePw('confPw', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Password tips --}}
                            <div class="p-3 rounded mb-4"
                                 style="background:#eff6ff;border:1px solid #bfdbfe">
                                <div class="fw-semibold mb-2" style="font-size:12px;color:#1d4ed8">
                                    <i class="bi bi-lightbulb me-1"></i>Password Tips
                                </div>
                                <ul class="mb-0 ps-3" style="font-size:12px;color:#3b82f6">
                                    <li>At least 8 characters long</li>
                                    <li>Mix uppercase, lowercase, numbers and symbols</li>
                                    <li>Don't reuse passwords from other sites</li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-1"></i> Change Password
                            </button>
                        </form>
                    </div>

                    {{-- ── TAB 3: Avatar Upload ── --}}
                    <div class="tab-pane fade" id="avatarUpload">
                        <form action="{{ route('profile.update') }}" method="POST"
                              enctype="multipart/form-data" id="avatarForm">
                            @csrf @method('PUT')

                            {{-- Drop zone --}}
                            <div id="dropZone"
                                 class="text-center p-5 rounded-3 mb-3"
                                 style="border:2px dashed #cbd5e1;background:#f8fafc;cursor:pointer;
                                        transition:all .2s"
                                 onclick="document.getElementById('avatarFile').click()"
                                 ondragover="handleDragOver(event)"
                                 ondragleave="handleDragLeave(event)"
                                 ondrop="handleDrop(event)">

                                <div id="dropContent">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             class="rounded-circle mb-3"
                                             style="width:80px;height:80px;object-fit:cover">
                                        <div class="fw-semibold" style="font-size:14px">Current Photo</div>
                                        <div class="text-muted" style="font-size:12px">
                                            Click or drag to replace
                                        </div>
                                    @else
                                        <i class="bi bi-cloud-upload"
                                           style="font-size:40px;color:#94a3b8;display:block;margin-bottom:12px"></i>
                                        <div class="fw-semibold" style="font-size:14px;color:#0f172a">
                                            Click to upload or drag & drop
                                        </div>
                                        <div class="text-muted" style="font-size:12px">
                                            PNG, JPG, JPEG up to 2MB
                                        </div>
                                    @endif
                                </div>

                                {{-- Preview after selection --}}
                                <div id="dropPreview" class="d-none">
                                    <img id="dropPreviewImg" src="" alt="Preview"
                                         class="rounded-circle mb-2"
                                         style="width:80px;height:80px;object-fit:cover;
                                                border:3px solid #4154f1">
                                    <div class="fw-semibold" style="font-size:13px;color:#0f172a">
                                        Ready to upload
                                    </div>
                                    <div id="dropFileName" class="text-muted" style="font-size:11px"></div>
                                </div>
                            </div>

                            <input type="file" id="avatarFile" name="avatar"
                                   class="d-none" accept="image/png,image/jpeg,image/jpg"
                                   onchange="handleFileSelect(this)">

                            @error('avatar')
                            <div class="alert alert-danger py-2 mb-3" style="font-size:13px">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                            @enderror

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                    <i class="bi bi-upload me-1"></i> Upload Photo
                                </button>
                                @if($user->avatar)
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="removeAvatar()">
                                    <i class="bi bi-trash me-1"></i> Remove Photo
                                </button>
                                @endif
                            </div>
                        </form>

                        {{-- Remove avatar form --}}
                        <form id="removeAvatarForm"
                              action="{{ route('profile.') }}"
                              method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
</section>

@push('styles')
<style>
.nav-tabs .nav-link { color: #64748b; border-bottom: 2px solid transparent; }
.nav-tabs .nav-link.active { color: #4154f1; border-bottom-color: #4154f1; font-weight: 600; }
.nav-tabs { border-bottom: none; }
.plan-trial      { background:#fef3c7;color:#92400e; }
.plan-starter    { background:#dbeafe;color:#1e40af; }
.plan-pro        { background:#ede9fe;color:#5b21b6; }
.plan-enterprise { background:#dcfce7;color:#14532d; }
#dropZone.dragover { border-color:#4154f1; background:#eff6ff; }
</style>
@endpush

@push('scripts')
<script>
// ── Avatar preview (header camera icon) ─────────────────────────────────────
function previewAvatar(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('avatarPreview');
        if (prev.tagName === 'IMG') {
            prev.src = e.target.result;
        }
        // Switch to avatar tab and populate
        document.getElementById('avatarFile').files = input.files;
        handleFileSelect(document.getElementById('avatarFile'));
        const tab = new bootstrap.Tab(document.getElementById('avatarTab'));
        tab.show();
    };
    reader.readAsDataURL(input.files[0]);
}

// ── File select handler ───────────────────────────────────────────────────────
function handleFileSelect(input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('dropContent').classList.add('d-none');
        document.getElementById('dropPreview').classList.remove('d-none');
        document.getElementById('dropPreviewImg').src = e.target.result;
        document.getElementById('dropFileName').textContent =
            file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        document.getElementById('uploadBtn').disabled = false;
    };
    reader.readAsDataURL(file);
}

// ── Drag and drop ─────────────────────────────────────────────────────────────
function handleDragOver(e) {
    e.preventDefault();
    document.getElementById('dropZone').classList.add('dragover');
}
function handleDragLeave(e) {
    document.getElementById('dropZone').classList.remove('dragover');
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('avatarFile').files = dt.files;
    handleFileSelect(document.getElementById('avatarFile'));
}

// ── Remove avatar ─────────────────────────────────────────────────────────────
function removeAvatar() {
    Swal.fire({
        title: 'Remove photo?',
        text: 'Your profile photo will be replaced with initials.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, remove it',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('removeAvatarForm').submit();
    });
}

// ── Password toggle ───────────────────────────────────────────────────────────
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.innerHTML = `<i class="bi bi-eye${isText ? '' : '-slash'}"></i>`;
}

// ── Password strength ─────────────────────────────────────────────────────────
function checkStrength(pw) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;
    if (pw.length >= 8)  score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = [
        { pct: 0,   color: '#e2e8f0', label: '' },
        { pct: 25,  color: '#ef4444', label: '⚠ Weak' },
        { pct: 50,  color: '#f59e0b', label: '◔ Fair' },
        { pct: 75,  color: '#3b82f6', label: '◑ Good' },
        { pct: 100, color: '#22c55e', label: '✓ Strong' },
    ];
    const l = levels[score];
    bar.style.width    = l.pct + '%';
    bar.style.background = l.color;
    text.textContent   = l.label;
    text.style.color   = l.color;
}

// ── Open password tab if errors ───────────────────────────────────────────────
@if($errors->has('current_password') || $errors->has('password'))
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Tab(document.getElementById('passwordTab')).show();
});
@endif
</script>
@endpush

@endsection