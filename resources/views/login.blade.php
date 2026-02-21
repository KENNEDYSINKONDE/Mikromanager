<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — MikroTik Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg:      #080b14;
            --surface: #0f1320;
            --card:    #141828;
            --border:  #1e2438;
            --primary: #3b82f6;
            --primary-glow: rgba(59,130,246,0.25);
            --text:    #f1f5f9;
            --muted:   #64748b;
            --success: #22c55e;
            --error:   #ef4444;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            display: grid;
            grid-template-columns: 1fr 480px;
        }

        /* ── Left panel ──────────────────────────────────── */
        .left-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            background: linear-gradient(135deg, #0f1724 0%, #0a0f1e 100%);
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 30% 40%, rgba(59,130,246,0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 70%, rgba(99,102,241,0.08) 0%, transparent 50%);
        }

        /* Animated grid lines */
        .grid-lines {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.04) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .left-content { position: relative; z-index: 1; }

        .left-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 60px;
        }

        .left-logo .icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            box-shadow: 0 4px 20px var(--primary-glow);
        }

        .left-logo .wordmark {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .left-panel h1 {
            font-size: 42px;
            font-weight: 800;
            color: var(--text);
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 18px;
        }

        .left-panel h1 span {
            background: linear-gradient(135deg, var(--primary), #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left-panel p {
            font-size: 16px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 380px;
            margin-bottom: 48px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature .dot {
            width: 32px; height: 32px;
            background: rgba(59,130,246,0.1);
            border: 1px solid rgba(59,130,246,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--primary);
            flex-shrink: 0;
        }

        .feature span {
            font-size: 14px;
            color: var(--muted);
        }

        /* ── Right panel (form) ──────────────────────────── */
        .right-panel {
            background: var(--card);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 40px;
            overflow-y: auto;
        }

        .form-header { margin-bottom: 36px; }
        .form-header h2 { font-size: 24px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .form-header p  { font-size: 14px; color: var(--muted); }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid;
        }
        .alert-error   { background: rgba(239,68,68,0.08);  border-color: rgba(239,68,68,0.2);  color: #fca5a5; }
        .alert-success { background: rgba(34,197,94,0.08);  border-color: rgba(34,197,94,0.2);  color: #86efac; }

        /* Form elements */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            transition: border-color .2s, box-shadow .2s;
        }

        .input-wrap:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .input-wrap.error { border-color: var(--error); }

        .input-wrap i {
            padding: 0 14px;
            color: var(--muted);
            font-size: 15px;
            flex-shrink: 0;
        }

        .input-wrap input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            padding: 12px 14px 12px 0;
        }

        .input-wrap input::placeholder { color: #2d3450; }

        .eye-btn {
            padding: 0 14px;
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 15px;
            transition: color .15s;
        }
        .eye-btn:hover { color: var(--text); }

        /* Remember + Forgot row */
        .form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember span {
            font-size: 13px;
            color: var(--muted);
        }

        .forgot {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
            transition: opacity .15s;
        }
        .forgot:hover { opacity: 0.75; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 20px var(--primary-glow);
        }

        .btn-login:hover    { background: #2563eb; transform: translateY(-1px); box-shadow: 0 8px 28px var(--primary-glow); }
        .btn-login:active   { transform: none; }
        .btn-login:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* Footer */
        .form-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        /* Spinner */
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .left-panel { display: none; }
            .right-panel { padding: 32px 24px; }
        }
    </style>
</head>
<body>

    {{-- ── Left Panel ── --}}
    <div class="left-panel">
        <div class="grid-lines"></div>
        <div class="left-content">
            <div class="left-logo">
                <div class="icon"><i class="bi bi-router"></i></div>
                <span class="wordmark">MikroTik Manager</span>
            </div>
            <h1>Manage your<br><span>network</span><br>with ease.</h1>
            <p>A professional ISP management platform for MikroTik routers. Generate vouchers, monitor traffic, and manage hotspot users — all from one place.</p>
            <div class="features">
                <div class="feature">
                    <div class="dot"><i class="bi bi-ticket-perforated"></i></div>
                    <span>Bulk voucher generation and management</span>
                </div>
                <div class="feature">
                    <div class="dot"><i class="bi bi-speedometer2"></i></div>
                    <span>Real-time router status and traffic monitoring</span>
                </div>
                <div class="feature">
                    <div class="dot"><i class="bi bi-shield-check"></i></div>
                    <span>Multi-router support with isolated data per router</span>
                </div>
                <div class="feature">
                    <div class="dot"><i class="bi bi-download"></i></div>
                    <span>Export to CSV, PDF and printable voucher cards</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right Panel (Form) ── --}}
    <div class="right-panel">

        <div class="form-header">
            <h2>Welcome back 👋</h2>
            <p>Sign in to your account to continue</p>
        </div>

        @if(session('error'))
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle-fill" style="margin-top:1px;flex-shrink:0"></i>
            <span>{!! session('error') !!}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill" style="margin-top:1px;flex-shrink:0"></i>
            <span>{!! session('success') !!}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="loginForm" novalidate>
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap {{ $errors->has('email') ? 'error' : '' }}">
                    <i class="bi bi-envelope"></i>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           autocomplete="email"
                           autofocus
                           required>
                </div>
                @error('email')
                    <div style="color:#fca5a5;font-size:12px;margin-top:5px">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap {{ $errors->has('password') ? 'error' : '' }}">
                    <i class="bi bi-lock"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="eye-btn" onclick="togglePass()" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div style="color:#fca5a5;font-size:12px;margin-top:5px">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember + Forgot --}}
            <div class="form-meta">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span class="spinner" id="spinner"></span>
                <i class="bi bi-box-arrow-in-right" id="loginIcon"></i>
                <span id="loginText">Sign In</span>
            </button>

        </form>

        <div class="form-footer">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:var(--primary);text-decoration:none">
                Start free 14-day trial
            </a>
            <br><br>
            MikroTik Manager &copy; {{ date('Y') }} &nbsp;·&nbsp; Secure Access
        </div>

    </div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Done', html: @json(session('success')), timer: 2500, timerProgressBar: true, showConfirmButton: false });
    });
</script>
@endif

<script>
function togglePass() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('loginForm').addEventListener('submit', function () {
    const btn  = document.getElementById('loginBtn');
    const icon = document.getElementById('loginIcon');
    const text = document.getElementById('loginText');
    const spin = document.getElementById('spinner');

    btn.disabled    = true;
    icon.style.display = 'none';
    spin.style.display = 'block';
    text.textContent   = 'Signing in…';
});
</script>

</body>
</html>
