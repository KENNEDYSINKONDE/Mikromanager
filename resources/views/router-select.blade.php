<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Router — MikroTik Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg: #080b14; --surface: #0f1320; --card: #141828;
            --border: #1e2438; --primary: #3b82f6;
            --primary-glow: rgba(59,130,246,0.2);
            --text: #f1f5f9; --muted: #64748b;
            --success: #22c55e; --danger: #ef4444;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--text);
        }

        /* Top bar */
        .topbar {
            height: 60px;
            background: var(--card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }
        .topbar .brand { display:flex; align-items:center; gap:10px; }
        .topbar .brand .icon {
            width:36px;height:36px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            border-radius:9px;
            display:flex;align-items:center;justify-content:center;
            font-size:16px;color:#fff;
        }
        .topbar .brand .name { font-size:15px; font-weight:700; }
        .topbar .user {
            display:flex;align-items:center;gap:10px;
            font-size:13px;color:var(--muted);
        }
        .topbar .user .avatar {
            width:32px;height:32px;border-radius:50%;
            background:linear-gradient(135deg,var(--primary),#6366f1);
            display:flex;align-items:center;justify-content:center;
            font-size:12px;font-weight:700;color:#fff;
        }
        .logout-btn {
            background:none;border:1px solid var(--border);
            color:var(--muted);padding:5px 12px;border-radius:7px;
            font-size:12px;cursor:pointer;font-family:'Inter',sans-serif;
            transition:all .15s;
        }
        .logout-btn:hover { border-color:var(--danger);color:var(--danger); }

        /* Main */
        .page { max-width: 900px; margin: 0 auto; padding: 48px 24px; }

        .page-header { text-align:center; margin-bottom:48px; }
        .page-header h1 { font-size:28px;font-weight:800;letter-spacing:-0.5px;margin-bottom:8px; }
        .page-header p  { font-size:15px;color:var(--muted); }

        /* Alert */
        .alert {
            padding:12px 16px;border-radius:10px;font-size:13.5px;
            margin-bottom:24px;display:flex;align-items:center;gap:10px;border:1px solid;
            max-width:600px;margin-left:auto;margin-right:auto;
        }
        .alert-success { background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.2);color:#86efac; }
        .alert-error   { background:rgba(239,68,68,0.08);border-color:rgba(239,68,68,0.2);color:#fca5a5; }

        /* Connect new card */
        .connect-card {
            background:var(--card);
            border:1px solid var(--border);
            border-radius:16px;
            padding:28px 32px;
            margin-bottom:32px;
        }
        .connect-card h3 {
            font-size:15px;font-weight:600;margin-bottom:20px;
            display:flex;align-items:center;gap:8px;
        }
        .connect-card h3 i { color:var(--primary); }

        .form-row { display:grid;grid-template-columns:1fr 120px 1fr 1fr auto;gap:12px;align-items:end; }

        label {
            display:block;font-size:11px;font-weight:600;
            text-transform:uppercase;letter-spacing:0.8px;
            color:var(--muted);margin-bottom:6px;
        }

        .input-wrap {
            display:flex;align-items:center;
            background:var(--surface);border:1px solid var(--border);
            border-radius:9px;overflow:hidden;transition:border-color .2s,box-shadow .2s;
        }
        .input-wrap:focus-within { border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-glow); }
        .input-wrap i { padding:0 11px;color:var(--muted);font-size:14px;flex-shrink:0; }
        .input-wrap input {
            flex:1;background:transparent;border:none;outline:none;
            color:var(--text);font-size:13px;font-family:'Inter',sans-serif;
            padding:10px 12px 10px 0;
        }
        .input-wrap input::placeholder { color:#2a3050; }

        .btn-connect {
            padding:10px 20px;
            background:var(--primary);color:#fff;border:none;
            border-radius:9px;font-size:13px;font-weight:600;
            font-family:'Inter',sans-serif;cursor:pointer;
            white-space:nowrap;display:flex;align-items:center;gap:6px;
            transition:all .15s;box-shadow:0 4px 16px var(--primary-glow);
            height:42px;
        }
        .btn-connect:hover { background:#2563eb;transform:translateY(-1px); }

        /* Recent routers grid */
        .section-title {
            font-size:12px;text-transform:uppercase;letter-spacing:1px;
            color:var(--muted);font-weight:600;margin-bottom:16px;
        }

        .routers-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
            gap:14px;
        }

        .router-card {
            background:var(--card);
            border:1px solid var(--border);
            border-radius:14px;
            padding:20px;
            cursor:pointer;
            transition:border-color .15s,transform .15s,box-shadow .15s;
            position:relative;
            overflow:hidden;
        }
        .router-card:hover {
            border-color:var(--primary);
            transform:translateY(-2px);
            box-shadow:0 8px 32px rgba(0,0,0,0.3);
        }
        .router-card::before {
            content:'';position:absolute;top:0;left:0;right:0;height:2px;
            background:linear-gradient(90deg,var(--primary),#6366f1);
            opacity:0;transition:opacity .15s;
        }
        .router-card:hover::before { opacity:1; }

        .router-status {
            display:flex;align-items:center;gap:6px;margin-bottom:12px;
        }
        .status-dot {
            width:8px;height:8px;border-radius:50%;
        }
        .dot-online  { background:var(--success);box-shadow:0 0 6px var(--success); }
        .dot-offline { background:var(--muted); }

        .status-text { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px; }
        .text-online  { color:var(--success); }
        .text-offline { color:var(--muted); }

        .router-name { font-size:16px;font-weight:700;margin-bottom:4px; }
        .router-host { font-size:12px;color:var(--muted);font-family:'Courier New',monospace; }

        .router-meta {
            display:flex;gap:12px;margin-top:14px;padding-top:14px;
            border-top:1px solid var(--border);
        }
        .router-meta .meta-item { font-size:11px;color:var(--muted); }
        .router-meta .meta-item strong { display:block;color:var(--text);font-size:12px;margin-bottom:1px; }

        .router-connect-btn {
            position:absolute;bottom:16px;right:16px;
            width:32px;height:32px;
            background:rgba(59,130,246,0.1);
            border:1px solid rgba(59,130,246,0.2);
            border-radius:8px;
            display:flex;align-items:center;justify-content:center;
            color:var(--primary);font-size:14px;
            transition:all .15s;
        }
        .router-card:hover .router-connect-btn {
            background:var(--primary);color:#fff;border-color:var(--primary);
        }

        .empty-state {
            text-align:center;padding:48px;color:var(--muted);
            background:var(--card);border:1px dashed var(--border);
            border-radius:14px;
        }
        .empty-state i { font-size:40px;opacity:0.3;display:block;margin-bottom:12px; }
        .empty-state p { font-size:14px; }

        /* Hidden form for quick reconnect */
        .d-none { display:none; }

        @media (max-width:768px) {
            .form-row { grid-template-columns:1fr; }
            .routers-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

    {{-- Top bar --}}
    <div class="topbar">
        <div class="brand">
            <div class="icon"><i class="bi bi-router"></i></div>
            <span class="name">MikroTik Manager</span>
        </div>
        <div class="user">
            <div class="avatar">{{ Auth::user()->initials }}</div>
            <span>{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="page">

        <div class="page-header">
            <h1>Connect to Router</h1>
            <p>Select a previously used router or enter new credentials to connect</p>
        </div>

        @if(session('error'))
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            {!! session('error') !!}
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {!! session('success') !!}
        </div>
        @endif

        {{-- New connection form --}}
        <div class="connect-card">
            <h3><i class="bi bi-plus-circle-fill"></i> New Connection</h3>
            <form method="POST" action="{{ route('router.connect') }}" id="newConnForm">

                @csrf
                <div class="form-row">
                    <div>
                        <label>Router IP</label>
                        <div class="input-wrap">
                            <i class="bi bi-hdd-network"></i>
                            <input type="text" name="host" placeholder="192.168.88.1" value="{{ old('host') }}" required>
                        </div>
                    </div>
                    <div>
                        <label>Port</label>
                        <div class="input-wrap">
                            <input type="number" name="port" placeholder="8728" value="{{ old('port', 8728) }}" style="padding-left:12px" required>
                        </div>
                    </div>
                    <div>
                        <label>Username</label>
                        <div class="input-wrap">
                            <i class="bi bi-person"></i>
                            <input type="text" name="username" placeholder="admin" value="{{ old('username', 'admin') }}" required>
                        </div>
                    </div>
                    <div>
                        <label>Password</label>
                        <div class="input-wrap">
                            <i class="bi bi-key"></i>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div>
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-connect">
                            <i class="bi bi-plug-fill"></i> Connect
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Recent routers --}}
        @if($recentRouters->count())
        <div class="section-title">Recent Routers</div>
        <div class="routers-grid">
            @foreach($recentRouters as $router)
            <div class="router-card" onclick="quickConnect({{ $router->id }}, '{{ $router->host }}', {{ $router->port }}, '{{ $router->username }}', '{{ addslashes($router->name) }}')">
                <div class="router-status">
                    <span class="status-dot {{ $router->status === 'online' ? 'dot-online' : 'dot-offline' }}"></span>
                    <span class="status-text {{ $router->status === 'online' ? 'text-online' : 'text-offline' }}">
                        {{ $router->status }}
                    </span>
                </div>
                <div class="router-name">{{ $router->name }}</div>
                <div class="router-host">{{ $router->host }}:{{ $router->port }}</div>
                <div class="router-meta">
                    <div class="meta-item">
                        <strong>{{ $router->username }}</strong>
                        API User
                    </div>
                    <div class="meta-item">
                        <strong>{{ $router->last_connected_at ? $router->last_connected_at->diffForHumans() : 'Never' }}</strong>
                        Last Connected
                    </div>
                </div>
                <div class="router-connect-btn">
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <i class="bi bi-hdd-network"></i>
            <p>No recent routers. Enter your MikroTik details above to connect.</p>
        </div>
        @endif

    </div>

    {{-- Hidden quick reconnect form --}}
    <form id="quickForm" method="POST" action="{{ route('router.connect') }}" class="d-none">
        @csrf
        <input type="hidden" name="host"     id="qf_host">
        <input type="hidden" name="port"     id="qf_port">
        <input type="hidden" name="username" id="qf_user">
        <input type="hidden" name="password" id="qf_pass">
    </form>

    <script>
    function quickConnect(id, host, port, user, name) {
        Swal.fire({
            title: name,
            html: `<div style="color:#64748b;font-size:13px;margin-bottom:4px">${host}:${port} · ${user}</div>`,
            input: 'password',
            inputPlaceholder: 'Enter password to reconnect',
            inputAttributes: { autocomplete: 'current-password' },
            confirmButtonText: '<i class="swal2-icon-text"></i> Connect',
            confirmButtonColor: '#3b82f6',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            background: '#141828',
            color: '#f1f5f9',
        }).then(result => {
            if (result.isConfirmed && result.value) {
                document.getElementById('qf_host').value = host;
                document.getElementById('qf_port').value = port;
                document.getElementById('qf_user').value = user;
                document.getElementById('qf_pass').value = result.value;
                document.getElementById('quickForm').submit();
            }
        });
    }

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon:'success', html: @json(session('success')), timer:2000, timerProgressBar:true, showConfirmButton:false, background:'#141828', color:'#f1f5f9' });
    });
    @endif
    </script>

</body>
</html>
