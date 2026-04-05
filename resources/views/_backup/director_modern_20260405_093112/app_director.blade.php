<?php
$user = App\User::select(
            'users.username',
            'users.secret',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            DB::raw("(select sum(total_voters) from pollingstation where pollingstation.constituency_id = constituency.id) as total_voters"),
            DB::raw("(select count(id) from pollingstation where pollingstation.constituency_id = constituency.id) as total_polling"),
            DB::raw("(select count(id) from electoralarea where electoralarea.constituency_id = users.constituency_id) as total_electoralArea"),
            DB::raw("(select count(id) from candidates where  candidates.constituency_id = users.constituency_id) as total_candidate")
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->first();

$resultSummary = \App\Model\ElectionResult::selectRaw('
        COUNT(*) as submitted,
        SUM(CASE WHEN verify_by_constituency = 1 THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN verify_by_constituency = 0 THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN pink_sheet_path IS NOT NULL AND pink_sheet_path != "" THEN 1 ELSE 0 END) as pink_sheets,
        SUM(obtained_votes) as total_valid_votes,
        SUM(total_rejected_ballot) as total_rejected_votes
    ')
    ->where('constituency_id', Auth::user()->constituency_id)
    ->first();

$submittedCount = (int) ($resultSummary->submitted ?? 0);
$confirmedCount = (int) ($resultSummary->confirmed ?? 0);
$pendingCount = (int) ($resultSummary->pending ?? 0);
$pinkSheetCount = (int) ($resultSummary->pink_sheets ?? 0);

$captureCoverage = ((int) ($user->total_polling ?? 0)) > 0
    ? round(($submittedCount / (int) $user->total_polling) * 100, 2)
    : 0;

$confirmationCoverage = $submittedCount > 0
    ? round(($confirmedCount / $submittedCount) * 100, 2)
    : 0;

$pinkSheetCoverage = $submittedCount > 0
    ? round(($pinkSheetCount / $submittedCount) * 100, 2)
    : 0;
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Auth::user()->name }} : {{ $config['name'] }}</title>

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
    <link rel="stylesheet" href="{{ asset('css/director-dashboard.css?v=20260405') }}">
    @yield("css")
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('Director.home') }}" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('Director.profile') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
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
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('Director.home') }}" class="brand-link">
            <img src="{{ asset($config['logo']) }}" alt="{{ $config['name'] }}" class="brand-image img-circle elevation-3" style="opacity: .8; max-height:33px;">
            <span class="brand-text font-weight-light">{{ $config['name'] }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('user_logo/' . Auth::user()->photo) }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="{{ route('Director.profile') }}" class="d-block">{{ Auth::user()->name }}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('Director.Result') }}" class="nav-link {{ Request::is('director/result*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Results</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Director.election') }}" class="nav-link {{ Request::is('director/election*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-vote-yea"></i>
                            <p>Capture Result</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Director.pollingAgent') }}" class="nav-link {{ Request::is('director/polling-agent*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Polling Agents</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Director.candidate') }}" class="nav-link {{ Request::is('director/candidate*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Parliamentary Candidates</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Director.ElectoralArea') }}" class="nav-link {{ Request::is('director/electoral-area*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marker-alt"></i>
                            <p>Electoral Areas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Director.PollingStation') }}" class="nav-link {{ Request::is('director/polling-station*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-signs"></i>
                            <p>Polling Stations</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="director-hero">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h1>Constituency Results Command Center</h1>
                            <p>
                                {{ $user->region_name ?? 'N/A' }} Region
                                <span class="mx-1">|</span>
                                {{ $user->constituency_name ?? 'N/A' }} Constituency
                            </p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <span class="badge badge-light">{{ $user->user_type_name ?? 'Director' }}</span>
                            <a href="{{ route('Director.election') }}" class="btn btn-sm btn-light ml-2">
                                <i class="fas fa-plus-circle mr-1"></i> Capture/Update Result
                            </a>
                        </div>
                    </div>
                </div>

                <div class="director-kpi-grid">
                    <div class="director-kpi-card">
                        <span class="director-kpi-title">Results Submitted</span>
                        <div class="director-kpi-value">{{ number_format($submittedCount) }}</div>
                        <span class="director-kpi-sub">{{ number_format($user->total_polling ?? 0) }} polling stations total</span>
                    </div>
                    <div class="director-kpi-card">
                        <span class="director-kpi-title">Confirmed Results</span>
                        <div class="director-kpi-value text-success">{{ number_format($confirmedCount) }}</div>
                        <span class="director-kpi-sub">{{ $confirmationCoverage }}% of submitted results</span>
                    </div>
                    <div class="director-kpi-card">
                        <span class="director-kpi-title">Pending Confirmation</span>
                        <div class="director-kpi-value text-warning">{{ number_format($pendingCount) }}</div>
                        <span class="director-kpi-sub">Needs final director action</span>
                    </div>
                    <div class="director-kpi-card">
                        <span class="director-kpi-title">Pink Sheet Coverage</span>
                        <div class="director-kpi-value">{{ number_format($pinkSheetCount) }}</div>
                        <span class="director-kpi-sub">{{ $pinkSheetCoverage }}% of submitted results</span>
                    </div>
                    <div class="director-kpi-card">
                        <span class="director-kpi-title">Capture Coverage</span>
                        <div class="director-kpi-value">{{ $captureCoverage }}%</div>
                        <div class="director-progress mt-2">
                            <span style="width: {{ min(100, max(0, $captureCoverage)) }}%;"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
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

    <!-- Footer -->
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
