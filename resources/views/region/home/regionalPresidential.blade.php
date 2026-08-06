@extends('layouts.app_national')

@section('content')
    @include('national.home.partials.result-analytics-styles')

    <div class="result-shell">
        @if ($lowCoverageWarning)
            <div class="alert alert-warning mb-0">
                <strong>Partial reporting:</strong> {{ $lowCoverageWarning }}
            </div>
        @endif

        <form method="GET" class="result-toolbar">
            <div class="form-group">
                <label for="startup_id">Election Startup</label>
                <select class="form-control" name="startup_id" id="startup_id">
                    @foreach ($startupOptions as $startup)
                        <option value="{{ $startup->id }}" {{ (int) $selectedStartupId === (int) $startup->id ? 'selected' : '' }}>
                            {{ $startup->election_name }}{{ (int) $startup->status === 1 ? ' | Active' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="flex:0 0 auto;min-width:unset;">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh View
                </button>
            </div>
            <div class="result-chip"><i class="fas fa-map-marked-alt"></i> {{ $regionDetail->name }}</div>
            <div class="result-chip"><i class="fas fa-clock"></i> {{ $summary['updated_at'] }}</div>
        </form>

        <div class="row">
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-broadcast-tower"></i> Coverage</span>
                    <div class="result-kpi__value">{{ $summary['constituency_coverage_percentage'] }}%</div>
                    <div class="result-kpi__sub">{{ number_format($summary['reporting_constituencies']) }} of {{ number_format($summary['total_constituencies']) }} constituencies reporting</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-check-double"></i> Valid Votes</span>
                    <div class="result-kpi__value">{{ number_format($summary['total_valid_votes']) }}</div>
                    <div class="result-kpi__sub">{{ number_format($summary['submissions']) }} verified presidential submissions</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-exclamation-circle"></i> Rejected Rate</span>
                    <div class="result-kpi__value">{{ $summary['rejected_rate'] }}%</div>
                    <div class="result-kpi__sub">{{ number_format($summary['total_rejected_ballots']) }} rejected from {{ number_format($summary['total_ballots']) }} ballots</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-trophy"></i> Leading Party</span>
                    <div class="result-kpi__value">{{ $summary['leading_party'] }}</div>
                    <div class="result-kpi__sub">{{ $summary['leading_party_name'] }} with {{ number_format($summary['leading_party_votes']) }} votes</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Regional Presidential Share</h3>
                            <div class="result-panel__sub">Verified presidential vote share across the region.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div style="height: 340px;">
                            <canvas id="regionalPresidentialChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Operational Notes</h3>
                            <div class="result-panel__sub">Fast context for regional follow-up and escalation.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div class="detail-stat">
                            <div class="detail-stat__label">Reporting Stations</div>
                            <div class="detail-stat__value">{{ number_format($summary['reporting_stations']) }}</div>
                            <div class="result-panel__sub">Out of {{ number_format($summary['total_polling_stations']) }} polling stations in the region.</div>
                        </div>
                        <div class="detail-stat mt-3">
                            <div class="detail-stat__label">Remaining Constituencies</div>
                            <div class="detail-stat__value">{{ number_format($summary['remaining_constituencies']) }}</div>
                            <div class="result-panel__sub">Constituencies with no verified presidential result yet for this startup.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="result-panel">
            <div class="result-panel__header">
                <div>
                    <h3 class="result-panel__title">Constituency Presidential Matrix</h3>
                    <div class="result-panel__sub">Presidential performance by constituency inside {{ $regionDetail->name }}.</div>
                </div>
            </div>
            <div class="result-panel__body">
                <div class="table-responsive">
                    <table class="table table-hover result-data-table" id="regionalPresidentialTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Constituency</th>
                                <th>Coverage</th>
                                <th>Reporting</th>
                                <th>Valid Votes</th>
                                <th>Rejected Rate</th>
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
        const partyBreakdown = @json($partyBreakdown);
        const constituencyRows = @json($constituencyRows->values());

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
            new Chart(document.getElementById('regionalPresidentialChart'), {
                type: 'doughnut',
                data: {
                    labels: partyBreakdown.map(item => `${item.party_initial} (${item.percentage}%)`),
                    datasets: [{
                        data: partyBreakdown.map(item => Number(item.votes || 0)),
                        backgroundColor: ['#0f6d5f', '#d97706', '#1d4ed8', '#be123c', '#334155', '#16a34a', '#7c3aed'],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } } }
                }
            });

            $('#regionalPresidentialTable').DataTable({
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
                    { data: 'total_valid_votes', render: data => `<strong>${formatNumber(data)}</strong>` },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="metric-stack">
                                    <strong>${Number(data.rejected_rate || 0).toFixed(2)}%</strong>
                                    <span>${formatNumber(data.total_rejected_ballots)} rejected ballots</span>
                                </div>
                            `;
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
