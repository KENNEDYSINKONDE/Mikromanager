@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <h1>Create Profile</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profiles.index') }}">Profiles</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">New Profile</h5>

                    <form action="{{ route('profiles.store') }}" method="POST">
                        @csrf

                        {{-- Profile Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Profile Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g. VIP"
                                   class="form-control @error('name') is-invalid @enderror"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Rate Limit + Session Timeout side by side --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="rate_limit" class="form-label">Rate Limit</label>
                                <input type="text"
                                       id="rate_limit"
                                       name="rate_limit"
                                       value="{{ old('rate_limit') }}"
                                       placeholder="e.g. 2M/2M"
                                       class="form-control @error('rate_limit') is-invalid @enderror">
                                <div class="form-text">
                                    Upload/Download e.g. <code>512K/1M</code>, <code>2M/2M</code>
                                </div>
                                @error('rate_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="session_time" class="form-label">Session Timeout</label>
                                <input type="text"
                                       id="session_time"
                                       name="session_time"
                                       value="{{ old('session_time') }}"
                                       placeholder="e.g. 2h"
                                       class="form-control @error('session_time') is-invalid @enderror">
                                <div class="form-text">
                                    e.g. <code>30m</code>, <code>2h</code>, <code>1d</code> — blank = unlimited
                                </div>
                                @error('session_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Shared Users --}}
                        <div class="mb-4">
                            <label for="shared_users" class="form-label">Shared Users</label>
                            <input type="number"
                                   id="shared_users"
                                   name="shared_users"
                                   value="{{ old('shared_users', 1) }}"
                                   min="1"
                                   max="9999"
                                   class="form-control @error('shared_users') is-invalid @enderror"
                                   style="max-width: 160px">
                            <div class="form-text">Max simultaneous logins with the same credentials.</div>
                            @error('shared_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Create Profile
                            </button>
                            <a href="{{ route('profiles.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Helper hints card --}}
        <div class="col-lg-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title text-muted">
                        <i class="bi bi-info-circle me-1"></i> Quick Reference
                    </h6>
                    <p class="small text-muted mb-2"><strong>Rate Limit format:</strong></p>
                    <table class="table table-sm table-borderless small mb-3">
                        <tbody>
                            <tr><td><code>1M/1M</code></td><td class="text-muted">1 Mbps up/down</td></tr>
                            <tr><td><code>512K/2M</code></td><td class="text-muted">512K up, 2M down</td></tr>
                            <tr><td><code>5M/10M</code></td><td class="text-muted">5M up, 10M down</td></tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-2"><strong>Session Timeout format:</strong></p>
                    <table class="table table-sm table-borderless small mb-0">
                        <tbody>
                            <tr><td><code>30m</code></td><td class="text-muted">30 minutes</td></tr>
                            <tr><td><code>2h</code></td><td class="text-muted">2 hours</td></tr>
                            <tr><td><code>1d</code></td><td class="text-muted">1 day</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
