@extends('admin.layouts.app')

@section('page_title', $constituency_detail->name.' Constituency')
@section('page_description', 'Verified parliamentary vote totals, reporting completeness, and polling-station submission status for this constituency.')

@section('page_actions')
    <a href="{{ route('SuperAdmin.parliamentaryResult', ['startup_id'=>$selectedStartupId]) }}" class="btn"><i class="fa fa-arrow-left"></i> All constituencies</a>
    <form method="GET" action="{{ route('SuperAdmin.constituencyView', $constituency_detail->id) }}"><select name="startup_id" class="form-control" onchange="this.form.submit()" style="min-width:220px;background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.24);color:#fff;">@foreach($startupOptions as $startup)<option value="{{ $startup->id }}" {{ (string)$selectedStartupId === (string)$startup->id ? 'selected' : '' }} style="color:#10243f;">{{ $startup->election_name }}</option>@endforeach</select></form>
@endsection

@section('css')
<style>
    .detail-stats { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; margin-bottom:20px; }
    .detail-stat { padding:18px; border:1px solid rgba(15,35,58,.08); border-radius:17px; background:#fff; box-shadow:0 10px 26px rgba(15,35,58,.055); }
    .detail-stat span { display:block; color:var(--admin-muted); font-size:9px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .detail-stat strong { display:block; margin-top:8px; font-family:'Space Grotesk',sans-serif; font-size:22px; }
    .detail-grid { display:grid; grid-template-columns:minmax(0,1.25fr) minmax(280px,.55fr); gap:20px; }
    .chart-space { position:relative; height:340px; padding:20px; }
    .party-list { margin:0; padding:0; list-style:none; }
    .party-list li { display:grid; grid-template-columns:42px 1fr auto; gap:10px; align-items:center; padding:13px 0; border-bottom:1px solid #edf1f5; }
    .party-list li:last-child { border-bottom:0; }
    .party-code { padding:7px 5px; border-radius:9px; background:#e9f7f2; color:#087152; text-align:center; font-size:10px; font-weight:800; }
    .party-list strong,.party-list small { display:block; }.party-list small{margin-top:3px;color:var(--admin-muted)}
    .submission-pill { display:inline-flex; padding:5px 8px; border-radius:999px; background:#eef2f6; color:#607084; font-size:9px; font-weight:800; text-transform:uppercase; }
    .submission-pill.is-received { background:#e8f7f1; color:#087152; }
    @media(max-width:1050px){.detail-stats{grid-template-columns:repeat(3,1fr)}.detail-grid{grid-template-columns:1fr}}
    @media(max-width:620px){.detail-stats{grid-template-columns:1fr 1fr}}
</style>
@endsection

@section('content')
<section class="detail-stats">
    <article class="detail-stat"><span>Current leader</span><strong>{{ $summary['leading_party'] }}</strong></article>
    <article class="detail-stat"><span>Reporting stations</span><strong>{{ number_format($summary['reporting_stations']) }}/{{ number_format($summary['total_polling_stations']) }}</strong></article>
    <article class="detail-stat"><span>Coverage</span><strong>{{ number_format($summary['coverage_percentage'],1) }}%</strong></article>
    <article class="detail-stat"><span>Verified valid votes</span><strong>{{ number_format($summary['total_valid_votes']) }}</strong></article>
    <article class="detail-stat"><span>Rejected ballot rate</span><strong>{{ number_format($summary['rejected_rate'],2) }}%</strong></article>
</section>

<div class="detail-grid">
    <section class="x_panel"><header class="x_title"><h2>Verified party vote totals</h2><span class="label label-success">{{ $selectedStartupName }}</span></header><div class="chart-space"><canvas id="partyChart"></canvas></div></section>
    <section class="x_panel"><header class="x_title"><h2>Party position</h2><span class="label label-default">{{ number_format($summary['total_valid_votes']) }} votes</span></header><div class="x_content"><ol class="party-list">@foreach($partyBreakdown as $row)<li><span class="party-code">{{ $row['party_initial'] }}</span><div><strong>{{ number_format($row['votes']) }}</strong><small>Verified votes</small></div><strong>{{ number_format($row['percentage'],2) }}%</strong></li>@endforeach</ol></div></section>
</div>

<section class="x_panel">
    <header class="x_title"><h2>Polling-station reporting register</h2><span class="label label-default">Updated {{ $summary['updated_at'] }}</span></header>
    <div class="x_content table-responsive"><table class="table table-hover" id="pollingRegister"><thead><tr><th>Polling station</th><th>Code</th><th>Registered voters</th><th>Status</th><th>Valid votes</th><th>Rejected</th><th>Total ballots</th></tr></thead><tbody>
        @foreach($subordinateRows as $row)<tr><td><strong>{{ $row['polling_station_name'] }}</strong></td><td>{{ $row['polling_station_code'] }}</td><td data-order="{{ $row['total_voters'] }}">{{ number_format($row['total_voters']) }}</td><td><span class="submission-pill {{ $row['submitted'] ? 'is-received' : '' }}">{{ $row['submitted'] ? 'Received' : 'Pending' }}</span></td><td data-order="{{ $row['total_valid_votes'] }}">{{ number_format($row['total_valid_votes']) }}</td><td data-order="{{ $row['total_rejected_ballots'] }}">{{ number_format($row['total_rejected_ballots']) }}</td><td data-order="{{ $row['total_ballots'] }}">{{ number_format($row['total_ballots']) }}</td></tr>@endforeach
    </tbody></table></div>
</section>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    $(function(){var rows=@json($partyBreakdown),palette=['#087f5b','#d99a19','#246b91','#c73737','#6d5a98','#4b7d49','#c26b36'];new Chart(document.getElementById('partyChart'),{type:'bar',data:{labels:rows.map(function(r){return r.party_initial;}),datasets:[{data:rows.map(function(r){return Number(r.votes||0);}),backgroundColor:palette,borderRadius:8,maxBarThickness:58}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{afterLabel:function(c){return Number(rows[c.dataIndex].percentage||0).toFixed(2)+'% of constituency votes';}}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#edf1f5'},ticks:{callback:function(v){return Number(v).toLocaleString();}}}}}});$('#pollingRegister').DataTable({pageLength:25,order:[[3,'desc'],[0,'asc']]});});
</script>
@endsection
