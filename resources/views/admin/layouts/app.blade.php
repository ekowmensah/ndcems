<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Administration') | {{ $config['name'] }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/nprogress/nprogress.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/iCheck/skins/flat/green.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/dropzone/dist/min/dropzone.min.css') }}" rel="stylesheet">
    <link href="{{ asset('gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">

    <style>
        :root {
            --admin-ink: #10243f;
            --admin-muted: #64748b;
            --admin-line: #dfe7ef;
            --admin-surface: #fff;
            --admin-canvas: #f2f6f8;
            --admin-navy: #0b1f36;
            --admin-green: #087f5b;
            --admin-green-dark: #056246;
            --admin-gold: #d99a19;
            --admin-red: #c73737;
            --admin-sidebar: 276px;
            --admin-radius: 20px;
            --admin-shadow: 0 18px 45px rgba(15, 35, 58, .08);
        }
        * { box-sizing: border-box; }
        html { background: var(--admin-canvas); }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 92% 4%, rgba(8,127,91,.1), transparent 28rem), linear-gradient(180deg, #f8fafb 0, var(--admin-canvas) 34rem);
            color: var(--admin-ink);
            font-family: 'Manrope', sans-serif;
            font-size: 14px;
        }
        a { color: var(--admin-green); }
        a:hover, a:focus { color: var(--admin-green-dark); text-decoration: none; }
        .admin-shell { min-height: 100vh; }
        .admin-sidebar {
            position: fixed; inset: 0 auto 0 0; z-index: 1035;
            width: var(--admin-sidebar); padding: 22px 18px; overflow-y: auto;
            color: #dce7f3;
            background: radial-gradient(circle at 20% 0, rgba(23,157,116,.23), transparent 20rem), linear-gradient(180deg, #102b45 0%, var(--admin-navy) 58%, #071827 100%);
            box-shadow: 16px 0 45px rgba(7,24,39,.16); transition: transform .25s ease;
        }
        .admin-brand { display:flex; align-items:center; gap:12px; padding:0 6px 20px; border-bottom:1px solid rgba(255,255,255,.1); color:#fff; }
        .admin-brand:hover { color:#fff; }
        .admin-brand img { width:48px; height:48px; padding:3px; border-radius:15px; background:#fff; object-fit:contain; }
        .admin-brand__name { display:block; font-family:'Space Grotesk',sans-serif; font-size:17px; font-weight:700; line-height:1.05; }
        .admin-brand__meta { display:block; margin-top:5px; color:#91a8bf; font-size:10px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; }
        .admin-operator { display:flex; align-items:center; gap:11px; margin:18px 0; padding:13px; border:1px solid rgba(255,255,255,.09); border-radius:16px; background:rgba(255,255,255,.055); }
        .admin-operator__avatar { display:grid; width:38px; height:38px; flex:0 0 38px; place-items:center; border-radius:12px; background:linear-gradient(135deg,var(--admin-green),#14a276); color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700; }
        .admin-operator strong { display:block; overflow:hidden; color:#fff; font-size:13px; text-overflow:ellipsis; white-space:nowrap; }
        .admin-operator span { color:#8fa8bf; font-size:11px; }
        .admin-nav-label { margin:22px 10px 8px; color:#69849e; font-size:10px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
        .admin-nav, .admin-subnav { margin:0; padding:0; list-style:none; }
        .admin-nav > li { margin-bottom:5px; }
        .admin-nav a, .admin-nav summary { display:flex; align-items:center; gap:11px; min-height:43px; padding:10px 12px; border-radius:13px; color:#c8d7e5; cursor:pointer; font-weight:650; transition:background .18s,color .18s,transform .18s; }
        .admin-nav a:hover, .admin-nav summary:hover { background:rgba(255,255,255,.075); color:#fff; transform:translateX(2px); }
        .admin-nav a.active { background:linear-gradient(135deg,rgba(16,154,113,.94),rgba(7,109,80,.98)); color:#fff; box-shadow:0 10px 24px rgba(4,77,57,.28); }
        .admin-nav i { width:18px; text-align:center; }
        .admin-nav details summary { list-style:none; }
        .admin-nav details summary::-webkit-details-marker { display:none; }
        .admin-nav details summary:after { margin-left:auto; content:'\f107'; font-family:FontAwesome; transition:transform .2s; }
        .admin-nav details[open] summary:after { transform:rotate(180deg); }
        .admin-subnav { padding:4px 0 7px 30px; }
        .admin-subnav a { min-height:34px; padding:7px 10px; color:#8fa8bf; font-size:12px; }
        .admin-main { min-height:100vh; margin-left:var(--admin-sidebar); }
        .admin-topbar { position:sticky; top:0; z-index:1020; display:flex; align-items:center; justify-content:space-between; min-height:70px; padding:12px 34px; border-bottom:1px solid rgba(15,35,58,.08); background:rgba(248,250,251,.88); backdrop-filter:blur(18px); }
        .admin-topbar__left, .admin-topbar__right { display:flex; align-items:center; gap:12px; }
        .admin-menu-toggle { display:none; width:42px; height:42px; border:1px solid var(--admin-line); border-radius:13px; background:#fff; color:var(--admin-ink); }
        .admin-context strong { display:block; font-family:'Space Grotesk',sans-serif; font-size:14px; }
        .admin-context span { color:var(--admin-muted); font-size:11px; }
        .admin-status { display:inline-flex; align-items:center; gap:7px; padding:8px 12px; border:1px solid #cde8de; border-radius:999px; background:#edf9f4; color:#087152; font-size:11px; font-weight:800; }
        .admin-status:before { width:7px; height:7px; border-radius:50%; background:#12a878; content:''; box-shadow:0 0 0 4px rgba(18,168,120,.13); }
        .admin-user-menu { position:relative; }
        .admin-user-menu > button { display:flex; align-items:center; gap:9px; padding:7px 9px; border:1px solid var(--admin-line); border-radius:14px; background:#fff; color:var(--admin-ink); }
        .admin-user-menu .dropdown-menu { right:0; left:auto; margin-top:9px; border:0; border-radius:14px; box-shadow:var(--admin-shadow); }
        .admin-page { padding:28px 34px 40px; }
        .admin-hero { position:relative; display:flex; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:24px; padding:27px 30px; overflow:hidden; border-radius:24px; background:radial-gradient(circle at 88% 12%,rgba(255,255,255,.18),transparent 14rem),linear-gradient(125deg,#102b45 0%,#123b4d 52%,#087f5b 100%); color:#fff; box-shadow:0 22px 48px rgba(11,31,54,.18); }
        .admin-hero:after { position:absolute; right:-45px; bottom:-90px; width:230px; height:230px; border:1px solid rgba(255,255,255,.14); border-radius:50%; content:''; }
        .admin-hero__eyebrow { color:#85e3c3; font-size:10px; font-weight:800; letter-spacing:.17em; text-transform:uppercase; }
        .admin-hero h1 { margin:7px 0 5px; color:#fff; font-family:'Space Grotesk',sans-serif; font-size:clamp(24px,3vw,36px); font-weight:700; }
        .admin-hero p { max-width:720px; margin:0; color:#c7d9e6; font-size:13px; line-height:1.7; }
        .admin-hero__actions { position:relative; z-index:1; display:flex; flex-wrap:wrap; gap:8px; }
        .admin-hero__actions .btn { border-color:rgba(255,255,255,.2); background:rgba(255,255,255,.1); color:#fff; }
        .admin-flash { margin-bottom:18px; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,35,58,.07); }
        .container { width:100%; max-width:none; }
        .row { margin-right:-10px; margin-left:-10px; }
        [class*='col-'] { padding-right:10px; padding-left:10px; }
        .x_panel, .panel, .card, .well { margin-bottom:20px; padding:0; overflow:hidden; border:1px solid rgba(15,35,58,.08); border-radius:var(--admin-radius); background:var(--admin-surface); box-shadow:var(--admin-shadow); }
        .x_title, .panel-heading, .card-header { display:flex; align-items:center; justify-content:space-between; min-height:64px; margin:0; padding:16px 20px; border:0; border-bottom:1px solid rgba(15,35,58,.07); background:linear-gradient(180deg,#fff,#fbfcfd); }
        .x_title h2, .panel-title, .card-header h3 { margin:0; color:var(--admin-ink); font-family:'Space Grotesk',sans-serif; font-size:17px; font-weight:700; }
        .x_content, .panel-body, .card-body { padding:21px; }
        .page-title, .title_left { height:auto; }
        .page-title h3, .container > .row h3 { color:var(--admin-ink); font-family:'Space Grotesk',sans-serif; font-weight:700; }
        .tile_count { margin:0 -10px 20px; }
        .tile_stats_count { min-height:128px; margin-bottom:20px; padding:20px !important; border:1px solid rgba(15,35,58,.08) !important; border-radius:18px; background:#fff; box-shadow:0 12px 30px rgba(15,35,58,.06); }
        .tile_stats_count:before { display:none; }
        .count_top { color:var(--admin-muted); font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
        .tile_stats_count .count { margin-top:12px; color:var(--admin-ink); font-family:'Space Grotesk',sans-serif; font-weight:700; }
        .form-horizontal .control-label { padding-top:11px; color:#405168; font-size:12px; font-weight:800; }
        .form-group { margin-bottom:18px; }
        .form-control { min-height:44px; padding:10px 13px; border:1px solid #d9e3ec; border-radius:12px; background:#fbfcfd; color:var(--admin-ink); box-shadow:none; transition:border-color .18s,box-shadow .18s,background .18s; }
        textarea.form-control { min-height:110px; }
        .form-control:focus { border-color:var(--admin-green); background:#fff; box-shadow:0 0 0 3px rgba(8,127,91,.1); }
        input[type=file].form-control { height:auto; padding:9px; }
        .form-horizontal .form-group { margin-right:0; margin-left:0; }
        .form-horizontal .form-group:last-child, .form-actions { padding-top:18px; border-top:1px solid var(--admin-line); }
        .required { color:var(--admin-red); }
        .btn { min-height:39px; padding:9px 15px; border-radius:11px; font-size:12px; font-weight:800; box-shadow:none; transition:transform .16s,box-shadow .16s; }
        .btn:hover { transform:translateY(-1px); }
        .btn-xs { min-height:30px; padding:6px 10px; border-radius:9px; }
        .btn-primary, .btn-success { border-color:var(--admin-green); background:var(--admin-green); }
        .btn-primary:hover, .btn-success:hover, .btn-primary:focus, .btn-success:focus { border-color:var(--admin-green-dark); background:var(--admin-green-dark); }
        .btn-info { border-color:#246b91; background:#246b91; }
        .btn-warning { border-color:var(--admin-gold); background:var(--admin-gold); color:#30250c; }
        .btn-danger { border-color:var(--admin-red); background:var(--admin-red); }
        .btn-default { border-color:var(--admin-line); background:#fff; color:var(--admin-ink); }
        .table-responsive { border:0; }
        .table { margin-bottom:0; color:#263950; }
        .table > thead > tr > th { padding:12px 10px; border-bottom:1px solid var(--admin-line); color:#66768a; font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; white-space:nowrap; }
        .table > tbody > tr > td { padding:13px 10px; border-top-color:#edf1f5; vertical-align:middle; }
        .table-hover > tbody > tr:hover { background:#f6faf8; }
        table.dataTable { width:100% !important; }
        .dataTables_wrapper .row { margin-bottom:12px; }
        .dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select { min-height:36px; border:1px solid var(--admin-line); border-radius:9px; }
        .pagination > li > a, .pagination > li > span { margin:0 2px; border:0; border-radius:9px; color:var(--admin-ink); }
        .pagination > .active > a, .pagination > .active > a:hover { background:var(--admin-green); }
        .alert { border:0; border-radius:14px; }
        .alert-success { background:#eaf8f2; color:#086247; }
        .alert-danger { background:#fff0f0; color:#9f2929; }
        .alert-warning { background:#fff8e7; color:#7b5914; }
        .label, .badge { padding:5px 8px; border-radius:999px; font-weight:700; }
        .img-thumbnail { border:1px solid var(--admin-line); border-radius:12px; object-fit:cover; }
        .admin-footer { padding:22px 34px; color:#7b8999; font-size:11px; }
        .admin-sidebar-backdrop { display:none; }
        @media (max-width:991px) {
            .admin-sidebar { transform:translateX(-105%); }
            .admin-main { margin-left:0; }
            .admin-menu-toggle { display:inline-grid; place-items:center; }
            body.admin-menu-open .admin-sidebar { transform:translateX(0); }
            body.admin-menu-open .admin-sidebar-backdrop { position:fixed; inset:0; z-index:1030; display:block; background:rgba(6,20,34,.54); }
            .admin-page, .admin-topbar { padding-right:18px; padding-left:18px; }
        }
        @media (max-width:767px) {
            .admin-status, .admin-context span, .admin-user-name { display:none; }
            .admin-hero { align-items:flex-start; flex-direction:column; padding:22px; }
            .admin-page { padding-top:18px; }
            .x_content, .panel-body, .card-body { padding:16px; }
            .form-horizontal .control-label { margin-bottom:6px; text-align:left; }
            .admin-footer { padding:18px; }
        }
    </style>
    @yield('css')
</head>
<body>
@php
    $adminUser = Auth::guard('superAdmin')->user();
    $routeName = optional(request()->route())->getName();
    $routeTitle = 'Administration';
    $routeTitles = [
        'SuperAdmin.home' => 'Operations overview',
        'SuperAdmin.parliamentaryResult' => 'Parliamentary control',
        'SuperAdmin.constituencyView' => 'Constituency analysis',
        'SuperAdmin.resultReport*' => 'Result exports',
        'SuperAdmin.result' => 'Result explorer',
        'SuperAdmin.electionType*' => 'Election types',
        'SuperAdmin.New.electionType' => 'New election type',
        'SuperAdmin.electionDetail*' => 'Election configuration',
        'SuperAdmin.electionNew*' => 'New election',
        'SuperAdmin.election' => 'Election startups',
        'SuperAdmin.*politicalParty*' => 'Political parties',
        'SuperAdmin.candidate*' => 'Candidates',
        'SuperAdmin.*Admin*' => 'Administrator access',
        'SuperAdmin.admin' => 'Administrator access',
        'SuperAdmin.*UserTypes*' => 'Manager roles',
        'SuperAdmin.*User*' => 'Field teams',
        'SuperAdmin.pollingAgent*' => 'Polling agents',
        'SuperAdmin.*region*' => 'Regions',
        'SuperAdmin.*constituency*' => 'Constituencies',
        'SuperAdmin.*ElectoralArea*' => 'Electoral areas',
        'SuperAdmin.*PollingStation*' => 'Polling stations',
        'SuperAdmin.country*' => 'Countries',
        'SuperAdmin.governance.*' => 'Governance audit',
        'SuperAdmin.imports.*' => 'Bulk imports',
        'SuperAdmin.security.*' => 'Security checkpoint',
    ];
    foreach ($routeTitles as $pattern => $title) {
        if (request()->routeIs($pattern)) {
            $routeTitle = $title;
            break;
        }
    }
@endphp
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="{{ route('SuperAdmin.home') }}" class="admin-brand">
            <img src="{{ asset($config['logo']) }}" alt="{{ $config['name'] }}">
            <span><span class="admin-brand__name">Election Command</span><span class="admin-brand__meta">Administration</span></span>
        </a>
        <div class="admin-operator">
            <div class="admin-operator__avatar">{{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}</div>
            <div style="min-width:0;"><strong>{{ $adminUser->name ?? 'Administrator' }}</strong><span>Super administrator</span></div>
        </div>

        <div class="admin-nav-label">Command center</div>
        <ul class="admin-nav">
            <li><a href="{{ route('SuperAdmin.home') }}" class="{{ Request::routeIs('SuperAdmin.home') ? 'active' : '' }}"><i class="fa fa-dashboard"></i> Overview</a></li>
            <li><details {{ Request::routeIs('SuperAdmin.result', 'SuperAdmin.parliamentaryResult', 'SuperAdmin.constituencyView', 'SuperAdmin.resultReport') ? 'open' : '' }}><summary><i class="fa fa-line-chart"></i> Results & reports</summary><ul class="admin-subnav">
                @foreach ($__electionTypes as $electionType)<li><a href="{{ route('SuperAdmin.result', $electionType['id']) }}">{{ $electionType['name'] }}</a></li>@endforeach
                <li><a href="{{ route('SuperAdmin.parliamentaryResult') }}">Parliamentary control</a></li>
            </ul></details></li>
        </ul>

        <div class="admin-nav-label">Election operations</div>
        <ul class="admin-nav">
            <li><a href="{{ route('SuperAdmin.election') }}" class="{{ Request::routeIs('SuperAdmin.election', 'SuperAdmin.electionNew', 'SuperAdmin.electionDetail') ? 'active' : '' }}"><i class="fa fa-calendar-check-o"></i> Election startups</a></li>
            <li><a href="{{ route('SuperAdmin.politicalParty') }}" class="{{ Request::routeIs('SuperAdmin.politicalParty', 'SuperAdmin.New.politicalParty', 'SuperAdmin.Edit.politicalParty') ? 'active' : '' }}"><i class="fa fa-flag"></i> Political parties</a></li>
            <li><details {{ Request::routeIs('SuperAdmin.candidate', 'SuperAdmin.candidateRegister', 'SuperAdmin.candidateEdit') ? 'open' : '' }}><summary><i class="fa fa-id-card-o"></i> Candidates</summary><ul class="admin-subnav">
                <li><a href="{{ route('SuperAdmin.candidate') }}">All candidates</a></li>
                @foreach ($__electionTypes as $electionType)<li><a href="{{ route('SuperAdmin.candidate', $electionType['id']) }}">{{ $electionType['name'] }}</a></li>@endforeach
            </ul></details></li>
        </ul>

        <div class="admin-nav-label">People & access</div>
        <ul class="admin-nav">
            <li><a href="{{ route('SuperAdmin.admin') }}" class="{{ Request::routeIs('SuperAdmin.admin', 'SuperAdmin.New.Admin', 'SuperAdmin.edit.Admin') ? 'active' : '' }}"><i class="fa fa-shield"></i> Administrators</a></li>
            <li><a href="{{ route('SuperAdmin.governance.audits') }}" class="{{ Request::routeIs('SuperAdmin.governance.audits') ? 'active' : '' }}"><i class="fa fa-history"></i> Governance audit</a></li>
            <li><a href="{{ route('SuperAdmin.governance.archived') }}" class="{{ Request::routeIs('SuperAdmin.governance.archived') ? 'active' : '' }}"><i class="fa fa-archive"></i> Archived accounts</a></li>
            <li><a href="{{ route('SuperAdmin.imports.index') }}" class="{{ Request::routeIs('SuperAdmin.imports.*') ? 'active' : '' }}"><i class="fa fa-upload"></i> Bulk imports</a></li>
            <li><details {{ Request::routeIs('SuperAdmin.Users', 'SuperAdmin.New.User', 'SuperAdmin.UsersEdit', 'SuperAdmin.pollingAgent') ? 'open' : '' }}><summary><i class="fa fa-users"></i> Field teams</summary><ul class="admin-subnav">
                <li><a href="{{ route('SuperAdmin.Users') }}">All managers</a></li>
                @foreach ($UTypes as $UType)<li><a href="{{ route('SuperAdmin.Users', $UType['id']) }}">{{ $UType['name'] }}s</a></li>@endforeach
            </ul></details></li>
        </ul>

        <div class="admin-nav-label">Territory setup</div>
        <ul class="admin-nav">
            <li><a href="{{ route('SuperAdmin.region') }}" class="{{ Request::routeIs('SuperAdmin.region', 'SuperAdmin.New.region', 'SuperAdmin.regionEdit') ? 'active' : '' }}"><i class="fa fa-map"></i> Regions</a></li>
            <li><a href="{{ route('SuperAdmin.constituency') }}" class="{{ Request::routeIs('SuperAdmin.constituency', 'SuperAdmin.New.constituency', 'SuperAdmin.constituencyEdit') ? 'active' : '' }}"><i class="fa fa-map-marker"></i> Constituencies</a></li>
            <li><a href="{{ route('SuperAdmin.ElectoralArea') }}" class="{{ Request::routeIs('SuperAdmin.ElectoralArea', 'SuperAdmin.New.ElectoralArea', 'SuperAdmin.ElectoralAreaEdit') ? 'active' : '' }}"><i class="fa fa-sitemap"></i> Electoral areas</a></li>
            <li><a href="{{ route('SuperAdmin.PollingStation') }}" class="{{ Request::routeIs('SuperAdmin.PollingStation', 'SuperAdmin.New.PollingStation', 'SuperAdmin.PollingStationEdit') ? 'active' : '' }}"><i class="fa fa-building-o"></i> Polling stations</a></li>
        </ul>
    </aside>
    <div class="admin-sidebar-backdrop" data-close-admin-menu></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar__left">
                <button class="admin-menu-toggle" type="button" data-toggle-admin-menu aria-label="Open navigation"><i class="fa fa-bars"></i></button>
                <div class="admin-context"><strong>{{ $routeTitle }}</strong><span>Election operations workspace</span></div>
            </div>
            <div class="admin-topbar__right">
                <span class="admin-status">System online</span>
                <div class="dropdown admin-user-menu">
                    <button type="button" data-toggle="dropdown"><span class="admin-operator__avatar" style="width:30px;height:30px;flex-basis:30px;border-radius:9px;">{{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}</span><span class="admin-user-name">{{ $adminUser->name ?? 'Administrator' }}</span><i class="fa fa-angle-down"></i></button>
                    <ul class="dropdown-menu"><li><a href="{{ route('SuperAdmin.admin') }}"><i class="fa fa-user-circle-o"></i> Administrator accounts</a></li><li class="divider"></li><li><a href="{{ route('SuperAdmin.logout') }}" onclick="event.preventDefault();document.getElementById('admin-logout-form').submit();"><i class="fa fa-sign-out"></i> Sign out</a></li></ul>
                </div>
                <form id="admin-logout-form" action="{{ route('SuperAdmin.logout') }}" method="POST" style="display:none;">{{ csrf_field() }}</form>
            </div>
        </header>

        <div class="admin-page">
            <section class="admin-hero">
                <div><span class="admin-hero__eyebrow">Election management system</span><h1>@yield('page_title', $routeTitle)</h1><p>@yield('page_description', 'Manage election readiness, field operations, territorial data, access, and verified reporting from one command center.')</p></div>
                <div class="admin-hero__actions">@yield('page_actions')</div>
            </section>
            @if ($msg = session('message'))<div class="alert alert-success admin-flash"><i class="fa fa-check-circle"></i> {{ $msg }}</div>@endif
            @if ($error = session('error'))<div class="alert alert-danger admin-flash"><i class="fa fa-exclamation-circle"></i> {{ $error }}</div>@endif
            @yield('content')
        </div>
        <footer class="admin-footer">{{ $config['name'] }} &middot; Election Operations Command Center &middot; {{ date('Y') }}</footer>
    </main>
</div>

<script src="{{ asset('gentelella/vendors/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/fastclick/lib/fastclick.js') }}"></script>
<script src="{{ asset('gentelella/vendors/nprogress/nprogress.js') }}"></script>
<script src="{{ asset('gentelella/vendors/iCheck/icheck.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js') }}"></script>
<script src="{{ asset('gentelella/vendors/jszip/dist/jszip.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/pdfmake/build/pdfmake.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/pdfmake/build/vfs_fonts.js') }}"></script>
<script src="{{ asset('gentelella/vendors/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('gentelella/vendors/dropzone/dist/min/dropzone.min.js') }}"></script>
<script src="{{ asset('gentelella/vendors/parsleyjs/dist/parsley.min.js') }}"></script>
<script>
    window.submitAdminAction = function (url, method, message) {
        if (message && !window.confirm(message)) return false;
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
            '<input type="hidden" name="_method" value="' + method + '">';
        document.body.appendChild(form);
        form.submit();
        return false;
    };
    (function () {
        var body = document.body;
        $('[data-toggle-admin-menu]').on('click', function () { body.classList.toggle('admin-menu-open'); });
        $('[data-close-admin-menu]').on('click', function () { body.classList.remove('admin-menu-open'); });
        if ($.fn.dataTable) {
            $.fn.dataTable.ext.errMode = 'throw';
            $.extend(true, $.fn.dataTable.defaults, { autoWidth:false, responsive:true, language:{ search:'', searchPlaceholder:'Search records...', lengthMenu:'Show _MENU_', info:'Showing _START_-_END_ of _TOTAL_', paginate:{previous:'Previous',next:'Next'} } });
        }
    })();
</script>
@yield('script')
</body>
</html>
