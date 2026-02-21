<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link {{ request()->is('dashboard') ? '' : 'collapsed' }}"
         href="{{ route('layout.dashboard') }}">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <!-- SYSTEM -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#system-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-cpu"></i><span>System</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="system-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{ url('/status') }}">
            <i class="bi bi-circle"></i><span>Identity</span>
          </a>
        </li>
        <li>
          <a href="{{ route('mikrotik.routerboard') }}">
            <i class="bi bi-circle"></i><span>RouterBOARD</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- INTERFACES -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#interfaces-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-diagram-3"></i><span>Interfaces</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="interfaces-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{ url('/mikrotik/interfaces') }}">
            <i class="bi bi-circle"></i><span>Interfaces</span>
          </a>
        </li>
        <li>
          <a href="{{ route('mikrotik.bridges') }}">
            <i class="bi bi-circle"></i><span>Bridges</span>
          </a>
        </li>
        <li>
          <a href="{{ route('mikrotik.bridge.ports') }}">
            <i class="bi bi-circle"></i><span>Bridge Ports</span>
          </a>
        </li>
        <li>
          <a href="{{ route('mikrotik.bridge.hosts') }}">
            <i class="bi bi-circle"></i><span>Bridge Hosts</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- HOTSPOT -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#hotspot-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-wifi"></i><span>Hotspot</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="hotspot-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{ route('hotspot.servers') }}">
            <i class="bi bi-circle"></i><span>Servers</span>
          </a>
        </li>
        <li>
          <a href="{{ route('hotspot.serverProfile') }}">
            <i class="bi bi-circle"></i><span>Server Profiles</span>
          </a>
        </li>
        <li>
          <a href="{{ route('profiles.index') }}">
            <i class="bi bi-circle"></i><span>User Profiles</span>
          </a>
        </li>
        <li>
          <a href="{{ route('vouchers.index') }}">
            <i class="bi bi-circle"></i><span>Vouchers</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- TERMINAL -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ url('/mikrotik/terminal') }}">
        <i class="bi bi-terminal"></i>
        <span>Terminal</span>
      </a>
    </li>


    {{-- ── Router Status & Disconnect ── --}}
    @if(isset($activeRouter))
    <li class="nav-item border-top mt-2 pt-1">
      <div class="nav-link d-flex align-items-center gap-2 py-2">
        <span class="status-light {{ $activeRouter->status === 'online' ? 'online' : 'offline' }}"></span>
        <div class="flex-fill overflow-hidden">
          <div class="small fw-semibold text-truncate" style="font-size:12px">{{ $activeRouter->name }}</div>
          <div class="text-muted" style="font-size:10px;font-family:monospace">{{ $activeRouter->host }}</div>
        </div>
        <form method="POST" action="{{ route('router.disconnect') }}" class="mb-0">
          @csrf
          <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-1" title="Change Router">
            <i class="bi bi-arrow-repeat" style="font-size:11px"></i>
          </button>
        </form>
      </div>
    </li>
    @endif

    {{-- ── User & Logout ── --}}
    @auth
    <li class="nav-item border-top mt-1">
      <div class="nav-link d-flex align-items-center gap-2 py-2">
        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
             style="width:30px;height:30px;font-size:11px">
          {{ Auth::user()->initials }}
        </div>
        <div class="flex-fill overflow-hidden">
          <div class="small fw-semibold text-truncate" style="font-size:12px">{{ Auth::user()->name }}</div>
          <div class="text-muted text-capitalize" style="font-size:10px">{{ Auth::user()->role }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mb-0">
          @csrf
          <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Logout">
            <i class="bi bi-box-arrow-right" style="font-size:11px"></i>
          </button>
        </form>
      </div>
    </li>
    @endauth

  </ul>

</aside>
