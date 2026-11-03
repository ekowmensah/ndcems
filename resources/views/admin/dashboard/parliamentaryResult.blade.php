@extends('admin.layouts.app')

@section('page_title', 'Parliamentary Control')
@section('page_description', 'Constituency leadership is based on verified parliamentary results. National vote share and constituency control are intentionally reported as separate measures.')

@section('page_actions')
    <form method="GET" action="{{ route('SuperAdmin.parliamentaryResult') }}">
        <select name="startup_id" class="form-control" onchange="this.form.submit()" style="min-width:240px;background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.24);color:#fff;">
            @forelse($startupOptions as $startup)<option value="{{ $startup->id }}" {{ (string)$selectedStartupId === (string)$startup->id ? 'selected' : '' }} style="color:#10243f;">{{ $startup->election_name }}</option>@empty<option>No parliamentary startup</option>@endforelse
        </select>
    </form>
@endsection

@section('css')
<style>
    .summary-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; margin-bottom:20px; }
    .summary-card { padding:18px; border:1px solid rgba(15,35,58,.08); border-radius:17px; background:#fff; box-shadow:0 10px 26px rgba(15,35,58,.055); }
    .summary-card span { display:block; color:var(--admin-muted); font-size:9px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .summary-card strong { display:block; margin-top:9px; font-family:'Space Grotesk',sans-serif; font-size:24px; }
    .summary-card small { display:block; margin-top:5px; color:var(--admin-muted); }
    .analysis-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(300px,.55fr); gap:20px; }
    .chart-space { position:relative; height:300px; padding:20px; }
    .definition-note { padding:20px; }
    .definition-note h3 { margin:0 0 10px; font-family:'Space Grotesk',sans-serif; font-size:16px; }
    .definition-note p { color:var(--admin-muted); font-size:12px; line-height:1.7; }
    .definition-note .rule { display:flex; gap:10px; padding:12px 0; border-top:1px solid #edf1f5; }
    .definition-note .rule i { color:var(--admin-green); }
    .coverage-cell { min-width:130px; }
    .coverage-track { height:6px; margin-top:5px; overflow:hidden; border-radius:99px; background:#e8eef2; }
    .coverage-track i { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--admin-green),#21b383); }
    .party-pill { display:inline-flex; min-width:46px; justify-content:center; padding:5px 8px; border-radius:9px; background:#e9f7f2; color:#087152; font-size:10px; font-weight:800; }
    .party-pill.is-tie { background:#fff4d8; color:#8b620d; }
    .party-pill.is-empty { background:#eef2f6; color:#718096; }
    @media(max-width:1100px){.summary-grid{grid-template-columns:repeat(3,1fr)}.analysis-grid{grid-template-columns:1fr}}
    @media(max-width:650px){.summary-grid{grid-template-columns:1fr 1fr}}
</style>
@endsection

@section('content')
@if($lowCoverageWarning)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> {{ $lowCoverageWarning }}</div>@endif

<section class="summary-grid">
    <article class="summary-card"><span>Reporting constituencies</span><strong>{{ number_format($summary['reporting_constituencies']) }} / {{ number_format($summary['total_constituencies']) }}</strong><small>{{ number_format($summary['constituency_coverage_percentage'], 1) }}% coverage</small></article>
    <article class="summary-card"><span>Decided leaders</span><strong>{{ number_format($summary['decided_constituencies']) }}</strong><small>Excludes tied constituencies</small></article>
    <article class="summary-card"><span>Tied positions</span><strong>{{ number_format($summary['tied_constituencies']) }}</strong><small>Requires additional reporting</small></article>
    <article class="summary-card"><span>Not yet reporting</span><strong>{{ number_format($summary['remaining_constituencies']) }}</strong><small>Of {{ number_format($summary['total_constituencies']) }} configured</small></article>
    <article class="summary-card"><span>Verified valid votes</span><strong>{{ number_format($summary['total_valid_votes']) }}</strong><small>Updated {{ $summary['updated_at'] }}</small></article>
</section>

<div class="analysis-grid">
    <section class="x_panel"><header class="x_title"><h2>Constituencies currently led</h2><span class="label label-success">{{ $selectedStartupName }}</span></header><div class="chart-space"><canvas id="controlChart"></canvas></div></section>
    <aside class="x_panel"><div class="definition-note"><h3>What “control” means</h3><p>This chart counts each reporting constituency once, based on the party with the highest verified aggregate vote. It is not national parliamentary vote share.</p><div class="rule"><i class="fa fa-check-circle"></i><span><strong>Led</strong><br><small>One party has the highest verified total.</small></span></div><div class="rule"><i class="fa fa-balance-scale"></i><span><strong>Tied</strong><br><small>Top parties currently have equal totals.</small></span></div><div class="rule"><i class="fa fa-clock-o"></i><span><strong>Unreported</strong><br><small>No verified constituency result is available.</small></span></div></div></aside>
</div>

<section class="x_panel">
    <header class="x_title"><h2>Constituency reporting register</h2><span class="label label-default">{{ number_format($constituencyRows->count()) }} constituencies</span></header>
    <div class="x_content table-responsive">
        <table class="table table-hover" id="constituencyResults">
            <thead><tr><th>Constituency</th><th>Current leader</th><th>Reporting coverage</th><th>Valid votes</th><th>Rejected rate</th><th>Last update</th><th>Action</th></tr></thead>
            <tbody>
            @foreach($constituencyRows as $row)
                <tr>
                    <td><strong>{{ $row['constituency_name'] }}</strong></td>
                    <td><span class="party-pill {{ $row['leading_party'] === 'TIE' ? 'is-tie' : ($row['leading_party'] === 'N/A' ? 'is-empty' : '') }}">{{ $row['leading_party'] }}</span></td>
                    <td data-order="{{ $row['coverage_percentage'] }}"><div class="coverage-cell"><strong>{{ $row['reporting_stations'] }}/{{ $row['total_polling_stations'] }}</strong> <small>({{ number_format($row['coverage_percentage'], 1) }}%)</small><div class="coverage-track"><i style="width:{{ min(100,$row['coverage_percentage']) }}%"></i></div></div></td>
                    <td data-order="{{ $row['total_valid_votes'] }}">{{ number_format($row['total_valid_votes']) }}</td>
                    <td data-order="{{ $row['rejected_rate'] }}">{{ number_format($row['rejected_rate'], 2) }}%</td>
                    <td>{{ $row['updated_at'] }}</td>
                    <td><a class="btn btn-default btn-xs" href="{{ route('SuperAdmin.constituencyView', ['id'=>$row['id'],'startup_id'=>$selectedStartupId]) }}">Inspect</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    $(function(){
        var rows=@json($leadershipBreakdown), palette=['#087f5b','#d99a19','#246b91','#c73737','#6d5a98','#607084'];
        new Chart(document.getElementById('controlChart'),{type:'bar',data:{labels:rows.map(function(r){return r.party_initial;}),datasets:[{data:rows.map(function(r){return Number(r.count||0);}),backgroundColor:palette,borderRadius:8,maxBarThickness:52}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return ' '+c.raw+' reporting constituencies';}}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#edf1f5'}}}}});
        $('#constituencyResults').DataTable({pageLength:25,order:[[2,'desc']],columnDefs:[{orderable:false,targets:6}]});
    });
</script>
@endsection
