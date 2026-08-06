@php
    $layoutStats = $nationalLayoutStats ?? [
        'total_constituency' => 0,
        'total_voters' => 0,
        'total_polling' => 0,
        'total_electoral_area' => 0,
    ];
    $currentUserType = collect($UTypes ?? [])->firstWhere('id', Auth::user()->user_type_id);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'National Dashboard') : {{ $config['name'] }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <!-- Daterangepicker -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- jQuery UI -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/dist/css/adminlte.min.css') }}">
    <style>
        :root {
            --national-bg: #eef3f8;
            --national-surface: rgba(255, 255, 255, 0.92);
            --national-surface-strong: #ffffff;
            --national-ink: #10233d;
            --national-muted: #61758d;
            --national-border: rgba(16, 35, 61, 0.08);
            --national-shadow: 0 22px 60px rgba(15, 23, 42, 0.09);
            --national-accent: #0f6d5f;
            --national-accent-2: #d98c2f;
            --national-danger: #c85a54;
        }

        body,
        .content-wrapper,
        .content-header,
        .content {
            font-family: "Manrope", sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(15, 109, 95, 0.14), transparent 30%),
                radial-gradient(circle at top right, rgba(217, 140, 47, 0.1), transparent 26%),
                linear-gradient(180deg, #f6f9fc 0%, var(--national-bg) 100%);
            color: var(--national-ink);
        }

        .content-wrapper {
            background: transparent;
        }

        .main-header.navbar {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(16, 35, 61, 0.06);
        }

        .brand-link {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-family: "Space Grotesk", sans-serif;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .main-sidebar {
            background:
                radial-gradient(circle at top, rgba(15, 109, 95, 0.18), transparent 28%),
                linear-gradient(180deg, #10233d 0%, #0d1728 100%);
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active,
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover,
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-treeview > .nav-item > .nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 14px;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link,
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-treeview > .nav-item > .nav-link {
            margin: 0 .75rem .35rem;
            border-radius: 14px;
        }

        .nav-sidebar .nav-treeview {
            padding-top: .35rem;
        }

        .content-header {
            padding-top: 1.5rem;
        }

        .national-hero {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(255, 255, 255, 0.82)),
                linear-gradient(135deg, rgba(15, 109, 95, 0.09), rgba(217, 140, 47, 0.06));
            border: 1px solid rgba(255, 255, 255, 0.68);
            border-radius: 28px;
            box-shadow: var(--national-shadow);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding: 1.5rem 1.6rem;
        }

        .national-hero-copy {
            flex: 1 1 420px;
            min-width: 280px;
        }

        .national-kicker {
            color: var(--national-accent);
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .16em;
            margin-bottom: .55rem;
            text-transform: uppercase;
        }

        .national-page-title {
            color: var(--national-ink);
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.8rem, 2vw, 2.4rem);
            font-weight: 700;
            letter-spacing: -.03em;
            margin: 0;
        }

        .national-page-description {
            color: var(--national-muted);
            font-size: .98rem;
            margin: .55rem 0 0;
            max-width: 760px;
        }

        .national-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: 1rem;
        }

        .national-badge {
            align-items: center;
            background: rgba(16, 35, 61, 0.05);
            border: 1px solid rgba(16, 35, 61, 0.08);
            border-radius: 999px;
            color: var(--national-ink);
            display: inline-flex;
            font-size: .78rem;
            font-weight: 700;
            gap: .4rem;
            padding: .55rem .9rem;
        }

        .national-badge.is-accent {
            background: rgba(15, 109, 95, 0.12);
            color: #0a5b50;
        }

        .national-hero-actions {
            align-items: flex-start;
            display: flex;
            flex: 0 1 auto;
            flex-wrap: wrap;
            gap: .75rem;
            justify-content: flex-end;
            min-width: 220px;
        }

        .national-hero-actions .btn {
            margin: 0;
        }

        .national-summary-card .info-box-number {
            font-size: 1.1rem;
        }

        .national-summary-card .info-box-number {
            font-size: 1.1rem;
        }

        .national-summary-card .info-box-text {
            white-space: normal;
        }

        .info-box,
        .small-box,
        .card,
        .panel,
        .x_panel {
            background: var(--national-surface);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 24px;
            box-shadow: var(--national-shadow);
            overflow: hidden;
        }

        .card-header,
        .panel-heading,
        .x_title {
            align-items: center;
            background: transparent;
            border-bottom: 1px solid var(--national-border);
            display: flex;
            flex-wrap: wrap;
            gap: .8rem;
            justify-content: space-between;
            margin: 0;
            padding: 1.2rem 1.35rem;
        }

        .panel-heading .btn,
        .x_title .btn,
        .card-header .btn,
        .panel-heading .form-control,
        .x_title .form-control,
        .card-header .form-control,
        .panel-heading select,
        .x_title select,
        .card-header select {
            float: none !important;
            margin: 0 !important;
        }

        .panel-body,
        .x_content,
        .card-body {
            padding: 1.35rem;
        }

        .panel-body > .col-md-12,
        .panel-body > .col-lg-12,
        .panel-body > .col-sm-12,
        .x_content > .col-md-12,
        .x_content > .col-lg-12,
        .x_content > .col-sm-12 {
            padding-left: 0;
            padding-right: 0;
        }

        .x_title h2,
        .card-title,
        .panel-title {
            color: var(--national-ink);
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.08rem;
            font-weight: 700;
            margin: 0;
        }

        .panel,
        .x_panel {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.15rem;
        }

        .control-label,
        .form-group label {
            color: var(--national-ink);
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .form-control,
        .custom-select,
        textarea.form-control {
            background: rgba(248, 250, 252, 0.95);
            border: 1px solid rgba(16, 35, 61, 0.1);
            border-radius: 16px;
            box-shadow: none;
            color: var(--national-ink);
            min-height: 3rem;
            padding: .8rem 1rem;
        }

        .form-control:focus,
        .custom-select:focus,
        textarea.form-control:focus {
            background: #fff;
            border-color: rgba(15, 109, 95, 0.42);
            box-shadow: 0 0 0 .18rem rgba(15, 109, 95, 0.12);
        }

        select.form-control.filter,
        .panel-heading > select.form-control,
        .card-header > select.form-control {
            flex: 1 0 180px;
            max-width: 240px;
            min-width: 180px;
        }

        .btn {
            border-radius: 999px;
            font-size: .9rem;
            font-weight: 700;
            letter-spacing: .01em;
            padding: .7rem 1.15rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #0f6d5f, #149f88);
            border-color: transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10233d, #20456f);
            border-color: transparent;
        }

        .btn-danger {
            background: linear-gradient(135deg, #b14945, #d86e68);
            border-color: transparent;
        }

        .btn-secondary,
        .btn-default,
        .btn-light {
            background: #edf2f7;
            border-color: transparent;
            color: var(--national-ink);
        }

        .table {
            color: var(--national-ink);
            margin-bottom: 0;
        }

        .table thead th {
            background: #f3f6fa;
            border-bottom: 0;
            border-top: 0;
            color: var(--national-muted);
            font-size: .73rem;
            font-weight: 800;
            letter-spacing: .08em;
            padding: .95rem 1rem;
            text-transform: uppercase;
        }

        .table td {
            border-top: 1px solid rgba(16, 35, 61, 0.06);
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(15, 109, 95, 0.04);
        }

        div.dataTables_wrapper div.dataTables_length select,
        div.dataTables_wrapper div.dataTables_filter input {
            background: rgba(248, 250, 252, 0.95);
            border: 1px solid rgba(16, 35, 61, 0.1);
            border-radius: 12px;
            min-height: 2.7rem;
            padding: .5rem .85rem;
        }

        div.dataTables_wrapper div.dataTables_filter label,
        div.dataTables_wrapper div.dataTables_length label {
            color: var(--national-muted);
            font-size: .84rem;
            font-weight: 700;
        }

        .pagination .page-link {
            border: 0;
            border-radius: 12px !important;
            color: var(--national-ink);
            margin: 0 .2rem;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #10233d, #20456f);
        }

        .alert {
            border: 0;
            border-radius: 18px;
            box-shadow: var(--national-shadow);
        }

        .national-section-label {
            color: rgba(255, 255, 255, .55);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            margin: 1rem 0 .4rem;
            padding: 0 1rem;
            text-transform: uppercase;
        }

        @media (max-width: 991.98px) {
            .national-hero {
                border-radius: 22px;
                padding: 1.25rem;
            }

            .national-hero-actions {
                justify-content: flex-start;
            }

            .panel-heading,
            .x_title,
            .card-header {
                padding: 1rem 1rem .9rem;
            }

            .panel-body,
            .x_content,
            .card-body {
                padding: 1rem;
            }
        }
    </style>
    @yield("css")
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('National.dashboard') }}" class="nav-link">Dashboard</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('logout') }}" class="dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('National.dashboard') }}" class="brand-link">
            <img src="{{ asset($config['logo']) }}" alt="{{ $config['name'] }}" class="brand-image img-circle elevation-3" style="opacity: .8; max-height:33px;">
            <span class="brand-text font-weight-light">{{ $config['name'] }}</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ Auth::user()->photo ? asset('user_logo/' . Auth::user()->photo) : asset($config['logo']) }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="{{ route('National.profile') }}" class="d-block">{{ Auth::user()->name }}</a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="national-section-label">Overview</li>
                    <li class="nav-item">
                        <a href="{{ route('National.dashboard') }}" class="nav-link {{ Request::routeIs('National.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('National.profile') }}" class="nav-link {{ Request::routeIs('National.profile') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-id-badge"></i>
                            <p>Profile</p>
                        </a>
                    </li>
                    <li class="national-section-label">Results</li>
                    <li class="nav-item">
                        <a href="{{ route('National.Presidential') }}" class="nav-link {{ Request::routeIs('National.Presidential', 'National.regionalResultView') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-flag"></i>
                            <p>Presidential Results</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('National.ConstituencyResult') }}" class="nav-link {{ Request::routeIs('National.ConstituencyResult', 'National.constituencyView') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-landmark"></i>
                            <p>Constituency Results</p>
                        </a>
                    </li>
                    <li class="national-section-label">Operations</li>
                    <li class="nav-item">
                        <a href="{{ route('National.pollingAgent') }}" class="nav-link {{ Request::is('national/polling-agent*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Polling Agent</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('National.candidate') }}" class="nav-link {{ Request::is('national/candidate*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Candidate</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('National.Users') }}" class="nav-link {{ Request::is('national/managers*') || Request::is('national/new-manager*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Managers</p>
                        </a>
                    </li>
                    <li class="national-section-label">Administration</li>
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link {{ Request::is('national/electoral-area*') || Request::is('national/polling-station*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Admin Section <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('National.ElectoralArea') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Electoral Area</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('National.PollingStation') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Polling Station</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div class="national-hero w-100">
                        <div class="national-hero-copy">
                            <div class="national-kicker">Enterprise Workspace</div>
                            <h1 class="national-page-title">@yield('page_title', 'National Dashboard')</h1>
                            <p class="national-page-description">@yield('page_description', 'National coordination, governance, and live election reporting from one secure workspace.')</p>
                            <div class="national-badges">
                                <span class="national-badge is-accent"><i class="fas fa-shield-alt"></i> Secure access</span>
                                <span class="national-badge"><i class="fas fa-satellite-dish"></i> Live reporting</span>
                                <span class="national-badge"><i class="fas fa-clock"></i> {{ \Illuminate\Support\Carbon::now('UTC')->format('M d, Y H:i') }} UTC</span>
                                @yield('page_badges')
                            </div>
                        </div>
                        <div class="national-hero-actions">
                            @yield('page_actions')
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box national-summary-card">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-shield"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Logged in as</span>
                                <span class="info-box-number">{{ $currentUserType['name'] ?? 'National User' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box national-summary-card">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-landmark"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Constituencies</span>
                                <span class="info-box-number">{{ number_format($layoutStats['total_constituency'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box national-summary-card">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-poll"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Polling Stations</span>
                                <span class="info-box-number">{{ number_format($layoutStats['total_polling'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box national-summary-card">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-map-marker-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Electoral Areas</span>
                                <span class="info-box-number">{{ number_format($layoutStats['total_electoral_area'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h4>{{ number_format($layoutStats['total_voters'] ?? 0) }}</h4>
                                <p>Total Registered Voters</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if($msg = session("message"))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="icon fas fa-check"></i> {{ $msg }}
                    </div>
                @endif
                @if($error = session("error"))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="icon fas fa-ban"></i> {{ $error }}
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; {{ date('Y') }} {{ $config['name'] }}.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- DataTables -->
<script src="{{ asset('AdminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('AdminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- jQuery UI -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!-- AdminLTE App -->
<script src="{{ asset('AdminLTE/dist/js/adminlte.min.js') }}"></script>
@yield("script")
<script>
    $.fn.dataTable.ext.errMode = 'throw';
    $(function () {
        if ($.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                responsive: true,
                autoWidth: false,
                language: {
                    search: "Quick search",
                    searchPlaceholder: "Search this table"
                }
            });
        }

        $('table.table').addClass('table-hover align-middle');
        $('.x_panel, .panel').addClass('enterprise-surface');
    });
</script>
</body>
</html>
