@extends('layouts.app_national')

@section('content')
    @include('national.home.partials.result-analytics-styles')

    <div class="result-shell">
        @if ($lowCoverageWarning)
            <div class="alert alert-warning mb-0">
                <strong>Partial reporting:</strong> {{ $lowCoverageWarning }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-map-marked-alt"></i> Region</span>
                    <div class="result-kpi__value">{{ $regionDetail->name }}</div>
                    <div class="result-kpi__sub">{{ number_format($presidential['summary']['total_constituencies']) }} constituencies under regional supervision</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-vote-yea"></i> Presidential Coverage</span>
                    <div class="result-kpi__value">{{ $presidential['summary']['constituency_coverage_percentage'] }}%</div>
                    <div class="result-kpi__sub">{{ number_format($presidential['summary']['reporting_constituencies']) }} of {{ number_format($presidential['summary']['total_constituencies']) }} constituencies reporting</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-landmark"></i> Parliamentary Control</span>
                    <div class="result-kpi__value">{{ $parliamentary['summary']['leading_party'] }}</div>
                    <div class="result-kpi__sub">{{ $parliamentary['summary']['leading_party_name'] }}{{ $parliamentary['summary']['leading_party_votes'] ? ' with '.number_format($parliamentary['summary']['leading_party_votes']).' decided constituencies' : '' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-check-double"></i> Verified Valid Votes</span>
                    <div class="result-kpi__value">{{ number_format($presidential['summary']['total_valid_votes']) }}</div>
                    <div class="result-kpi__sub">Presidential verified valid votes in the active startup context</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Regional Presidential Share</h3>
                            <div class="result-panel__sub">Current verified presidential vote share across the region.</div>
                        </div>
                        <span class="result-chip"><i class="fas fa-chart-pie"></i> Live snapshot</span>
                    </div>
                    <div class="result-panel__body">
                        <div style="height: 340px;">
                            <canvas id="regionPresidentialChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Parliamentary Control Map</h3>
                            <div class="result-panel__sub">Constituency leaders across reported parliamentary constituencies.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div style="height: 340px;">
                            <canvas id="regionParliamentaryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="result-panel">
            <div class="result-panel__header">
                <div>
                    <h3 class="result-panel__title">Constituency Operating Table</h3>
                    <div class="result-panel__sub">Top constituency performance inside the region based on verified presidential submissions.</div>
                </div>
                <span class="result-chip"><i class="fas fa-table"></i> Regional command view</span>
            </div>
            <div class="result-panel__body">
                <div class="table-responsive">
                    <table class="table table-hover result-data-table" id="regionDashboardTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Constituency</th>
                                <th>Coverage</th>
                                <th>Reporting</th>
                                <th>Valid Votes</th>
                                <th>Leading Party</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const presidentialBreakdown = @json($presidential['partyBreakdown']);
        const parliamentaryLeadership = @json($parliamentary['leadershipBreakdown']);
        const constituencyRows = @json($presidential['constituencyRows']->values());

        function formatNumber(value) {
            return Number(value || 0).toLocaleString();
        }

        function coverageBar(value) {
            const percent = Math.max(0, Math.min(100, Number(value || 0)));
            return `
                <div class="metric-stack">
                    <strong>${percent.toFixed(2)}%</strong>
                    <div class="coverage-track"><span style="width:${percent}%;"></span></div>
                </div>
            `;
        }

        $(function () {
            new Chart(document.getElementById('regionPresidentialChart'), {
                type: 'doughnut',
                data: {
                    labels: presidentialBreakdown.map(item => `${item.party_initial} (${item.percentage}%)`),
                    datasets: [{
                        data: presidentialBreakdown.map(item => Number(item.votes || 0)),
                        backgroundColor: ['#0f6d5f', '#d97706', '#1d4ed8', '#be123c', '#334155', '#16a34a', '#7c3aed'],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } }
                    }
                }
            });

            new Chart(document.getElementById('regionParliamentaryChart'), {
                type: 'bar',
                data: {
                    labels: parliamentaryLeadership.map(item => item.party_initial),
                    datasets: [{
                        label: 'Constituencies Leading',
                        data: parliamentaryLeadership.map(item => Number(item.count || 0)),
                        backgroundColor: ['#0f6d5f', '#d97706', '#1d4ed8', '#be123c', '#334155', '#16a34a', '#7c3aed'],
                        borderRadius: 12,
                        borderSkipped: false
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });

            $('#regionDashboardTable').DataTable({
                data: constituencyRows,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[3, 'desc']],
                columns: [
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="metric-stack">
                                    <strong>${data.constituency_name}</strong>
                                    <span>${formatNumber(data.total_voters)} registered voters</span>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'coverage_percentage',
                        render: function (data) {
                            return coverageBar(data);
                        }
                    },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="metric-stack">
                                    <strong>${formatNumber(data.reporting_stations)} / ${formatNumber(data.total_polling_stations)}</strong>
                                    <span>${formatNumber(data.submissions)} verified submissions</span>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'total_valid_votes',
                        render: function (data) {
                            return `<strong>${formatNumber(data)}</strong>`;
                        }
                    },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="metric-stack">
                                    <strong>${data.leading_party}</strong>
                                    <span>${data.leading_party_name}</span>
                                </div>
                            `;
                        }
                    },
                    { data: 'updated_at' }
                ]
            });
        });
    </script>
@endsection
