@extends('layouts.app_national')

@section('content')
<?php

/* $dataPoints = array(
	array("label"=> "WordPress", "y"=> 60.0),
	array("label"=> "Joomla", "y"=> 6.5),
	array("label"=> "Drupal", "y"=> 4.6),
	array("label"=> "Magento", "y"=> 2.4),
	array("label"=> "Blogger", "y"=> 1.9),
	array("label"=> "Shopify", "y"=> 1.8),
	array("label"=> "Bitrix", "y"=> 1.5),
	array("label"=> "Squarespace", "y"=> 1.5),
	array("label"=> "PrestaShop", "y"=> 1.3),
	array("label"=> "Wix", "y"=> 0.9),
	array("label"=> "OpenCart", "y"=> 0.8)
); */

?>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>

@endsection
@section("script")

<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


<script>
    var chart;
    window.onload = function () {

chart = new CanvasJS.Chart("chartContainer", {
    animationEnabled: true,
    theme: "light2",
    title: {
        text: "{{$constituency_detail->name}} Constituency Result"
    },

    data: [{
        type: "column",
        yValueFormatString: "#,###.##\"%\"",
         indexLabel: "{y}",
        indexLabelPlacement: "inside",
        indexLabelFontColor: "white",
        dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
    }]
});
chart.render();

/* var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	title: {
		text: "{{$constituency_detail->name}} Constituency Result"
	},
	axisX: {
		interval: 1
	},
	axisY: {
		title: "",
		scaleBreaks: {
			type: "wavy",
			customBreaks: [{
				startValue: 80,
				endValue: 210
				},
				{
					startValue: 230,
					endValue: 600
				}
		]}
	},
	data: [{
		type: "bar",
        yValueFormatString: "#,###.##\"%\"",
         indexLabel: "{y}",
        indexLabelPlacement: "inside",
        indexLabelFontColor: "white",
        dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
        	{ label: "Israel", y: 17.8, gdp: 5.8, url: "israel.png" }

	}]
});
chart.render(); */

}

    var data;
     function getData(){

        var _token = $('input[name="_token"]').val();

           $.ajax({
               url: "{{route('SuperAdmin.presidentialResultAjax')}}",
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
    /* setInterval(function(){
      // getData()
    }, 3000); */


    </script>

        @endsection
