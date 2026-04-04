<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{$config['name']}}</title>

    <!-- Bootstrap -->
    <link href="{{ asset('gentelella/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset('gentelella/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{ asset('gentelella/vendors/nprogress/nprogress.css') }}" rel="stylesheet">
    <!-- iCheck -->
    <link href="{{ asset('gentelella/vendors/iCheck/skins/flat/green.css') }}" rel="stylesheet">
    <!-- Datatables -->
    <link href="{{ asset('gentelella/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/dropzone/dist/min/dropzone.min.css') }}"  rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="{{ asset('gentelella/build/css/custom.min.css') }}" rel="stylesheet">

    <!-- bootstrap-progressbar -->
    <link href="{{ asset('gentelella/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css') }}" rel="stylesheet">
    <!-- JQVMap -->
    <link href="{{ asset('gentelella/vendors/jqvmap/dist/jqvmap.min.css') }}" rel="stylesheet">
    <!-- bootstrap-daterangepicker -->
    <link href="{{ asset('gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <style>
      .right_col{
        min-height: 100vh !important;
      }
    </style>
    @yield("css")
</head>
<body class="nav-md">

<div class="container body">
        <div class="main_container">
            <div class="col-md-3 left_col">
                <div class="left_col scroll-view">
                    <div class="navbar nav_title" style="border: 0;">
                    <a class="navbar-brand" href="{{ url('/admin') }}">
                    <span></span>
                    <span><img style="widht:190px;height:70px" src='{{ asset($config['logo']) }}' class="img-responsive" /></span>
                    </a>
                    </div>

                    <div class="clearfix"></div>

                    <!-- menu profile quick info -->
                    <div class="profile clearfix">
                        <div class="profile_pic">
                            <!-- <img src="images/img.jpg" alt="..." class="img-circle profile_img"> -->
                        </div>
                        <div class="profile_info row" style="width:100%">
                            <span>Welcome, <strong style="color:white;text-align:center;">{{ Auth::guard("superAdmin")->user()->name }}</strong></span>
                        </div>
                    </div>
                    <!-- /menu profile quick info -->
                    <br />

                    <!-- sidebar menu -->
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                            <h3>General</h3>
                            <ul class="nav side-menu">
                            @if (Auth::guard("superAdmin")->check())
                                <li class="{{ (Request::is('super-admin') ? 'active' : '') }}"><a href="{{route('SuperAdmin.home')}}"><i class="fa fa-home"></i> Dashboard </a></li>
                                <li><a><i class="fa fa-user"></i>Results <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu" >
                                        {{-- <li><a href="{{route('SuperAdmin.home')}}">Presidential Results</a></li>
                                        <li><a href="{{route('SuperAdmin.parliamentaryResult')}}">Parliamentary Results</a></li>
                                        <li>
                                            ______
                                        </li> --}}
                                        @foreach ($__electionTypes as $electionType)
                                        <li><a href="{{route('SuperAdmin.result',$electionType['id'])}}">{{$electionType['name']}} </a></li>
                                        @endforeach
                                    </ul>
                                </li>
                            <li class=""><a href="{{route('SuperAdmin.admin')}}"><i class="fa fa-user"></i> Admin Users </a></li>

                            {{-- <li class="{{ (Request::is('admin') ? 'active' : '') }}"><a href="{{route()}}"><i class="fa fa-user"></i> User </a></li> --}}
                            <li><a><i class="fa fa-user"></i>Managers <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" >
                                    {{-- <li><a href="{{route('SuperAdmin.UserTypes')}}">Manager Types</a></li> --}}
                                    <li><a href="{{route('SuperAdmin.Users')}}">Managers</a></li>
                                    @foreach ($UTypes as $UType)
                                        <li><a href="{{route('SuperAdmin.Users',$UType['id'])}}">{{$UType['name']}}s</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            <li><a><i class="fa fa-tree"></i>Admin Section <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" >
                                    {{-- <li><a href="{{route('SuperAdmin.country')}}">Country</a></li> --}}
                                    <li><a href="{{route('SuperAdmin.region')}}">Region</a></li>
                                    <li><a href="{{route('SuperAdmin.constituency')}}">Constituency</a></li>
                                    <li><a href="{{route('SuperAdmin.ElectoralArea')}}">Electoral Area</a></li>
                                    <li><a href="{{route('SuperAdmin.PollingStation')}}">Polling Station</a></li>
                                </ul>
                            </li>

                            {{-- <li class=""><a href="{{route('SuperAdmin.pollingAgent')}}"><i class="fa fa-user"></i>Polling Agents </a></li> --}}

                            <li><a><i class="fa fa-book"></i>Election <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" >
                                    {{-- <li><a href="{{route('SuperAdmin.electionType')}}">Election Types</a></li> --}}
                                    <li><a href="{{route('SuperAdmin.election')}}">Election</a></li>

                                </ul>
                            </li>
                            <li><a><i class="fa fa-female"></i>Political Party <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" >
                                    <li><a href="{{route('SuperAdmin.politicalParty')}}">Political Party</a></li>
                                </ul>
                            </li>
                            {{-- <li class=""><a href="{{route('SuperAdmin.candidate')}}"><i class="fa fa-user"></i> Candidate </a></li> --}}
                            <li><a><i class="fa fa-user"></i>Candidate <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" >
                                    <li><a href="{{route('SuperAdmin.candidate')}}">Candidate</a></li>

                                    @foreach ($__electionTypes as $electionType)
                                        <li><a href="{{route('SuperAdmin.candidate',$electionType['id'])}}">{{$electionType['name']}} Candidate</a></li>
                                    @endforeach
                                </ul>
                            </li>

                            <li><a><i class="fa fa-user"></i>Reports <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" >
                                   @foreach ($__electionTypes as $electionType)
                                    <li><a href="{{route('SuperAdmin.resultReport',$electionType['id'])}}">{{$electionType['name']}} </a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif

                        </div>


                    </div>

                </div>
            </div>
            <div class="top_nav">
                <div class="nav_menu" style="margin-bottom:-1px !important;">
                    <nav>
                        <div class="nav toggle">
                            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                        </div>

                        <ul class="nav navbar-nav navbar-right">
                        @if (Auth::guest())
                            <li><a href="{{ route('SuperAdmin.login') }}">Login</a></li>
                         <!--    <li><a href="{{ route('register') }}">Register</a></li> -->
                        @else
                            <li class="">
                                <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    {{-- <img src="images/img.jpg" alt=""> --}}{{ Auth::user()->name }}
                                    <span class=" fa fa-angle-down"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-usermenu pull-right">
                                <li>
                                        <a href="{{ route('SuperAdmin.logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('SuperAdmin.logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                    <li>
                                        <form id="logout-form" action="{{ route('SuperAdmin.logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                            </ul>
                            </li>
                        @endif

                        </ul>
                    </nav>
                </div>

            </div>


            <div class="container body">
                <div class="main_container">
                    <!-- page content -->
                    <div class="right_col" role="main">
                            <div class="container">
                                        <div class="row">
                                            <div class="col-md-12" style=" float:  right;">
                                            @if($msg = session("message"))
                                                <div  class="alert alert-success alert-dismissible fade in" role="alert">

                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                                    </button>
                                                    <strong> {{$msg}} </strong>
                                                </div>

                                            @endif
                                            @if($error = session("error"))
                                            <div  class="alert alert-danger alert-dismissible fade in" role="alert">

                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                                    </button>
                                                    <strong> {{$error}} </strong>
                                                </div>

                                            @endif
                                            </div>
                                        </div>

                            </div>

                            @yield('content')
    <div style="clear:both;"></div>
                        </div>

      <!-- jQuery -->
    <script src="{{ asset('gentelella/vendors/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap -->

    <!-- FastClick -->
    <script src="{{ asset('gentelella/vendors/fastclick/lib/fastclick.js') }}"></script>
    <!-- NProgress -->
    <script src="{{ asset('gentelella/vendors/nprogress/nprogress.js') }}"></script>
    <!-- iCheck -->
    <script src="{{ asset('gentelella/vendors/iCheck/icheck.min.js') }}"></script>
    <!-- Datatables -->
    <script src="{{ asset('gentelella/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net/js/filterDropDown.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/datatables.net-scroller/js/dataTables.scroller.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/jszip/dist/jszip.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/pdfmake/build/vfs_fonts.js') }}"></script>
{{-- <script src="{{ asset('tooltip/jBox.all.js') }}"></script> --}}
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js">


    <!-- jQuery -->
    <script src="{{ asset('gentelella/vendors/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('gentelella/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('gentelella/vendors/fastclick/lib/fastclick.js') }}"></script>
    <!-- NProgress -->
    <script src="{{ asset('gentelella/vendors/nprogress/nprogress.js') }}"></script>
    <!-- Chart.js -->
    <script src="{{ asset('gentelella/vendors/Chart.js/dist/Chart.min.js') }}"></script>
    <!-- gauge.js -->
    <script src="{{ asset('gentelella/vendors/gauge.js/dist/gauge.min.js') }}"></script>
    <!-- bootstrap-progressbar -->
    <script src="{{ asset('gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js') }}"></script>
    <!-- iCheck -->
    <script src="{{ asset('gentelella/vendors/iCheck/icheck.min.js') }}"></script>
    <!-- Skycons -->
    <script src="{{ asset('gentelella/vendors/skycons/skycons.js') }}"></script>
    <!-- Flot -->
    <script src="{{ asset('gentelella/vendors/Flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/Flot/jquery.flot.pie.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/Flot/jquery.flot.time.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/Flot/jquery.flot.stack.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/Flot/jquery.flot.resize.js') }}"></script>
    <!-- Flot plugins -->
    <script src="{{ asset('gentelella/vendors/flot.orderbars/js/jquery.flot.orderBars.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/flot-spline/js/jquery.flot.spline.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/flot.curvedlines/curvedLines.js') }}"></script>
    <!-- DateJS -->
    <script src="{{ asset('gentelella/vendors/DateJS/build/date.js') }}"></script>
    <!-- JQVMap -->
    <script src="{{ asset('gentelella/vendors/jqvmap/dist/jquery.vmap.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js') }}"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="{{ asset('gentelella/vendors/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('gentelella/vendors/dropzone/dist/min/dropzone.min.js') }}"></script>

    <!-- Custom Theme Scripts -->
    <script src="{{ asset('gentelella/build/js/custom.js') }}"></script>


    @yield("script")
    <script>
        $.fn.dataTable.ext.errMode = 'throw';
    </script>
</body>
</html>
