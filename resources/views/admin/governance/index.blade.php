@extends('admin.layouts.app')

@section('page_title', 'Governance Audit')
@section('page_description', 'Review successful administrative changes, responsible operators, request context, and response outcomes.')

@section('css')
<style>
    .audit-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}.audit-summary article{padding:18px;border:1px solid var(--admin-line);border-radius:15px;background:#fff}.audit-summary span{display:block;color:#718096;font-size:10px;font-weight:800;text-transform:uppercase}.audit-summary strong{display:block;margin-top:6px;color:var(--admin-ink);font-size:24px}.audit-filter{display:grid;grid-template-columns:2fr 1.4fr 1fr 1fr 1fr auto;gap:10px;align-items:end}.audit-filter label{display:block;margin-bottom:5px;color:#718096;font-size:9px;font-weight:800;text-transform:uppercase}.audit-payload{max-width:360px;margin:0;white-space:pre-wrap;word-break:break-word;color:#53677b;font-size:10px}.audit-method{display:inline-flex;padding:5px 8px;border-radius:99px;background:#eaf4f0;color:#087152;font-size:9px;font-weight:800}.audit-page{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-top:1px solid var(--admin-line)}
    @media(max-width:900px){.audit-filter{grid-template-columns:1fr 1fr}.audit-summary{grid-template-columns:1fr}}@media(max-width:520px){.audit-filter{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
<section class="audit-summary">
    <article><span>Recorded changes</span><strong>{{ number_format($audits->total()) }}</strong></article>
    <article><span>Current page</span><strong>{{ number_format($audits->count()) }}</strong></article>
    <article><span>Retention</span><strong>Append-only</strong></article>
</section>

<div class="x_panel"><div class="x_title"><h2>Audit filters</h2><div class="clearfix"></div></div><div class="x_content">
    <form method="GET" class="audit-filter">
        <div><label>Event or route</label><input class="form-control" name="event" value="{{ $filters['event'] ?? '' }}" placeholder="e.g. users_delete"></div>
        <div><label>Operator</label><input class="form-control" name="actor" value="{{ $filters['actor'] ?? '' }}"></div>
        <div><label>Method</label><select class="form-control" name="method"><option value="">All</option>@foreach(['POST','PUT','PATCH','DELETE'] as $method)<option value="{{ $method }}" {{ ($filters['method'] ?? '') === $method ? 'selected' : '' }}>{{ $method }}</option>@endforeach</select></div>
        <div><label>From</label><input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div><label>To</label><input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div><button class="btn btn-primary">Apply</button> <a class="btn btn-default" href="{{ route('SuperAdmin.governance.audits') }}">Reset</a></div>
    </form>
</div></div>

<div class="x_panel"><div class="x_title"><h2>Administrative change ledger</h2><div class="clearfix"></div></div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Time</th><th>Operator</th><th>Action</th><th>Endpoint</th><th>Request context</th><th>Status</th></tr></thead><tbody>
@forelse($audits as $audit)
<tr><td>{{ $audit->created_at->format('M d, Y') }}<br><small>{{ $audit->created_at->format('H:i:s') }}</small></td><td><strong>{{ $audit->actor_name ?: 'Unknown' }}</strong><br><small>{{ $audit->ip_address }}</small></td><td><span class="audit-method">{{ $audit->method }}</span><br><small>{{ str_replace('_', ' ', $audit->event) }}</small></td><td><strong>{{ $audit->route ?: 'Unnamed route' }}</strong><br><small>{{ $audit->path }}</small></td><td><pre class="audit-payload">{{ $audit->request_data ? json_encode($audit->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'No request fields' }}</pre></td><td><span class="label label-success">{{ $audit->response_status }}</span></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted" style="padding:35px">No governance events match the selected filters.</td></tr>@endforelse
</tbody></table></div>
@if($audits->hasPages())<div class="audit-page"><span>Showing {{ $audits->firstItem() }}-{{ $audits->lastItem() }} of {{ $audits->total() }}</span><div>@if(!$audits->onFirstPage())<a class="btn btn-default btn-sm" href="{{ $audits->previousPageUrl() }}">Previous</a>@endif @if($audits->hasMorePages())<a class="btn btn-default btn-sm" href="{{ $audits->nextPageUrl() }}">Next</a>@endif</div></div>@endif
</div>
@endsection
