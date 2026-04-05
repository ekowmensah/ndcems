@extends('layouts.app_director')

@section('css')
<style>
    .collation-page {
        padding-bottom: 20px;
    }
    .collation-shell {
        background: linear-gradient(180deg, #f8fbf9 0%, #ffffff 100%);
        border: 1px solid #e8ecef;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        padding: 18px;
    }
    .collation-title {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: 0.3px;
        color: #1f2937;
        margin: 0;
    }
    .collation-sub {
        color: #64748b;
        font-size: 0.9rem;
    }
    .party-card {
        border-radius: 14px;
        padding: 14px;
        color: #0f172a;
        min-height: 140px;
        position: relative;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }
    .party-card.rank-1 { background: #dff5e7; }
    .party-card.rank-2 { background: #e6ecff; }
    .party-card.rank-3 { background: #ffe6ec; }
    .party-rank-badge {
        position: absolute;
        right: 12px;
        top: 12px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #fff;
        background: #ef4444;
        font-size: 0.8rem;
    }
    .party-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    .candidate-photo {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.8);
        background: #fff;
    }
    .party-main-name {
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.2;
        margin: 0;
    }
    .party-candidate {
        font-size: 0.8rem;
        color: #475569;
        margin: 0;
    }
    .party-stats {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 8px;
    }
    .party-votes {
        font-size: 0.95rem;
        font-weight: 700;
    }
    .party-percentage {
        background: rgba(15, 23, 42, 0.85);
        color: #fff;
        font-size: 0.88rem;
        font-weight: 800;
        border-radius: 8px;
        padding: 4px 10px;
    }
    .coverage-panel {
        background: #fff;
        border: 1px solid #e7ebef;
        border-radius: 14px;
        padding: 14px;
    }
    .coverage-label {
        font-size: 0.9rem;
        font-weight: 700;
        color: #334155;
    }
    .coverage-progress {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e6edf2;
        overflow: hidden;
        margin-top: 10px;
    }
    .coverage-progress > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #15803d);
        width: 0;
        transition: width .3s ease;
    }
    .coverage-meta {
        margin-top: 8px;
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 700;
    }
    .vote-strip {
        border-radius: 12px;
        padding: 12px 14px;
        color: #fff;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: 10px;
    }
    .vote-strip .value {
        font-size: 1.35rem;
        margin-top: 3px;
        text-transform: none;
        letter-spacing: 0;
    }
    .strip-valid { background: linear-gradient(120deg, #1f9f49, #147f37); }
    .strip-rejected { background: linear-gradient(120deg, #ef4444, #dc2626); }
    .breakdown-card {
        background: #fff;
        border: 1px solid #e8ecef;
        border-radius: 14px;
        overflow: hidden;
    }
    .breakdown-card .table thead th {
        background: #f8fafc;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        border-top: 0;
    }
    .last-updated {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
    }
    @media (max-width: 992px) {
        .collation-title { font-size: 1.45rem; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid collation-page">
    <div class="collation-shell">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
            <div>
                <h2 class="collation-title">{{ $constituencyCollation['title'] ?? 'NDC Election Collation' }}</h2>
                <div class="collation-sub">
                    {{ $constituencyCollation['context']['constituency_name'] ?? 'Constituency' }} Constituency
                    •
                    {{ $constituencyCollation['context']['region_name'] ?? 'Region' }} Region
                </div>
                <div class="collation-sub mt-1">
                    Election:
                    {{ $constituencyCollation['context']['election_name'] ?? 'Not specified' }}
                    @if(!empty($constituencyCollation['context']['election_type_name']))
                        ({{ $constituencyCollation['context']['election_type_name'] }})
                    @endif
                </div>
            </div>
            <div class="text-right mt-2 mt-md-0">
                <a href="{{ route('Director.Result') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-chart-line mr-1"></i> View Collated Results
                </a>
                <div class="last-updated mt-2" id="collation_last_updated">Updated {{ now()->format('H:i:s') }}</div>
            </div>
        </div>

        <div class="row" id="party_cards_container">
            @forelse(($constituencyCollation['top_parties'] ?? []) as $party)
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="party-card rank-{{ min(3, (int)($party['rank'] ?? 3)) }}">
                        <span class="party-rank-badge">{{ $party['rank'] ?? '-' }}</span>
                        <div class="party-head">
                            <img src="{{ $party['candidate_photo_url'] ?? asset('AdminLTE/dist/img/avatar5.png') }}" class="candidate-photo" alt="Candidate">
                            <div>
                                <p class="party-main-name mb-1">{{ $party['party_initial'] ?? 'N/A' }}</p>
                                <p class="party-candidate">{{ $party['party_name'] ?? 'Party' }}</p>
                            </div>
                        </div>
                        <div class="party-stats">
                            <div class="party-votes">Votes: {{ number_format($party['votes'] ?? 0) }}</div>
                            <div class="party-percentage">{{ number_format($party['percentage'] ?? 0, 2) }}%</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light border mb-0">No confirmed parliamentary collation data available yet.</div></div>
            @endforelse
        </div>

        <div class="row mt-1">
            <div class="col-lg-7 mb-3">
                <div class="coverage-panel">
                    <div class="coverage-label">POLLING STATIONS IN vs TOTAL POLLING STATIONS</div>
                    <div class="coverage-progress">
                        <span id="coverage_progress_bar" style="width: {{ min(100, max(0, (float)($constituencyCollation['coverage']['percent'] ?? 0))) }}%;"></span>
                    </div>
                    <div class="coverage-meta" id="coverage_meta">
                        {{ number_format($constituencyCollation['coverage']['percent'] ?? 0, 2) }}% /
                        {{ number_format($constituencyCollation['coverage']['reported'] ?? 0) }} of
                        {{ number_format($constituencyCollation['coverage']['total'] ?? 0) }}
                    </div>

                    <div class="vote-strip strip-valid mt-3">
                        TOTAL VALID VOTES
                        <div class="value" id="total_valid_votes_strip">{{ number_format($constituencyCollation['valid_votes'] ?? 0) }}</div>
                    </div>
                    <div class="vote-strip strip-rejected">
                        TOTAL REJECTED BALLOT
                        <div class="value" id="total_rejected_votes_strip">{{ number_format($constituencyCollation['rejected_votes'] ?? 0) }}
                            <small style="font-size:0.8rem;opacity:0.9;">({{ number_format($constituencyCollation['rejected_rate'] ?? 0, 2) }}%)</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-3">
                <div class="breakdown-card">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <strong>Top Polling Stations (Valid Votes)</strong>
                        <span class="badge badge-light">Constituency</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Polling Station</th>
                                    <th class="text-right">Valid Votes</th>
                                </tr>
                            </thead>
                            <tbody id="collation_station_table_body">
                                @forelse(($topStationsChart['labels'] ?? collect()) as $idx => $name)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $name }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format(($topStationsChart['valid_votes'][$idx] ?? 0)) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No station data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const collationDataUrl = "{{ route('Director.collationData') }}";
    const fallbackCandidateAvatar = "{{ asset('AdminLTE/dist/img/avatar5.png') }}";

    function renderPartyCards(parties) {
        const box = document.getElementById('party_cards_container');
        if (!box) return;
        if (!Array.isArray(parties) || parties.length === 0) {
            box.innerHTML = '<div class="col-12"><div class="alert alert-light border mb-0">No confirmed parliamentary collation data available yet.</div></div>';
            return;
        }
        let html = '';
        parties.forEach(function (party, index) {
            const rank = Number(party.rank || (index + 1));
            html += `
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="party-card rank-${Math.min(3, rank)}">
                        <span class="party-rank-badge">${rank}</span>
                        <div class="party-head">
                            <img src="${party.candidate_photo_url || fallbackCandidateAvatar}" class="candidate-photo" alt="Candidate" onerror="this.src='${fallbackCandidateAvatar}'">
                            <div>
                                <p class="party-main-name mb-1">${party.party_initial || 'N/A'}</p>
                                <p class="party-candidate">${party.party_name || 'Party'}</p>
                            </div>
                        </div>
                        <div class="party-stats">
                            <div class="party-votes">Votes: ${Number(party.votes || 0).toLocaleString()}</div>
                            <div class="party-percentage">${Number(party.percentage || 0).toFixed(2)}%</div>
                        </div>
                    </div>
                </div>
            `;
        });
        box.innerHTML = html;
    }

    function renderStations(topStationsChart) {
        const body = document.getElementById('collation_station_table_body');
        if (!body) return;
        const labels = (topStationsChart && topStationsChart.labels) ? topStationsChart.labels : [];
        const validVotes = (topStationsChart && topStationsChart.valid_votes) ? topStationsChart.valid_votes : [];

        if (!labels.length) {
            body.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No station data yet</td></tr>';
            return;
        }

        let html = '';
        labels.forEach(function(label, idx) {
            html += `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${label}</td>
                    <td class="text-right font-weight-bold">${Number(validVotes[idx] || 0).toLocaleString()}</td>
                </tr>
            `;
        });
        body.innerHTML = html;
    }

    function renderCollation(data) {
        const collation = data.constituencyCollation || {};
        const coverage = collation.coverage || {};

        renderPartyCards(collation.top_parties || []);

        const percent = Number(coverage.percent || 0);
        const reported = Number(coverage.reported || 0);
        const total = Number(coverage.total || 0);

        const bar = document.getElementById('coverage_progress_bar');
        if (bar) bar.style.width = `${Math.min(100, Math.max(0, percent))}%`;

        const meta = document.getElementById('coverage_meta');
        if (meta) {
            meta.textContent = `${percent.toFixed(2)}% / ${reported.toLocaleString()} of ${total.toLocaleString()}`;
        }

        const valid = document.getElementById('total_valid_votes_strip');
        if (valid) valid.textContent = Number(collation.valid_votes || 0).toLocaleString();

        const rejected = document.getElementById('total_rejected_votes_strip');
        if (rejected) {
            rejected.innerHTML = `${Number(collation.rejected_votes || 0).toLocaleString()} <small style="font-size:0.8rem;opacity:0.9;">(${Number(collation.rejected_rate || 0).toFixed(2)}%)</small>`;
        }

        renderStations(data.topStationsChart || {});

        const updated = document.getElementById('collation_last_updated');
        if (updated) updated.textContent = `Updated ${new Date().toLocaleTimeString()}`;
    }

    function loadCollationData() {
        fetch(collationDataUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(response) { return response.json(); })
            .then(function(data) { renderCollation(data); })
            .catch(function() {});
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initial = {
            constituencyCollation: @json($constituencyCollation),
            topStationsChart: @json($topStationsChart)
        };
        renderCollation(initial);
        setInterval(loadCollationData, 10000);
    });
</script>
@endsection
