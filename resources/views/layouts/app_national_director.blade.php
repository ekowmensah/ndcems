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

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
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
        .national-summary-card .info-box-number {
            font-size: 1.1rem;
        }

        .national-summary-card .info-box-text {
            white-space: normal;
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
                    <div>
                        <h1 class="m-0 text-dark">@yield('page_title', 'National Dashboard')</h1>
                        <small class="text-muted">National coordination and results reporting overview</small>
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
</script>
</body>
</html>
