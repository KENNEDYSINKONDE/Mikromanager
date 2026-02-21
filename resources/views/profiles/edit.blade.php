@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <h1>Edit Profile</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profiles.index') }}">Profiles</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profiles.show', $profile['name']) }}">{{ $profile['name'] }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center gap-2">
                        Edit Profile
                        <span class="badge bg-primary fw-normal">{{ $profile['name'] }}</span>
                    </h5>

                    <form action="{{ route('profiles.update', $profile['name']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Profile Name (read-only) --}}
                        <div class="mb-3">
                            <label class="form-label">Profile Name</label>
                            <input type="text"
                                   value="{{ $profile['name'] }}"
                                   class="form-control bg-light"
                                   disabled>
                            <div class="form-text text-warning">
                                <i class="bi bi-lock me-1"></i>
                                Profile name cannot be changed after creation.
                            </div>
                        </div>

                        {{-- Rate Limit + Session Timeout side by side --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="rate_limit" class="form-label">Rate Limit</label>
                                <input type="text"
                                       id="rate_limit"
                                       name="rate_limit"
                                       value="{{ old('rate_limit', $profile['rate-limit'] ?? '') }}"
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
                                       value="{{ old('session_time', $profile['session-timeout'] ?? '') }}"
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
                                   value="{{ old('shared_users', $profile['shared-users'] ?? 1) }}"
                                   min="1"
                                   max="9999"
                                   class="form-control @error('shared_users') is-invalid @enderror"
                                   style="max-width: 160px">
                            <div class="form-text">Max simultaneous logins.</div>
                            @error('shared_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-floppy me-1"></i> Save Changes
                            </button>
                            <a href="{{ route('profiles.show', $profile['name']) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Current values preview card --}}
        <div class="col-lg-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title text-muted">
                        <i class="bi bi-clock-history me-1"></i> Current Values
                    </h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                            <span class="text-muted small">Rate Limit</span>
                            @if(!empty($profile['rate-limit']))
                                <span class="badge bg-success">{{ $profile['rate-limit'] }}</span>
                            @else
                                <span class="badge bg-secondary">Unlimited</span>
                            @endif
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                            <span class="text-muted small">Session Timeout</span>
                            @if(!empty($profile['session-timeout']) && $profile['session-timeout'] !== '0s')
                                <span class="badge bg-info text-dark">{{ $profile['session-timeout'] }}</span>
                            @else
                                <span class="badge bg-secondary">Unlimited</span>
                            @endif
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                            <span class="text-muted small">Shared Users</span>
                            <strong>{{ $profile['shared-users'] ?? 1 }}</strong>
                        </li>
                    </ul>

                    <hr>

                    <div class="d-grid">
                        <a href="{{ route('profiles.show', $profile['name']) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i> View Full Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
