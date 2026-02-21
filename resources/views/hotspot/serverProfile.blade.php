
@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Hotspot Server Profiles</h4>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Hotspot IP</th>
                 <th>Dns Name</th>
                <th>HTML Dir</th>
                <th>Login By</th>
                <th>Shared Users</th>
                <th>Rate Limit</th>
                <th>Session Timeout</th>
                <th>Idle Timeout</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($profiles as $index => $profile)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $profile['name'] ?? '-' }}</td>
                <td>{{ $profile['hotspot-address'] ?? '-' }}</td>
                <td>{{ $profile['dns-name'] ?? '-' }}</td>
                <td>{{ $profile['html-directory'] ?? '-' }}</td>
                <td>{{ $profile['login-by'] ?? '-' }}</td>
                <td>{{ $profile['shared-users'] ?? '1' }}</td>
                <td>{{ $profile['rate-limit'] ?? 'Unlimited' }}</td>
                <td>{{ $profile['session-timeout'] ?? 'none' }}</td>
                <td>{{ $profile['idle-timeout'] ?? 'none' }}</td>
                <td>
                    @if (($profile['disabled'] ?? 'false') === 'true')
                    <span class="badge bg-danger">Disabled</span>
                    @else
                    <span class="badge bg-success">Active</span>
                    @endif
                </td>
            </tr>

            @empty
            <tr>
                <td colspan="8" class="text-center">
                    No hotspot profiles found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection