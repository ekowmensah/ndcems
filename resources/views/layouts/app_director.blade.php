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
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.constituency_id = constituency.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.constituency_id = constituency.id) as total_polling"),
            DB::raw("(select count(id) from ElectoralArea where  ElectoralArea.constituency_id = users.constituency_id) as total_electoralArea"),
            DB::raw("(select count(id) from candidates where  candidates.constituency_id = users.constituency_id) as total_candidate")
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->first();
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
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <!-- Info Boxes -->
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-shield"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Logged in as</span>
                                        <span class="info-box-number">{{ $user->user_type_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-globe-africa"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Region</span>
                                        <span class="info-box-number">{{ $user->region_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-landmark"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Constituency</span>
                                        <span class="info-box-number">{{ $user->constituency_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-poll"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Polling Stations</span>
                                        <span class="info-box-number">{{ $user->total_polling ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4>{{ number_format($user->total_electoralArea ?? 0) }}</h4>
                                        <p>Electoral Areas</p>
                                    </div>
                                    <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4>{{ number_format($user->total_voters ?? 0) }}</h4>
                                        <p>Registered Voters</p>
                                    </div>
                                    <div class="icon"><i class="fas fa-users"></i></div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4>{{ $user->total_candidate ?? 0 }}</h4>
                                        <p>Candidates</p>
                                    </div>
                                    <div class="icon"><i class="fas fa-user-check"></i></div>
                                </div>
                            </div>
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
