@extends('layouts.app_agent')
@section('css')
<style>
    :root {
        --ndc-green: #006B3F;
        --ndc-green-dark: #004D2E;
        --ndc-red: #CE1126;
        --ndc-gold: #FCD116;
        --ndc-black: #1a1a1a;
    }
    .results-list-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .results-list-card .card-header {
        background: linear-gradient(135deg, var(--ndc-green), var(--ndc-green-dark));
        color: #fff;
        padding: 16px 20px;
        border: none;
    }
    .results-list-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.05rem;
    }
    .results-table-wrap {
        padding: 12px;
    }
    .results-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .results-table thead th {
        background: #f6f9f7;
        color: #2d2d2d;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 8px;
        border-bottom: 1px solid #e5ece8;
        white-space: nowrap;
    }
    .results-table tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
        font-size: 0.85rem;
    }
    .results-table tbody tr:hover {
        background: #f8fdf9;
    }
    .pink-sheet-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 14px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .pink-sheet-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #d9d9d9;
        display: block;
        margin-bottom: 6px;
    }
    .pink-sheet-ok {
        background: #e8f5e9;
        color: var(--ndc-green);
    }
    .pink-sheet-missing {
        background: #fdeaea;
        color: var(--ndc-red);
    }
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .status-confirmed {
        background: #e8f5e9;
        color: var(--ndc-green);
    }
    .status-pending {
        background: #fff8e1;
        color: #e6a700;
    }
    .result-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
    }
    .btn-view {
        background: #e3f2fd;
        color: #1565c0;
    }
    .btn-view:hover { background: #bbdefb; color: #0d47a1; text-decoration: none; }
    .btn-edit {
        background: #fff3e0;
        color: #e65100;
    }
    .btn-edit:hover { background: #ffe0b2; color: #bf360c; text-decoration: none; }
    .btn-upload {
        background: #f3e5f5;
        color: #6a1b9a;
    }
    .btn-upload:hover { background: #e1bee7; color: #4a148c; text-decoration: none; }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .empty-state i { font-size: 2.5rem; margin-bottom: 12px; color: #ddd; }

    @media (max-width: 768px) {
        .results-table-wrap {
            overflow-x: auto;
        }
    }
</style>
@endsection
@section('content')

<div class="container mt-3">
    <div class="card results-list-card">
        <div class="card-header">
            <h5><i class="fas fa-poll mr-2"></i>My Submitted Results</h5>
        </div>
        <div class="card-body p-0">
            @if(count($electionResults) > 0)
                <div class="results-table-wrap">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Election</th>
                                <th>Type</th>
                                <th>Reg. Voters</th>
                                <th>Valid Votes</th>
                                <th>Rejected</th>
                                <th>Total Ballot</th>
                                <th>Over Voting</th>
                                <th>Status</th>
                                <th>Pink Sheet</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($electionResults as $electionResult)
                            <tr>
                                <td>{{ $electionResult->election_name }}</td>
                                <td>{{ $electionResult->election_type_name }}</td>
                                <td>{{ number_format($electionResult->total_voters) }}</td>
                                <td>{{ number_format($electionResult->obtained_votes) }}</td>
                                <td>{{ number_format($electionResult->total_rejected_ballot) }}</td>
                                <td>{{ number_format($electionResult->total_ballot) }}</td>
                                <td>
                                    @if(($electionResult->total_ballot - $electionResult->total_voters) > 0)
                                        <span class="pink-sheet-badge pink-sheet-missing">+{{ number_format($electionResult->total_ballot - $electionResult->total_voters) }}</span>
                                    @else
                                        <span class="pink-sheet-badge pink-sheet-ok">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($electionResult->verify_by_constituency == 1)
                                        <span class="status-badge status-confirmed">Confirmed</span>
                                    @else
                                        <span class="status-badge status-pending">Unconfirmed</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($electionResult->pink_sheet_path))
                                        <img src="{{ route('Agent.ViewPinkSheet', $electionResult->id) }}" alt="Pink Sheet Preview" class="pink-sheet-thumb">
                                        <span class="pink-sheet-badge pink-sheet-ok">Uploaded</span>
                                        <a href="javascript:void(0)" class="btn-action btn-view open-pink-sheet-preview"
                                           data-url="{{ route('Agent.ViewPinkSheet', $electionResult->id) }}">View</a>
                                        @if($electionResult->verify_by_constituency != 1)
                                            <a href="javascript:void(0)" class="btn-action btn-upload open-pink-sheet-modal"
                                               data-startup-id="{{ $electionResult->election_start_up_id }}"
                                               data-result-id="{{ $electionResult->id }}">
                                                <i class="fas fa-upload"></i> Reupload
                                            </a>
                                        @else
                                            <span class="pink-sheet-badge status-confirmed">Locked</span>
                                        @endif
                                    @else
                                        <span class="pink-sheet-badge pink-sheet-missing">Not Uploaded</span>
                                        @if($electionResult->verify_by_constituency != 1)
                                            <a href="javascript:void(0)" class="btn-action btn-upload open-pink-sheet-modal"
                                               data-startup-id="{{ $electionResult->election_start_up_id }}"
                                               data-result-id="{{ $electionResult->id }}">
                                                <i class="fas fa-upload"></i> Upload
                                            </a>
                                        @else
                                            <span class="pink-sheet-badge status-confirmed">Locked</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('Agent.viewResults', [$electionResult->election_start_up_id, $electionResult->id]) }}" class="btn-action btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if($electionResult->verify_by_constituency != 1)
                                        <a href="{{ route('Agent.Home', [$electionResult->election_start_up_id, $electionResult->id]) }}" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox d-block"></i>
                    <p class="font-weight-bold">No results submitted yet</p>
                    <p class="text-muted">Go to <a href="{{ route('Agent.election') }}" style="color:var(--ndc-green); font-weight:600;">Capture Result</a> to enter your first election result.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="pinkSheetPreviewModal" tabindex="-1" role="dialog" aria-labelledby="pinkSheetPreviewLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="pinkSheetPreviewLabel">Pink Sheet Preview</h4>
            </div>
            <div class="modal-body text-center">
                <img id="pink-sheet-preview-image" src="" alt="Pink Sheet" style="max-width:100%;max-height:75vh;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pinkSheetUploadModal" tabindex="-1" role="dialog" aria-labelledby="pinkSheetUploadLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="pink-sheet-upload-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="pinkSheetUploadLabel">Upload Pink Sheet</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="election_result_id" id="pink-sheet-result-id">
                    <div class="form-group">
                        <label>Select image</label>
                        <input type="file" name="pink_sheet" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                        <small class="text-muted">Allowed: JPG, PNG, WEBP. Max size: 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
$(document).on('click', '.open-pink-sheet-modal', function () {
    var startupId = $(this).data('startup-id');
    var resultId = $(this).data('result-id');
    var uploadUrl = "{{ route('Agent.UploadPinkSheet', ':startup') }}".replace(':startup', startupId);
    $('#pink-sheet-upload-form').attr('action', uploadUrl);
    $('#pink-sheet-result-id').val(resultId);
    $('#pinkSheetUploadModal').modal('show');
});
$(document).on('click', '.open-pink-sheet-preview', function () {
    var url = $(this).data('url');
    $('#pink-sheet-preview-image').attr('src', url);
    $('#pinkSheetPreviewModal').modal('show');
});
</script>
@endsection
