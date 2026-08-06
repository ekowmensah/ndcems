@extends('layouts.app_national_director')

@section('page_title', $regionalDetail->name.' Presidential Detail')
@section('page_description', 'Regional presidential drilldown with verified totals, party share, and constituency-level reporting quality.')
@section('page_badges')
    <span class="national-badge is-accent"><i class="fas fa-map-marked-alt"></i> {{ $selectedStartupName }}</span>
    <span class="national-badge"><i class="fas fa-check-circle"></i> Verified regional snapshot</span>
@endsection
@section('page_actions')
    <a href="{{ route('National.Presidential', ['startup_id' => $selectedStartupId]) }}" class="btn btn-light">
        <i class="fas fa-arrow-left mr-1"></i> Back to Presidential Results
    </a>
@endsection

@section('content')
    @include('national.home.partials.result-analytics-styles')

    <div class="result-shell">
        <form method="GET" action="{{ route('National.regionalResultView', [$regionalDetail->id, $regionalDetail->id]) }}" class="result-toolbar">
            <div class="form-group">
                <label for="startup_id">Election Startup</label>
                <select class="form-control" name="startup_id" id="startup_id">
                    @foreach ($startupOptions as $startup)
                        <option value="{{ $startup->id }}" {{ (int) $selectedStartupId === (int) $startup->id ? 'selected' : '' }}>
                            {{ $startup->election_name }}{{ (int) $startup->status === 1 ? ' • Active' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="flex:0 0 auto;min-width:unset;">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Region
                </button>
            </div>
            <div class="result-chip"><i class="fas fa-clock"></i> Updated {{ $summary['updated_at'] }}</div>
        </form>

        <div class="row">
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-map-pin"></i> Region</span>
                    <div class="result-kpi__value">{{ $regionalDetail->name }}</div>
                    <div class="result-kpi__sub">{{ number_format($summary['reporting_stations']) }} verified stations submitted presidential results</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-broadcast-tower"></i> Coverage</span>
                    <div class="result-kpi__value">{{ $summary['coverage_percentage'] }}%</div>
                    <div class="result-kpi__sub">{{ number_format($summary['reporting_stations']) }} / {{ number_format($summary['total_polling_stations']) }} polling stations</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-check-double"></i> Valid Votes</span>
                    <div class="result-kpi__value">{{ number_format($summary['total_valid_votes']) }}</div>
                    <div class="result-kpi__sub">{{ number_format($summary['submissions']) }} verified submissions included</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-trophy"></i> Leading Party</span>
                    <div class="result-kpi__value">{{ $summary['leading_party'] }}</div>
                    <div class="result-kpi__sub">{{ $summary['leading_party_name'] }} with {{ number_format($summary['leading_votes']) }} votes</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Regional Party Share</h3>
                            <div class="result-panel__sub">Verified presidential totals aggregated across this region only.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div style="height: 340px;">
                            <canvas id="regionalPartyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Quality Signals</h3>
                            <div class="result-panel__sub">Useful checks before escalating a regional result to leadership.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div class="result-summary-grid">
                            <div class="detail-stat">
                                <div class="detail-stat__label">Rejected Rate</div>
                                <div class="detail-stat__value">{{ $summary['rejected_rate'] }}%</div>
                            </div>
                            <div class="detail-stat">
                                <div class="detail-stat__label">Total Ballots</div>
                                <div class="detail-stat__value">{{ number_format($summary['total_ballots']) }}</div>
                            </div>
                            <div class="detail-stat">
                                <div class="detail-stat__label">Rejected Ballots</div>
                                <div class="detail-stat__value">{{ number_format($summary['total_rejected_ballots']) }}</div>
                            </div>
                            <div class="detail-stat">
                                <div class="detail-stat__label">Verified Submissions</div>
                                <div class="detail-stat__value">{{ number_format($summary['submissions']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="result-panel">
            <div class="result-panel__header">
                <div>
                    <h3 class="result-panel__title">Constituency Breakdown Within {{ $regionalDetail->name }}</h3>
                    <div class="result-panel__sub">Coverage and ballot quality for constituencies inside this region.</div>
                </div>
            </div>
            <div class="result-panel__body">
                <div class="table-responsive">
                    <table class="table table-hover result-data-table" id="regionalConstituencyTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Constituency</th>
                                <th>Coverage</th>
                                <th>Reporting</th>
                                <th>Valid Votes</th>
                                <th>Rejected</th>
                                <th>Total Ballots</th>
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
        const subordinateRows = @json($subordinateRows->values());

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
            new Chart(document.getElementById('regionalPartyChart'), {
                type: 'polarArea',
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
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 10 }
                        }
                    }
                }
            });

            $('#regionalConstituencyTable').DataTable({
                data: subordinateRows,
                paging: subordinateRows.length > 10,
                searching: subordinateRows.length > 0,
                info: subordinateRows.length > 10,
                order: [[3, 'desc']],
                columns: [
                    { data: 'name' },
                    {
                        data: 'coverage_percentage',
                        render: function (data) {
                            return coverageBar(data);
                        }
                    },
                    {
                        data: null,
                        render: function (data) {
                            return `${formatNumber(data.reporting_stations)} / ${formatNumber(data.total_polling_stations)}`;
                        }
                    },
                    {
                        data: 'total_valid_votes',
                        render: function (data) {
                            return `<strong>${formatNumber(data)}</strong>`;
                        }
                    },
                    {
                        data: 'total_rejected_ballots',
                        render: function (data) {
                            return formatNumber(data);
                        }
                    },
                    {
                        data: 'total_ballots',
                        render: function (data) {
                            return formatNumber(data);
                        }
                    }
                ]
            });
        });
    </script>
@endsection
