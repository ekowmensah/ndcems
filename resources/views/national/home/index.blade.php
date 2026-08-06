@extends('layouts.app_national_director')

@section('page_title', 'National Dashboard')
@section('page_description', 'A live command center for national reporting coverage, party share, regional performance, and election operating context.')
@section('page_badges')
    <span class="national-badge"><i class="fas fa-sync-alt"></i> Auto refresh every 15s</span>
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
            setInterval(refreshNationalChart, 15000);
        });
    </script>
@endsection
