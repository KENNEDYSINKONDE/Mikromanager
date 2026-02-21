@extends('layouts.app')

@section('content')
<div class="container">

    {{-- INTERFACES TABLE --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Router Interfaces</h4>
            <small>Flags: R - RUNNING, S - SLAVE</small>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Flags</th>
                        <th>Name</th>
                        <th>MTU</th>
                        <th>MAC Address</th>
                        <th>ARP</th>
                        <th>Switch</th>
                        <th>TX</th>
                        <th>RX</th>
                        <th>TX Packets</th>
                        <th>RX Packets</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interfaces as $index => $interface)
                    <tr>
                        <td>{{ $index }}</td>
                        <td>
                            @php
                            $flags = '';
                            if(isset($interface['running']) && $interface['running'] === 'true') $flags .= 'R ';
                            if(isset($interface['slave']) && $interface['slave'] === 'true') $flags .= 'S';
                            @endphp
                            {{ trim($flags) ?: '-' }}
                        </td>
                        <td>{{ $interface['name'] ?? '-' }}</td>
                        <td>{{ $interface['actual-mtu'] ?? '-' }}</td>
                        <td>{{ $interface['mac-address'] ?? '-' }}</td>
                        <td>{{ $interface['arp'] ?? 'enabled' }}</td>
                        <td>{{ $interface['switch'] ?? '-' }}</td>
                        <td>{{ $interface['tx'] ?? '-' }}</td>
                        <td>{{ $interface['rx'] ?? '-' }}</td>
                        <td>{{ $interface['tx-packet'] ?? '-' }}</td>
                        <td>{{ $interface['rx-packet'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ETHERNET TABLE --}}
    <div class="card shadow">
        <div class="card-header">
            <h4>Ethernet Interfaces</h4>
            <small>Flags: R - RUNNING, S - SLAVE</small>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Flags</th>
                        <th>Name</th>
                        <th>MTU</th>
                        <th>MAC Address</th>
                        <th>ARP</th>
                        <th>Switch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interfaces as $index => $interface)
                    @if($interface['type'] === 'ether') {{-- Only ethernet --}}
                    <tr>
                        <td>{{ $index }}</td>
                        <td>
                            @php
                            $flags = '';
                            if(isset($interface['running']) && $interface['running'] === 'true') $flags .= 'R ';
                            if(isset($interface['slave']) && $interface['slave'] === 'true') $flags .= 'S';
                            @endphp
                            {{ trim($flags) ?: '-' }}
                        </td>
                        <td>{{ $interface['name'] ?? '-' }}</td>
                        <td>{{ $interface['actual-mtu'] ?? '-' }}</td>
                        <td>{{ $interface['mac-address'] ?? '-' }}</td>
                        <td>{{ $interface['arp'] ?? 'enabled' }}</td>
                        <td>{{ $interface['switch'] ?? '-' }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection