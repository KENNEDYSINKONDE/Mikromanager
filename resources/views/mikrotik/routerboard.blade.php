@extends('layouts.app')

@section('content')
<div class="container">

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-3" id="mikrotikTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="identity-tab" data-bs-toggle="tab" data-bs-target="#identity" type="button" role="tab">Identity</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="routerboard-tab" data-bs-toggle="tab" data-bs-target="#routerboard" type="button" role="tab">RouterBOARD</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">Resources</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="license-tab" data-bs-toggle="tab" data-bs-target="#license" type="button" role="tab">License</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="clock-tab" data-bs-toggle="tab" data-bs-target="#clock" type="button" role="tab">Clock</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="packages-tab" data-bs-toggle="tab" data-bs-target="#packages" type="button" role="tab">Packages</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="mikrotikTabContent">

        <!-- Identity Table -->
        <div class="tab-pane fade show active" id="identity" role="tabpanel">
            <div class="card shadow mb-3">
                <div class="card-header">Router Identity</div>
                <div class="card-body">
                    @if(!empty($identity))
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($identity as $id)
                                <tr>
                                    <td>{{ $id['name'] ?? '-' }}</td>
                                    <td>
                                        @if(!empty($id))
                                            <span class="text-success">🟢 Online</span>
                                        @else
                                            <span class="text-danger">🔴 Offline</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No identity info found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- RouterBOARD Table -->
        <div class="tab-pane fade" id="routerboard" role="tabpanel">
            <div class="card shadow mb-3">
                <div class="card-header">RouterBOARD Info</div>
                <div class="card-body">
                    @if(!empty($routerboard))
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>RouterBOARD</th>
                                    <th>Model</th>
                                    <th>Revision</th>
                                    <th>Serial Number</th>
                                    <th>Firmware Type</th>
                                    <th>Factory Firmware</th>
                                    <th>Current Firmware</th>
                                    <th>Upgrade Firmware</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($routerboard as $rb)
                                <tr>
                                    <td>{{ $rb['routerboard'] ?? '-' }}</td>
                                    <td>{{ $rb['model'] ?? '-' }}</td>
                                    <td>{{ $rb['revision'] ?? '-' }}</td>
                                    <td>{{ $rb['serial-number'] ?? '-' }}</td>
                                    <td>{{ $rb['firmware-type'] ?? '-' }}</td>
                                    <td>{{ $rb['factory-firmware'] ?? '-' }}</td>
                                    <td>{{ $rb['current-firmware'] ?? '-' }}</td>
                                    <td>{{ $rb['upgrade-firmware'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No RouterBOARD info found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Resources Table -->
        <div class="tab-pane fade" id="resources" role="tabpanel">
            <div class="card shadow mb-3">
                <div class="card-header">System Resources</div>
                <div class="card-body">
                    @if(!empty($resources))
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    @foreach(array_keys($resources[0]) as $key)
                                        <th>{{ ucwords(str_replace('-', ' ', $key)) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resources as $res)
                                <tr>
                                    @foreach($res as $val)
                                        <td>{{ $val ?? '-' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No system resources info found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- License Table -->
        <div class="tab-pane fade" id="license" role="tabpanel">
            <div class="card shadow mb-3">
                <div class="card-header">License Info</div>
                <div class="card-body">
                    @if(!empty($license))
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    @foreach(array_keys($license[0]) as $key)
                                        <th>{{ ucwords(str_replace('-', ' ', $key)) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($license as $lic)
                                <tr>
                                    @foreach($lic as $val)
                                        <td>{{ $val ?? '-' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No license info found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Clock Table -->
        <div class="tab-pane fade" id="clock" role="tabpanel">
            <div class="card shadow mb-3">
                <div class="card-header">Clock Info</div>
                <div class="card-body">
                    @if(!empty($clock))
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    @foreach(array_keys($clock[0]) as $key)
                                        <th>{{ ucwords(str_replace('-', ' ', $key)) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clock as $c)
                                <tr>
                                    @foreach($c as $val)
                                        <td>{{ $val ?? '-' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No clock info found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Packages Table -->
        <div class="tab-pane fade" id="packages" role="tabpanel">
            <div class="card shadow mb-3">
                <div class="card-header">Installed Packages</div>
                <div class="card-body">
                    @if(!empty($packages))
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    @foreach(array_keys($packages[0]) as $key)
                                        <th>{{ ucwords(str_replace('-', ' ', $key)) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $index => $pkg)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @foreach($pkg as $val)
                                        <td>{{ $val ?? '-' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No package info found.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
