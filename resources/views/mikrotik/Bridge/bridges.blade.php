@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Bridge Interfaces</h4>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Flags</th>
                <th>Name</th>
                <th>MTU</th>
                <th>Actual MTU</th>
                <th>MAC Address</th>
                <th>Protocol Mode</th>
                <th>Fast Forward</th>
                <th>IGMP Snooping</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bridges as $index => $bridge)
            <tr>
                <td>{{ $index }}</td>
                <td>
                    @php
                        $flags = '';
                        if(isset($bridge['disabled']) && $bridge['disabled'] === 'true') $flags .= 'X ';
                        if(isset($bridge['running']) && $bridge['running'] === 'true') $flags .= 'R ';
                        if(isset($bridge['dynamic']) && $bridge['dynamic'] === 'true') $flags .= 'D ';
                    @endphp
                    {{ trim($flags) ?: '-' }}
                </td>
                <td>{{ $bridge['name'] ?? '-' }}</td>
                <td>{{ $bridge['mtu'] ?? '-' }}</td>
                <td>{{ $bridge['actual-mtu'] ?? '-' }}</td>
                <td>{{ $bridge['mac-address'] ?? '-' }}</td>
                <td>{{ $bridge['protocol-mode'] ?? '-' }}</td>
                <td>{{ $bridge['fast-forward'] ?? '-' }}</td>
                <td>{{ $bridge['igmp-snooping'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
