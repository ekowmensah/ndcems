@extends('layouts.app_director')
@section('css')
<style>
    .result-detail-shell { margin-top: 10px; }
    .result-head-card,
    .result-summary-card,
    .result-table-card,
    .result-pinksheet-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .result-head-card .card-header {
        background: linear-gradient(135deg, #006B3F, #004D2E);
        color: #fff;
        border: none;
        padding: 14px 18px;
    }
    .result-head-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    .result-head-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid #e9ecef;
    }
    .result-head-item .label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 700;
    }
    .result-head-item .value {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.92rem;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .summary-box {
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }
    .summary-box .s-label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
    }
    .summary-box .s-value {
        font-size: 1.4rem;
        font-weight: 800;
        margin-top: 2px;
    }
    .summary-valid .s-value { color: #006B3F; }
    .summary-rejected .s-value { color: #CE1126; }
    .summary-total .s-value { color: #111827; }

    .result-table-card .table thead th {
        background: #f8fafc;
        border-top: 0;
        font-size: 0.74rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: .5px;
    }
    .party-badge {
        display: inline-block;
        border-radius: 999px;
        padding: 3px 8px;
        background: #ecfdf5;
        color: #065f46;
        font-weight: 700;
        font-size: .74rem;
    }
    .votes-badge {
        font-weight: 800;
        color: #004D2E;
        font-size: 1rem;
    }
    .result-pinksheet-frame {
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 4px;
        background: #fff;
    }

    @media (max-width: 992px) {
        .result-head-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .result-head-grid,
        .summary-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
@section('content')
<div class="container-fluid result-detail-shell">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-2 mb-md-0"><i class="fas fa-poll-h mr-2" style="color:#006B3F"></i>Result Detail</h4>
        <a href="{{ route('Director.Result') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Result List
        </a>
    </div>

    @if($electionResult && isset($electionResult) && count($electionResult) != 0)
        @php $resultMeta = $electionResult->toArray()[0]; @endphp

        <div class="card result-head-card">
            <div class="card-header">
                <strong><i class="fas fa-info-circle mr-1"></i>Polling Station Context</strong>
            </div>
            <div class="card-body">
                <div class="result-head-grid">
                    <div class="result-head-item">
                        <div class="label">Election</div>
                        <div class="value">{{ $electionStartupDetail->election_name ?? 'N/A' }}</div>
                    </div>
                    <div class="result-head-item">
                        <div class="label">Election Type</div>
                        <div class="value">{{ $electionStartupDetail->name ?? 'N/A' }}</div>
                    </div>
                    <div class="result-head-item">
                        <div class="label">Polling Station</div>
                        <div class="value">{{ $user->PollingStation_name ?? 'N/A' }}</div>
                    </div>
                    <div class="result-head-item">
                        <div class="label">Polling Station Code</div>
                        <div class="value">{{ $user->PollingStation_Id ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card result-summary-card">
            <div class="card-body">
                <div class="summary-grid">
                    <div class="summary-box summary-valid">
                        <div class="s-label">Valid Votes</div>
                        <div class="s-value">{{ number_format($resultMeta['obtained_votes']) }}</div>
                    </div>
                    <div class="summary-box summary-rejected">
                        <div class="s-label">Rejected Ballots</div>
                        <div class="s-value">{{ number_format($resultMeta['total_rejected_ballot']) }}</div>
                    </div>
                    <div class="summary-box summary-total">
                        <div class="s-label">Total Ballot</div>
                        <div class="s-value">{{ number_format($resultMeta['total_ballot']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card result-table-card">
            <div class="card-header bg-white border-0 pb-0">
                <strong><i class="fas fa-users mr-1" style="color:#006B3F"></i>Candidate Vote Breakdown</strong>
            </div>
            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Candidate Name</th>
                                <th style="width:180px;">Party</th>
                                <th style="width:180px;" class="text-right">Votes Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($electionResult as $index => $_electionResult)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $_electionResult->first_name }} {{ $_electionResult->last_name }}</strong></td>
                                    <td><span class="party-badge">{{ $_electionResult->party_initial }}</span></td>
                                    <td class="text-right"><span class="votes-badge">{{ number_format($_electionResult->party_election_result_obtained_vote) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card result-pinksheet-card">
            <div class="card-header bg-white border-0 pb-0">
                <strong><i class="fas fa-file-image mr-1" style="color:#CE1126"></i>Pink Sheet Evidence</strong>
            </div>
            <div class="card-body">
                @if(!empty($resultMeta['pink_sheet_path']))
                    <p class="mb-2">
                        <a href="{{ route('Director.ViewPinkSheet', $resultMeta['id']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Open Full Image
                        </a>
                        <a href="{{ route('Director.DownloadPinkSheet', $resultMeta['id']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success ml-1">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                    </p>
                    <img src="{{ route('Director.ViewPinkSheet', $resultMeta['id']) }}" alt="Pink Sheet" class="result-pinksheet-frame">
                @else
                    <div class="alert alert-light border mb-0 text-muted">Pink sheet not uploaded for this result.</div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-warning">No result details found.</div>
    @endif
</div>

@endsection
@section('script')
@endsection
