<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — MikroTik Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg:#080b14; --surface:#0f1320; --card:#141828; --border:#1e2438;
            --primary:#3b82f6; --primary-glow:rgba(59,130,246,0.25);
            --text:#f1f5f9; --muted:#64748b; --success:#22c55e; --error:#ef4444;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body { min-height:100vh; background:var(--bg); font-family:'Inter',sans-serif;
               display:grid; grid-template-columns:1fr 520px; }

        /* Left panel */
        .left-panel { position:relative; overflow:hidden; display:flex; flex-direction:column;
                      justify-content:center; padding:60px;
                      background:linear-gradient(135deg,#0f1724 0%,#0a0f1e 100%); }
        .left-panel::before { content:''; position:absolute; inset:0;
            background:radial-gradient(ellipse at 30% 40%,rgba(59,130,246,0.15) 0%,transparent 60%),
                        radial-gradient(ellipse at 70% 70%,rgba(99,102,241,0.08) 0%,transparent 50%); }
        .grid-lines { position:absolute; inset:0;
            background-image:linear-gradient(rgba(59,130,246,0.04) 1px,transparent 1px),
                              linear-gradient(90deg,rgba(59,130,246,0.04) 1px,transparent 1px);
            background-size:50px 50px; }
        .left-content { position:relative; z-index:1; }
        .left-logo { display:flex; align-items:center; gap:12px; margin-bottom:48px; }
        .left-logo .icon { width:44px; height:44px; background:linear-gradient(135deg,var(--primary),#6366f1);
            border-radius:12px; display:flex; align-items:center; justify-content:center;
            font-size:20px; color:#fff; box-shadow:0 4px 20px var(--primary-glow); }
        .left-logo .wordmark { font-size:18px; font-weight:700; color:var(--text); }
        .left-panel h1 { font-size:36px; font-weight:800; color:var(--text); line-height:1.2;
                         letter-spacing:-0.8px; margin-bottom:16px; }
        .left-panel h1 span { background:linear-gradient(135deg,var(--primary),#818cf8);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .left-panel p { font-size:15px; color:var(--muted); line-height:1.7; max-width:360px; margin-bottom:40px; }

        .plan-card { background:rgba(59,130,246,0.06); border:1px solid rgba(59,130,246,0.15);
                     border-radius:12px; padding:20px 24px; margin-bottom:16px; }
        .plan-card .plan-name { font-size:13px; font-weight:700; color:var(--primary);
                                text-transform:uppercase; letter-spacing:0.8px; margin-bottom:12px; }
        .plan-features { display:flex; flex-direction:column; gap:8px; }
        .plan-feature { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--muted); }
        .plan-feature i { color:var(--success); font-size:12px; }
        .trial-badge { display:inline-flex; align-items:center; gap:6px; margin-top:16px;
            background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.2);
            border-radius:20px; padding:6px 14px; font-size:12px; color:var(--success); font-weight:600; }

        /* Right panel */
        .right-panel { background:var(--card); border-left:1px solid var(--border);
                       display:flex; flex-direction:column; justify-content:center;
                       padding:40px 36px; overflow-y:auto; }
        .form-header { margin-bottom:28px; }
        .form-header h2 { font-size:22px; font-weight:700; color:var(--text); margin-bottom:6px; }
        .form-header p  { font-size:14px; color:var(--muted); }

        .section-label { font-size:11px; font-weight:700; text-transform:uppercase;
                         letter-spacing:1px; color:var(--muted); margin-bottom:14px;
                         padding-bottom:8px; border-bottom:1px solid var(--border); }

        .form-group { margin-bottom:14px; }
        label { display:block; font-size:11px; font-weight:600; text-transform:uppercase;
                letter-spacing:0.8px; color:var(--muted); margin-bottom:6px; }
        .input-wrap { display:flex; align-items:center; background:var(--surface);
                      border:1px solid var(--border); border-radius:9px;
                      transition:border-color .2s, box-shadow .2s; }
        .input-wrap:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-glow); }
        .input-wrap.error { border-color:var(--error); }
        .input-wrap i { padding:0 12px; color:var(--muted); font-size:14px; flex-shrink:0; }
        .input-wrap input { flex:1; background:transparent; border:none; outline:none;
                            color:var(--text); font-size:13px; font-family:'Inter',sans-serif;
                            padding:11px 12px 11px 0; }
        .input-wrap input::placeholder { color:#2d3450; }
        .field-error { color:#fca5a5; font-size:11px; margin-top:4px; }

        .alert { padding:11px 14px; border-radius:9px; font-size:13px; margin-bottom:16px;
                 display:flex; align-items:flex-start; gap:9px; border:1px solid; }
        .alert-error   { background:rgba(239,68,68,0.08);  border-color:rgba(239,68,68,0.2);  color:#fca5a5; }

        .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

        .btn-register { width:100%; padding:12px; background:var(--primary); color:#fff;
            border:none; border-radius:9px; font-size:14px; font-weight:600;
            font-family:'Inter',sans-serif; cursor:pointer; transition:all .2s;
            display:flex; align-items:center; justify-content:center; gap:8px;
            box-shadow:0 4px 20px var(--primary-glow); margin-top:6px; }
        .btn-register:hover { background:#2563eb; transform:translateY(-1px); }
        .btn-register:disabled { opacity:.6; cursor:not-allowed; transform:none; }

        .form-footer { margin-top:20px; text-align:center; font-size:12px; color:var(--muted); }
        .form-footer a { color:var(--primary); text-decoration:none; }
        .form-footer a:hover { text-decoration:underline; }

        .spinner { width:15px; height:15px; border:2px solid rgba(255,255,255,0.3);
                   border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; display:none; }
        @keyframes spin { to { transform:rotate(360deg); } }

        @media(max-width:768px) {
            body { grid-template-columns:1fr; }
            .left-panel { display:none; }
            .right-panel { padding:28px 20px; }
        }
    </style>
</head>
<body>

    {{-- Left Panel --}}
    <div class="left-panel">
        <div class="grid-lines"></div>
        <div class="left-content">
            <div class="left-logo">
                <div class="icon"><i class="bi bi-router"></i></div>
                <span class="wordmark">MikroTik Manager</span>
            </div>
            <h1>Start managing<br>your <span>network</span><br>today.</h1>
            <p>Join ISPs across the region using MikroTik Manager to run their hotspot business professionally.</p>

            <div class="plan-card">
                <div class="plan-name">Free Trial — 14 Days</div>
                <div class="plan-features">
                    <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> 1 MikroTik router</div>
                    <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Up to 100 vouchers</div>
                    <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Full voucher management</div>
                    <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> PDF & CSV export</div>
                    <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Hotspot profiles</div>
                    <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> No credit card required</div>
                </div>
                <div class="trial-badge">
                    <i class="bi bi-shield-check"></i> No credit card required
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="right-panel">
        <div class="form-header">
            <h2>Create your account</h2>
            <p>Set up your ISP workspace in under a minute.</p>
        </div>

        @if(session('error'))
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle-fill" style="margin-top:1px;flex-shrink:0"></i>
            <span>{!! session('error') !!}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" id="regForm" novalidate>
            @csrf

            <div class="section-label">Your Business</div>

            <div class="form-group">
                <label>Business / ISP Name</label>
                <div class="input-wrap {{ $errors->has('business_name') ? 'error' : '' }}">
                    <i class="bi bi-building"></i>
                    <input type="text" name="business_name" value="{{ old('business_name') }}"
                           placeholder="e.g. JumuiyaConnect" required>
                </div>
                @error('business_name')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="section-label" style="margin-top:20px">Your Account</div>

            <div class="row-2">
                <div class="form-group">
                    <label>Your Name</label>
                    <div class="input-wrap {{ $errors->has('name') ? 'error' : '' }}">
                        <i class="bi bi-person"></i>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Full name" required>
                    </div>
                    @error('name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Phone (optional)</label>
                    <div class="input-wrap">
                        <i class="bi bi-phone"></i>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="+255 7xx xxx xxx">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap {{ $errors->has('email') ? 'error' : '' }}">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="you@yourisp.com" required>
                </div>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="row-2">
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap {{ $errors->has('password') ? 'error' : '' }}">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" placeholder="Min 8 chars" required>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" name="password_confirmation" placeholder="Repeat password" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-register" id="regBtn">
                <span class="spinner" id="spin"></span>
                <i class="bi bi-rocket-takeoff" id="regIcon"></i>
                <span id="regText">Start Free Trial</span>
            </button>

        </form>

        <div class="form-footer">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
            &nbsp;·&nbsp; MikroTik Manager &copy; {{ date('Y') }}
        </div>
    </div>

<script>
document.getElementById('regForm').addEventListener('submit', function() {
    const btn = document.getElementById('regBtn');
    btn.disabled = true;
    document.getElementById('regIcon').style.display = 'none';
    document.getElementById('spin').style.display  = 'block';
    document.getElementById('regText').textContent = 'Creating account…';
});
</script>

</body>
</html>
