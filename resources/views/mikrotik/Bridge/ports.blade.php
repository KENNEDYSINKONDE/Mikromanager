@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Bridge Ports</h4>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Flags</th>
                <th>Interface</th>
                <th>Bridge</th>
                <th>HW Offload</th>
                <th>Trusted</th>
                <th>Fast Leave</th>
                <th>PVID</th>
                <th>Frame Types</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ports as $index => $port)
            <tr>
                <td>{{ $index }}</td>
                <td>
                    @php
                        $flags = '';
                        if(isset($port['inactive']) && $port['inactive'] === 'true') $flags .= 'I ';
                        if(isset($port['hw-offload']) && $port['hw-offload'] === 'true') $flags .= 'H ';
                    @endphp
                    {{ trim($flags) ?: '-' }}
                </td>
                <td>{{ $port['interface'] ?? '-' }}</td>
                <td>{{ $port['bridge'] ?? '-' }}</td>
                <td>{{ $port['hw-offload'] ?? '-' }}</td>
                <td>{{ $port['trusted'] ?? '-' }}</td>
                <td>{{ $port['fast-leave'] ?? '-' }}</td>
                <td>{{ $port['pvid'] ?? '-' }}</td>
                <td>{{ $port['frame-types'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
