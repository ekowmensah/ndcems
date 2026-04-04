<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $config['name'] }} - Login</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">

    <style>
        :root {
            --ndc-green: #006B3F;
            --ndc-green-dark: #004D2E;
            --ndc-red: #CE1126;
            --ndc-black: #1a1a1a;
            --ndc-white: #ffffff;
            --ndc-gold: #FCD116;
        }

        body {
            background: linear-gradient(135deg, var(--ndc-green-dark) 0%, var(--ndc-black) 50%, var(--ndc-red) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 420px;
        }

        .login-logo img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid var(--ndc-gold);
            object-fit: cover;
            margin-bottom: 12px;
        }

        .login-logo a {
            color: var(--ndc-white);
            font-weight: 700;
            font-size: 1.4rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .login-logo .subtitle {
            display: block;
            font-size: 0.85rem;
            font-weight: 300;
            color: rgba(255,255,255,0.8);
            margin-top: 4px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4), 0 5px 15px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--ndc-green) 0%, var(--ndc-green-dark) 100%);
            padding: 20px 30px;
            text-align: center;
        }

        .card-header-custom h4 {
            margin: 0;
            color: var(--ndc-white);
            font-weight: 600;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
        }

        .card-header-custom .header-accent {
            width: 50px;
            height: 3px;
            background: var(--ndc-gold);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--ndc-black);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--ndc-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 107, 63, 0.15);
        }

        .input-group-text {
            border-radius: 8px 0 0 8px;
            border: 2px solid #e0e0e0;
            border-right: none;
            background: #f8f9fa;
            color: var(--ndc-green);
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--ndc-green);
            color: var(--ndc-green-dark);
        }

        .btn-ndc {
            background: linear-gradient(135deg, var(--ndc-green) 0%, var(--ndc-green-dark) 100%);
            border: none;
            color: var(--ndc-white);
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-ndc:hover {
            background: linear-gradient(135deg, var(--ndc-green-dark) 0%, var(--ndc-black) 100%);
            color: var(--ndc-white);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 107, 63, 0.4);
        }

        .custom-checkbox .custom-control-label::before {
            border-radius: 4px;
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--ndc-green);
            border-color: var(--ndc-green);
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

        .footer-text {
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            margin-top: 20px;
        }

        .alert {
            border-radius: 8px;
            border: none;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-box">
    <!-- Logo -->
    <div class="login-logo">
        <div>
            <img src="{{ asset($config['logo']) }}" alt="{{ $config['name'] }}">
        </div>
        <a href="{{ url('/') }}">
            {{ $config['name'] }}
            <span class="subtitle">Election Collation &amp; Monitoring System</span>
        </a>
    </div>

    <!-- Login Card -->
    <div class="card">
        <div class="ndc-stripe"></div>
        <div class="card-header-custom">
            <h4><i class="fas fa-shield-alt mr-2"></i>Administrator Login</h4>
            <div class="header-accent"></div>
        </div>

        <div class="card-body">
            @if ($errors->has('email'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first('email') }}
                </div>
            @endif
            @if ($errors->has('password'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="{{ route('SuperAdmin.login') }}">
                @csrf

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope mr-1"></i> Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input id="email" type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock mr-1"></i> Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               name="password" required placeholder="Enter your password">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="remember" style="font-weight:400; text-transform:none; font-size:0.9rem;">
                            Remember Me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-ndc">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
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
