@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Bridge Hosts</h4>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Flags</th>
                <th>MAC Address</th>
                <th>On Interface</th>
                <th>Bridge</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hosts as $index => $host)
            <tr>
                <td>{{ $index }}</td>
                <td>
                    @php
                        $flags = '';
                        if(isset($host['dynamic']) && $host['dynamic'] === 'true') $flags .= 'D ';
                        if(isset($host['local']) && $host['local'] === 'true') $flags .= 'L ';
                    @endphp
                    {{ trim($flags) ?: '-' }}
                </td>
                <td>{{ $host['mac-address'] ?? '-' }}</td>
                <td>{{ $host['on-interface'] ?? '-' }}</td>
                <td>{{ $host['bridge'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
