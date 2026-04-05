@extends('layouts.app_director')

@section('content')
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Valid Votes</span>
            <div class="director-kpi-value" id="kpi_valid_votes">{{ number_format($dashboardStats['total_valid_votes']) }}</div>
            <span class="director-kpi-sub">Current aggregated valid votes</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Rejected Ballots</span>
            <div class="director-kpi-value text-warning" id="kpi_rejected_votes">{{ number_format($dashboardStats['total_rejected_votes']) }}</div>
            <span class="director-kpi-sub">Rejected ballot load</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Pink Sheet Rate</span>
            <div class="director-kpi-value" id="kpi_pink_rate">{{ $dashboardStats['pink_sheet_rate'] }}%</div>
            <span class="director-kpi-sub">Submission evidence completeness</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Dashboard Refresh</span>
            <div class="director-kpi-value text-info" id="dashboard_refresh_status">Live</div>
            <span class="director-kpi-sub" id="dashboard_last_refresh">Updated {{ now()->format('H:i:s') }}</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Submission vs Confirmation Trend (14 Days)</h3>
                <span class="director-chip director-chip-primary">Line Chart</span>
            </div>
            <div class="card-body">
                <canvas id="trendDailyChart" height="100"></canvas>
            </div>
        </div>

        <div class="card director-card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Top Polling Stations by Valid Votes</h3>
                <span class="director-chip director-chip-success">Bar Chart</span>
            </div>
            <div class="card-body">
                <canvas id="topStationsChart" height="110"></canvas>
            </div>
        </div>

        <div class="card director-card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Parliamentary Results (Constituency)</h3>
                <span class="director-chip director-chip-primary">Full Graph</span>
            </div>
            <div class="card-body">
                <div style="height: 420px; position: relative; overflow: hidden;">
                    <canvas id="parliamentaryResultsChart" style="height:100% !important;"></canvas>
                </div>
                <div id="parliamentary_results_list" class="mt-3"></div>
            </div>
        </div>

        <div class="card director-card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Election Type Performance Matrix</h3>
                <span class="director-chip director-chip-warning">Operational Table</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Election Type</th>
                                <th>Submissions</th>
                                <th>Confirmations</th>
                                <th>Valid Votes</th>
                                <th>Coverage</th>
                            </tr>
                        </thead>
                        <tbody id="election_type_perf_body">
                            @forelse ($electionTypePerformance as $type)
                                <tr>
                                    <td>
                                        <strong>{{ $type->election_type_name }}</strong>
                                        <div class="text-muted small">Confirmation: {{ $type->confirmation_rate }}%</div>
                                    </td>
                                    <td>{{ number_format($type->submissions) }}</td>
                                    <td>{{ number_format($type->confirmations) }}</td>
                                    <td>{{ number_format($type->valid_votes) }}</td>
                                    <td style="min-width:180px;">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>{{ $type->completion }}%</span>
                                            <span>of polling stations</span>
                                        </div>
                                        <div class="director-progress">
                                            <span style="width: {{ min(100, max(0, $type->completion)) }}%;"></span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No election performance data available yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Result Status Distribution</h3>
                <span class="director-chip director-chip-danger">Doughnut</span>
            </div>
            <div class="card-body">
                <canvas id="statusBreakdownChart" height="220"></canvas>
            </div>
        </div>

        <div class="card director-card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Startup Progress Ladder</h3>
                <span class="director-chip director-chip-success">Coverage</span>
            </div>
            <div class="card-body" id="startup_progress_body">
                @forelse ($startupPerformance as $startup)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <strong>{{ $startup->election_name }}</strong>
                                <div class="text-muted small">{{ $startup->election_type_name }}</div>
                            </div>
                            <div class="text-right">
                                <span class="director-chip director-chip-warning mr-1">S: {{ number_format($startup->submissions) }}</span>
                                <span class="director-chip director-chip-success">C: {{ number_format($startup->confirmations) }}</span>
                            </div>
                        </div>
                        <div class="small text-muted mb-1">Coverage {{ $startup->coverage }}% | Confirmation {{ $startup->confirmation_rate }}%</div>
                        <div class="director-progress">
                            <span style="width: {{ min(100, max(0, $startup->coverage)) }}%;"></span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No active election startups configured.</p>
                @endforelse
            </div>
        </div>

        <div class="card director-card mt-3">
            <div class="card-header">
                <h3 class="card-title">Action Center</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Keep the constituency reporting pipeline moving using the priority actions below.</p>
                <a href="{{ route('Director.Result') }}" class="btn btn-primary btn-sm mr-2 mb-2">
                    <i class="fas fa-chart-line mr-1"></i>Open Live Results
                </a>
                <a href="{{ route('Director.election') }}" class="btn btn-success btn-sm mb-2">
                    <i class="fas fa-vote-yea mr-1"></i>Capture / Edit Result
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const dashboardDataUrl = "{{ route('Director.dashboardData') }}";
    const fallbackCandidateAvatar = "{{ asset('AdminLTE/dist/img/avatar5.png') }}";
    const fallbackPartyLogo = "{{ asset('AdminLTE/dist/img/AdminLTELogo.png') }}";
    const chartDefaults = {
        plugins: { legend: { labels: { boxWidth: 10, usePointStyle: true } } },
        maintainAspectRatio: false
    };

    let statusChart = null;
    let trendChart = null;
    let stationsChart = null;
    let parliamentaryChart = null;

    function makeOrUpdateChart(instance, elementId, config) {
        if (instance) {
            instance.data = config.data;
            instance.options = config.options;
            instance.update();
            return instance;
        }
        const ctx = document.getElementById(elementId);
        return new Chart(ctx, config);
    }

    function renderElectionTypePerformance(rows) {
        const tbody = document.getElementById('election_type_perf_body');
        if (!tbody) return;
        if (!rows || !rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No election performance data available yet.</td></tr>';
            return;
        }
        let html = '';
        rows.forEach(function(row) {
            const completion = Math.max(0, Math.min(100, Number(row.completion || 0)));
            html += `
                <tr>
                    <td>
                        <strong>${row.election_type_name}</strong>
                        <div class="text-muted small">Confirmation: ${row.confirmation_rate}%</div>
                    </td>
                    <td>${Number(row.submissions || 0).toLocaleString()}</td>
                    <td>${Number(row.confirmations || 0).toLocaleString()}</td>
                    <td>${Number(row.valid_votes || 0).toLocaleString()}</td>
                    <td style="min-width:180px;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>${completion}%</span>
                            <span>of polling stations</span>
                        </div>
                        <div class="director-progress">
                            <span style="width:${completion}%;"></span>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function renderStartupProgress(rows) {
        const container = document.getElementById('startup_progress_body');
        if (!container) return;
        if (!rows || !rows.length) {
            container.innerHTML = '<p class="text-muted mb-0">No active election startups configured.</p>';
            return;
        }
        let html = '';
        rows.forEach(function(startup) {
            const coverage = Math.max(0, Math.min(100, Number(startup.coverage || 0)));
            html += `
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <strong>${startup.election_name}</strong>
                            <div class="text-muted small">${startup.election_type_name}</div>
                        </div>
                        <div class="text-right">
                            <span class="director-chip director-chip-warning mr-1">S: ${Number(startup.submissions || 0).toLocaleString()}</span>
                            <span class="director-chip director-chip-success">C: ${Number(startup.confirmations || 0).toLocaleString()}</span>
                        </div>
                    </div>
                    <div class="small text-muted mb-1">Coverage ${coverage}% | Confirmation ${startup.confirmation_rate}%</div>
                    <div class="director-progress">
                        <span style="width:${coverage}%;"></span>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function renderParliamentaryList(rows) {
        const container = document.getElementById('parliamentary_results_list');
        if (!container) return;
        if (!rows || !rows.length) {
            container.innerHTML = '<p class="text-muted mb-0">No confirmed parliamentary results yet.</p>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>#</th><th>Candidate</th><th>Party</th><th class="text-right">Votes</th></tr></thead><tbody>';
        rows.forEach(function (row, index) {
            const candidatePhoto = row.candidate_photo_url || fallbackCandidateAvatar;
            const partyLogo = row.party_logo_url || fallbackPartyLogo;
            html += `
                <tr>
                    <td class="align-middle">${index + 1}</td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <img src="${candidatePhoto}" alt="${row.candidate_name}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid #ddd;" onerror="this.src='${fallbackCandidateAvatar}'">
                            <div class="ml-2">
                                <strong>${row.candidate_name}</strong>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <img src="${partyLogo}" alt="${row.party_initial}" style="width:26px;height:26px;border-radius:4px;object-fit:cover;border:1px solid #ddd;" onerror="this.src='${fallbackPartyLogo}'">
                            <span class="ml-2">${row.party_initial}</span>
                        </div>
                    </td>
                    <td class="text-right align-middle"><strong>${Number(row.votes || 0).toLocaleString()}</strong></td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function renderDashboard(data) {
        const stats = data.dashboardStats || {};
        document.getElementById('kpi_valid_votes').textContent = Number(stats.total_valid_votes || 0).toLocaleString();
        document.getElementById('kpi_rejected_votes').textContent = Number(stats.total_rejected_votes || 0).toLocaleString();
        document.getElementById('kpi_pink_rate').textContent = `${Number(stats.pink_sheet_rate || 0)}%`;
        document.getElementById('dashboard_last_refresh').textContent = `Updated ${new Date().toLocaleTimeString()}`;

        const status = data.statusBreakdown || { confirmed: 0, pending: 0, pink_sheet_missing: 0 };
        statusChart = makeOrUpdateChart(statusChart, 'statusBreakdownChart', {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Missing Pink Sheet'],
                datasets: [{
                    data: [status.confirmed || 0, status.pending || 0, status.pink_sheet_missing || 0],
                    backgroundColor: ['#16a34a', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: Object.assign({}, chartDefaults, {
                cutout: '64%',
                plugins: { legend: { position: 'bottom' } }
            })
        });

        const trend = data.trendDaily || { labels: [], submissions: [], confirmations: [] };
        trendChart = makeOrUpdateChart(trendChart, 'trendDailyChart', {
            type: 'line',
            data: {
                labels: trend.labels || [],
                datasets: [
                    {
                        label: 'Submissions',
                        data: trend.submissions || [],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    },
                    {
                        label: 'Confirmations',
                        data: trend.confirmations || [],
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    }
                ]
            },
            options: Object.assign({}, chartDefaults, {
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { position: 'top' } }
            })
        });

        const top = data.topStationsChart || { labels: [], valid_votes: [] };
        stationsChart = makeOrUpdateChart(stationsChart, 'topStationsChart', {
            type: 'bar',
            data: {
                labels: top.labels || [],
                datasets: [{
                    label: 'Valid Votes',
                    data: top.valid_votes || [],
                    backgroundColor: '#0ea5e9',
                    borderRadius: 6
                }]
            },
            options: Object.assign({}, chartDefaults, {
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            })
        });

        const parliamentary = data.parliamentaryResultsChart || { labels: [], votes: [] };
        parliamentaryChart = makeOrUpdateChart(parliamentaryChart, 'parliamentaryResultsChart', {
            type: 'bar',
            data: {
                labels: parliamentary.labels || [],
                datasets: [{
                    label: 'Confirmed Votes',
                    data: parliamentary.votes || [],
                    backgroundColor: '#16a34a',
                    borderRadius: 6,
                    maxBarThickness: 38
                }]
            },
            options: Object.assign({}, chartDefaults, {
                scales: {
                    y: { beginAtZero: true },
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 14,
                            maxRotation: 50,
                            minRotation: 30
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Votes: ${Number(context.parsed.y || 0).toLocaleString()}`;
                            }
                        }
                    }
                }
            })
        });
        renderParliamentaryList(parliamentary.rows || []);

        renderElectionTypePerformance(data.electionTypePerformance || []);
        renderStartupProgress(data.startupPerformance || []);
    }

    function loadDashboardData() {
        fetch(dashboardDataUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                renderDashboard(data);
            })
            .catch(function() {
                document.getElementById('dashboard_refresh_status').textContent = 'Retrying';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initial = {
            dashboardStats: @json($dashboardStats),
            electionTypePerformance: @json($electionTypePerformance),
            startupPerformance: @json($startupPerformance),
            statusBreakdown: @json($statusBreakdown),
            trendDaily: @json($trendDaily),
            topStationsChart: @json($topStationsChart),
            parliamentaryResultsChart: @json($parliamentaryResultsChart)
        };
        renderDashboard(initial);
        setInterval(loadDashboardData, 20000);
    });
</script>
@endsection
