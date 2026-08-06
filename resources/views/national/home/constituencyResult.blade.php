@extends('layouts.app_national_director')

@section('page_title', 'National Parliamentary Results')
@section('page_description', 'Verified parliamentary performance by constituency, designed for national decision-making with stronger context and clean drilldowns.')
@section('page_badges')
    <span class="national-badge is-accent"><i class="fas fa-landmark"></i> {{ $selectedStartupName }}</span>
    <span class="national-badge"><i class="fas fa-check-circle"></i> Verified constituency-confirmed totals</span>
@endsection
@section('page_actions')
    <a href="{{ route('National.result', app(\App\Services\National\NationalDashboardService::class)->getConstituencyElectionTypeId()) }}" class="btn btn-light">
        <i class="fas fa-filter mr-1"></i> Open Result Explorer
    </a>
@endsection

@section('content')
    @include('national.home.partials.result-analytics-styles')
    @php
        $topConstituencies = collect($constituencyRows)->sortByDesc('total_valid_votes')->take(6)->values();
        $leader = collect($partyBreakdown)->first();
    @endphp

    <div class="result-shell">
        <form method="GET" class="result-toolbar" id="startupFilterForm">
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
                    <i class="fas fa-sync-alt mr-1"></i> Refresh View
                </button>
            </div>
            <div class="result-chip">
                <i class="fas fa-map"></i>
                {{ number_format($summary['reporting_constituencies']) }} constituencies currently reporting
            </div>
            <div class="result-chip">
                <i class="fas fa-clock"></i>
                Last update: {{ $summary['updated_at'] }}
            </div>
        </form>

        <div class="row">
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-broadcast-tower"></i> Reporting Coverage</span>
                    <div class="result-kpi__value">{{ $summary['coverage_percentage'] }}%</div>
                    <div class="result-kpi__sub">{{ number_format($summary['reporting_stations']) }} of {{ number_format($summary['total_polling_stations']) }} polling stations verified</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-check-double"></i> Valid Votes</span>
                    <div class="result-kpi__value">{{ number_format($summary['total_valid_votes']) }}</div>
                    <div class="result-kpi__sub">{{ number_format($summary['submissions']) }} parliamentary submissions aggregated nationally</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3">
                <div class="result-kpi">
                    <span class="result-kpi__label"><i class="fas fa-exclamation-circle"></i> Rejected Rate</span>
                    <div class="result-kpi__value">{{ $summary['rejected_rate'] }}%</div>
                    <div class="result-kpi__sub">{{ number_format($summary['total_rejected_ballots']) }} rejected from {{ number_format($summary['total_ballots']) }} total ballots</div>
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
            <div class="col-lg-8 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">National Parliamentary Share</h3>
                            <div class="result-panel__sub">Party share across all verified parliamentary results in the selected startup.</div>
                        </div>
                        <span class="result-chip"><i class="fas fa-chart-bar"></i> National party weight</span>
                    </div>
                    <div class="result-panel__body">
                        <div style="height: 360px;">
                            <canvas id="parliamentaryPartyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Constituency Hotspots</h3>
                            <div class="result-panel__sub">Highest-volume constituencies by verified parliamentary votes.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div class="insight-list">
                            @forelse ($topConstituencies as $row)
                                <div class="insight-row">
                                    <div class="metric-stack">
                                        <strong>{{ $row['constituency_name'] }}</strong>
                                        <span>{{ $row['leading_party'] }} leading • {{ number_format($row['leading_votes']) }} votes</span>
                                    </div>
                                    <div class="text-right" style="min-width:120px;">
                                        <strong>{{ number_format($row['total_valid_votes']) }}</strong>
                                        <span>{{ $row['coverage_percentage'] }}% coverage</span>
                                    </div>
                                </div>
                            @empty
                                <div class="result-empty-state">No verified constituency results available yet.</div>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            <div class="detail-stat">
                                <div class="detail-stat__label">National Front Runner</div>
                                <div class="detail-stat__value">{{ $leader['party_initial'] ?? 'N/A' }}</div>
                                <div class="result-panel__sub">{{ isset($leader['votes']) ? number_format($leader['votes']) : '0' }} verified votes currently place this party ahead nationally.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="result-panel">
            <div class="result-panel__header">
                <div>
                    <h3 class="result-panel__title">Constituency Performance Matrix</h3>
                    <div class="result-panel__sub">Each row summarizes one constituency from verified parliamentary submissions only.</div>
                </div>
                <span class="result-chip"><i class="fas fa-table"></i> Drill into constituency detail</span>
            </div>
            <div class="result-panel__body">
                <div class="table-responsive">
                    <table class="table table-hover result-data-table" id="constituencyResultsTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Constituency</th>
                                <th>Coverage</th>
                                <th>Reporting</th>
                                <th>Valid Votes</th>
                                <th>Rejected Rate</th>
                                <th>Leading Party</th>
                                <th>Updated</th>
                                <th></th>
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
        const selectedStartupId = @json($selectedStartupId);
        const constituencyDetailUrlTemplate = @json(route('National.constituencyView', '__id__'));

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

        function constituencyDetailUrl(row) {
            let url = constituencyDetailUrlTemplate.replace('__id__', row.constituency_id);
            if (selectedStartupId) {
                url += `?startup_id=${encodeURIComponent(selectedStartupId)}`;
            }
            return url;
        }

        $(function () {
            const ctx = document.getElementById('parliamentaryPartyChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: partyBreakdown.map(item => item.party_initial),
                    datasets: [{
                        label: 'Votes',
                        data: partyBreakdown.map(item => Number(item.votes || 0)),
                        backgroundColor: ['#0f6d5f', '#d97706', '#1d4ed8', '#be123c', '#334155', '#16a34a', '#7c3aed'],
                        borderRadius: 12,
                        borderSkipped: false
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return Number(value).toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            $('#constituencyResultsTable').DataTable({
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
                    { data: 'updated_at' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return `<a href="${constituencyDetailUrl(data)}" class="btn btn-primary btn-sm">View</a>`;
                        }
                    }
                ]
            });
        });
    </script>
@endsection
