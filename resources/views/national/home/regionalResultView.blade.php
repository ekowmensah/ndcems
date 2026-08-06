@extends('layouts.app_national_director')

@section('page_title', $regionalDetail->name.' Result')
@section('page_description', 'Detailed regional result breakdown for national performance review and escalation analysis.')
@section('page_actions')
    <a href="{{ route('National.Presidential') }}" class="btn btn-light">Back to Presidential Results</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Region Summary</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong> {{ $regionalDetail->name }}</p>
                    <p class="mb-0 text-muted">Result breakdown for the selected regional submission.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Party Breakdown</h3>
                </div>
                <div class="card-body">
                    <div id="regionalChartContainer" style="height: 370px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        $(function () {
            const chart = new CanvasJS.Chart("regionalChartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "{{ $regionalDetail->name }} Result"
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
