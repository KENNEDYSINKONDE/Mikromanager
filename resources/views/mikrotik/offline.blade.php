@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <div class="alert alert-danger shadow-sm">
        <h3 class="mb-3"><i class="bi bi-wifi-off"></i> Router is Offline</h3>
        <p>We could not connect to the MikroTik router. Please check if it is powered on and connected to the network.</p>
        <a href="{{ url()->current() }}" class="btn btn-primary mt-3">Retry Connection</a>
    </div>
    <div class="text-muted mt-3">
        <small>If the problem persists, contact your network administrator.</small>
    </div>
</div>
@endsection
