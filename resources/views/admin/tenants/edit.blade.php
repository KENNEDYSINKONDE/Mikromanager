@extends('admin.layout')
@section('title', 'Edit ' . $tenant->name)
@section('page-title', 'Edit: ' . $tenant->name)

@section('content')

<div class="row justify-content-center">
<div class="col-lg-8">

<a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i> Back
</a>

<div class="stat-card">
    <h6 class="fw-bold mb-4">Edit ISP Account</h6>

    <form action="{{ route('admin.tenants.update', $tenant) }}" method="POST">
        @csrf @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">ISP Name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $tenant->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Email *</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $tenant->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Phone</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ old('phone', $tenant->phone) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:12px">Plan *</label>
                <select name="plan" class="form-select">
                    @foreach(['trial','starter','pro','enterprise'] as $plan)
                    <option value="{{ $plan }}" {{ $tenant->plan === $plan ? 'selected' : '' }}>{{ ucfirst($plan) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:12px">Status *</label>
                <select name="status" class="form-select">
                    @foreach(['active','suspended','cancelled'] as $status)
                    <option value="{{ $status }}" {{ $tenant->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-4">
        <h6 class="fw-bold mb-3" style="font-size:13px">Plan Limits</h6>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold" style="font-size:12px">Max Routers</label>
                <input type="number" name="max_routers" class="form-control"
                       value="{{ old('max_routers', $tenant->max_routers) }}" min="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" style="font-size:12px">Max Vouchers</label>
                <input type="number" name="max_vouchers" class="form-control"
                       value="{{ old('max_vouchers', $tenant->max_vouchers) }}" min="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" style="font-size:12px">Max Users</label>
                <input type="number" name="max_users" class="form-control"
                       value="{{ old('max_users', $tenant->max_users) }}" min="1" required>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Trial Ends At</label>
                <input type="date" name="trial_ends_at" class="form-control"
                       value="{{ old('trial_ends_at', $tenant->trial_ends_at?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Subscription Ends At</label>
                <input type="date" name="subscription_ends_at" class="form-control"
                       value="{{ old('subscription_ends_at', $tenant->subscription_ends_at?->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i> Save Changes
            </button>
            <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

</div>
</div>

@endsection
