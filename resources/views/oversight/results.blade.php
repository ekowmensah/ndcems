@extends($layout)

@section('page_title', $scopeLabel.' Result Oversight')
@section('page_description', 'Review submission readiness, integrity exceptions, evidence, and constituency decisions without changing approval authority.')

@section('css')
<style>
    .oversight-header{margin-bottom:20px;padding:24px;border-radius:18px;background:linear-gradient(125deg,#102b45,#087f5b);color:#fff}.oversight-header h1{margin:0 0 6px;font-size:26px;font-weight:800}.oversight-header p{max-width:760px;margin:0;color:#cfe2df;font-size:13px}
    .oversight-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:18px}.oversight-stat{padding:17px;border:1px solid #e2e8ef;border-radius:15px;background:#fff;box-shadow:0 10px 26px rgba(15,35,58,.06)}.oversight-stat span{display:block;color:#718096;font-size:9px;font-weight:800;letter-spacing:.07em;text-transform:uppercase}.oversight-stat strong{display:block;margin-top:7px;color:#10243f;font-size:24px}.oversight-stat small{color:#718096}
    .oversight-card{margin-bottom:18px;border:1px solid #e2e8ef;border-radius:17px;background:#fff;box-shadow:0 12px 30px rgba(15,35,58,.06);overflow:hidden}.oversight-card__head{display:flex;align-items:center;justify-content:space-between;padding:16px 19px;border-bottom:1px solid #e8edf2}.oversight-card__head h3{margin:0;font-size:16px;font-weight:800}.oversight-card__body{padding:18px}
    .oversight-filter{display:grid;grid-template-columns:repeat(5,minmax(150px,1fr));gap:12px}.oversight-filter label{display:block;margin-bottom:6px;color:#718096;font-size:9px;font-weight:800;text-transform:uppercase}.oversight-filter .form-control{height:42px;border-radius:10px}.oversight-filter__actions{display:flex;align-items:flex-end;gap:8px}
    .oversight-note{margin-bottom:18px;padding:13px 16px;border-left:4px solid #246b91;border-radius:10px;background:#eaf3f8;color:#315268;font-size:12px}
    .oversight-ops{display:flex;flex-wrap:wrap;gap:9px;margin:-7px 0 18px}.oversight-ops span{padding:7px 11px;border:1px solid #dfe7ed;border-radius:999px;background:#fff;color:#4a5f70;font-size:10px;font-weight:700}.oversight-ops strong{color:#10243f}
    .oversight-table{width:100%;margin:0}.oversight-table th{padding:11px 9px;border-top:0;color:#718096;font-size:9px;text-transform:uppercase;white-space:nowrap}.oversight-table td{padding:12px 9px;vertical-align:middle;border-top:1px solid #edf1f5;font-size:12px}.oversight-table strong{color:#10243f}
    .status-pill,.issue-pill{display:inline-flex;margin:2px;padding:5px 8px;border-radius:999px;font-size:9px;font-weight:800;text-transform:uppercase}.status-approved{background:#e8f7f1;color:#087152}.status-pending{background:#fff4d8;color:#8b620d}.status-rejected{background:#fff0f0;color:#a12d2d}.issue-pill{background:#fff0f0;color:#a12d2d}.issue-pill.is-escalated{background:#eeeafb;color:#66509a}.clean-pill{background:#eef5f3;color:#087152}
    .pagination-row{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-top:1px solid #edf1f5;color:#718096;font-size:11px}.pagination-row__links{display:flex;gap:7px}.pagination-row__links a,.pagination-row__links span{padding:7px 11px;border:1px solid #dce5ed;border-radius:8px;background:#fff}.pagination-row__links span{opacity:.55}
    @media(max-width:1100px){.oversight-grid{grid-template-columns:repeat(3,1fr)}.oversight-filter{grid-template-columns:repeat(3,1fr)}}@media(max-width:700px){.oversight-grid,.oversight-filter{grid-template-columns:1fr 1fr}}@media(max-width:480px){.oversight-grid,.oversight-filter{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
@if($routePrefix === 'Region')<div class="oversight-header"><h1>{{ $scopeLabel }} Result Oversight</h1><p>Read-only monitoring of constituency decisions, evidence quality, reporting exceptions, and escalations in your assigned region.</p></div>@endif

<div class="oversight-note"><i class="fas fa-shield-alt"></i> <strong>Authority boundary:</strong> only constituency directors approve, reject, or reopen results. This workspace provides review and escalation capabilities only. Official result pages continue to use approved submissions exclusively.</div>
<div class="oversight-ops"><span><strong>{{ number_format($summary['stale_pending']) }}</strong> pending over 24 hours</span><span><strong>{{ number_format($summary['reopened']) }}</strong> reopened submissions</span><span><strong>{{ number_format($summary['escalated']) }}</strong> with open escalations</span></div>

<section class="oversight-grid">
    <article class="oversight-stat"><span>Total submissions</span><strong>{{ number_format($summary['total']) }}</strong><small>Current filter scope</small></article>
    <article class="oversight-stat"><span>Approved</span><strong>{{ number_format($summary['approved']) }}</strong><small>{{ $summary['approval_rate'] }}% approval rate</small></article>
    <article class="oversight-stat"><span>Pending review</span><strong>{{ number_format($summary['pending']) }}</strong><small>Awaiting constituency</small></article>
    <article class="oversight-stat"><span>Rejected</span><strong>{{ number_format($summary['rejected']) }}</strong><small>Awaiting correction</small></article>
    <article class="oversight-stat"><span>Integrity exceptions</span><strong>{{ number_format($summary['over_voting'] + $summary['arithmetic_mismatch'] + $summary['party_mismatch']) }}</strong><small>{{ number_format($summary['missing_pink_sheets']) }} missing evidence</small></article>
</section>

<section class="oversight-card">
    <div class="oversight-card__head"><h3>Review filters</h3><a href="{{ route($routePrefix.'.resultReview') }}" class="btn btn-sm btn-default">Reset</a></div>
    <div class="oversight-card__body"><form method="GET" class="oversight-filter">
        <div><label>Status</label><select name="status" class="form-control"><option value="">All statuses</option>@foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
        <div><label>Exception</label><select name="anomaly" class="form-control"><option value="">All records</option>@foreach(['over_voting'=>'Over-voting','arithmetic'=>'Arithmetic mismatch','party_mismatch'=>'Party total mismatch','missing_pink_sheet'=>'Missing pink sheet','escalated'=>'Open escalation','stale_pending'=>'Pending over 24 hours','reopened'=>'Reopened submission'] as $value=>$label)<option value="{{ $value }}" {{ ($filters['anomaly'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
        <div><label>Election type</label><select name="election_type_id" class="form-control"><option value="">All types</option>@foreach($electionTypes as $type)<option value="{{ $type->id }}" {{ (string)($filters['election_type_id'] ?? '') === (string)$type->id ? 'selected' : '' }}>{{ $type->name }}</option>@endforeach</select></div>
        <div><label>Election startup</label><select name="startup_id" class="form-control"><option value="">All startups</option>@foreach($startupOptions as $startup)<option value="{{ $startup->id }}" {{ (string)($filters['startup_id'] ?? '') === (string)$startup->id ? 'selected' : '' }}>{{ $startup->election_name }}</option>@endforeach</select></div>
        @if(!$regionScope)<div><label>Region</label><select name="region_id" class="form-control"><option value="">All regions</option>@foreach($regions as $region)<option value="{{ $region->id }}" {{ (string)($filters['region_id'] ?? '') === (string)$region->id ? 'selected' : '' }}>{{ $region->name }}</option>@endforeach</select></div>@endif
        <div class="oversight-filter__actions"><button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply filters</button></div>
    </form></div>
</section>

<section class="oversight-card">
    <div class="oversight-card__head"><h3>Submission review queue</h3><span class="badge badge-light">{{ number_format($rows->total()) }} records</span></div>
    <div class="table-responsive"><table class="table oversight-table"><thead><tr><th>Polling station</th><th>Election</th><th>Geography</th><th>Status</th><th>Coverage</th><th>Exceptions</th><th>Updated</th><th></th></tr></thead><tbody>
        @forelse($rows as $row)
        @php
            $issues=[];
            if((int)$row->total_ballot > (int)$row->total_voters) $issues[]='Over-voting';
            if((int)$row->total_ballot !== ((int)$row->obtained_votes + (int)$row->total_rejected_ballot)) $issues[]='Arithmetic';
            if((int)$row->party_votes !== (int)$row->obtained_votes) $issues[]='Party totals';
            if(empty($row->pink_sheet_path)) $issues[]='No pink sheet';
            if($row->approval_status === 'pending' && optional($row->updated_at)->lt(now()->subHours(24))) $issues[]='Pending >24h';
        @endphp
        <tr><td><strong>{{ $row->polling_station_name }}</strong><br><small>{{ $row->polling_station_code }}</small></td><td>{{ $row->election_name }}<br><small>{{ $row->election_type_name }}</small></td><td>{{ $row->constituency_name }}<br><small>{{ $row->region_name }}</small></td><td><span class="status-pill status-{{ $row->approval_status }}">{{ $row->approval_status }}</span>@if($row->reviewer_name)<br><small>{{ $row->reviewer_name }}</small>@endif</td><td>{{ number_format($row->total_ballot) }}/{{ number_format($row->total_voters) }}<br><small>{{ $row->total_voters > 0 ? number_format(($row->total_ballot/$row->total_voters)*100,1) : 0 }}% turnout</small></td><td>@forelse($issues as $issue)<span class="issue-pill">{{ $issue }}</span>@empty<span class="issue-pill clean-pill">Clear</span>@endforelse @if($row->reopening_count)<span class="issue-pill">Reopened</span>@endif @if($row->open_escalations)<span class="issue-pill is-escalated">{{ $row->open_escalations }} escalated</span>@endif</td><td>{{ optional($row->updated_at)->format('M d, H:i') }}</td><td><a href="{{ route($routePrefix.'.resultReviewDetail',$row->id) }}" class="btn btn-sm btn-outline-primary">Review</a></td></tr>
        @empty<tr><td colspan="8" class="text-center text-muted" style="padding:35px;">No submissions match the selected filters.</td></tr>@endforelse
    </tbody></table></div>
    @if($rows->hasPages())<div class="pagination-row"><span>Showing {{ $rows->firstItem() }}-{{ $rows->lastItem() }} of {{ $rows->total() }}</span><div class="pagination-row__links">@if($rows->onFirstPage())<span>Previous</span>@else<a href="{{ $rows->previousPageUrl() }}">Previous</a>@endif @if($rows->hasMorePages())<a href="{{ $rows->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</div></div>@endif
</section>
@endsection
