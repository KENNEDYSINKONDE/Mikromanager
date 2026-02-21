@extends('layouts.app')

@section('content')

<div class="card text-center">
    <h3>Router Status</h3>

    @if(!empty($identity))
        <p><strong>Router Name:</strong> {{ $identity[0]['name'] ?? 'Unknown' }}</p>
        <p><strong>Status:</strong> 🟢 Online</p>
    @else
        <p><strong>Status:</strong> 🔴 Offline</p>
    @endif
</div>

@endsection
