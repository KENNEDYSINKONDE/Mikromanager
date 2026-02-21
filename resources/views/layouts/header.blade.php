@php
    $user          = Auth::user();
    $tenant        = $currentTenant  ?? null;
    $router        = $activeRouter   ?? null;

    // ── Live notification badges ────────────────────────────────────────────
    $alerts = [];

    if ($router) {
        // Unsynced vouchers
        $unsynced = \App\Models\Voucher::where('router_id', $router->id)
                        ->where('mikrotik_synced', false)->count();
        if ($unsynced > 0) {
            $alerts[] = [
                'icon'  => 'bi-cloud-slash',
                'color' => 'warning',
                'title' => "{$unsynced} voucher(s) not synced",
                'sub'   => 'Click to go to vouchers → sync',
                'time'  => 'Pending',
                'url'   => route('vouchers.index'),
            ];
        }
    }

    if ($tenant && $tenant->isOnTrial()) {
        $dLeft = $tenant->trialDaysLeft();
        if ($dLeft <= 5) {
            $alerts[] = [
                'icon'  => 'bi-clock-history',
                'color' => $dLeft <= 2 ? 'danger' : 'warning',
                'title' => "Trial expires in {$dLeft} day(s)!",
                'sub'   => 'Upgrade now to keep access.',
                'time'  => 'Urgent',
                'url'   => '#',
            ];
        }
    }

    // Voucher counts
    $totalVouchers  = $router ? \App\Models\Voucher::where('router_id', $router->id)->count()        : 0;
    $activeVouchers = $router ? \App\Models\Voucher::where('router_id', $router->id)->where('status','active')->count() : 0;
@endphp

<header id="header" class="header fixed-top d-flex align-items-center">

    {{-- ═══════════════════════════════ LOGO ═══════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('layout.dashboard') }}" class="logo d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;border-radius:8px;
                        background:linear-gradient(135deg,#4154f1,#7c3aed);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-router" style="color:#fff;font-size:16px"></i>
            </div>
            <span class="d-none d-lg-block fw-bold" style="font-size:15px;letter-spacing:-.3px;color:#012970">
                MIKRO<span style="color:#4154f1">MANAGER</span>
            </span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    {{-- ════════════════════════ ROUTER STATUS PILL ═══════════════════════ --}}
    @if($router)
    <div class="d-none d-lg-flex align-items-center ms-3 gap-2"
         style="background:#f0fdf4;border:1px solid #bbf7d0;
                border-radius:20px;padding:4px 12px 4px 10px;cursor:default"
         title="Connected Router">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;
                     box-shadow:0 0 0 3px rgba(34,197,94,.2);display:inline-block;flex-shrink:0"></span>
        <span style="font-size:12px;font-weight:600;color:#15803d;max-width:120px;
                     white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            {{ $router->identity ?? $router->name }}
        </span>
        <span style="font-size:11px;color:#86efac;font-family:monospace">
            {{ $router->host }}
        </span>
    </div>
    @endif

    {{-- ═══════════════════════════ SEARCH BAR ════════════════════════════ --}}
    <div class="search-bar">
        <form class="search-form d-flex align-items-center"
              method="GET" action="{{ route('vouchers.index') }}">
            <input type="text" name="search"
                   placeholder="Search vouchers…"
                   value="{{ request('search') }}"
                   title="Search vouchers by username or batch">
            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
        </form>
    </div>

    {{-- ═══════════════════════════ NAV ICONS ═════════════════════════════ --}}
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            {{-- Mobile search toggle --}}
            <li class="nav-item d-block d-lg-none">
                <a class="nav-link nav-icon search-bar-toggle" href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li>

            {{-- ── Voucher quick counter ── --}}
            @if($router)
            <li class="nav-item d-none d-xl-flex align-items-center me-1">
                <a href="{{ route('vouchers.index') }}"
                   class="nav-link d-flex align-items-center gap-1 py-0"
                   style="font-size:12px"
                   title="{{ $activeVouchers }} active / {{ $totalVouchers }} total vouchers">
                    <i class="bi bi-ticket-perforated" style="font-size:17px;color:#4154f1"></i>
                    <span style="line-height:1.2">
                        <span style="font-weight:700;color:#0f172a">{{ $activeVouchers }}</span>
                        <span style="color:#94a3b8"> / {{ $totalVouchers }}</span>
                    </span>
                </a>
            </li>
            @endif

            {{-- ── Notifications ── --}}
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    @if(count($alerts) > 0)
                        <span class="badge bg-danger badge-number">{{ count($alerts) }}</span>
                    @endif
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications"
                    style="min-width:320px">
                    <li class="dropdown-header d-flex align-items-center justify-content-between">
                        <span>
                            @if(count($alerts) > 0)
                                <strong>{{ count($alerts) }}</strong> notification(s)
                            @else
                                Notifications
                            @endif
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>

                    @forelse($alerts as $a)
                    <li class="notification-item">
                        <a href="{{ $a['url'] }}" class="d-flex align-items-start gap-2 text-decoration-none">
                            <i class="bi {{ $a['icon'] }} text-{{ $a['color'] }} mt-1"
                               style="font-size:20px;flex-shrink:0"></i>
                            <div style="flex:1">
                                <h4 style="font-size:13px;font-weight:600;color:#0f172a;margin:0 0 2px">
                                    {{ $a['title'] }}
                                </h4>
                                <p style="font-size:11px;color:#64748b;margin:0 0 2px">{{ $a['sub'] }}</p>
                                <p style="font-size:10px;color:#94a3b8;margin:0">{{ $a['time'] }}</p>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @empty
                    <li class="py-3 text-center">
                        <i class="bi bi-check-circle-fill text-success"
                           style="font-size:28px;display:block;margin-bottom:6px"></i>
                        <span style="font-size:12px;color:#64748b">Everything looks good!</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @endforelse

                    <li class="dropdown-footer">
                        <a href="{{ route('layout.dashboard') }}">View Dashboard</a>
                    </li>
                </ul>
            </li>

            {{-- ── Router dropdown ── --}}
            @if($router)
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" title="Router Info">
                    <i class="bi bi-router"></i>
                    <span class="badge bg-success badge-number"
                          style="width:7px;height:7px;padding:0;min-width:unset;border-radius:50%"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow"
                    style="min-width:270px;padding:0">

                    {{-- Router header --}}
                    <li class="px-3 py-3"
                        style="background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:6px 6px 0 0">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:40px;height:40px;border-radius:10px;
                                        background:rgba(255,255,255,.08);
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i class="bi bi-router" style="color:#22c55e;font-size:20px"></i>
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="fw-bold text-truncate"
                                     style="color:#f1f5f9;font-size:14px">
                                    {{ $router->identity ?? $router->name }}
                                </div>
                                <div style="font-family:monospace;font-size:11px;color:#64748b">
                                    {{ $router->host }}:{{ $router->port }}
                                </div>
                                <span class="badge bg-success mt-1" style="font-size:9px">
                                    <i class="bi bi-circle-fill me-1" style="font-size:6px"></i>Online
                                </span>
                            </div>
                        </div>
                    </li>

                    {{-- Router details --}}
                    <li class="px-3 py-2">
                        <table style="font-size:12px;width:100%;border-collapse:separate;border-spacing:0 4px">
                            @if($router->model)
                            <tr>
                                <td style="color:#94a3b8;width:40%"><i class="bi bi-cpu me-1"></i>Model</td>
                                <td style="font-weight:600;color:#0f172a">{{ $router->model }}</td>
                            </tr>
                            @endif
                            @if($router->version)
                            <tr>
                                <td style="color:#94a3b8"><i class="bi bi-tag me-1"></i>Version</td>
                                <td style="font-weight:600;color:#0f172a">RouterOS {{ $router->version }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="color:#94a3b8"><i class="bi bi-person me-1"></i>User</td>
                                <td style="font-weight:600;color:#0f172a;font-family:monospace">{{ $router->username }}</td>
                            </tr>
                            @if($router->last_connected_at)
                            <tr>
                                <td style="color:#94a3b8"><i class="bi bi-clock me-1"></i>Last seen</td>
                                <td style="font-weight:600;color:#0f172a">{{ $router->last_connected_at->diffForHumans() }}</td>
                            </tr>
                            @endif
                        </table>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2"
                           href="{{ route('mikrotik.routerboard') }}">
                            <i class="bi bi-motherboard text-primary"></i>
                            RouterBOARD Info
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2"
                           href="{{ route('router.select') }}">
                            <i class="bi bi-arrow-left-right text-secondary"></i>
                            Switch Router
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('router.disconnect') }}" method="POST">
                            @csrf
                            <button class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                    onclick="return confirm('Disconnect from {{ $router->identity ?? $router->name }}?')">
                                <i class="bi bi-plug"></i> Disconnect
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ── Profile dropdown ── --}}
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0"
                   href="#" data-bs-toggle="dropdown">

                    {{-- Avatar --}}
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}"
                             alt="Profile" class="rounded-circle"
                             style="width:34px;height:34px;object-fit:cover;border:2px solid #e2e8f0">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:34px;height:34px;flex-shrink:0;
                                    background:linear-gradient(135deg,#4154f1,#7c3aed);
                                    color:#fff;font-size:12px;border:2px solid #e2e8f0">
                            {{ $user->initials }}
                        </div>
                    @endif

                    <span class="d-none d-md-block dropdown-toggle ps-2"
                          style="font-size:13px;font-weight:500;max-width:130px;
                                 white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $user->name }}
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile"
                    style="min-width:260px;padding:0;overflow:hidden">

                    {{-- ── Profile header ── --}}
                    <li style="background:linear-gradient(135deg,#0f172a,#1e293b);padding:16px 20px">
                        <div class="d-flex align-items-center gap-3">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}"
                                     class="rounded-circle"
                                     style="width:50px;height:50px;object-fit:cover;
                                            border:2px solid rgba(255,255,255,.15);flex-shrink:0">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                     style="width:50px;height:50px;flex-shrink:0;
                                            background:linear-gradient(135deg,#4154f1,#7c3aed);
                                            color:#fff;font-size:18px;
                                            border:2px solid rgba(255,255,255,.15)">
                                    {{ $user->initials }}
                                </div>
                            @endif
                            <div style="flex:1;min-width:0">
                                <div class="fw-bold text-truncate"
                                     style="color:#f1f5f9;font-size:14px">
                                    {{ $user->name }}
                                </div>
                                <div class="text-truncate" style="font-size:11px;color:#64748b">
                                    {{ $user->email }}
                                </div>
                                <div class="mt-1 d-flex gap-1 flex-wrap">
                                    <span style="background:rgba(65,84,241,.3);color:#818cf8;
                                                 font-size:10px;padding:1px 8px;border-radius:20px;
                                                 font-weight:600">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if($tenant)
                                    <span style="background:rgba(255,255,255,.08);color:#94a3b8;
                                                 font-size:10px;padding:1px 8px;border-radius:20px">
                                        {{ Str::limit($tenant->name, 20) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Trial progress bar --}}
                        @if($tenant && $tenant->isOnTrial())
                        @php $dLeft = $tenant->trialDaysLeft(); $pct = max(5, min(100, round(($dLeft/14)*100))); @endphp
                        <div class="mt-3 p-2 rounded-2"
                             style="background:rgba({{ $dLeft <= 3 ? '239,68,68' : '245,158,11' }},.12);
                                    border:1px solid rgba({{ $dLeft <= 3 ? '239,68,68' : '245,158,11' }},.3)">
                            <div class="d-flex justify-content-between align-items-center mb-1"
                                 style="font-size:11px">
                                <span style="color:{{ $dLeft <= 3 ? '#fca5a5' : '#fde68a' }};font-weight:600">
                                    <i class="bi bi-clock-history me-1"></i>Free Trial
                                </span>
                                <span style="color:{{ $dLeft <= 3 ? '#f87171' : '#fbbf24' }};font-weight:700">
                                    {{ $dLeft }}d left
                                </span>
                            </div>
                            <div style="height:4px;background:rgba(255,255,255,.1);border-radius:2px">
                                <div style="height:4px;border-radius:2px;width:{{ $pct }}%;
                                            background:{{ $dLeft <= 3 ? '#ef4444' : '#f59e0b' }};
                                            transition:width .3s"></div>
                            </div>
                        </div>
                        @endif

                        {{-- Last login info --}}
                        @if($user->last_login_at)
                        <div class="mt-2 d-flex align-items-center gap-1"
                             style="font-size:10px;color:#475569">
                            <i class="bi bi-shield-check text-success" style="font-size:11px"></i>
                            Last login {{ $user->last_login_at->diffForHumans() }}
                            @if($user->last_login_ip)
                            · <span style="font-family:monospace">{{ $user->last_login_ip }}</span>
                            @endif
                        </div>
                        @endif
                    </li>

                    {{-- ── Menu items ── --}}
                    <li style="padding:6px 0">
                        <a class="dropdown-item d-flex align-items-center gap-2"
                           href="{{ route('profile.show') }}"
                           style="font-size:13px;padding:8px 16px">
                            <span style="width:28px;height:28px;border-radius:7px;background:#eff6ff;
                                         display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i class="bi bi-person-fill" style="color:#4154f1;font-size:13px"></i>
                            </span>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>

                    <li style="padding:6px 0">
                        <a class="dropdown-item d-flex align-items-center gap-2"
                           href="{{ route('router.select') }}"
                           style="font-size:13px;padding:8px 16px">
                            <span style="width:28px;height:28px;border-radius:7px;background:#f0fdf4;
                                         display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i class="bi bi-router" style="color:#22c55e;font-size:13px"></i>
                            </span>
                            <span>{{ $router ? 'Switch Router' : 'Connect Router' }}</span>
                            @if($router)
                            <span class="ms-auto badge bg-success"
                                  style="font-size:9px;padding:2px 7px">Connected</span>
                            @else
                            <span class="ms-auto badge bg-warning text-dark"
                                  style="font-size:9px;padding:2px 7px">None</span>
                            @endif
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>

                    <li style="padding:6px 0">
                        <a class="dropdown-item d-flex align-items-center gap-2"
                           href="{{ route('vouchers.index') }}"
                           style="font-size:13px;padding:8px 16px">
                            <span style="width:28px;height:28px;border-radius:7px;background:#fffbeb;
                                         display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i class="bi bi-ticket-perforated" style="color:#f59e0b;font-size:13px"></i>
                            </span>
                            <span>Vouchers</span>
                            @if($router)
                            <span class="ms-auto" style="font-size:11px;color:#94a3b8">
                                <strong style="color:#0f172a">{{ $activeVouchers }}</strong> active
                            </span>
                            @endif
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>

                    @if($user->isSuperAdmin())
                    <li style="padding:6px 0">
                        <a class="dropdown-item d-flex align-items-center gap-2"
                           href="{{ route('admin.dashboard') }}"
                           style="font-size:13px;padding:8px 16px">
                            <span style="width:28px;height:28px;border-radius:7px;background:#fef2f2;
                                         display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i class="bi bi-shield-lock" style="color:#ef4444;font-size:13px"></i>
                            </span>
                            <span>Admin Panel</span>
                            <span class="ms-auto badge bg-danger"
                                  style="font-size:9px;padding:2px 7px">ADMIN</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>
                    @endif

                    <li style="padding:6px 0 10px">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2"
                                    style="font-size:13px;padding:8px 16px;color:#ef4444">
                                <span style="width:28px;height:28px;border-radius:7px;background:#fef2f2;
                                             display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-box-arrow-right" style="color:#ef4444;font-size:13px"></i>
                                </span>
                                Sign Out
                            </button>
                        </form>
                    </li>

                </ul>
            </li>

        </ul>
    </nav>

</header>