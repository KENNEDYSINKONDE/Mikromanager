@extends('layouts.app')

@section('title', 'Terminal')

@section('content')

<div class="pagetitle">
  <h1>MikroTik Terminal</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('layout.dashboard') }}">Home</a></li>
      <li class="breadcrumb-item active">Terminal</li>
    </ol>
  </nav>
</div>

<div class="row">
  <div class="col-lg-9">

    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between py-2"
           style="background:#0f172a;border-bottom:1px solid #1e293b">
        <div class="d-flex align-items-center gap-2">
          <span style="width:12px;height:12px;border-radius:50%;background:#ef4444;display:inline-block"></span>
          <span style="width:12px;height:12px;border-radius:50%;background:#f59e0b;display:inline-block"></span>
          <span style="width:12px;height:12px;border-radius:50%;background:#22c55e;display:inline-block"></span>
          <span class="text-muted ms-2" style="font-size:12px;color:#64748b!important">
            {{ $activeRouter->identity ?? $activeRouter->name }} — {{ $activeRouter->host }}
          </span>
        </div>
        <button id="clearBtn" class="btn btn-sm" style="font-size:11px;color:#64748b;background:none;border:none">
          <i class="bi bi-trash"></i> Clear
        </button>
      </div>

      {{-- Terminal output --}}
      <pre id="terminal-output"
           style="background:#0f172a;color:#22c55e;padding:16px;margin:0;
                  height:450px;overflow-y:auto;font-family:'Courier New',monospace;
                  font-size:13px;line-height:1.6;border:none;border-radius:0">
<span style="color:#64748b">MikroTik Manager Terminal — {{ now()->format('d M Y H:i') }}</span>
<span style="color:#64748b">Router: {{ $activeRouter->identity ?? $activeRouter->name }} ({{ $activeRouter->host }}:{{ $activeRouter->port }})</span>
<span style="color:#64748b">Type a command and press Enter. Type 'help' to see allowed commands.</span>

</pre>

      {{-- Input --}}
      <div class="d-flex align-items-center"
           style="background:#0f172a;border-top:1px solid #1e293b;padding:8px 16px">
        <span style="color:#22c55e;font-family:monospace;font-size:13px;white-space:nowrap;margin-right:8px">
          [{{ $activeRouter->username }}@{{ $activeRouter->identity ?? 'router' }}] &gt;
        </span>
        <input type="text"
               id="terminal-input"
               style="flex:1;background:transparent;border:none;outline:none;
                      color:#f1f5f9;font-family:'Courier New',monospace;font-size:13px"
               placeholder="/ip hotspot user print"
               autocomplete="off"
               autofocus>
        <span id="spinner" style="display:none;color:#64748b;font-size:12px">
          <i class="bi bi-arrow-repeat"></i>
        </span>
      </div>
    </div>

  </div>

  {{-- Allowed commands sidebar --}}
  <div class="col-lg-3">
    <div class="card">
      <div class="card-body p-3">
        <h6 class="fw-bold mb-3" style="font-size:13px">
          <i class="bi bi-shield-check text-success me-1"></i> Allowed Commands
        </h6>
        <div style="font-size:11px;font-family:monospace;color:#64748b;line-height:2">
          @foreach([
            '/ip hotspot',
            '/ip dhcp-server',
            '/ip dhcp-client',
            '/ip address',
            '/ip route',
            '/queue',
            '/interface',
            '/system identity',
            '/system resource',
            '/system routerboard',
            '/system clock',
            '/system package',
            '/system license',
            '/ping',
          ] as $cmd)
          <div class="cmd-hint" style="cursor:pointer;padding:2px 4px;border-radius:3px"
               onclick="document.getElementById('terminal-input').value='{{ $cmd }} print';document.getElementById('terminal-input').focus()">
            {{ $cmd }}
          </div>
          @endforeach
        </div>

        <hr>
        <h6 class="fw-bold mb-2" style="font-size:12px">Quick Commands</h6>
        @foreach([
          ['/system resource print',     'Resource usage'],
          ['/system identity print',     'Router identity'],
          ['/ip hotspot user print',     'Hotspot users'],
          ['/ip address print',          'IP addresses'],
          ['/interface print',           'Interfaces'],
          ['/ip route print',            'Routes'],
        ] as [$cmd, $label])
        <button class="btn btn-sm btn-outline-secondary w-100 mb-1 text-start"
                style="font-size:11px;padding:4px 8px"
                onclick="runQuick('{{ $cmd }}')">
          <i class="bi bi-terminal me-1"></i> {{ $label }}
        </button>
        @endforeach
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const input   = document.getElementById('terminal-input');
const output  = document.getElementById('terminal-output');
const spinner = document.getElementById('spinner');
const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Command history
let history = [], histIdx = -1;

function appendLine(text, color = '#22c55e') {
    const span = document.createElement('span');
    span.style.color = color;
    span.textContent = text + '\n';
    output.appendChild(span);
    output.scrollTop = output.scrollHeight;
}

function runCommand(command) {
    if (!command.trim()) return;

    // Add to history
    history.unshift(command);
    if (history.length > 50) history.pop();
    histIdx = -1;

    // Handle built-in help
    if (command.trim().toLowerCase() === 'help') {
        appendLine('[admin@router] > help', '#94a3b8');
        appendLine('Allowed command prefixes:', '#f59e0b');
        ['/ip hotspot', '/ip address', '/ip route', '/queue',
         '/interface', '/system', '/ping'].forEach(c => appendLine('  ' + c, '#64748b'));
        return;
    }

    // Show command in terminal
    appendLine(`[{{ $activeRouter->username ?? 'admin' }}@{{ $activeRouter->identity ?? 'router' }}] > ${command}`, '#94a3b8');

    // Disable input while running
    input.disabled = true;
    spinner.style.display = 'inline';

    fetch('{{ route("mikrotik.command") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ command }),
    })
    .then(r => r.json())
    .then(data => {
        const text = data.output || 'OK';

        // Try to pretty-print JSON
        try {
            const parsed = JSON.parse(text);
            if (Array.isArray(parsed)) {
                if (parsed.length === 0) {
                    appendLine('(no results)', '#64748b');
                } else {
                    parsed.forEach((row, i) => {
                        appendLine(`--- [${i}] ---`, '#3b82f6');
                        Object.entries(row).forEach(([k, v]) => {
                            appendLine(`  ${k.padEnd(24)} = ${v}`, '#22c55e');
                        });
                    });
                }
            } else {
                appendLine(JSON.stringify(parsed, null, 2), '#22c55e');
            }
        } catch {
            // Not JSON — show as plain text
            const color = text.startsWith('⛔') ? '#ef4444' :
                          text.startsWith('Error') ? '#ef4444' : '#22c55e';
            text.split('\n').forEach(line => appendLine(line, color));
        }
    })
    .catch(err => {
        appendLine('⛔ Request failed: ' + err.message, '#ef4444');
    })
    .finally(() => {
        input.disabled = false;
        spinner.style.display = 'none';
        input.focus();
        appendLine(''); // blank line between commands
    });
}

function runQuick(cmd) {
    input.value = cmd;
    input.focus();
    runCommand(cmd);
    input.value = '';
}

// Enter to run
input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const cmd = input.value.trim();
        if (cmd) {
            runCommand(cmd);
            input.value = '';
        }
    }

    // Arrow up/down for history
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (histIdx < history.length - 1) {
            histIdx++;
            input.value = history[histIdx];
        }
    }
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (histIdx > 0) {
            histIdx--;
            input.value = history[histIdx];
        } else {
            histIdx = -1;
            input.value = '';
        }
    }
});

// Clear button
document.getElementById('clearBtn').addEventListener('click', () => {
    output.innerHTML = '';
    appendLine('Terminal cleared.', '#64748b');
});

// Highlight quick command hints on hover
document.querySelectorAll('.cmd-hint').forEach(el => {
    el.addEventListener('mouseenter', () => el.style.background = '#f1f5f9');
    el.addEventListener('mouseleave', () => el.style.background = 'none');
});
</script>
@endpush