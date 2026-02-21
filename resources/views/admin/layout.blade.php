<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') — MikroTik Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sidebar-w: 260px;
            --header-h: 60px;
            --bg: #f0f2f5;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-active: #3b82f6;
            --primary: #3b82f6;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; }

        /* Sidebar */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w); background: var(--sidebar-bg);
            display: flex; flex-direction: column; z-index: 100;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-logo .icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 16px; flex-shrink: 0;
        }
        .sidebar-logo .text { font-size: 14px; font-weight: 700; color: #f1f5f9; }
        .sidebar-logo .badge-admin {
            font-size: 9px; background: #ef4444; color: #fff;
            padding: 1px 6px; border-radius: 4px; font-weight: 700;
            letter-spacing: 0.5px; margin-left: 4px;
        }

        .sidebar-section { padding: 20px 16px 8px; font-size: 10px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            color: #475569; }

        .sidebar-nav { padding: 0 10px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; border-radius: 8px; margin-bottom: 2px;
            color: var(--sidebar-text); text-decoration: none;
            font-size: 13px; font-weight: 500; transition: all .15s;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.06); color: #f1f5f9; }
        .sidebar-nav a.active { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .sidebar-nav a i { font-size: 15px; width: 20px; text-align: center; }
        .sidebar-nav .badge { margin-left: auto; font-size: 10px; }

        .sidebar-bottom {
            margin-top: auto; padding: 16px; border-top: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-user { display: flex; align-items: center; gap: 10px; }
        .sidebar-user .avatar {
            width: 34px; height: 34px; border-radius: 8px;
            background: linear-gradient(135deg,#3b82f6,#6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .sidebar-user .info { flex: 1; min-width: 0; }
        .sidebar-user .name { font-size: 12px; font-weight: 600; color: #f1f5f9;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user .role { font-size: 10px; color: #64748b; }
        .sidebar-user .logout-btn {
            width: 28px; height: 28px; border-radius: 6px; border: none;
            background: rgba(239,68,68,0.1); color: #ef4444;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .15s; flex-shrink: 0;
        }
        .sidebar-user .logout-btn:hover { background: rgba(239,68,68,0.2); }

        /* Main content */
        .admin-main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .admin-header {
            height: var(--header-h); background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px; position: sticky; top: 0; z-index: 50;
        }
        .admin-header h1 { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
        .admin-header .breadcrumb { font-size: 12px; color: #94a3b8; margin: 0; }

        .admin-content { padding: 28px; }

        /* Cards */
        .stat-card { background: #fff; border-radius: 12px; padding: 20px 24px;
            border: 1px solid #e2e8f0; }
        .stat-card .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; }
        .stat-card .stat-label { font-size: 12px; color: #94a3b8; font-weight: 500; margin-top: 2px; }
        .stat-card .stat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        /* Table */
        .admin-table { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .admin-table .table { margin: 0; font-size: 13px; }
        .admin-table .table th { background: #f8fafc; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;
            border-bottom: 1px solid #e2e8f0; padding: 12px 16px; white-space: nowrap; }
        .admin-table .table td { padding: 13px 16px; vertical-align: middle;
            border-bottom: 1px solid #f1f5f9; color: #334155; }
        .admin-table .table tr:last-child td { border-bottom: none; }
        .admin-table .table tr:hover td { background: #f8fafc; }

        /* Badges */
        .plan-badge { display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .plan-trial      { background: #fef3c7; color: #92400e; }
        .plan-starter    { background: #dbeafe; color: #1e40af; }
        .plan-pro        { background: #ede9fe; color: #5b21b6; }
        .plan-enterprise { background: #dcfce7; color: #14532d; }

        .status-active    { background: #dcfce7; color: #14532d; }
        .status-suspended { background: #fee2e2; color: #7f1d1d; }
        .status-cancelled { background: #f1f5f9; color: #475569; }

        /* Alerts */
        .flash-success, .flash-error { position:fixed; top:20px; right:20px; z-index:9999;
            max-width:380px; border-radius:10px; padding:14px 18px;
            display:flex; align-items:flex-start; gap:10px; box-shadow:0 8px 30px rgba(0,0,0,0.12); }
        .flash-success { background:#f0fdf4; border:1px solid #86efac; color:#14532d; }
        .flash-error   { background:#fef2f2; border:1px solid #fca5a5; color:#7f1d1d; }

        /* Impersonation banner */
        .impersonate-bar {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff; padding: 8px 24px;
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Impersonation banner --}}
@if(session('impersonating_as'))
<div class="impersonate-bar">
    <span><i class="bi bi-person-fill-gear me-2"></i>
        Impersonating: <strong>{{ Auth::user()->name }}</strong>
        ({{ Auth::user()->tenant->name ?? 'Unknown' }})
    </span>
    <a href="{{ route('admin.impersonate.stop') }}" class="text-white fw-bold">
        <i class="bi bi-x-circle me-1"></i> Stop Impersonating
    </a>
</div>
@endif

{{-- Sidebar --}}
<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <div class="icon"><i class="bi bi-shield-lock"></i></div>
        <div>
            <div class="text">MikroTik Manager <span class="badge-admin">ADMIN</span></div>
        </div>
    </div>

    <div class="sidebar-section">Main</div>
    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
    </nav>

    <div class="sidebar-section">ISP Management</div>
    <nav class="sidebar-nav">
        <a href="{{ route('admin.tenants.index') }}" class="{{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> All ISPs
            <span class="badge bg-primary badge">{{ \App\Models\Tenant::count() }}</span>
        </a>
        <a href="{{ route('admin.tenants.create') }}">
            <i class="bi bi-plus-circle"></i> Add New ISP
        </a>
    </nav>

    <div class="sidebar-section">Quick Stats</div>
    <nav class="sidebar-nav">
        <a href="{{ route('admin.tenants.index') }}?plan=trial">
            <i class="bi bi-clock-history text-warning"></i> On Trial
            <span class="badge bg-warning text-dark badge">{{ \App\Models\Tenant::where('plan','trial')->count() }}</span>
        </a>
        <a href="{{ route('admin.tenants.index') }}?status=suspended">
            <i class="bi bi-slash-circle text-danger"></i> Suspended
            <span class="badge bg-danger badge">{{ \App\Models\Tenant::where('status','suspended')->count() }}</span>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <div class="sidebar-user">
            <div class="avatar">{{ Auth::user()->initials }}</div>
            <div class="info">
                <div class="name">{{ Auth::user()->name }}</div>
                <div class="role">Super Admin</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn" title="Logout">
                    <i class="bi bi-box-arrow-right" style="font-size:13px"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Main --}}
<div class="admin-main">
    <div class="admin-header">
        <div>
            <h1>@yield('page-title', 'Admin Panel')</h1>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted" style="font-size:12px">{{ now()->format('d M Y, H:i') }}</span>
            <a href="{{ route('layout.dashboard') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> View App
            </a>
        </div>
    </div>

    <div class="admin-content">
        {{-- Flash messages --}}
        @if(session('success'))
        <div class="flash-success" id="flash">
            <i class="bi bi-check-circle-fill mt-1" style="flex-shrink:0"></i>
            <span>{!! session('success') !!}</span>
            <button onclick="document.getElementById('flash').remove()" style="background:none;border:none;margin-left:auto;cursor:pointer;color:inherit">✕</button>
        </div>
        @endif
        @if(session('error'))
        <div class="flash-error" id="flash-err">
            <i class="bi bi-exclamation-circle-fill mt-1" style="flex-shrink:0"></i>
            <span>{!! session('error') !!}</span>
            <button onclick="document.getElementById('flash-err').remove()" style="background:none;border:none;margin-left:auto;cursor:pointer;color:inherit">✕</button>
        </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
// Auto-dismiss flash after 4s
setTimeout(() => {
    document.getElementById('flash')?.remove();
    document.getElementById('flash-err')?.remove();
}, 4000);

// SweetAlert confirm for dangerous actions
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form') || document.getElementById(this.dataset.form);
        Swal.fire({
            title: this.dataset.confirm,
            text: this.dataset.confirmText || '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: this.dataset.confirmBtn || 'Yes, proceed',
        }).then(r => { if (r.isConfirmed && form) form.submit(); });
    });
});
</script>
</body>
</html>
