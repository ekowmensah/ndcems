<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Administrator Sign In | {{ $config['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        :root { --ink:#10243f; --navy:#0b1f36; --green:#087f5b; --green-dark:#056246; --muted:#66768a; --line:#dce5ed; --gold:#d99a19; }
        * { box-sizing:border-box; }
        body { min-height:100vh; margin:0; background:#edf3f4; color:var(--ink); font-family:'Manrope',sans-serif; }
        .login-shell { display:grid; min-height:100vh; grid-template-columns:minmax(420px,1.12fr) minmax(420px,.88fr); }
        .login-brand { position:relative; display:flex; min-height:100vh; flex-direction:column; justify-content:space-between; padding:52px clamp(38px,7vw,104px); overflow:hidden; background:radial-gradient(circle at 78% 18%,rgba(30,181,134,.28),transparent 25rem),linear-gradient(145deg,#112f4b 0%,var(--navy) 63%,#071725 100%); color:#fff; }
        .login-brand:before,.login-brand:after { position:absolute; border:1px solid rgba(255,255,255,.1); border-radius:50%; content:''; }
        .login-brand:before { right:-140px; bottom:-180px; width:480px; height:480px; }
        .login-brand:after { right:-50px; bottom:-90px; width:300px; height:300px; }
        .brand-lockup { position:relative; z-index:1; display:flex; align-items:center; gap:14px; }
        .brand-lockup img { width:58px; height:58px; padding:4px; border-radius:17px; background:#fff; object-fit:contain; }
        .brand-lockup strong { display:block; font-family:'Space Grotesk',sans-serif; font-size:19px; }
        .brand-lockup span { display:block; margin-top:5px; color:#8fa8bf; font-size:10px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        .brand-message { position:relative; z-index:1; max-width:660px; margin:80px 0; }
        .brand-message__eyebrow { display:inline-flex; align-items:center; gap:8px; margin-bottom:20px; color:#76e1bc; font-size:10px; font-weight:800; letter-spacing:.18em; text-transform:uppercase; }
        .brand-message__eyebrow:before { width:28px; height:2px; background:#76e1bc; content:''; }
        .brand-message h1 { margin:0; color:#fff; font-family:'Space Grotesk',sans-serif; font-size:clamp(38px,5vw,68px); line-height:1.02; letter-spacing:-.045em; }
        .brand-message p { max-width:570px; margin:24px 0 0; color:#b9ccda; font-size:15px; line-height:1.8; }
        .trust-row { position:relative; z-index:1; display:flex; flex-wrap:wrap; gap:22px; color:#8fa8bf; font-size:11px; font-weight:700; }
        .trust-row span { display:flex; align-items:center; gap:8px; }.trust-row i{color:#38c894}
        .login-panel { display:flex; min-height:100vh; align-items:center; justify-content:center; padding:44px; background:radial-gradient(circle at 90% 10%,rgba(8,127,91,.1),transparent 22rem),#f7f9fa; }
        .login-card { width:100%; max-width:450px; }
        .mobile-brand { display:none; align-items:center; gap:11px; margin-bottom:30px; }.mobile-brand img{width:46px;height:46px;padding:3px;border-radius:13px;background:#fff;object-fit:contain}.mobile-brand strong{font-family:'Space Grotesk',sans-serif}
        .login-card__eyebrow { color:var(--green); font-size:10px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        .login-card h2 { margin:10px 0 8px; font-family:'Space Grotesk',sans-serif; font-size:34px; letter-spacing:-.025em; }
        .login-card__intro { margin:0 0 32px; color:var(--muted); font-size:13px; line-height:1.7; }
        .alert { position:relative; margin-bottom:16px; padding:13px 40px 13px 14px; border-radius:12px; background:#fff0f0; color:#9d2929; font-size:12px; }.alert button{position:absolute;right:12px;top:9px;border:0;background:none;font-size:18px;color:inherit}
        .field { margin-bottom:19px; }.field label{display:flex;justify-content:space-between;margin-bottom:8px;color:#405168;font-size:11px;font-weight:800}.field label span{color:var(--muted);font-weight:600}
        .input-wrap { position:relative; }.input-wrap i{position:absolute;left:15px;top:50%;color:#8795a6;transform:translateY(-50%)}
        .input-wrap input { width:100%; height:50px; padding:11px 14px 11px 43px; border:1px solid var(--line); border-radius:13px; outline:0; background:#fff; color:var(--ink); font:500 13px 'Manrope',sans-serif; transition:border-color .18s,box-shadow .18s; }.input-wrap input:focus{border-color:var(--green);box-shadow:0 0 0 4px rgba(8,127,91,.1)}
        .login-options { display:flex; align-items:center; justify-content:space-between; margin:3px 0 24px; color:var(--muted); font-size:11px; }.login-options label{display:flex;align-items:center;gap:8px;cursor:pointer}.login-options input{accent-color:var(--green)}
        .submit-button { display:flex; width:100%; height:50px; align-items:center; justify-content:center; gap:9px; border:0; border-radius:13px; background:linear-gradient(135deg,var(--green),var(--green-dark)); color:#fff; cursor:pointer; font:800 12px 'Manrope',sans-serif; box-shadow:0 14px 28px rgba(8,127,91,.22); transition:transform .18s,box-shadow .18s; }.submit-button:hover{transform:translateY(-1px);box-shadow:0 18px 34px rgba(8,127,91,.27)}
        .security-note { display:flex; gap:10px; margin-top:24px; padding:15px; border:1px solid var(--line); border-radius:13px; background:#fff; color:var(--muted); font-size:10px; line-height:1.6; }.security-note i{margin-top:2px;color:var(--green)}
        .login-footer { margin-top:28px; color:#8b98a7; font-size:10px; text-align:center; }
        @media(max-width:900px){.login-shell{display:block}.login-brand{display:none}.login-panel{min-height:100vh;padding:30px 22px}.mobile-brand{display:flex}.login-card h2{font-size:29px}}
    </style>
</head>
<body>
<main class="login-shell">
    <section class="login-brand">
        <a class="brand-lockup" href="{{ url('/') }}" style="color:#fff;text-decoration:none;"><img src="{{ asset($config['logo']) }}" alt="{{ $config['name'] }}"><span><strong>{{ $config['name'] }}</strong><span>Election command platform</span></span></a>
        <div class="brand-message"><span class="brand-message__eyebrow">Secure operations workspace</span><h1>One source of truth for election readiness.</h1><p>Coordinate structures, field teams, election startups, and verified national reporting through a single operational command center.</p></div>
        <div class="trust-row"><span><i class="fas fa-check-circle"></i> Verified reporting</span><span><i class="fas fa-shield-alt"></i> Restricted access</span><span><i class="fas fa-chart-line"></i> Operational intelligence</span></div>
    </section>
    <section class="login-panel">
        <div class="login-card">
            <div class="mobile-brand"><img src="{{ asset($config['logo']) }}" alt=""><strong>{{ $config['name'] }}</strong></div>
            <span class="login-card__eyebrow">Administration</span><h2>Welcome back</h2><p class="login-card__intro">Sign in with your administrator account to continue to the election operations workspace.</p>
            @foreach(['email','password'] as $field)@if($errors->has($field))<div class="alert"><button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button><i class="fas fa-exclamation-circle"></i> {{ $errors->first($field) }}</div>@endif @endforeach
            <form method="POST" action="{{ route('SuperAdmin.login') }}">@csrf
                <div class="field"><label for="email">Email address <span>Administrator account</span></label><div class="input-wrap"><i class="fas fa-envelope"></i><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com"></div></div>
                <div class="field"><label for="password">Password <span>Case sensitive</span></label><div class="input-wrap"><i class="fas fa-lock"></i><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"></div></div>
                <div class="login-options"><label><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Keep me signed in</label></div>
                <button class="submit-button" type="submit">Enter command center <i class="fas fa-arrow-right"></i></button>
            </form>
            <div class="security-note"><i class="fas fa-shield-alt"></i><span>This workspace contains sensitive election operations data. Access is logged and limited to authorized administrators.</span></div>
            <div class="login-footer">&copy; {{ date('Y') }} {{ $config['name'] }}. Authorized use only.</div>
        </div>
    </section>
</main>
</body>
</html>
