@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Hotspot Servers</h4>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Interface</th>
                <th>Profile</th>
                <th>Address Pool</th>
                <th>Idle Timeout</th>
                <th>Addresses per MAC</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servers as $index => $server)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $server['name'] ?? '-' }}</td>
                <td>{{ $server['interface'] ?? '-' }}</td>
                <td>{{ $server['profile'] ?? '-' }}</td>
                <td>{{ $server['address-pool'] ?? '-' }}</td>
                <td>{{ $server['idle-timeout'] ?? 'none' }}</td>
                <td>
                    {{ $server['addresses-per-mac'] ?? 'default' }}
                </td>


                <td>
                    @if (isset($server['disabled']) && $server['disabled'] === 'true')
                    <span class="badge bg-danger">Disabled</span>
                    @else
                    <span class="badge bg-success">Active</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No hotspot servers found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection