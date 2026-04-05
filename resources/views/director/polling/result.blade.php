@extends('layouts.app_director')

@section('css')
<style>
    .confirm-modal-header {
        background: linear-gradient(135deg, #006b3f, #004d2e);
        color: #fff;
        border-bottom: none;
    }
    .confirm-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    .confirm-kpi {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 10px;
    }
    .confirm-kpi-label {
        font-size: 0.68rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-weight: 700;
    }
    .confirm-kpi-value {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 2px;
    }
    .confirm-checklist {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        padding: 12px;
    }
    .confirm-checklist ul {
        margin: 0;
        padding-left: 18px;
        color: #374151;
    }
    .confirm-checklist li { margin-bottom: 6px; }
    .confirm-detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        margin-top: 10px;
    }
    .confirm-detail-card .detail-title {
        padding: 8px 12px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    #confirmCandidatesTable {
        max-height: 230px;
        overflow-y: auto;
    }
    .confirm-pink-sheet-preview {
        width: 100%;
        max-height: 220px;
        object-fit: contain;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        padding: 3px;
    }
    @media (max-width: 767px) {
        .confirm-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endsection

@section('content')
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header confirm-modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-check-circle mr-1"></i> Confirm Submitted Result</h4>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                    <strong id="confirmElectionName">Election</strong>
                    <span class="badge badge-warning" id="confirmElectionType">Type</span>
                </div>
                <div class="text-muted small" id="confirmStationLabel">Polling station</div>
            </div>

            <div class="confirm-kpi-grid mb-3">
                <div class="confirm-kpi">
                    <div class="confirm-kpi-label">Registered</div>
                    <div class="confirm-kpi-value" id="confirmRegistered">0</div>
                </div>
                <div class="confirm-kpi">
                    <div class="confirm-kpi-label">Valid Votes</div>
                    <div class="confirm-kpi-value" id="confirmValidVotes">0</div>
                </div>
                <div class="confirm-kpi">
                    <div class="confirm-kpi-label">Rejected</div>
                    <div class="confirm-kpi-value" id="confirmRejected">0</div>
                </div>
                <div class="confirm-kpi">
                    <div class="confirm-kpi-label">Over-Voting</div>
                    <div class="confirm-kpi-value" id="confirmOverVoting">No</div>
                </div>
            </div>

            <div class="confirm-checklist">
                <p class="mb-2"><strong>Before confirming, verify that:</strong></p>
                <ul>
                    <li>All candidate vote entries have been reviewed.</li>
                    <li>Rejected ballots are correctly captured.</li>
                    <li>Pink sheet evidence is uploaded and visible.</li>
                    <li>Over-voting status has been checked.</li>
                </ul>
                <a href="#" id="confirmViewLink" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="fa fa-eye mr-1"></i> Open Full Result Details
                </a>
            </div>

            <div class="row mt-2">
                <div class="col-md-7 mb-2 mb-md-0">
                    <div class="confirm-detail-card">
                        <div class="detail-title">Candidate Vote Breakdown</div>
                        <div id="confirmCandidatesTable" class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Candidate</th>
                                        <th>Party</th>
                                        <th class="text-right">Votes</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmCandidateTbody">
                                    <tr><td colspan="4" class="text-muted text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="confirm-detail-card">
                        <div class="detail-title">Pink Sheet</div>
                        <div class="p-2">
                            <div id="confirmPinkSheetMissing" class="text-muted small">No pink sheet available.</div>
                            <img id="confirmPinkSheetImage" src="" alt="Pink Sheet" class="confirm-pink-sheet-preview" style="display:none;">
                            <div id="confirmPinkSheetLinks" class="mt-2" style="display:none;">
                                <a href="#" id="confirmPinkSheetViewLink" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary mr-1">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <a href="#" id="confirmPinkSheetDownloadLink" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success">
                                    <i class="fa fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <a href="" class="btn btn-success" id="confirmBtn" >Confirm Result</a>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="pinkSheetUploadModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="director-pink-sheet-upload-form" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Upload Pink Sheet</h4>
            </div>
            <div class="modal-body">
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
<div class="modal fade" id="pinkSheetPreviewModal" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Pink Sheet Preview</h4>
        </div>
        <div class="modal-body text-center">
            <p><a href="#" id="directorPinkSheetOpenLink" target="_blank" rel="noopener">Open Full Image</a></p>
            <img id="directorPinkSheetPreviewImage" src="" alt="Pink Sheet" style="max-width:100%;max-height:75vh;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;">
        </div>
        <div class="modal-footer">
            <span id="directorPinkSheetActionHint" style="float:left;color:#666;display:none;">Confirmed result: print/download enabled.</span>
            <a href="#" id="directorPinkSheetDownloadBtn" class="btn btn-success" target="_blank" rel="noopener" style="display:none;">
                <span class="fa fa-download"></span> Download
            </a>
            <button type="button" id="directorPinkSheetPrintBtn" class="btn btn-primary" style="display:none;">
                <span class="fa fa-print"></span> Print
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<!-- 
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Submitted Results</span>
            <div class="director-kpi-value" id="summary_submitted" data-default="{{ (int) ($resultSummary['submitted'] ?? 0) }}">{{ number_format($resultSummary['submitted'] ?? 0) }}</div>
            <span class="director-kpi-sub">Out of {{ number_format($resultSummary['total_polling_stations'] ?? 0) }} polling stations</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Confirmed</span>
            <div class="director-kpi-value text-success" id="summary_confirmed" data-default="{{ (int) ($resultSummary['confirmed'] ?? 0) }}">{{ number_format($resultSummary['confirmed'] ?? 0) }}</div>
            <span class="director-kpi-sub">{{ $resultSummary['confirmation_rate'] ?? 0 }}% confirmation rate</span>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Pending</span>
            <div class="director-kpi-value text-warning" id="summary_pending" data-default="{{ (int) ($resultSummary['pending'] ?? 0) }}">{{ number_format($resultSummary['pending'] ?? 0) }}</div>
            <span class="director-kpi-sub">Awaiting review</span>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Pink Sheets</span>
            <div class="director-kpi-value" id="summary_pink" data-default="{{ (int) ($resultSummary['pink_sheets'] ?? 0) }}">{{ number_format($resultSummary['pink_sheets'] ?? 0) }}</div>
            <span class="director-kpi-sub">{{ $resultSummary['pink_sheet_rate'] ?? 0 }}% uploaded</span>
        </div>
    </div>
    <div class="col-md-2 col-sm-12">
        <div class="director-kpi-card">
            <span class="director-kpi-title">Capture Coverage</span>
            <div class="director-kpi-value" id="summary_coverage">{{ $resultSummary['coverage_rate'] ?? 0 }}%</div>
            <div class="director-progress mt-2">
                <span style="width: {{ min(100, max(0, $resultSummary['coverage_rate'] ?? 0)) }}%;"></span>
            </div>
        </div>
    </div>
</div> -->

<div class="row mb-3">
    <div class="col-lg-4 col-md-6">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Result Status</h3>
                <span class="director-chip director-chip-success">Doughnut</span>
            </div>
            <div class="card-body" style="height:280px;">
                <canvas id="resultStatusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Pink Sheet Compliance</h3>
                <span class="director-chip director-chip-warning">Pie</span>
            </div>
            <div class="card-body" style="height:280px;">
                <canvas id="pinkSheetComplianceChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Risk Snapshot</h3>
                <span class="director-chip director-chip-danger">Quality</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Over-Voting Cases</span>
                        <strong id="summary_overvoting">{{ number_format($resultSummary['over_voting_cases'] ?? 0) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Valid Votes</span>
                        <strong id="summary_valid_votes">{{ number_format($resultSummary['total_valid_votes'] ?? 0) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Rejected Ballots</span>
                        <strong id="summary_rejected_votes">{{ number_format($resultSummary['total_rejected_votes'] ?? 0) }}</strong>
                    </div>
                </div>
                <div class="small text-muted">Data refreshes automatically every 15 seconds.</div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-8">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Hourly Submission Trend (24h)</h3>
                <span class="director-chip director-chip-primary">Line</span>
            </div>
            <div class="card-body" style="height:320px;">
                <canvas id="hourlyTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Party Vote Share</h3>
                <span class="director-chip director-chip-primary">Pie</span>
            </div>
            <div class="card-body" style="height:320px;">
                <canvas id="partyVoteChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="card director-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Election Type Throughput</h3>
                <span class="director-chip director-chip-success">Bar</span>
            </div>
            <div class="card-body" style="height:300px;">
                <canvas id="electionTypeBarChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card director-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Constituency Result Records</h3>
        <div style="width: 260px;">
            <select class="form-control filter" name="election_type_id" id="election_type_id" required>
                <option value="all">All Election Types</option>
                @foreach ($election as $country)
                    <option value="{{$country->id}}">{{$country->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" id="table">
                <thead>
                <tr>
                    <th>Polling Station</th>
                    <th>Election Type</th>
                    <th>Election Name</th>
                    <th>Reg. Voters</th>
                    <th>Valid Votes</th>
                    <th>Total Ballots</th>
                    <th>Rejected Ballot</th>
                    <th>Pink Sheet</th>
                    <th>OverVoting</th>
                    <th></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>







@endsection

@section("script")
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script type="text/javascript">
   const analyticsUrl = "{{ route('Director.ResultAnalytics') }}";
   const resultDetailUrlTemplate = "{{ route('Director.ResultDetail', ':id') }}";
   const chartPalette = ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed', '#0ea5e9', '#84cc16', '#f97316'];
   const initialAnalytics = {
       resultSummary: @json($resultSummary),
       statusPie: @json($statusPie),
       pinkSheetPie: @json($pinkSheetPie),
       electionTypeBar: @json($electionTypeBar),
       partyVotePie: @json($partyVotePie),
       trendHourly: @json($trendHourly)
   };

   let data_table = null;
   let statusChart = null;
   let pinkSheetChart = null;
   let electionTypeChart = null;
   let partyVoteChart = null;
   let hourlyTrendChart = null;

   function createOrUpdateChart(instance, elementId, config) {
      if (instance) {
          instance.data = config.data;
          instance.options = config.options;
          instance.update();
          return instance;
      }
      const ctx = document.getElementById(elementId);
      return new Chart(ctx, config);
   }

   function renderAnalytics(payload) {
      const summary = payload.resultSummary || {};

      $('#summary_submitted').text(Number(summary.submitted || 0).toLocaleString());
      $('#summary_confirmed').text(Number(summary.confirmed || 0).toLocaleString());
      $('#summary_pending').text(Number(summary.pending || 0).toLocaleString());
      $('#summary_pink').text(Number(summary.pink_sheets || 0).toLocaleString());
      $('#summary_coverage').text(`${Number(summary.coverage_rate || 0)}%`);
      $('#summary_overvoting').text(Number(summary.over_voting_cases || 0).toLocaleString());
      $('#summary_valid_votes').text(Number(summary.total_valid_votes || 0).toLocaleString());
      $('#summary_rejected_votes').text(Number(summary.total_rejected_votes || 0).toLocaleString());

      const statusPie = payload.statusPie || { labels: [], values: [] };
      statusChart = createOrUpdateChart(statusChart, 'resultStatusChart', {
          type: 'doughnut',
          data: {
              labels: statusPie.labels || [],
              datasets: [{ data: statusPie.values || [], backgroundColor: ['#16a34a', '#f59e0b'], borderWidth: 0 }]
          },
          options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '63%' }
      });

      const pinkPie = payload.pinkSheetPie || { labels: [], values: [] };
      pinkSheetChart = createOrUpdateChart(pinkSheetChart, 'pinkSheetComplianceChart', {
          type: 'pie',
          data: {
              labels: pinkPie.labels || [],
              datasets: [{ data: pinkPie.values || [], backgroundColor: ['#2563eb', '#dc2626'], borderWidth: 0 }]
          },
          options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
      });

      const electionBar = payload.electionTypeBar || { labels: [], submissions: [], confirmations: [] };
      electionTypeChart = createOrUpdateChart(electionTypeChart, 'electionTypeBarChart', {
          type: 'bar',
          data: {
              labels: electionBar.labels || [],
              datasets: [
                  { label: 'Submissions', data: electionBar.submissions || [], backgroundColor: '#2563eb', borderRadius: 6 },
                  { label: 'Confirmations', data: electionBar.confirmations || [], backgroundColor: '#16a34a', borderRadius: 6 }
              ]
          },
          options: {
              maintainAspectRatio: false,
              scales: { y: { beginAtZero: true } },
              plugins: { legend: { position: 'top' } }
          }
      });

      const partyPie = payload.partyVotePie || { labels: [], values: [] };
      partyVoteChart = createOrUpdateChart(partyVoteChart, 'partyVoteChart', {
          type: 'pie',
          data: {
              labels: partyPie.labels || [],
              datasets: [{
                  data: partyPie.values || [],
                  backgroundColor: chartPalette.slice(0, Math.max((partyPie.labels || []).length, 1)),
                  borderWidth: 0
              }]
          },
          options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
      });

      const hourly = payload.trendHourly || { labels: [], submissions: [] };
      hourlyTrendChart = createOrUpdateChart(hourlyTrendChart, 'hourlyTrendChart', {
          type: 'line',
          data: {
              labels: hourly.labels || [],
              datasets: [{
                  label: 'Submissions',
                  data: hourly.submissions || [],
                  borderColor: '#0ea5e9',
                  backgroundColor: 'rgba(14,165,233,0.16)',
                  fill: true,
                  pointRadius: 2,
                  tension: 0.28
              }]
          },
          options: {
              maintainAspectRatio: false,
              scales: { y: { beginAtZero: true } },
              plugins: { legend: { position: 'top' } }
          }
      });
   }

   function loadAnalytics() {
      const electionTypeId = $('#election_type_id').val() || 'all';
      $.ajax({
          url: analyticsUrl,
          type: 'GET',
          data: { election_type_id: electionTypeId },
          success: function(data) {
              renderAnalytics(data);
          }
      });
   }

   $(document).ready(function () {
       renderAnalytics(initialAnalytics);

       $('select option')
           .filter(function() {
               return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
           })
           .remove();

       data_table = $('#table').DataTable({
           processing: true,
           serverSide: true,
           pageLength: 10,
           lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
           stateSave: true,
           order: [[1, "asc"]],
           ajax: {
               url: '{!! route("Director.pollingStationResultAajax") !!}',
               data: function (d) {
                   d.election_type_id = $('#election_type_id').val();
               }
           },
           columnDefs: [
               { searchable: false, targets: 1 },
               { searchable: false, targets: 2 },
               { searchable: false, targets: 3 },
               { searchable: false, targets: 4 },
               { searchable: false, targets: 5 },
               { searchable: false, targets: 6 },
               { searchable: false, targets: 7 },
               { searchable: false, targets: 8 },
               { searchable: false, targets: 9 }
           ],
           columns: [
               {
                   mData: null,
                   name: "name",
                   mRender: function (data) {
                       return `<span title="Polling Agent: ${data.agent_name} (${data.agent_phoneno})">${data.name}</span>`;
                   }
               },
               { data: 'election_type_name', name: 'election_type.name' },
               { data: 'election_name', name: 'election_startup_detail.election_name' },
               { data: 'total_voters', name: 'pollingstation.total_voters' },
               { data: 'obtained_votes', name: 'election_result.obtained_votes' },
               { data: 'total_ballot', name: 'election_result.total_ballot' },
               { data: 'total_rejected_ballot', name: 'election_result.total_rejected_ballot' },
               {
                   mData: null,
                   name: "pink_sheet_path",
                   mRender: function (data) {
                       if (data.pink_sheet_path) {
                           var pinkSheetUrl = "{{ route('Director.ViewPinkSheet', ':number') }}".replace(':number', data.election_result_id);
                           var pinkSheetDownloadUrl = "{{ route('Director.DownloadPinkSheet', ':number') }}".replace(':number', data.election_result_id);
                           var reuploadButton = data.verify_by_constituency == 0
                               ? `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="openDirectorPinkSheetModal(${data.election_result_id})">Reupload</a>`
                               : `<span style="color:#2e7d32;font-weight:600;">Locked</span>`;
                           return `
                               <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                   <img src="${pinkSheetUrl}" alt="Pink Sheet" style="width:46px;height:46px;object-fit:cover;border:1px solid #d9d9d9;border-radius:4px;">
                                   <a href="javascript:void(0)" style="color:#1565c0;font-weight:600;" onclick="openDirectorPinkSheetPreview('${pinkSheetUrl}', ${data.verify_by_constituency}, '${pinkSheetDownloadUrl}')">View</a>
                                   ${reuploadButton}
                               </div>
                           `;
                       }
                       if (data.verify_by_constituency == 0) {
                           return `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="openDirectorPinkSheetModal(${data.election_result_id})">Upload</a>`;
                       }
                       return `<span style="color:#999;">Not uploaded (locked)</span>`;
                   }
               },
               {
                   mData: null,
                   name: "election_result_id",
                   mRender: function (data) {
                       if ((data.total_ballot - data.total_voters) > 0) {
                           return `<span class="fa fa-close" style="color:red;font-size:16px"> ${data.total_ballot - data.total_voters} </span>`;
                       }
                       return `<span class="fa fa-check-square-o" style="color:green;font-size:16px"> No </span>`;
                   }
               },
               {
                   mData: null,
                   name: "election_result_id",
                   mRender: function (data) {
                       var url = "{{ route('Director.viewResults',':number') }}".replace(':number', data.election_result_id);
                       var confirm = "{{ route('Director.confirmResults',':number') }}".replace(':number', data.election_result_id);
                       var del = "{{ route('Director.deleteResults',':number') }}".replace(':number', data.election_result_id);
                       var edit = "{{ route('Director.editResult',[':number',':number1']) }}"
                           .replace(':number', data.election_start_up_id)
                           .replace(':number1', data.election_result_id);
                       var xlx = "{{ route('Director.resultsXlx',':number') }}".replace(':number', data.election_result_id);
                       if (data.election_result_user_id) {
                           edit = edit + "/" + data.election_result_user_id;
                       }
                       if (data.verify_by_constituency == 0) {
                           return `
                               <a style="color:white"
                                  href="javascript:void(0)"
                                  class="js-open-confirm"
                                  data-result-id="${data.election_result_id}"
                                  data-view-url="${url}"
                                  data-confirm-url="${confirm}"
                                  data-election-name="${(data.election_name || '').replace(/\"/g, '&quot;')}"
                                  data-election-type="${(data.election_type_name || '').replace(/\"/g, '&quot;')}"
                                  data-station-name="${(data.name || '').replace(/\"/g, '&quot;')}"
                                  data-registered="${Number(data.total_voters || 0)}"
                                  data-valid="${Number(data.obtained_votes || 0)}"
                                  data-rejected="${Number(data.total_rejected_ballot || 0)}"
                                  data-total-ballot="${Number(data.total_ballot || 0)}"
                               ><span class="fa fa-spinner fa-pulse" title="Confirm Results" style="color:red;font-size:16px"></span></a>
                               <a style="color:white" href="${url}"><span class="fa fa-eye" title="View Results" style="color:#0000CD;font-size:16px"></span></a>
                               <a style="color:white" href="${edit}"><span class="fa fa-edit" title="Edit/Update Results" style="color:#FF4500;font-size:16px"></span></a>
                               <a style="color:white" onclick="return confirm('Are you sure?')" href="${del}"><span class="fa fa-trash-o" title="Delete Results" style="color:#FF0000;font-size:16px"></span></a>
                           `;
                       }
                       return `
                           <a style="color:white" onclick="return confirm('Are you sure?')" href="${confirm}"><span class="fa fa-check-circle" title="Unconfirm Result" style="color:#006400;font-size:18px"></span></a>
                           <a style="color:white" href="${url}"><span class="fa fa-eye" title="View Results" style="color:#0000CD;font-size:18px"></span></a>
                           <a style="color:white" onclick="return confirm('Are you sure?')" href="${del}"><span class="fa fa-trash-o" title="Delete Results" style="color:#FF0000;font-size:16px"></span></a>
                           <a style="color:white" href="${xlx}"><span class="fa fa-file-excel-o" title="Export Results" style="color:green;font-size:18px"></span></a>
                       `;
                   }
               }
           ]
       });

       $('.filter').on('change', function () {
           data_table.draw();
           loadAnalytics();
       });

       setInterval(function () {
           if (data_table) {
               data_table.ajax.reload(null, false);
           }
           loadAnalytics();
       }, 15000);
   });

   function confirmation_popup(payload){
       const registered = Number(payload.registered || 0);
       const validVotes = Number(payload.validVotes || 0);
       const rejectedVotes = Number(payload.rejectedVotes || 0);
       const totalBallot = Number(payload.totalBallot || 0);
       const overVoting = totalBallot > registered ? (totalBallot - registered).toLocaleString() : 'No';

       $('#confirmElectionName').text(payload.electionName || 'Election Result');
       $('#confirmElectionType').text(payload.electionType || 'Election Type');
       $('#confirmStationLabel').text(payload.stationName ? `Polling Station: ${payload.stationName}` : 'Polling Station');
       $('#confirmRegistered').text(registered.toLocaleString());
       $('#confirmValidVotes').text(validVotes.toLocaleString());
       $('#confirmRejected').text(rejectedVotes.toLocaleString());
       $('#confirmOverVoting').text(overVoting);
       $('#confirmBtn').attr('href', payload.confirmUrl || '#');
       $('#confirmViewLink').attr('href', payload.viewUrl || '#');

       $('#confirmCandidateTbody').html('<tr><td colspan="4" class="text-muted text-center">Loading...</td></tr>');
       $('#confirmPinkSheetImage').hide().attr('src', '');
       $('#confirmPinkSheetMissing').text('No pink sheet available.').show();
       $('#confirmPinkSheetLinks').hide();

       if(payload.resultId){
            const detailUrl = resultDetailUrlTemplate.replace(':id', payload.resultId);
            $.ajax({
                url: detailUrl,
                type: 'GET',
                success: function(res){
                    if(Array.isArray(res.candidates) && res.candidates.length){
                        let rows = '';
                        res.candidates.forEach(function(item, idx){
                            rows += `<tr>
                                <td>${idx + 1}</td>
                                <td>${item.candidate_name || '-'}</td>
                                <td><span class="badge badge-light">${item.party_initial || '-'}</span></td>
                                <td class="text-right font-weight-bold">${Number(item.votes || 0).toLocaleString()}</td>
                            </tr>`;
                        });
                        $('#confirmCandidateTbody').html(rows);
                    } else {
                        $('#confirmCandidateTbody').html('<tr><td colspan="4" class="text-muted text-center">No candidate rows found.</td></tr>');
                    }

                    if(res.has_pink_sheet && res.pink_sheet_view_url){
                        $('#confirmPinkSheetImage').attr('src', res.pink_sheet_view_url).show();
                        $('#confirmPinkSheetMissing').hide();
                        $('#confirmPinkSheetViewLink').attr('href', res.pink_sheet_view_url);
                        $('#confirmPinkSheetDownloadLink').attr('href', res.pink_sheet_download_url || '#');
                        $('#confirmPinkSheetLinks').show();
                    }
                },
                error: function(){
                    $('#confirmCandidateTbody').html('<tr><td colspan="4" class="text-danger text-center">Failed to load details.</td></tr>');
                }
            });
       }

       $('#myModal').modal('show');
   }

   function openDirectorPinkSheetModal(electionResultId){
        var uploadUrl = "{{ route('Director.UploadPinkSheet', ':number') }}";
        uploadUrl = uploadUrl.replace(':number', electionResultId);
        $('#director-pink-sheet-upload-form').attr('action', uploadUrl);
        $('#pinkSheetUploadModal').modal('show');
   }

   function openDirectorPinkSheetPreview(url, isConfirmed, downloadUrl){
        $('#directorPinkSheetPreviewImage').attr('src', url);
        $('#directorPinkSheetOpenLink').attr('href', url);
        if(parseInt(isConfirmed, 10) === 1){
            $('#directorPinkSheetActionHint').show();
            $('#directorPinkSheetDownloadBtn').attr('href', downloadUrl).show();
            $('#directorPinkSheetPrintBtn').show().off('click').on('click', function () {
                var printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Pink Sheet</title></head><body style="margin:0;text-align:center;"><img src="' + url + '" style="max-width:100%;height:auto;" onload="window.print();window.close();"></body></html>');
                printWindow.document.close();
            });
        }else{
            $('#directorPinkSheetActionHint').hide();
            $('#directorPinkSheetDownloadBtn').hide().attr('href', '#');
            $('#directorPinkSheetPrintBtn').hide().off('click');
        }
        $('#pinkSheetPreviewModal').modal('show');
   }

   $('body').on('click', '.js-open-confirm', function() {
        const $btn = $(this);
        confirmation_popup({
            resultId: $btn.data('result-id'),
            viewUrl: $btn.data('view-url'),
            confirmUrl: $btn.data('confirm-url'),
            electionName: $btn.data('election-name'),
            electionType: $btn.data('election-type'),
            stationName: $btn.data('station-name'),
            registered: $btn.data('registered'),
            validVotes: $btn.data('valid'),
            rejectedVotes: $btn.data('rejected'),
            totalBallot: $btn.data('total-ballot')
        });
   });

</script>
@endsection

