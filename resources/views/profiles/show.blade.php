@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <h1>Profile Details</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profiles.index') }}">Profiles</a></li>
            <li class="breadcrumb-item active">{{ $profile['name'] }}</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">

        {{-- Profile Info Card --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        {{ $profile['name'] }}
                        <span class="badge bg-primary">Profile</span>
                    </h5>

                    <ul class="list-group list-group-flush mt-2">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Rate Limit</span>
                            @if(!empty($profile['rate-limit']))
                                <span class="badge bg-success fs-6">{{ $profile['rate-limit'] }}</span>
                            @else
                                <span class="badge bg-secondary">Unlimited</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Session Timeout</span>
                            @if(!empty($profile['session-timeout']) && $profile['session-timeout'] !== '0s')
                                <span class="badge bg-info text-dark fs-6">{{ $profile['session-timeout'] }}</span>
                            @else
                                <span class="badge bg-secondary">Unlimited</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Shared Users</span>
                            <strong>{{ $profile['shared-users'] ?? 1 }}</strong>
                        </li>
                        @if(!empty($profile['idle-timeout']))
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Idle Timeout</span>
                            <strong>{{ $profile['idle-timeout'] }}</strong>
                        </li>
                        @endif
                        @if(!empty($profile['address-pool']))
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Address Pool</span>
                            <strong>{{ $profile['address-pool'] }}</strong>
                        </li>
                        @endif
                    </ul>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('profiles.edit', $profile['name']) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                        <a href="{{ route('profiles.index') }}" class="btn btn-secondary btn-sm ms-auto">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Raw Data Card --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Raw API Data
                        <small class="text-muted fw-normal fs-6 ms-2">MikroTik response</small>
                    </h5>

                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Parameter</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profile as $key => $value)
                            <tr>
                                <td><code>{{ $key }}</code></td>
                                <td>
                                    @if($value === '' || $value === null)
                                        <span class="text-muted">—</span>
                                    @elseif($value === 'false')
                                        <span class="badge bg-danger">false</span>
                                    @elseif($value === 'true')
                                        <span class="badge bg-success">true</span>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Delete Modal — form action is hardcoded, no JS required --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete profile
                <strong class="text-danger">{{ $profile['name'] }}</strong>?
                This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('profiles.destroy', $profile['name']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
