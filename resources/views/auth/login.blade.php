<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $config['name'] }} - Agent Login</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">

    <style>
        :root {
            --ndc-green: #006B3F;
            --ndc-green-dark: #004D2E;
            --ndc-green-light: #00894F;
            --ndc-red: #CE1126;
            --ndc-black: #1a1a1a;
            --ndc-white: #ffffff;
            --ndc-gold: #FCD116;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--ndc-black);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(ellipse at top left, rgba(0,107,63,0.4) 0%, transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(206,17,38,0.3) 0%, transparent 50%),
                var(--ndc-black);
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            padding: 16px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--ndc-gold);
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(252,209,22,0.3);
        }

        .login-logo .app-name {
            display: block;
            color: var(--ndc-white);
            font-weight: 700;
            font-size: 1.3rem;
            margin-top: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .login-logo .app-subtitle {
            display: block;
            font-size: 0.8rem;
            font-weight: 300;
            color: rgba(255,255,255,0.6);
            margin-top: 2px;
        }

        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            overflow: hidden;
            background: var(--ndc-white);
        }

        .ndc-stripe {
            height: 4px;
            background: linear-gradient(90deg,
                var(--ndc-red) 0%, var(--ndc-red) 25%,
                var(--ndc-black) 25%, var(--ndc-black) 50%,
                var(--ndc-green) 50%, var(--ndc-green) 75%,
                var(--ndc-gold) 75%, var(--ndc-gold) 100%
            );
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--ndc-green) 0%, var(--ndc-green-dark) 100%);
            padding: 18px 24px;
            text-align: center;
        }

        .card-header-custom h4 {
            margin: 0;
            color: var(--ndc-white);
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.3px;
        }

        .card-header-custom .header-accent {
            width: 40px;
            height: 3px;
            background: var(--ndc-gold);
            margin: 8px auto 0;
            border-radius: 2px;
        }

        .card-header-custom .header-note {
            color: rgba(255,255,255,0.7);
            font-size: 0.78rem;
            margin-top: 6px;
        }

        .card-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 0 10px 10px 0;
            border: 2px solid #e0e0e0;
            padding: 12px 14px;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: auto;
        }

        .form-control:focus {
            border-color: var(--ndc-green);
            box-shadow: 0 0 0 0.15rem rgba(0, 107, 63, 0.15);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 2px solid #e0e0e0;
            border-right: none;
            background: #f4f6f9;
            color: var(--ndc-green);
            padding: 0 14px;
            font-size: 1rem;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--ndc-green);
            color: var(--ndc-green-dark);
            background: #e8f5e9;
        }

        .btn-ndc {
            background: linear-gradient(135deg, var(--ndc-green) 0%, var(--ndc-green-dark) 100%);
            border: none;
            color: var(--ndc-white);
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-ndc:hover, .btn-ndc:focus {
            background: linear-gradient(135deg, var(--ndc-green-dark) 0%, var(--ndc-black) 100%);
            color: var(--ndc-white);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 107, 63, 0.4);
        }

        .btn-ndc:active {
            transform: translateY(0);
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--ndc-green);
            border-color: var(--ndc-green);
        }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            color: var(--ndc-green);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: var(--ndc-green-dark);
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            color: rgba(255,255,255,0.45);
            font-size: 0.75rem;
            margin-top: 18px;
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.85rem;
            padding: 10px 14px;
        }

        /* Mobile optimizations */
        @media (max-width: 480px) {
            .login-wrapper { padding: 12px; max-width: 100%; }
            .login-logo img { width: 70px; height: 70px; }
            .login-logo .app-name { font-size: 1.15rem; }
            .card-body { padding: 20px 18px; }
            .form-control { padding: 11px 12px; font-size: 16px; /* prevents iOS zoom */ }
            .btn-ndc { padding: 13px; font-size: 0.95rem; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Logo -->
    <div class="login-logo">
        <img src="{{ asset($config['logo']) }}" alt="{{ $config['name'] }}">
        <span class="app-name">{{ $config['name'] }}</span>
        <span class="app-subtitle">Election Collation &amp; Monitoring System</span>
    </div>

    <!-- Login Card -->
    <div class="login-card card">
        <div class="ndc-stripe"></div>
        <div class="card-header-custom">
            <h4><i class="fas fa-user-circle mr-2"></i>Agent &amp; Director Login</h4>
            <div class="header-accent"></div>
            <p class="header-note">Sign in with your Card ID</p>
        </div>

        <div class="card-body">
            @if ($errors->has('username'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first('username') }}
                </div>
            @endif
            @if ($errors->has('password'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="username"><i class="fas fa-id-card mr-1"></i> Card ID Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                        </div>
                        <input id="username" type="text" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                               name="username" value="{{ old('username') }}" required autofocus
                               placeholder="Enter your Card ID" inputmode="text" autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock mr-1"></i> Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               name="password" required placeholder="Enter your password" autocomplete="current-password">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="remember" style="font-weight:400; text-transform:none; font-size:0.9rem; color:#555;">
                            Remember Me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-ndc">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>

                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        <i class="fas fa-question-circle mr-1"></i> Forgot Your Password?
                    </a>
                @endif
            </form>
        </div>

        <div class="ndc-stripe"></div>
    </div>

    <p class="footer-text">&copy; {{ date('Y') }} {{ $config['name'] }}. All rights reserved.</p>
</div>

<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('AdminLTE/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
