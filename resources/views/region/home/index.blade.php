@extends('layouts.app_national')
@section('content')
                <div class="container-fluid">
                        <br>
                        <div class="row">

                        <div class="col-md-12">
                            <div class="panel" >
                                <div class="panel-heading">

                                </div>
                                <div class="panel-body">
                                <div class="col-md-12">
<div id="chartContainer" style="height: 370px; width: 100%;"></div>

                                </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
@section('script')
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script>
    var chart;
    window.onload = function () {

    chart = new CanvasJS.Chart("chartContainer", {
    animationEnabled: true,
    theme: "light2",
    title: {
        text: "National Election Result"
    },
    axisY: {
        suffix: "%",
        scaleBreaks: {
          //  autoCalculate: true
        }
    },
    data: [{
        type: "column",
        yValueFormatString: "#,###.##\"%\"",

       // yValueFormatString: "#,##0\"%\"",
        indexLabel: "{y}",
        indexLabelPlacement: "inside",
        indexLabelFontColor: "white",
        dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
    }]
});
chart.render();

}

    var data;
     function getData(){

        var _token = $('input[name="_token"]').val();

           $.ajax({
               url: "{{route('Region.presidentialResultAjax')}}",
               type: 'POST',
               data: {
                   _token : _token
               },
               success: function (data1) {
                chart.options.data[0].dataPoints  = data1;
                chart.render()
               }
           });


    }
    setInterval(function(){
        getData()
    }, 3000);


    </script>
@endsection
