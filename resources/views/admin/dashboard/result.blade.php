@extends('admin.layouts.app')

@section('page_title', $election->name.' Results')
@section('page_description', 'Explore verified vote totals by election startup and geography. Percentages are calculated from the selected scope, not mixed across elections.')

@section('page_actions')
    <a href="{{ route('SuperAdmin.resultReport', $id) }}" class="btn"><i class="fa fa-file-excel-o"></i> Export report</a>
@endsection

@section('css')
<style>
    .filter-panel { padding:20px; }
    .filter-grid { display:grid; grid-template-columns:repeat(5,minmax(150px,1fr)); gap:12px; }
    .filter-field label { display:block; margin-bottom:7px; color:var(--admin-muted); font-size:9px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .result-context { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:16px; padding-top:16px; border-top:1px solid var(--admin-line); }
    .result-context strong { display:block; font-size:13px; }
    .result-context span { color:var(--admin-muted); font-size:11px; }
    .result-layout { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(290px,.65fr); gap:20px; }
    .chart-space { position:relative; height:440px; padding:22px; }
    .ranking-list { margin:0; padding:0; list-style:none; }
    .ranking-list li { display:grid; grid-template-columns:34px minmax(0,1fr) auto; align-items:center; gap:10px; padding:14px 0; border-bottom:1px solid #edf1f5; }
    .ranking-list li:last-child { border-bottom:0; }
    .rank-number { display:grid; width:30px; height:30px; place-items:center; border-radius:10px; background:#eef5f3; color:var(--admin-green); font-size:11px; font-weight:800; }
    .ranking-list strong, .ranking-list small { display:block; }
    .ranking-list small { margin-top:3px; color:var(--admin-muted); }
    .rank-share { color:var(--admin-ink); font-family:'Space Grotesk',sans-serif; font-weight:700; }
    .loading-mask { position:absolute; inset:0; z-index:2; display:none; place-items:center; border-radius:inherit; background:rgba(255,255,255,.78); color:var(--admin-green); backdrop-filter:blur(2px); }
    .is-loading .loading-mask { display:grid; }
    @media(max-width:1100px){ .filter-grid{grid-template-columns:repeat(3,1fr);} }
    @media(max-width:850px){ .result-layout{grid-template-columns:1fr;} }
    @media(max-width:620px){ .filter-grid{grid-template-columns:1fr;} .chart-space{height:360px;} }
</style>
@endsection

@section('content')
<section class="x_panel">
    <div class="filter-panel">
        <div class="filter-grid">
            <div class="filter-field"><label for="election_start_up_id">Election startup</label><select class="form-control result-filter" id="election_start_up_id">@forelse($details as $detail)<option value="{{ $detail->id }}" {{ (string)$selectedStartupId === (string)$detail->id ? 'selected' : '' }}>{{ $detail->election_name }}</option>@empty<option value="">No startup configured</option>@endforelse</select></div>
            <div class="filter-field"><label for="region_id">Region</label><select class="form-control result-filter" id="region_id"><option value="all">All regions</option>@foreach($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach</select></div>
            <div class="filter-field"><label for="constituency_id">Constituency</label><select class="form-control result-filter" id="constituency_id" disabled><option value="all">All constituencies</option></select></div>
            <div class="filter-field"><label for="electoral_area_id">Electoral area</label><select class="form-control result-filter" id="electoral_area_id" disabled><option value="all">All electoral areas</option></select></div>
            <div class="filter-field"><label for="polling_station_id">Polling station</label><select class="form-control result-filter" id="polling_station_id" disabled><option value="all">All polling stations</option></select></div>
        </div>
        <div class="result-context"><div><strong id="scopeTitle">National verified position</strong><span id="scopeSubtitle">{{ $selectedStartupName }}</span></div><button type="button" class="btn btn-default btn-xs" id="resetFilters"><i class="fa fa-refresh"></i> Reset geography</button></div>
    </div>
</section>

<div class="result-layout">
    <section class="x_panel" id="chartPanel">
        <header class="x_title"><h2>Vote share and volume</h2><span class="label label-success">Verified only</span></header>
        <div class="chart-space"><div class="loading-mask"><i class="fa fa-circle-o-notch fa-spin fa-2x"></i></div><canvas id="resultChart"></canvas></div>
    </section>
    <section class="x_panel">
        <header class="x_title"><h2>Party ranking</h2><span id="totalVotes" class="label label-default"></span></header>
        <div class="x_content"><ol class="ranking-list" id="rankingList"></ol></div>
    </section>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var rows = @json($initialBreakdown);
        var palette = ['#087f5b','#d99a19','#246b91','#c73737','#6d5a98','#4b7d49','#c26b36','#607084'];
        var chart = new Chart(document.getElementById('resultChart'), {
            type:'bar', data:{labels:[],datasets:[{label:'Verified votes',data:[],backgroundColor:palette,borderRadius:8,maxBarThickness:58}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{afterLabel:function(context){return Number(rows[context.dataIndex].percentage||0).toFixed(2)+'% of selected scope';}}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#edf1f5'},ticks:{callback:function(value){return Number(value).toLocaleString();}}}}}
        });

        function render(data) {
            rows = data || [];
            chart.data.labels = rows.map(function(row){ return row.party_initial; });
            chart.data.datasets[0].data = rows.map(function(row){ return Number(row.votes || 0); });
            chart.update();
            var total = rows.reduce(function(sum,row){ return sum + Number(row.votes || 0); }, 0);
            $('#totalVotes').text(total.toLocaleString()+' votes');
            $('#rankingList').html(rows.map(function(row,index){ return '<li><span class="rank-number">'+(index+1)+'</span><div><strong>'+escapeHtml(row.party_initial || 'N/A')+'</strong><small>'+Number(row.votes||0).toLocaleString()+' verified votes</small></div><span class="rank-share">'+Number(row.percentage||0).toFixed(2)+'%</span></li>'; }).join(''));
        }
        function escapeHtml(value) { return $('<div>').text(value).html(); }
        function filters() { return {election_type_id:'{{ $id }}',election_start_up_id:$('#election_start_up_id').val(),region_id:$('#region_id').val(),constituency_id:$('#constituency_id').val(),electoral_area_id:$('#electoral_area_id').val(),polling_station_id:$('#polling_station_id').val()}; }
        function refresh() {
            $('#chartPanel').addClass('is-loading');
            $.ajax({url:'{{ route('SuperAdmin.allResultAjax') }}',type:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},data:filters()})
                .done(render).fail(function(){ window.alert('The result view could not be refreshed. Please try again.'); }).always(function(){ $('#chartPanel').removeClass('is-loading'); updateScope(); });
        }
        function updateScope() {
            var labels = ['polling_station_id','electoral_area_id','constituency_id','region_id'].map(function(id){ var el=$('#'+id); return el.val() && el.val()!=='all' ? el.find('option:selected').text() : null; }).filter(Boolean);
            $('#scopeTitle').text(labels[0] || 'National verified position');
            $('#scopeSubtitle').text($('#election_start_up_id option:selected').text());
        }
        function populate(url, data, target, placeholder) {
            var select=$(target).prop('disabled',true).html('<option value="all">Loading...</option>');
            return $.ajax({url:url,type:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},data:data}).done(function(items){ select.html('<option value="all">'+placeholder+'</option>'); $.each(items,function(_,item){ select.append($('<option>',{value:item.id,text:item.name})); }); select.prop('disabled',false); });
        }
        $('#election_start_up_id').on('change',refresh);
        $('#region_id').on('change',function(){ $('#electoral_area_id,#polling_station_id').prop('disabled',true).html('<option value="all">All</option>'); if(this.value==='all'){ $('#constituency_id').prop('disabled',true).html('<option value="all">All constituencies</option>'); refresh(); return; } populate('{{ route('SuperAdmin.getConstituency') }}',{region_id:this.value},'#constituency_id','All constituencies').always(refresh); });
        $('#constituency_id').on('change',function(){ $('#polling_station_id').prop('disabled',true).html('<option value="all">All polling stations</option>'); if(this.value==='all'){ $('#electoral_area_id').prop('disabled',true).html('<option value="all">All electoral areas</option>'); refresh(); return; } populate('{{ route('SuperAdmin.getElectral') }}',{constituency_id:this.value},'#electoral_area_id','All electoral areas').always(refresh); });
        $('#electoral_area_id').on('change',function(){ if(this.value==='all'){ $('#polling_station_id').prop('disabled',true).html('<option value="all">All polling stations</option>'); refresh(); return; } populate('{{ route('SuperAdmin.getPollingStation') }}',{electoralarea_id:this.value},'#polling_station_id','All polling stations').always(refresh); });
        $('#polling_station_id').on('change',refresh);
        $('#resetFilters').on('click',function(){ $('#region_id').val('all'); $('#constituency_id,#electoral_area_id,#polling_station_id').prop('disabled',true).html('<option value="all">All</option>'); refresh(); });
        render(rows); updateScope();
    })();
</script>
@endsection
