@extends('layouts.app_director')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Election Type Performance</h3>
                <span class="director-chip director-chip-primary">Live Aggregation</span>
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
                        <tbody>
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

        <div class="card director-card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Active Election Startup Progress</h3>
                <span class="director-chip director-chip-success">Operational Tracking</span>
            </div>
            <div class="card-body">
                @forelse ($startupPerformance as $startup)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <strong>{{ $startup->election_name }}</strong>
                                <div class="text-muted small">{{ $startup->election_type_name }}</div>
                            </div>
                            <div class="text-right">
                                <span class="director-chip director-chip-warning mr-1">Submitted: {{ number_format($startup->submissions) }}</span>
                                <span class="director-chip director-chip-success">Confirmed: {{ number_format($startup->confirmations) }}</span>
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
    </div>

    <div class="col-lg-4">
        <div class="card director-card">
            <div class="card-header">
                <h3 class="card-title">Statistical Snapshot</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Valid Votes</span>
                        <strong>{{ number_format($dashboardStats['total_valid_votes']) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Rejected Ballots</span>
                        <strong>{{ number_format($dashboardStats['total_rejected_votes']) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Capture Coverage</span>
                        <strong>{{ $dashboardStats['coverage_rate'] }}%</strong>
                    </div>
                    <div class="director-progress mt-2">
                        <span style="width: {{ min(100, max(0, $dashboardStats['coverage_rate'])) }}%;"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Confirmation Rate</span>
                        <strong>{{ $dashboardStats['confirmation_rate'] }}%</strong>
                    </div>
                    <div class="director-progress mt-2">
                        <span style="width: {{ min(100, max(0, $dashboardStats['confirmation_rate'])) }}%;"></span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Pink Sheet Coverage</span>
                        <strong>{{ $dashboardStats['pink_sheet_rate'] }}%</strong>
                    </div>
                    <div class="director-progress mt-2">
                        <span style="width: {{ min(100, max(0, $dashboardStats['pink_sheet_rate'])) }}%;"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card director-card mt-3">
            <div class="card-header">
                <h3 class="card-title">Director Workflow</h3>
            </div>
            <div class="card-body">
                <ol class="pl-3 mb-0">
                    <li class="mb-2">Polling agents capture constituency polling-station results.</li>
                    <li class="mb-2">Pink sheets are uploaded and linked to each submission.</li>
                    <li class="mb-2">Director reviews, edits unconfirmed entries, and validates over-voting.</li>
                    <li class="mb-2">Director confirms only fully verified result records.</li>
                    <li>Confirmed records become locked for audit consistency.</li>
                </ol>
                <div class="mt-3">
                    <a href="{{ route('Director.Result') }}" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-chart-line mr-1"></i>Open Results
                    </a>
                    <a href="{{ route('Director.election') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-vote-yea mr-1"></i>Capture Result
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
