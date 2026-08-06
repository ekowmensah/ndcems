@extends('layouts.app_national_director')

@section('page_title', $constituency_detail->name.' Result')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Constituency Summary</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong> {{ $constituency_detail->name }}</p>
                    <p class="mb-2"><strong>Region ID:</strong> {{ $constituency_detail->region_id }}</p>
                    <p class="mb-0 text-muted">Current party-share view for the selected constituency.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Party Breakdown</h3>
                </div>
                <div class="card-body">
                    <div id="constituencyChartContainer" style="height: 370px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        $(function () {
            const chart = new CanvasJS.Chart("constituencyChartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "{{ $constituency_detail->name }} Constituency Result"
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
                    dataPoints: @json($dataPoints, JSON_NUMERIC_CHECK)
                }]
            });

            chart.render();
        });
    </script>
@endsection
