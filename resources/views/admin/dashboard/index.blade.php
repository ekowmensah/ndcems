@extends('admin.layouts.app')

@section('page_title', 'Election Operations Overview')
@section('page_description', 'Monitor verified reporting, election readiness, territorial coverage, and access capacity from one national command center.')

@section('page_actions')
    <a href="{{ route('SuperAdmin.electionNew') }}" class="btn"><i class="fa fa-plus-circle"></i> New election</a>
    <a href="{{ route('SuperAdmin.parliamentaryResult') }}" class="btn"><i class="fa fa-pie-chart"></i> Parliamentary control</a>
@endsection

@section('css')
<style>
    .command-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:20px; }
    .command-stat { position:relative; min-height:142px; padding:20px; overflow:hidden; border:1px solid rgba(15,35,58,.08); border-radius:18px; background:#fff; box-shadow:0 12px 30px rgba(15,35,58,.06); }
    .command-stat:after { position:absolute; right:-28px; bottom:-36px; width:105px; height:105px; border-radius:50%; background:var(--stat-tint,#edf8f4); content:''; }
    .command-stat__icon { position:relative; z-index:1; display:grid; width:38px; height:38px; place-items:center; border-radius:12px; background:var(--stat-tint,#edf8f4); color:var(--stat-color,var(--admin-green)); }
    .command-stat strong { position:relative; z-index:1; display:block; margin-top:14px; color:var(--admin-ink); font-family:'Space Grotesk',sans-serif; font-size:27px; line-height:1; }
    .command-stat span { position:relative; z-index:1; display:block; margin-top:7px; color:var(--admin-muted); font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
    .ops-grid { display:grid; grid-template-columns:minmax(0,1.55fr) minmax(310px,.8fr); gap:20px; }
    .ops-grid + .ops-grid { margin-top:20px; }
    .metric-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border-bottom:1px solid var(--admin-line); }
    .metric-strip > div { padding:18px 20px; border-right:1px solid var(--admin-line); }
    .metric-strip > div:last-child { border-right:0; }
    .metric-strip span { display:block; color:var(--admin-muted); font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .metric-strip strong { display:block; margin-top:7px; font-family:'Space Grotesk',sans-serif; font-size:20px; }
    .chart-wrap { position:relative; min-height:300px; padding:20px; }
    .chart-wrap--compact { min-height:260px; }
    .startup-list, .access-list { margin:0; padding:0; list-style:none; }
    .startup-list li, .access-list li { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:14px 0; border-bottom:1px solid #edf1f5; }
    .startup-list li:last-child, .access-list li:last-child { border-bottom:0; }
    .startup-list strong, .access-list strong { display:block; font-size:13px; }
    .startup-list small, .access-list small { display:block; margin-top:4px; color:var(--admin-muted); }
    .status-pill { flex:0 0 auto; padding:5px 9px; border-radius:999px; background:#eef2f6; color:#607084; font-size:9px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .status-pill.is-active { background:#e8f7f1; color:#087152; }
    .access-bar { width:105px; height:7px; overflow:hidden; border-radius:99px; background:#e9eef3; }
    .access-bar i { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--admin-green),#1bb685); }
    .empty-state { padding:36px 20px; color:var(--admin-muted); text-align:center; }
    @media(max-width:1200px){ .command-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:900px){ .ops-grid{grid-template-columns:1fr;} }
    @media(max-width:640px){ .command-grid,.metric-strip{grid-template-columns:1fr 1fr;} .metric-strip>div:nth-child(2){border-right:0;} }
</style>
@endsection

@section('content')
@php
    $presidentialSummary = $presidential['summary'];
    $parliamentarySummary = $parliamentary['summary'];
    $maxUserCount = max((int) $userTypeDetail->max('user_type__count'), 1);
@endphp

<section class="command-grid" aria-label="System inventory">
    <article class="command-stat"><div class="command-stat__icon"><i class="fa fa-map"></i></div><strong>{{ number_format($systemStats['regions']) }}</strong><span>Regions configured</span></article>
    <article class="command-stat" style="--stat-color:#246b91;--stat-tint:#eaf3f8"><div class="command-stat__icon"><i class="fa fa-map-marker"></i></div><strong>{{ number_format($systemStats['constituencies']) }}</strong><span>Constituencies</span></article>
    <article class="command-stat" style="--stat-color:#a56b08;--stat-tint:#fff6df"><div class="command-stat__icon"><i class="fa fa-building-o"></i></div><strong>{{ number_format($systemStats['polling_stations']) }}</strong><span>Polling stations</span></article>
    <article class="command-stat" style="--stat-color:#a33b3b;--stat-tint:#fff0f0"><div class="command-stat__icon"><i class="fa fa-users"></i></div><strong>{{ number_format($systemStats['registered_voters']) }}</strong><span>Registered voters</span></article>
</section>

<div class="ops-grid">
    <section class="x_panel">
        <header class="x_title"><h2>Presidential vote position</h2>@if($presidentialElectionTypeId)<a href="{{ route('SuperAdmin.result', $presidentialElectionTypeId) }}" class="btn btn-default btn-xs">Explore results</a>@endif</header>
        <div class="metric-strip">
            <div><span>Reporting stations</span><strong>{{ number_format($presidentialSummary['reporting_stations']) }}</strong></div>
            <div><span>National coverage</span><strong>{{ number_format($presidentialSummary['coverage_percentage'], 1) }}%</strong></div>
            <div><span>Verified valid votes</span><strong>{{ number_format($presidentialSummary['total_valid_votes']) }}</strong></div>
            <div><span>Current leader</span><strong>{{ $presidentialSummary['leading_party'] }}</strong></div>
        </div>
        @if($presidential['lowCoverageWarning'])<div class="alert alert-warning" style="margin:18px 20px 0;"><i class="fa fa-exclamation-triangle"></i> {{ $presidential['lowCoverageWarning'] }}</div>@endif
        <div class="chart-wrap"><canvas id="presidentialChart"></canvas></div>
    </section>

    <section class="x_panel">
        <header class="x_title"><h2>Election startups</h2><a href="{{ route('SuperAdmin.election') }}" class="btn btn-default btn-xs">Manage</a></header>
        <div class="x_content">
            @if($activeStartups->isEmpty())
                <div class="empty-state"><i class="fa fa-calendar-times-o fa-2x"></i><p>No election startup has been configured.</p></div>
            @else
                <ul class="startup-list">
                    @foreach($activeStartups as $startup)
                        <li><div><strong>{{ $startup->election_name }}</strong><small>{{ $startup->election_type_name }} @if($startup->start) &middot; {{ \Carbon\Carbon::parse($startup->start)->format('M d, Y') }} @endif</small></div><span class="status-pill {{ $startup->status ? 'is-active' : '' }}">{{ $startup->status ? 'Active' : 'Inactive' }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</div>

<div class="ops-grid">
    <section class="x_panel">
        <header class="x_title"><h2>Parliamentary constituency control</h2><a href="{{ route('SuperAdmin.parliamentaryResult') }}" class="btn btn-default btn-xs">Open analysis</a></header>
        <div class="metric-strip">
            <div><span>Reporting</span><strong>{{ number_format($parliamentarySummary['reporting_constituencies']) }}/{{ number_format($parliamentarySummary['total_constituencies']) }}</strong></div>
            <div><span>Decided</span><strong>{{ number_format($parliamentarySummary['decided_constituencies']) }}</strong></div>
            <div><span>Tied</span><strong>{{ number_format($parliamentarySummary['tied_constituencies']) }}</strong></div>
            <div><span>Unreported</span><strong>{{ number_format($parliamentarySummary['remaining_constituencies']) }}</strong></div>
        </div>
        <div class="chart-wrap chart-wrap--compact"><canvas id="parliamentaryChart"></canvas></div>
    </section>

    <section class="x_panel">
        <header class="x_title"><h2>People and access</h2><a href="{{ route('SuperAdmin.Users') }}" class="btn btn-default btn-xs">Manage teams</a></header>
        <div class="x_content">
            <div style="display:flex;gap:10px;margin-bottom:10px;"><span class="status-pill is-active">{{ number_format($systemStats['field_users']) }} field users</span><span class="status-pill">{{ number_format($systemStats['administrators']) }} admins</span></div>
            <ul class="access-list">
                @foreach($userTypeDetail as $detail)
                    <li><div><strong>{{ $detail->name }}</strong><small>{{ number_format($detail->user_type__count) }} assigned</small></div><div class="access-bar"><i style="width:{{ min(100, ((int) $detail->user_type__count / $maxUserCount) * 100) }}%"></i></div></li>
                @endforeach
            </ul>
        </div>
    </section>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var palette = ['#087f5b','#d99a19','#246b91','#c73737','#6d5a98','#4b7d49','#c26b36','#607084'];
        function renderChart(id, rows, valueKey, horizontal) {
            var canvas = document.getElementById(id);
            if (!canvas || typeof Chart === 'undefined') return;
            new Chart(canvas, {
                type: horizontal ? 'bar' : 'doughnut',
                data: { labels: rows.map(function(row){ return row.party_initial; }), datasets: [{ data: rows.map(function(row){ return Number(row[valueKey] || 0); }), backgroundColor: palette, borderWidth: 0, borderRadius: horizontal ? 7 : 0 }] },
                options: { responsive:true, maintainAspectRatio:false, indexAxis:horizontal?'y':'x', cutout:horizontal?undefined:'66%', plugins:{ legend:{ position:horizontal?'bottom':'right', labels:{ usePointStyle:true, boxWidth:8, padding:16, font:{family:'Manrope',size:11} } }, tooltip:{ callbacks:{ label:function(context){ var row=rows[context.dataIndex]; return ' '+row.party_initial+': '+Number(row[valueKey]||0).toLocaleString()+(valueKey==='votes'?' votes':' constituencies'); } } } }, scales:horizontal?{x:{beginAtZero:true,ticks:{precision:0},grid:{color:'#edf1f5'}},y:{grid:{display:false}}}:{} }
            });
        }
        renderChart('presidentialChart', @json($presidential['partyBreakdown']), 'votes', false);
        renderChart('parliamentaryChart', @json($parliamentary['leadershipBreakdown']), 'count', true);
    })();
</script>
@endsection
