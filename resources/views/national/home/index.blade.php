@extends('layouts.app_national_director')

@section('page_title', 'National Dashboard')
@section('page_description', 'A live command center for national reporting coverage, party share, regional performance, and election operating context.')
@section('page_badges')
    <span class="national-badge"><i class="fas fa-sync-alt"></i> Auto refresh every 15s</span>
@endsection

@section('css')
<style>
    .command-map-card{overflow:hidden;border:0;border-radius:20px;background:#fff;box-shadow:0 14px 34px rgba(14,38,58,.09)}
    .command-map-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;padding:20px 22px;border-bottom:1px solid #e8eeec;background:linear-gradient(115deg,#102b45,#124b52)}
    .command-map-head h3{margin:0;color:#fff;font-size:19px;font-weight:800}.command-map-head p{margin:4px 0 0;color:#c9dedb;font-size:12px}
    .map-metrics{display:flex;gap:5px;padding:4px;border-radius:12px;background:rgba(255,255,255,.11)}.map-metric{border:0;border-radius:9px;padding:8px 11px;background:transparent;color:#dbe9e7;font-size:11px;font-weight:700;cursor:pointer}.map-metric.active{background:#fff;color:#123b4d}
    .command-map-body{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(270px,.7fr);min-height:520px}.map-stage{position:relative;display:flex;align-items:center;justify-content:center;min-height:520px;padding:18px;background:radial-gradient(circle at 35% 30%,#f8fcfa,#e9f1ef 72%)}
    #ghanaOperationsMap{width:100%;height:480px;filter:drop-shadow(0 9px 12px rgba(23,52,62,.13))}.ghana-region{stroke:#fff;stroke-width:1.4;vector-effect:non-scaling-stroke;cursor:pointer;transition:fill .25s,opacity .2s,stroke-width .2s}.ghana-region:hover,.ghana-region.is-selected{opacity:.88;stroke:#102b45;stroke-width:3}
    .map-tooltip{position:absolute;z-index:4;display:none;pointer-events:none;padding:9px 11px;border-radius:9px;background:#102b45;color:#fff;box-shadow:0 8px 18px rgba(0,0,0,.18);font-size:11px}.map-empty{color:#6f7f88;text-align:center}.map-detail{padding:23px;border-left:1px solid #e5ece9;background:#fff}.map-detail__eyebrow{display:block;color:#087f5b;font-size:9px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.map-detail h4{margin:5px 0 4px;color:#102b45;font-size:22px;font-weight:800}.map-detail__leader{margin-bottom:17px;color:#667781;font-size:12px}.map-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}.map-detail-stat{padding:12px;border-radius:11px;background:#f2f7f5}.map-detail-stat span{display:block;color:#70818a;font-size:8px;font-weight:800;text-transform:uppercase}.map-detail-stat strong{display:block;margin-top:3px;color:#102b45;font-size:17px}.map-alert{margin-top:13px;padding:11px;border-radius:10px;background:#fff5df;color:#7b5914;font-size:11px}.map-legend{display:flex;align-items:center;gap:7px;margin-top:17px;color:#6f7f88;font-size:10px}.map-legend i{display:inline-block;width:25px;height:7px;border-radius:99px}.map-attribution{margin-top:15px;color:#8a989f;font-size:9px}.map-attribution a{color:#537a76}
    @media(max-width:900px){.command-map-body{grid-template-columns:1fr}.map-detail{border-top:1px solid #e5ece9;border-left:0}.map-stage{min-height:430px}#ghanaOperationsMap{height:400px}}
    @media(max-width:560px){.command-map-head{align-items:flex-start}.map-metrics{width:100%;overflow:auto}.map-metric{white-space:nowrap}.map-stage{min-height:360px;padding:8px}#ghanaOperationsMap{height:340px}}
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['reporting_stations'] ?? 0) }}</h3>
                    <p>Reporting Polling Stations</p>
                </div>
                <div class="icon"><i class="fas fa-broadcast-tower"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($summary['reporting_percentage'] ?? 0, 1) }}%</h3>
                    <p>National Reporting Coverage</p>
                </div>
                <div class="icon"><i class="fas fa-signal"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($summary['total_ballots'] ?? 0) }}</h3>
                    <p>Total Ballots Captured</p>
                </div>
                <div class="icon"><i class="fas fa-vote-yea"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($summary['rejected_ballot_rate'] ?? 0, 1) }}%</h3>
                    <p>Rejected Ballot Rate</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <section class="command-map-card" aria-labelledby="commandMapTitle">
                <header class="command-map-head">
                    <div><h3 id="commandMapTitle"><i class="fas fa-map-marked-alt mr-2"></i>National Operations Map</h3><p>Geographic reporting readiness, constituency approval posture, and incident pressure by region.</p></div>
                    <div class="map-metrics" role="group" aria-label="Map metric">
                        <button type="button" class="map-metric active" data-map-metric="reporting_percentage">Reporting</button>
                        <button type="button" class="map-metric" data-map-metric="approval_percentage">Approval</button>
                        <button type="button" class="map-metric" data-map-metric="active_incidents">Incidents</button>
                    </div>
                </header>
                <div class="command-map-body">
                    <div class="map-stage" id="mapStage"><svg id="ghanaOperationsMap" role="img" aria-label="Map of Ghana regions colored by reporting coverage"></svg><div class="map-tooltip" id="mapTooltip"></div></div>
                    <aside class="map-detail" id="mapDetail"><div class="map-empty"><i class="fas fa-map-marker-alt fa-2x mb-2"></i><br>Select a region to inspect operations.</div></aside>
                </div>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">National Party Share</h3>
                    <div class="card-tools text-muted">
                        Updated {{ $lastUpdatedAt ?? 'Not available' }}
                    </div>
                </div>
                <div class="card-body">
                    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Active Election Window</h3>
                </div>
                <div class="card-body">
                    <h4 class="mb-2">{{ $activeElection['name'] ?? 'No active election startup' }}</h4>
                    <p class="text-muted mb-2">{{ $activeElection['date_range'] ?? 'Awaiting configuration' }}</p>
                    <span class="badge badge-{{ ($activeElection['status'] ?? '') === 'Active' ? 'success' : 'secondary' }}">
                        {{ $activeElection['status'] ?? 'Inactive' }}
                    </span>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Result Records</span>
                        <strong>{{ number_format($summary['total_results'] ?? 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Rejected Ballots</span>
                        <strong>{{ number_format($summary['total_rejected_ballots'] ?? 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Polling Stations</span>
                        <strong>{{ number_format($summary['total_polling_stations'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('National.Presidential') }}" class="list-group-item list-group-item-action">
                            Review presidential result feed
                        </a>
                        <a href="{{ route('National.ConstituencyResult') }}" class="list-group-item list-group-item-action">
                            Review constituency breakdown
                        </a>
                        <a href="{{ route('National.pollingAgent') }}" class="list-group-item list-group-item-action">
                            Inspect polling agent coverage
                        </a>
                        <a href="{{ route('National.Users') }}" class="list-group-item list-group-item-action">
                            Manage national reporting users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Regional Reporting Snapshot</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                        <tr>
                            <th>Region</th>
                            <th>Reporting</th>
                            <th>Coverage</th>
                            <th>Total Ballots</th>
                            <th>Rejected</th>
                            <th>Registered Voters</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($regionalPerformance as $region)
                            <tr>
                                <td>{{ $region['name'] }}</td>
                                <td>{{ number_format($region['reporting_stations']) }} / {{ number_format($region['total_polling_stations']) }}</td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-success" style="width: {{ $region['reporting_percentage'] }}%"></div>
                                    </div>
                                    <span class="badge badge-light">{{ number_format($region['reporting_percentage'], 1) }}%</span>
                                </td>
                                <td>{{ number_format($region['total_ballots']) }}</td>
                                <td>{{ number_format($region['total_rejected_ballots']) }}</td>
                                <td>{{ number_format($region['total_voters']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No regional reporting data available yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        let nationalDashboardChart;
        const initialChartData = @json($chartDataPoints ?? []);
        let regionalMapData = @json($regionalMapData ?? []);
        let ghanaBoundaries = null;
        let activeMapMetric = 'reporting_percentage';
        let selectedMapRegion = null;
        const mapReviewUrl = @json(route('National.resultReview'));

        function normalizedRegionName(name) {
            return String(name || '').toLowerCase().replace(/\bregion\b/g, '').replace(/[^a-z0-9]/g, '');
        }

        function mapColor(value, metric) {
            value = Number(value || 0);
            if (metric === 'active_incidents') {
                if (value === 0) return '#b9d9c8';
                if (value <= 2) return '#efc46c';
                if (value <= 5) return '#df7c4d';
                return '#b93838';
            }
            if (value <= 0) return '#dbe4e2';
            if (value < 25) return '#c7d9cf';
            if (value < 50) return '#8fc0a6';
            if (value < 75) return '#47a17d';
            return '#087f5b';
        }

        function geometryPoints(geometry) {
            const polygons = geometry.type === 'Polygon' ? [geometry.coordinates] : geometry.coordinates;
            return polygons.flatMap(polygon => polygon.flatMap(ring => ring));
        }

        function renderOperationsMap() {
            if (!ghanaBoundaries) return;
            const svg = document.getElementById('ghanaOperationsMap');
            const width = 720, height = 500, padding = 18;
            svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
            const points = ghanaBoundaries.features.flatMap(feature => geometryPoints(feature.geometry));
            const xs = points.map(point => point[0]), ys = points.map(point => point[1]);
            const minX = Math.min(...xs), maxX = Math.max(...xs), minY = Math.min(...ys), maxY = Math.max(...ys);
            const scale = Math.min((width - padding * 2) / (maxX - minX), (height - padding * 2) / (maxY - minY));
            const offsetX = (width - (maxX - minX) * scale) / 2;
            const offsetY = (height - (maxY - minY) * scale) / 2;
            const project = point => [offsetX + (point[0] - minX) * scale, height - (offsetY + (point[1] - minY) * scale)];
            const byName = Object.fromEntries(regionalMapData.map(region => [normalizedRegionName(region.name), region]));
            const legacyParentByBoundary = {
                ahafo: 'brongahafo', bono: 'brongahafo', bonoeast: 'brongahafo',
                savannah: 'northern', northeast: 'northern',
                oti: 'volta', westernnorth: 'western'
            };

            svg.innerHTML = '';
            ghanaBoundaries.features.forEach(feature => {
                const polygons = feature.geometry.type === 'Polygon' ? [feature.geometry.coordinates] : feature.geometry.coordinates;
                const pathData = polygons.map(polygon => polygon.map(ring => ring.map((point, index) => {
                    const projected = project(point);
                    return `${index ? 'L' : 'M'}${projected[0].toFixed(2)},${projected[1].toFixed(2)}`;
                }).join(' ') + ' Z').join(' ')).join(' ');
                const boundaryName = feature.properties.shapeName;
                const boundaryKey = normalizedRegionName(boundaryName);
                const parentKey = legacyParentByBoundary[boundaryKey];
                const matchedRegion = byName[boundaryKey] || (parentKey ? byName[parentKey] : null);
                const region = matchedRegion ? Object.assign({}, matchedRegion, {
                    boundary_name: boundaryName.replace(/\s+Region$/i, ''),
                    legacy_group: Boolean(parentKey && !byName[boundaryKey])
                }) : null;
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                path.setAttribute('class', `ghana-region${selectedMapRegion && region && selectedMapRegion.id === region.id ? ' is-selected' : ''}`);
                path.setAttribute('fill', region ? mapColor(region[activeMapMetric], activeMapMetric) : '#eef2f1');
                path.setAttribute('tabindex', '0');
                path.setAttribute('aria-label', `${boundaryName}: ${region ? mapMetricText(region) : 'No configured region match'}`);
                path.addEventListener('mouseenter', event => showMapTooltip(event, boundaryName, region));
                path.addEventListener('mousemove', positionMapTooltip);
                path.addEventListener('mouseleave', hideMapTooltip);
                path.addEventListener('click', () => selectMapRegion(region));
                path.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); selectMapRegion(region); } });
                svg.appendChild(path);
            });

            if (!selectedMapRegion && regionalMapData.length) selectMapRegion(regionalMapData[0], false);
        }

        function mapMetricText(region) {
            if (activeMapMetric === 'active_incidents') return `${Number(region.active_incidents || 0).toLocaleString()} active incidents`;
            const label = activeMapMetric === 'approval_percentage' ? 'approval readiness' : 'reporting coverage';
            return `${Number(region[activeMapMetric] || 0).toFixed(1)}% ${label}`;
        }

        function showMapTooltip(event, boundaryName, region) {
            const tooltip = document.getElementById('mapTooltip');
            tooltip.innerHTML = `<strong>${escapeMapHtml(region ? region.boundary_name : boundaryName)}</strong><br>${region ? mapMetricText(region) : 'Not configured in the system'}${region && region.legacy_group ? `<br><em>Uses ${escapeMapHtml(region.name)} legacy grouping</em>` : ''}`;
            tooltip.style.display = 'block';
            positionMapTooltip(event);
        }

        function positionMapTooltip(event) {
            const stage = document.getElementById('mapStage'), tooltip = document.getElementById('mapTooltip');
            const box = stage.getBoundingClientRect();
            tooltip.style.left = `${Math.min(event.clientX - box.left + 12, box.width - tooltip.offsetWidth - 8)}px`;
            tooltip.style.top = `${Math.max(8, event.clientY - box.top - 34)}px`;
        }

        function hideMapTooltip() { document.getElementById('mapTooltip').style.display = 'none'; }
        function escapeMapHtml(value) { const node = document.createElement('div'); node.textContent = String(value || ''); return node.innerHTML; }

        function selectMapRegion(region, rerender = true) {
            if (!region) return;
            selectedMapRegion = region;
            document.getElementById('mapDetail').innerHTML = `
                <span class="map-detail__eyebrow">Regional command brief</span><h4>${escapeMapHtml(region.name)}</h4>
                <div class="map-detail__leader">Approved presidential leader: <strong>${escapeMapHtml(region.leader)}</strong> (${Number(region.leader_votes || 0).toLocaleString()} votes)</div>
                <div class="map-detail-grid">
                    <div class="map-detail-stat"><span>Reporting</span><strong>${Number(region.reporting_percentage || 0).toFixed(1)}%</strong></div>
                    <div class="map-detail-stat"><span>Approval</span><strong>${Number(region.approval_percentage || 0).toFixed(1)}%</strong></div>
                    <div class="map-detail-stat"><span>Stations in</span><strong>${Number(region.reporting_stations || 0).toLocaleString()} / ${Number(region.total_polling_stations || 0).toLocaleString()}</strong></div>
                    <div class="map-detail-stat"><span>Approved</span><strong>${Number(region.approved || 0).toLocaleString()}</strong></div>
                    <div class="map-detail-stat"><span>Pending / rejected</span><strong>${Number(region.pending || 0).toLocaleString()} / ${Number(region.rejected || 0).toLocaleString()}</strong></div>
                    <div class="map-detail-stat"><span>Active incidents</span><strong>${Number(region.active_incidents || 0).toLocaleString()}</strong></div>
                </div>
                ${(Number(region.anomalies || 0) || Number(region.priority_incidents || 0)) ? `<div class="map-alert"><strong>Attention:</strong> ${Number(region.anomalies || 0)} integrity anomalies and ${Number(region.priority_incidents || 0)} priority incidents.</div>` : ''}
                ${region.legacy_group ? `<div class="map-alert"><strong>Boundary notice:</strong> ${escapeMapHtml(region.boundary_name)} is reported under the configured ${escapeMapHtml(region.name)} legacy region. Modernize the region master data before splitting its metrics.</div>` : ''}
                <a class="btn btn-primary btn-sm btn-block mt-3" href="${mapReviewUrl}?region_id=${encodeURIComponent(region.id)}">Open regional review queue</a>
                <div class="map-legend"><span>Low</span><i style="background:linear-gradient(90deg,#dbe4e2,#087f5b)"></i><span>High <span id="mapLegendMetric">readiness</span></span></div>
                <div class="map-attribution">Boundaries: <a href="https://www.geoboundaries.org/" target="_blank" rel="noopener">geoBoundaries</a> (CC BY 4.0), source OpenStreetMap, 2021.</div>`;
            if (rerender) renderOperationsMap();
        }

        function refreshOperationsMap() {
            $.get(@json(route('National.dashboardMap'))).done(function (data) {
                regionalMapData = data;
                if (selectedMapRegion) selectedMapRegion = regionalMapData.find(region => region.id === selectedMapRegion.id) || null;
                renderOperationsMap();
                if (selectedMapRegion) selectMapRegion(selectedMapRegion, false);
            });
        }

        function renderNationalChart(dataPoints) {
            nationalDashboardChart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Current National Party Share"
                },
                axisY: {
                    suffix: "%",
                    maximum: 100
                },
                data: [{
                    type: "column",
                    yValueFormatString: "#,###.##\"%\"",
                    indexLabel: "{y}%",
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "white",
                    dataPoints: dataPoints
                }]
            });

            nationalDashboardChart.render();
        }

        function refreshNationalChart() {
            $.ajax({
                url: "{{ route('National.dashboardChart') }}",
                type: 'GET',
                success: function (dataPoints) {
                    if (!nationalDashboardChart) {
                        renderNationalChart(dataPoints);
                        return;
                    }

                    nationalDashboardChart.options.data[0].dataPoints = dataPoints;
                    nationalDashboardChart.render();
                }
            });
        }

        $(function () {
            renderNationalChart(initialChartData);
            fetch(@json(asset('data/ghana-regions.geojson')))
                .then(response => { if (!response.ok) throw new Error('Boundary data unavailable'); return response.json(); })
                .then(data => { ghanaBoundaries = data; renderOperationsMap(); })
                .catch(() => { document.getElementById('mapStage').innerHTML = '<div class="map-empty">The geographic boundary file could not be loaded.</div>'; });
            $('.map-metric').on('click', function () {
                activeMapMetric = $(this).data('map-metric');
                $('.map-metric').removeClass('active'); $(this).addClass('active');
                renderOperationsMap();
            });
            setInterval(refreshNationalChart, 15000);
            setInterval(refreshOperationsMap, 15000);
        });
    </script>
@endsection
