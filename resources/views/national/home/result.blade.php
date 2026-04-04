@extends('layouts.app_national_director')
@section('content')



            <div >
                <div class="container-fluid">
                    <br>

                    <div class="row">

                        <div class="col-md-12">
                            <div class="panel" >
                                <div class="panel-heading">
                                    <select  style="width:25vh;display: inline-block;" class="form-control filter" name="election_start_up_id" id="election_start_up_id"  required>
                                        <option value="all" >Select<option>
                                            @foreach ($details as $electionType)
                                                <option value="{{$electionType->id}}"  >{{$electionType->election_name}}<option>
                                             @endforeach
                                    </select>
                                    <select style="width:25vh;display: inline-block;" class="form-control filter" name="region_id" id="region_id"  required>
                                            <option value="all" >Regions (All)<option>
                                                @foreach ($regions as $electionType)
                                                <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                                                 @endforeach
                                        </select>
                                        <select style="width:25vh;display: inline-block;" class="form-control filter" name="constituency_id" id="constituency_id"  required>


                                            </select>
                                            <select style="width:25vh;display: inline-block;" class="form-control filter" name="electoralarea_id" id="electoralarea_id"  required>


                                                </select>
                                                <select style="width:25vh;display: inline-block; " class="form-control filter" name="polling_station_id" id="polling_station_id"  required>


                                                    </select>
                                </div>
                                <div class="panel-body">

                        <div id="chartContainer" style="height: 370px; width: 100%;"></div>

                                </div>
                            </div>
                        </div>


                    </div>
                    <!-- end new -->
                    <!-- new 1 -->

                </div>
                <!-- end new 1 -->
                <!-- new 1 -->

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
        text: "{{$election->name}}"
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
        dataPoints: []
    }]
});
chart.render();

}

var data;
     function getData(){
        var _token = $('input[name="_token"]').val();
        var constituency_id;
        var region_id;
        var electoralarea_id;
        var polling_station_id;
        var election_start_up_id;
        var election_type_id = "{{ $id}}"
            if(election_type_id!==""){
                election_type_id = election_type_id
            }
            else
            {
                election_type_id = "all"
            }

            if($('#constituency_id').val()){
                constituency_id = $('#constituency_id').val();
              }else {
                constituency_id ="all"
              }

            if($('#region_id').val()){
                region_id = $('#region_id').val();
              }else{
                region_id = "all"
              }
            if($('#electoralarea_id').val()){
                electoralarea_id = $('#electoralarea_id').val();
              }else{
                electoralarea_id = "all"
              }
            if($('#polling_station_id').val()){
                polling_station_id = $('#polling_station_id').val();
              }else{
                polling_station_id = "all"
              }
            if($('#election_start_up_id').val()){
                election_start_up_id = $('#election_start_up_id').val();
              }else{
                election_start_up_id = "all"
              }
        console.log("region_id : "+region_id)

           $.ajax({
               url: "{{route('National.allResultAjax')}}",
               type: 'POST',
               data: {
                   _token : _token,
                   election_start_up_id:election_start_up_id,
                   polling_station_id:polling_station_id,
                   electoralarea_id:electoralarea_id,
                   region_id:region_id,
                   constituency_id:constituency_id,
                   election_type_id:election_type_id

               },
               success: function (data1) {
                if(data1)
                    chart.options.data[0].dataPoints  = data1;
                else
                    chart.options.data[0].dataPoints  = [];

                chart.render()
               }
           });
    }

$('document').ready(function(){
    $(".filter").on('change', '', function (e) {
            getData()
        });
            $('select option')
            .filter(function() {
                return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
            })
            .remove();

    $("#region_id").on('change', '', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
        $("#constituency_id").empty();
        var region_id = $("#region_id").val()
           // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                var _token = $('input[name="_token"]').val();
            $.ajax({
                    type: "POST",
                    url: '{{route("National.getConstituency")}}',
                    data: {region_id:region_id,_token:_token},
                    //dataType: "JSON",
                    success: function (result) {

                        $('#constituency_id')
                                .append($("<option></option>")
                                            .attr("value","all")
                                            .text("Select Constituency"));
                        $.each(result, function(key, value) {

                            $('#constituency_id')
                                .append($("<option></option>")
                                            .attr("value",value.id)
                                            .text(value.name));
                        });
                       }
                });
            });

            $("#constituency_id").on('change', '', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                $("#electoralarea_id").empty();
                var constituency_id = $("#constituency_id").val()
                // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                        var _token = $('input[name="_token"]').val();
                    $.ajax({
                            type: "POST",
                            url: '{{route("National.getElectral")}}',
                            data: {constituency_id:constituency_id,_token:_token},
                            //dataType: "JSON",
                            success: function (result) {
                                $('#electoralarea_id')
                                        .append($("<option></option>")
                                                    .attr("value","all")
                                                    .text("Select Electral Area"));
                                $.each(result, function(key, value) {
                                    $('#electoralarea_id')
                                        .append($("<option></option>")
                                                    .attr("value",value.id)
                                                    .text(value.name));
                                });
                            }
                        });
                    });


                    $("#electoralarea_id").on('change', '', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                $("#polling_station_id").empty();
                var electoralarea_id = $("#electoralarea_id").val()
                // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                        var _token = $('input[name="_token"]').val();
                    $.ajax({
                            type: "POST",
                            url: '{{route("National.getPollingStation")}}',
                            data: {electoralarea_id:electoralarea_id,_token:_token},
                            //dataType: "JSON",
                            success: function (result) {
                                $('#polling_station_id')
                                        .append($("<option></option>")
                                                    .attr("value","all")
                                                    .text("Select Polling station"));
                                $.each(result, function(key, value) {
                                    $('#polling_station_id')
                                        .append($("<option></option>")
                                                    .attr("value",value.id)
                                                    .text(value.name));
                                });
                            }
                        });
                    });

                    $("#election_start_up_id").on('change', '', function (e) {
                        $('#region_id').val($("#region_id option:first").val());
                        $('#constituency_id').val($("#constituency_id option:first").val());
                        $('#electoralarea_id').val($("#electoralarea_id option:first").val());
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
                    $("#region_id").on('change', '', function (e) {
                        $('#constituency_id').val($("#constituency_id option:first").val());
                        $('#electoralarea_id').val($("#electoralarea_id option:first").val());
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
                    $("#constituency_id").on('change', '', function (e) {
                        $('#electoralarea_id').val($("#electoralarea_id option:first").val());
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
                    $("#electoralarea_id").on('change', '', function (e) {
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
            })
</script>
@endsection
