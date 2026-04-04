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
<body style="background-image: linear-gradient(black, red, white, green)">
<div id="custom-bootstrap-menu" class="navbar navbar-default navbar-fixed-top" role="navigation" style="background-image: linear-gradient(black, red, white, green)">
    <div class="container-fluid">

        <div class="navbar-header"><a class="navbar-brand" href="{{url('/')}}" style="margin:5px;padding:5px;"><img style="width:70px;height:70px;border:15px;border-radius:50%;" border="5" src="{{ asset('user_logo/'.Auth::user()->photo) }}" class="img-responsive" /></a>

            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-menubuilder"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse navbar-menubuilder">
            <ul class="nav navbar-nav navbar-right">

                <li><a href="{{route('Agent.election')}}" class="{{ (Request::is('Agent.election') ? 'active' : '') }}"><button btn-warning><i class="lnr lnr-home"></i> Capture Result</button></a></li>
      <li><a href="{{route('Agent.results')}}" ><i class="fa fa-database"></i> Results</a></li>
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
            </ul>
        </div>
    </div>
</div>
<!--<div id="wrapper">
		<!-- NAVBAR -->
	<!--	<nav class="navbar navbar-default navbar-fixed-top">

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
    <!--    <div id="sidebar-nav" class="sidebar">
			<div class="sidebar-scroll">
				<nav>
					<ul class="nav">
						<li><a href="{{route('Agent.election')}}" class="{{ (Request::is('Agent.election') ? 'active' : '') }}"><i class="lnr lnr-home"></i> <span>Capture Result</span></a></li>
						<li><a href="{{route('Agent.results')}}" ><i class="fa fa-database"></i> <span>Results</span></a></li>

					</ul>
				</nav>
			</div>
		</div> -->






            <div class="main">
                    @if(!Request::is('payment-2'))
                        @if($msg = session("message"))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">x</span></button>
                                <i class="fa fa-check-circle"></i> {{$msg}}
                            </div>

                        @endif
                        @if($error = session("error"))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">x/span></button>
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
				<p class="copyright">� 2017  All Rights Reserved.</p>
			</div>
        </footer>
    </div>
    </div>

</body>
</html>
