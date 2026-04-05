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
            //"constituency.name as constituency_name"
            DB::raw("(select count(id) from constituency where  region.id = constituency.region_id) as total_constituency"),

            DB::raw("(select sum(total_voters) from pollingstation where  pollingstation.region_id = region.id) as total_voters"),
            DB::raw("(select count(id) from pollingstation where  pollingstation.region_id = region.id) as total_polling"),
            DB::raw("(select count(id) from electoralarea where  electoralarea.region_id = region.id) as total_electoralArea")
            //DB::raw("(select count(id) from candidates where  candidates.constituency_id = users.constituency_id) as total_candidate")

            //DB::raw("(select count(id) from electoralarea where  electoralarea.constituency_id = constituency.id) as total_electral")
            //"pollingstation.name as PollingStation_name",
            //"electoralarea.name as ElectoralArea_name"
            //"pollingstation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        //->join('constituency','constituency.id','=','users.constituency_id')
        //->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
        //->join('pollingstation','pollingstation.id','=','users.polling_station_id')
        ->first();

?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{Auth::user()->name}} : {{$config['name']}} </title>



    <!-- Bootstrap -->
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="{{ asset('klorofil/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('klorofil/assets/vendor/font-awesome/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('klorofil/assets/vendor/linearicons/style.css') }}">
	<link rel="stylesheet" href="{{ asset('klorofil/assets/vendor/chartist/css/chartist-custom.css') }}">
	<!-- MAIN CSS -->
	<link rel="stylesheet" href="{{ asset('klorofil/assets/css/main.css') }}">
	<!-- FOR DEMO PURPOSES ONLY. You should remove this in your project -->
	<link rel="stylesheet" href="{{ asset('klorofil/assets/css/demo.css') }}">
	<!-- GOOGLE FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="{{ asset('gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    @yield("css")
</head>
<body>

<div id="wrapper">
		<!-- NAVBAR -->
		<nav class="navbar navbar-default navbar-fixed-top">

            <nav class="navbar navbar-default navbar-fixed-top">
                    
			<div class="brand" style="padding:0">
                <a  href="">
                    <img src="{{ asset($config['logo']) }}" alt="{{$config['name']}} " style="height:80px;width:190px" class="img-responsive logo">
                   </a>
			</div>
			<div class="container-fluid">
				<div class="navbar-btn">
					<button type="button" class="btn-toggle-fullwidth"><i class="lnr lnr-arrow-left-circle"></i></button>
				</div>


				<div id="navbar-menu">
					<ul class="nav navbar-nav navbar-right">


						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                 {{-- <span class="img-circle">{{ Auth::user()->name }}</span> --}}
                                 <p style="display: inline-block !important;margin: 0 0 0px !important; " class="img-circle">{{ Auth::user()->name }}</p>
                                  <i class="icon-submenu lnr lnr-chevron-down">
                                    </i>
                        </a>

                            <ul class="dropdown-menu">
                                <li>
                                <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                                     <i class="lnr lnr-exit"></i>  Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                    <li>
						<li>
                            <a href="{{route('Region.profile')}}" class="{{ (Request::is('Region.profile') ? 'active' : '') }}"><i class="lnr lnr-home"></i> <span>Profile</span></a></li>
                        </li>
							</ul>
						</li>
						<!-- <li>
							<a class="update-pro" href="https://www.themeineed.com/downloads/klorofil-pro-bootstrap-admin-dashboard-template/?utm_source=klorofil&utm_medium=template&utm_campaign=KlorofilPro" title="Upgrade to Pro" target="_blank"><i class="fa fa-rocket"></i> <span>UPGRADE TO PRO</span></a>
						</li> -->
					</ul>
				</div>
			</div>
        </nav>
        </nav>
<br><br><br>
        <div id="sidebar-nav" class="sidebar">
			<div class="sidebar-scroll">
				<nav>
					<ul class="nav">

						<li><a href="{{route('Region.dashboard')}}" class="{{ (Request::is('Region.dashboard') ? 'active' : '') }}"><i class="lnr lnr-home"></i> <span>Dashboard</span></a></li>
                        <li>
							<a href="#apiPages" data-toggle="collapse" class="collapsed"> <span>Results</span> <i class="icon-submenu lnr lnr-chevron-left"></i></a>
							<div id="apiPages" class="collapse ">
								<ul class="nav">

                                    <li><a href="{{route('Region.Presidential')}}">Presidential Results</a></li>
                                    <li><a href="{{route('Region.Regional')}}">Regional Results</a></li>
                                </ul>
							</div>
                        </li>
					</ul>
				</nav>
			</div>
		</div>






            <div class="main">
                
                <div class="panel-body">
                                        <div class="col-md-4" style="background-color:#3cfb01;">
                                                <strong> Logged In as : </strong>{{$user->user_type_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong> Region : </strong>{{$user->region_name}}
                                        </div>
                                        <div class="col-md-4">
                                            <strong> Registered Voters : </strong>{{number_format($user->total_voters)}}
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>

                                        <div class="col-md-4">
                                                <strong>Total Constituencies : </strong>{{$user->total_constituency}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong> Total Polling Station : </strong>{{$user->total_polling}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong> Total Electoral Area : </strong>{{$user->total_electoralArea}}
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>
                                      {{--  <div class="col-md-4">
                                                <strong> Registered Voters : </strong>{{number_format($user->total_voters)}}
                                        </div>--}}
                                       {{-- <div class="col-md-4">
                                                <strong>Constituency : </strong>{{$user->constituency_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Electoral Area  : </strong> {{$user->ElectoralArea_name}}
                                        </div> --}}
                                </div>
                
                
                    @if(!Request::is('payment-2'))
                        @if($msg = session("message"))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <i class="fa fa-check-circle"></i> {{$msg}}
                            </div>

                        @endif
                        @if($error = session("error"))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <i class="fa fa-check-circle"></i> {{$error}}
                            </div>
                        @endif

                    @endif
                @yield('content')
            </div>



	<script src="{{ asset('klorofil/assets/vendor/jquery/jquery.min.js') }}"></script>
	<script src="{{ asset('klorofil/assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('klorofil/assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
	<script src="{{ asset('klorofil/assets/vendor/jquery.easy-pie-chart/jquery.easypiechart.min.js') }}"></script>
	<script src="{{ asset('klorofil/assets/vendor/chartist/js/chartist.min.js') }}"></script>
	<script src="{{ asset('klorofil/assets/scripts/klorofil-common.js') }}"></script>

    <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        <!-- Bootstrap JavaScript -->
        <!-- <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script> -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  @yield("script");
  <script>
		$.fn.dataTable.ext.errMode = 'throw';
	</script>
    <div class="clearfix"></div>
        <footer>
			<div class="container-fluid">
				<p class="copyright">© 2017  All Rights Reserved.</p>
			</div>
        </footer>
    </div>
    </div>

</body>
</html>

