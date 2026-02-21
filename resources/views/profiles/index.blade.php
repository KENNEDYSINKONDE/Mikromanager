@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <h1>Hotspot Profiles</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Profiles</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
                        <h5 class="card-title mb-0">User Profiles</h5>
                        <a href="{{ route('profiles.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> New Profile
                        </a>
                    </div>

                    <table class="table table-bordered table-hover datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Rate Limit</th>
                                <th>Session Timeout</th>
                                <th>Shared Users</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profiles as $i => $profile)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $profile['name'] ?? '—' }}</strong></td>
                                <td>
                                    @if(!empty($profile['rate-limit']))
                                        <span class="badge bg-success">{{ $profile['rate-limit'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">Unlimited</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($profile['session-timeout']) && $profile['session-timeout'] !== '0s')
                                        <span class="badge bg-info text-dark">{{ $profile['session-timeout'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">Unlimited</span>
                                    @endif
                                </td>
                                <td>{{ $profile['shared-users'] ?? 1 }}</td>
                                <td class="text-center">

                                    {{-- View --}}
                                    <a href="{{ route('profiles.show', $profile['name']) }}"
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('profiles.edit', $profile['name']) }}"
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Delete — triggers its own modal per row --}}
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal-{{ $i }}">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    {{-- Per-row delete modal (no JS needed) --}}
                                    <div class="modal fade" id="deleteModal-{{ $i }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Profile
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    Are you sure you want to delete profile
                                                    <strong>{{ $profile['name'] }}</strong>?
                                                    This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Cancel
                                                    </button>
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

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No profiles found.
                                    <a href="{{ route('profiles.create') }}">Create one</a>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</section>
@endsection
