@php
    $dashboardTypeLabel = $dashboardTypeLabel ?? 'Live Results';
    $dashboardTitle = $dashboardTitle ?? 'Live Statistical Results';
    $dashboardSubtitle = $dashboardSubtitle ?? 'Real-time update stream';
    $themeGradientStart = $themeGradientStart ?? '#0a2a38';
    $themeGradientEnd = $themeGradientEnd ?? '#154734';
    $themeAccent = $themeAccent ?? '#22c55e';
    $themeHighlight = $themeHighlight ?? '#facc15';
    $themeAccentSoft = $themeAccentSoft ?? '#86efac';
    $activePage = $activePage ?? 'parliament';
    $appLogo = isset($config['logo']) ? asset($config['logo']) : 'https://www.ndcresults.live/img/logo.png';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $dashboardTitle }} - NDC EMS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="https://www.ndcresults.live/img/logo.png" type="image/png" sizes="16x16">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css?v=4.001">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        :root { --start: {{ $themeGradientStart }}; --end: {{ $themeGradientEnd }}; --accent: {{ $themeAccent }}; --accent-soft: {{ $themeAccentSoft }}; --highlight: {{ $themeHighlight }}; --border: rgba(148,163,184,.25); --txt:#e2e8f0; --muted:#94a3b8; --card:rgba(15,23,42,.72); }
        body { margin: 0; min-height: 100vh; color: var(--txt); font-family: "Sora", sans-serif; background: radial-gradient(circle at 20% 5%, rgba(250,204,21,.2), transparent 35%), linear-gradient(135deg, var(--start), var(--end)); background-attachment: fixed; }
        .shell { max-width: 1240px; margin: 0 auto; padding: 14px; }
        .top-nav { position: sticky; top: 0; z-index: 999; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; border: 1px solid var(--border); border-radius: 14px; padding: 10px 12px; background: rgba(2,6,23,.83); backdrop-filter: blur(8px); }
        .brand { display:flex; align-items:center; gap: 10px; }
        .brand img { width: 62px; height: 42px; object-fit: contain; border-radius: 8px; background: #fff; padding: 4px; }
        .brand strong { display:block; font-family: "Space Grotesk", sans-serif; letter-spacing: .04em; text-transform: uppercase; font-size: 14px; }
        .brand span { display:block; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        .nav-links { display:flex; gap: 8px; flex-wrap: wrap; }
        .pill { border: 1px solid var(--border); border-radius: 999px; padding: 8px 13px; color: var(--txt); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; background: rgba(15,23,42,.75); }
        .pill:hover, .pill:focus, .pill.active { color: #0b1120; text-decoration: none; background: linear-gradient(120deg, var(--highlight), var(--accent)); border-color: transparent; }
        .hero { margin-top: 12px; border: 1px solid var(--border); border-radius: 18px; padding: 20px; background: linear-gradient(120deg, rgba(15,23,42,.86), rgba(15,23,42,.58)); box-shadow: 0 14px 30px rgba(2,6,23,.35); }
        .tag { display: inline-flex; align-items: center; gap: 7px; border: 1px solid var(--border); border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 700; color: var(--accent-soft); letter-spacing: .08em; text-transform: uppercase; }
        .tag-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 5px rgba(34,197,94,.2); animation: pulse 1.6s infinite; }
        @keyframes pulse { 0%,100% { transform: scale(.9); opacity: .8; } 60% { transform: scale(1.12); opacity: 1; } }
        .hero h1 { margin: 10px 0 8px; font-family: "Space Grotesk", sans-serif; font-size: 30px; color: #f8fafc; }
        .hero p { margin: 0; color: #cbd5e1; font-size: 14px; }
        .updated { margin-top: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; font-size: 11px; }
        .stats { margin-top: 15px; display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 10px; }
        .stat { border: 1px solid var(--border); border-radius: 12px; padding: 12px; background: rgba(15,23,42,.58); }
        .stat .label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .07em; font-weight: 700; }
        .stat .value { font-size: 28px; font-family: "Space Grotesk", sans-serif; font-weight: 700; margin-top: 3px; color: #f8fafc; }
        .stat .note { margin-top: 5px; font-size: 11px; color: #cbd5e1; }
        .track { margin-top: 8px; height: 9px; border-radius: 999px; background: rgba(148,163,184,.35); overflow: hidden; }
        .track > span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, var(--highlight), var(--accent)); transition: width .35s ease; }
        .panel { margin-top: 12px; border: 1px solid var(--border); border-radius: 14px; padding: 14px; background: var(--card); }
        .panel-title { margin: 0 0 10px; color: var(--accent-soft); text-transform: uppercase; font-size: 12px; letter-spacing: .08em; font-weight: 700; }
        .filters { display:grid; gap: 10px; grid-template-columns: repeat(5, minmax(0,1fr)); }
        .filter-input { width: 100%; height: 42px; border-radius: 10px; border: 1px solid rgba(148,163,184,.35); background: rgba(15,23,42,.92); color: #f8fafc; padding: 0 9px; font-size: 12px; }
        .leaders { display:grid; gap: 10px; grid-template-columns: repeat(3, minmax(0,1fr)); }
        .leader { border:1px solid var(--border); border-radius:12px; padding: 12px; background: rgba(15,23,42,.64); }
        .leader .top { display:flex; justify-content: space-between; align-items: center; }
        .badge-rank { border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 700; background: rgba(30,41,59,.9); }
        .leader .pct { font-family: "Space Grotesk", sans-serif; font-size: 16px; font-weight: 700; }
        .leader .name { margin-top: 7px; font-weight: 700; font-size: 15px; color: #f8fafc; }
        .leader .meta { font-size: 11px; color: #cbd5e1; text-transform: uppercase; letter-spacing: .05em; }
        .leader .votes { margin-top: 6px; font-size: 12px; color: var(--accent-soft); font-weight: 700; }
        .leader .meter { margin-top: 7px; height: 6px; border-radius: 999px; background: rgba(148,163,184,.3); overflow: hidden; }
        .leader .meter > span { display:block; height:100%; background: linear-gradient(90deg, var(--highlight), var(--accent)); transition: width .35s ease; }
        .table-wrap { overflow-x: auto; }
        table.results { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 760px; }
        table.results thead th { color: var(--muted); text-transform: uppercase; font-size: 11px; letter-spacing: .08em; padding: 11px 10px; border-bottom: 1px solid var(--border); }
        table.results tbody td { padding: 12px 10px; border-bottom: 1px solid rgba(148,163,184,.2); color: #f8fafc; font-size: 13px; vertical-align: middle; }
        .rank { display:inline-flex; align-items:center; justify-content:center; min-width: 38px; height:24px; border-radius: 999px; background: rgba(30,41,59,.95); font-size: 11px; font-weight: 700; }
        .cand { display:flex; align-items:center; gap: 10px; }
        .cand img { width:44px; height:44px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(148,163,184,.45); background: #1e293b; }
        .cand strong { display:block; color: #f8fafc; font-size: 13px; }
        .cand span { display:block; color: #cbd5e1; font-size: 11px; margin-top: 2px; }
        .votes { font-family: "Space Grotesk", sans-serif; font-size: 20px; font-weight: 700; color: #fef08a; }
        .share .value { display:inline-block; min-width: 57px; font-weight: 700; }
        .share .bar { display:inline-block; width: calc(100% - 60px); height: 8px; border-radius: 999px; background: rgba(148,163,184,.3); overflow: hidden; vertical-align: middle; margin-left: 3px; }
        .share .bar > span { display: block; height: 100%; background: linear-gradient(90deg, var(--highlight), var(--accent)); transition: width .35s ease; }
        .empty { text-align:center; padding: 24px 10px; color: #cbd5e1; text-transform: uppercase; letter-spacing: .06em; font-size: 12px; }
        .footer { margin-top: 14px; text-align: center; font-size: 12px; color: #cbd5e1; }
        .footer a { color: var(--accent-soft); }
        @media (max-width: 1100px) { .stats { grid-template-columns: repeat(2,minmax(0,1fr)); } .filters { grid-template-columns: repeat(2,minmax(0,1fr)); } .leaders { grid-template-columns: repeat(2,minmax(0,1fr)); } }
        @media (max-width: 760px) { .hero h1 { font-size: 24px; } .stats, .filters, .leaders { grid-template-columns: 1fr; } .nav-links { width: 100%; } .pill { flex: 1; text-align: center; } }
    </style>
</head>
<body>
<div class="shell">
    <div class="top-nav">
        <div class="brand">
            <a href="{{ route('landing') }}"><img src="{{ $appLogo }}" alt="NDC logo"></a>
            <div>
                <strong>NDC Election Monitoring</strong>
                <span>Live Results Transfer System</span>
            </div>
        </div>
        <div class="nav-links">
            <a href="{{ route('parliament') }}" class="pill {{ $activePage === 'parliament' ? 'active' : '' }}">Parliament</a>
            <a href="{{ route('president') }}" class="pill {{ $activePage === 'president' ? 'active' : '' }}">President</a>
            <a href="{{ route('login') }}" class="pill">Login</a>
        </div>
    </div>
    <section class="hero">
        <span class="tag"><span class="tag-dot"></span>{{ $dashboardTypeLabel }}</span>
        <h1>{{ $dashboardTitle }}</h1>
        <p>{{ $dashboardSubtitle }}</p>
        <div class="updated">Last Updated: <span id="last_updated_at">{{ now()->format('d M Y H:i:s') }} UTC</span></div>
        <div class="stats">
            <div class="stat">
                <div class="label">Confirmed Polling Stations</div>
                <div class="value" id="polling_count">{{ $polling_count }}</div>
                <div class="note">Confirmed by constituency directors</div>
            </div>
            <div class="stat">
                <div class="label">Total Polling Stations</div>
                <div class="value" id="all_polling_count">{{ $all_polling_count }}</div>
                <div class="note">Current reporting universe</div>
            </div>
            <div class="stat">
                <div class="label">Coverage Ratio</div>
                <div class="value" id="coverage_percent">0.00%</div>
                <div class="track"><span id="coverage_progress"></span></div>
            </div>
            <div class="stat">
                <div class="label">Active Election Sets</div>
                <div class="value">{{ count($electionStartupDetail) }}</div>
                <div class="note">Live election contexts available</div>
            </div>
        </div>
    </section>
    <section class="panel">
        <h2 class="panel-title">Filters</h2>
        <div class="filters">
            <select id="election_start_up_id" class="filter-input filter">
                <option value="all">All Election Sets</option>
                @foreach ($electionStartupDetail as $electionStartup)
                    <option value="{{ $electionStartup->id }}" {{ $electionStartup->id == $id ? 'selected' : '' }}>{{ $electionStartup->election_name }} - {{ $electionStartup->name }}</option>
                @endforeach
            </select>
            <select class="filter-input filter" id="region_id"><option value="all">All Regions</option>@foreach ($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach</select>
            <select class="filter-input filter" id="constituency_id"><option value="all">All Constituencies</option></select>
            <select class="filter-input filter" id="electoralarea_id"><option value="all">All Electoral Areas</option></select>
            <select class="filter-input filter" id="polling_station_id"><option value="all">All Polling Stations</option></select>
        </div>
    </section>
    <section class="panel">
        <h2 class="panel-title">Top Performers</h2>
        <div class="leaders" id="leaderboard_container"></div>
    </section>
    <section class="panel">
        <h2 class="panel-title">Ranked Results Table</h2>
        <div class="table-wrap">
            <table class="results">
                <thead>
                <tr><th style="width:72px;">Rank</th><th>Candidate</th><th style="width:220px;">Party</th><th style="width:170px;">Votes</th><th style="width:250px;">Vote Share</th></tr>
                </thead>
                <tbody id="result_table_body"></tbody>
            </table>
        </div>
    </section>
    <div class="footer">2026 Copyright: <a href="https://facebook.com/ekowmenzah" target="_blank">Ekow - AOB NDC</a></div>
</div>
<script>
    (function () {
        const state = {
            fixedElectionStartupId: @json($id ?: ''),
            newElectionType: @json($newElectionType),
            csrfToken: @json(csrf_token()),
            candidatePhotoBase: @json(asset('candidate_logo')),
            fallbackAvatar: @json($appLogo),
            initialData: @json($allElectionResults)
        };
        const routes = {
            ajaxResult: @json(route('ajaxResult')),
            ajaxCountResult: @json(route('ajaxCountResult')),
            constituency: @json(route('getConstituency')),
            electral: @json(route('getElectral')),
            pollingStation: @json(route('getPollingStation'))
        };
        function esc(text) { return $('<div>').text(text == null ? '' : text).html(); }
        function num(n) { return Number(n || 0).toLocaleString('en-US'); }
        function pct(n) { return Number(n || 0).toFixed(2) + '%'; }
        function name(item) { return [item.first_name || '', item.last_name || ''].join(' ').trim() || 'Not Available'; }
        function photo(file) { return file ? state.candidatePhotoBase + '/' + encodeURIComponent(file) : state.fallbackAvatar; }
        function payload() {
            return {
                election_type_id: state.fixedElectionStartupId || '',
                newElectionType: state.newElectionType,
                election_start_up_id: $('#election_start_up_id').val() || 'all',
                region_id: $('#region_id').val() || 'all',
                constituency_id: $('#constituency_id').val() || 'all',
                electoralarea_id: $('#electoralarea_id').val() || 'all',
                polling_station_id: $('#polling_station_id').val() || 'all'
            };
        }
        function updateClock() {
            const now = new Date();
            $('#last_updated_at').text(now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-GB', { hour12: false }) + ' UTC');
        }
        function updateCoverage(confirmed, total) {
            const c = Number(confirmed || 0); const t = Number(total || 0); const p = t > 0 ? (c / t) * 100 : 0;
            $('#coverage_percent').text(p.toFixed(2) + '%');
            $('#coverage_progress').css('width', Math.min(100, Math.max(0, p)) + '%');
        }
        function renderLeaders(data) {
            const box = $('#leaderboard_container'); box.empty();
            if (!Array.isArray(data) || data.length === 0) { box.append('<div class="empty" style="grid-column:1/-1;">No results captured yet.</div>'); return; }
            data.slice(0, 3).forEach(function (item, i) {
                const per = Number(item.percentage || 0); const meter = Math.min(100, Math.max(2, per));
                box.append('<article class="leader"><div class="top"><span class="badge-rank">#' + (i + 1) + '</span><span class="pct">' + pct(per) + '</span></div><div class="name">' + esc(name(item)) + '</div><div class="meta">' + esc(item.political_party_name || 'Unknown Party') + ' (' + esc(item.party_initial || 'N/A') + ')</div><div class="votes">' + num(item.party_election_result_obtained_vote) + ' Votes</div><div class="meter"><span style="width:' + meter + '%;"></span></div></article>');
            });
        }
        function renderTable(data) {
            const body = $('#result_table_body'); body.empty();
            if (!Array.isArray(data) || data.length === 0) { body.append('<tr><td colspan="5" class="empty">No results available for selected filters.</td></tr>'); return; }
            data.forEach(function (item, i) {
                const per = Number(item.percentage || 0); const meter = Math.min(100, Math.max(2, per)); const p = esc(photo(item.photo)); const fallback = esc(state.fallbackAvatar);
                body.append('<tr><td><span class="rank">#' + (i + 1) + '</span></td><td><div class="cand"><img src="' + p + '" alt="' + esc(name(item)) + '" onerror="this.src=\'' + fallback + '\';"><div><strong>' + esc(name(item)) + '</strong><span>Candidate profile</span></div></div></td><td>' + esc(item.political_party_name || 'Unknown Party') + ' (' + esc(item.party_initial || 'N/A') + ')</td><td class="votes">' + num(item.party_election_result_obtained_vote) + '</td><td class="share"><span class="value">' + pct(per) + '</span><span class="bar"><span style="width:' + meter + '%;"></span></span></td></tr>');
            });
        }
        function post(url, data) { return $.ajax({ url: url, type: 'POST', data: data, headers: { 'X-CSRF-TOKEN': state.csrfToken } }); }
        function refresh() {
            post(routes.ajaxResult, payload()).done(function (data) { renderLeaders(data); renderTable(data); updateClock(); });
            post(routes.ajaxCountResult, payload()).done(function (data) { const c = data && data.polling_count ? data.polling_count : 0; const t = data && data.all_polling_count ? data.all_polling_count : 0; $('#polling_count').text(c); $('#all_polling_count').text(t); updateCoverage(c, t); });
        }
        function fillSelect($select, defaultLabel, records) {
            $select.empty().append($('<option>').val('all').text(defaultLabel));
            if (!Array.isArray(records)) { return; }
            records.forEach(function (item) { if (item && item.id && item.name) { $select.append($('<option>').val(item.id).text(item.name)); } });
        }
        function loadConstituencies(regionId) {
            if (!regionId || regionId === 'all') { fillSelect($('#constituency_id'), 'All Constituencies', []); fillSelect($('#electoralarea_id'), 'All Electoral Areas', []); fillSelect($('#polling_station_id'), 'All Polling Stations', []); return; }
            post(routes.constituency, { region_id: regionId }).done(function (result) { fillSelect($('#constituency_id'), 'All Constituencies', result); fillSelect($('#electoralarea_id'), 'All Electoral Areas', []); fillSelect($('#polling_station_id'), 'All Polling Stations', []); });
        }
        function loadElectoral(constituencyId) {
            if (!constituencyId || constituencyId === 'all') { fillSelect($('#electoralarea_id'), 'All Electoral Areas', []); fillSelect($('#polling_station_id'), 'All Polling Stations', []); return; }
            post(routes.electral, { constituency_id: constituencyId }).done(function (result) { fillSelect($('#electoralarea_id'), 'All Electoral Areas', result); fillSelect($('#polling_station_id'), 'All Polling Stations', []); });
        }
        function loadPolling(electoralareaId) {
            if (!electoralareaId || electoralareaId === 'all') { fillSelect($('#polling_station_id'), 'All Polling Stations', []); return; }
            post(routes.pollingStation, { electoralarea_id: electoralareaId }).done(function (result) { fillSelect($('#polling_station_id'), 'All Polling Stations', result); });
        }
        $(document).ready(function () {
            renderLeaders(state.initialData); renderTable(state.initialData); updateCoverage($('#polling_count').text(), $('#all_polling_count').text());
            $('#region_id').on('change', function () { loadConstituencies($(this).val()); refresh(); });
            $('#constituency_id').on('change', function () { loadElectoral($(this).val()); refresh(); });
            $('#electoralarea_id').on('change', function () { loadPolling($(this).val()); refresh(); });
            $('#election_start_up_id, #polling_station_id').on('change', refresh);
            refresh();
            setInterval(refresh, 3000);
        });
    })();
</script>
</body>
</html>
