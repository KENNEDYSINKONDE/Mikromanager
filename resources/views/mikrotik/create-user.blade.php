@extends('layouts.app')

@section('content')

<div class="card">
    <h3>Create Hotspot User</h3>

    <form method="POST" action="{{ route('mikrotik.user.store') }}">
        @csrf

        <div style="margin-bottom: 10px;">
            <label>Username</label><br>
            <input type="text" name="username" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Password</label><br>
            <input type="text" name="password" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Profile</label><br>
            <select name="profile">
                <option value="default">Default</option>
                <option value="1M-profile">1 Mbps</option>
                <option value="2M-profile">2 Mbps</option>
            </select>
        </div>

        <button type="submit">Create User</button>
    </form>
</div>

@endsection
