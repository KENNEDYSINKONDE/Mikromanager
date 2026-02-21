@extends('admin.layout')
@section('title', 'Add New ISP')
@section('page-title', 'Add New ISP Account')

@section('content')

<div class="row justify-content-center">
<div class="col-lg-7">

<a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i> Back
</a>

<div class="stat-card">
    <h6 class="fw-bold mb-4">Create ISP Account</h6>

    <form action="{{ route('admin.tenants.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:12px">Business / ISP Name *</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="e.g. JumuiyaConnect Ltd" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Owner Name *</label>
                <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                       value="{{ old('owner_name') }}" placeholder="Full name" required>
                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Phone</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ old('phone') }}" placeholder="+255 7xx xxx xxx">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:12px">Email Address *</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="owner@isp.com" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Password *</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="Min 8 characters" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px">Plan *</label>
                <select name="plan" class="form-select @error('plan') is-invalid @enderror">
                    @foreach(['trial'=>'Trial (14 days)','starter'=>'Starter','pro'=>'Pro','enterprise'=>'Enterprise'] as $val => $label)
                    <option value="{{ $val }}" {{ old('plan','trial') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i> Create ISP Account
            </button>
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

</div>
</div>

@endsection
