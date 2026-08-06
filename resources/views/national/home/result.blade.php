@extends('layouts.app_national_director')

@section('page_title', 'Result Explorer')
@section('page_description', 'Interactive national explorer for verified results, from election startup down to polling station level.')
@section('page_badges')
    <span class="national-badge is-accent"><i class="fas fa-layer-group"></i> {{ $selectedStartupName }}</span>
    <span class="national-badge"><i class="fas fa-filter"></i> Multi-level filtering</span>
@endsection

@section('content')
    @include('national.home.partials.result-analytics-styles')

    <div class="result-shell">
        <div class="result-toolbar">
            <div class="form-group">
                <label for="election_start_up_id">Election Startup</label>
                <select class="form-control filter" name="election_start_up_id" id="election_start_up_id">
                    <option value="all">All startups</option>
                    @foreach ($details as $detail)
                        <option value="{{ $detail->id }}" {{ (int) $selectedStartupId === (int) $detail->id ? 'selected' : '' }}>
                            {{ $detail->election_name }}{{ (int) $detail->status === 1 ? ' • Active' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="region_id">Region</label>
                <select class="form-control filter" name="region_id" id="region_id">
                    <option value="all">All regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="constituency_id">Constituency</label>
                <select class="form-control filter" name="constituency_id" id="constituency_id">
                    <option value="all">All constituencies</option>
                </select>
            </div>
            <div class="form-group">
                <label for="electoralarea_id">Electoral Area</label>
                <select class="form-control filter" name="electoralarea_id" id="electoralarea_id">
                    <option value="all">All electoral areas</option>
                </select>
            </div>
            <div class="form-group">
                <label for="polling_station_id">Polling Station</label>
                <select class="form-control filter" name="polling_station_id" id="polling_station_id">
                    <option value="all">All polling stations</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">{{ $election->name }} Share Explorer</h3>
                            <div class="result-panel__sub">The chart updates from verified results only, based on your selected filters.</div>
                        </div>
                        <span class="result-chip"><i class="fas fa-chart-pie"></i> Live filter output</span>
                    </div>
                    <div class="result-panel__body">
                        <div style="height: 390px;">
                            <canvas id="resultExplorerChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="result-panel">
                    <div class="result-panel__header">
                        <div>
                            <h3 class="result-panel__title">Current Scope</h3>
                            <div class="result-panel__sub">Keep track of the exact filter context behind the chart.</div>
                        </div>
                    </div>
                    <div class="result-panel__body">
                        <div class="result-summary-grid">
                            <div class="detail-stat">
                                <div class="detail-stat__label">Election Type</div>
                                <div class="detail-stat__value" style="font-size:1.1rem;">{{ $election->name }}</div>
                            </div>
                            <div class="detail-stat">
                                <div class="detail-stat__label">Startup Context</div>
                                <div class="detail-stat__value" id="scope_startup" style="font-size:1.1rem;">{{ $selectedStartupName }}</div>
                            </div>
                            <div class="detail-stat">
                                <div class="detail-stat__label">Region Scope</div>
                                <div class="detail-stat__value" id="scope_region" style="font-size:1.1rem;">All regions</div>
                            </div>
                            <div class="detail-stat">
                                <div class="detail-stat__label">Lowest Active Scope</div>
                                <div class="detail-stat__value" id="scope_lowest" style="font-size:1.1rem;">National</div>
                            </div>
                        </div>
                        <div class="mt-4 result-panel__sub">
                            This explorer defaults to the selected startup for clarity, but you can switch to <strong>All startups</strong> when comparing historical totals.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const initialBreakdown = @json($initialBreakdown);
        const electionTypeId = @json($id);
        const chartEndpoint = @json(route('National.allResultAjax'));
        const constituencyEndpoint = @json(route('National.getConstituency'));
        const electoralAreaEndpoint = @json(route('National.getElectral'));
        const pollingStationEndpoint = @json(route('National.getPollingStation'));
        const csrfToken = @json(csrf_token());

        let explorerChart = null;

        function buildChart(rows) {
            const labels = rows.map(item => item.party_initial ? `${item.party_initial} (${item.percentage}%)` : item.label);
            const values = rows.map(item => Number(item.votes || item.y || 0));
            const ctx = document.getElementById('resultExplorerChart');

            if (explorerChart) {
                explorerChart.data.labels = labels;
                explorerChart.data.datasets[0].data = values;
                explorerChart.update();
                return;
            }

            explorerChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Votes',
                        data: values,
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
        }

        function getSelectedText(selector) {
            const element = document.querySelector(selector);
            if (!element || !element.selectedOptions.length) {
                return 'All';
            }
            return element.selectedOptions[0].text.trim();
        }

        function refreshScopeSummary() {
            document.getElementById('scope_startup').textContent = getSelectedText('#election_start_up_id');
            document.getElementById('scope_region').textContent = getSelectedText('#region_id');

            let lowest = 'National';
            if ($('#polling_station_id').val() && $('#polling_station_id').val() !== 'all') {
                lowest = getSelectedText('#polling_station_id');
            } else if ($('#electoralarea_id').val() && $('#electoralarea_id').val() !== 'all') {
                lowest = getSelectedText('#electoralarea_id');
            } else if ($('#constituency_id').val() && $('#constituency_id').val() !== 'all') {
                lowest = getSelectedText('#constituency_id');
            } else if ($('#region_id').val() && $('#region_id').val() !== 'all') {
                lowest = getSelectedText('#region_id');
            }

            document.getElementById('scope_lowest').textContent = lowest;
        }

        function requestResults() {
            $.ajax({
                url: chartEndpoint,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    election_type_id: electionTypeId,
                    election_start_up_id: $('#election_start_up_id').val() || 'all',
                    region_id: $('#region_id').val() || 'all',
                    constituency_id: $('#constituency_id').val() || 'all',
                    electoralarea_id: $('#electoralarea_id').val() || 'all',
                    polling_station_id: $('#polling_station_id').val() || 'all'
                },
                success: function (rows) {
                    buildChart(rows || []);
                    refreshScopeSummary();
                }
            });
        }

        function loadOptions(url, payload, targetSelector, placeholder) {
            $.ajax({
                url: url,
                type: 'POST',
                data: Object.assign({_token: csrfToken}, payload),
                success: function (rows) {
                    const select = $(targetSelector);
                    select.empty().append(`<option value="all">${placeholder}</option>`);
                    $.each(rows, function (_, row) {
                        select.append(`<option value="${row.id}">${row.name}</option>`);
                    });
                    select.trigger('change.select2');
                }
            });
        }

        $(function () {
            buildChart(initialBreakdown);
            refreshScopeSummary();

            $('.filter').on('change', function () {
                requestResults();
            });

            $('#region_id').on('change', function () {
                $('#constituency_id').html('<option value="all">All constituencies</option>');
                $('#electoralarea_id').html('<option value="all">All electoral areas</option>');
                $('#polling_station_id').html('<option value="all">All polling stations</option>');

                if ($(this).val() !== 'all') {
                    loadOptions(constituencyEndpoint, {region_id: $(this).val()}, '#constituency_id', 'All constituencies');
                }
            });

            $('#constituency_id').on('change', function () {
                $('#electoralarea_id').html('<option value="all">All electoral areas</option>');
                $('#polling_station_id').html('<option value="all">All polling stations</option>');

                if ($(this).val() !== 'all') {
                    loadOptions(electoralAreaEndpoint, {constituency_id: $(this).val()}, '#electoralarea_id', 'All electoral areas');
                }
            });

            $('#electoralarea_id').on('change', function () {
                $('#polling_station_id').html('<option value="all">All polling stations</option>');

                if ($(this).val() !== 'all') {
                    loadOptions(pollingStationEndpoint, {electoralarea_id: $(this).val()}, '#polling_station_id', 'All polling stations');
                }
            });
        });
    </script>
@endsection
