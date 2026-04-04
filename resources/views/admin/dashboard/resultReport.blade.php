@extends('admin.layouts.app')

@section('content')
<br>
<div class="container">


    <div class="row">
        <div class="col-md-8 col-sm-8 col-xs-8 col-md-offset-2">
            <div class="x_panel">

              <div class="x_content ">
                    <form id="demo-form2" method="POST" action="{{route('SuperAdmin.resultReportPost',[$id])}}" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" >

                        @csrf
                        <input type="hidden" name="election_type_id" value="{{$id}}">
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Election <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select  style="width:25vh;display: inline-block;" class="form-control filter" name="election_start_up_id" id="election_start_up_id"  >
                {{-- <option value="all" >Constituencies (All)<option> --}}
                        <option value="all" >Select<option>

                    @foreach ($details as $electionType)
                        <option value="{{$electionType->id}}"  >{{$electionType->election_name}}<option>
                     @endforeach
            </select>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Regions <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select style="width:25vh;display: inline-block;" class="form-control filter" name="region_id" id="region_id"  >
                <option value="all" >Regions (All)<option>
                    @foreach ($regions as $electionType)
                    <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                     @endforeach
            </select>
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Constituency <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select style="width:25vh;display: inline-block;" class="form-control filter" name="constituency_id" id="constituency_id"  >


            </select>
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Electoral Area <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select style="width:25vh;display: inline-block;" class="form-control filter" name="electoralarea_id" id="electoralarea_id"  >


            </select>
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Polling Station <span class="required">*</span>
        </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select style="width:25vh;display: inline-block; " class="form-control filter" name="polling_station_id" id="polling_station_id"  >


            </select>
        </div>
      </div>

      <div class="ln_solid"></div>
                        <div class="form-group">
                          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button class="btn btn-primary" type="reset">Reset</button>
                            <button type="submit" class="btn btn-success">Export Xlx</button>
                          </div>
                        </div>
    </form>
</div>
</div>
</div>
</div>
<br><br>

</div>
@endsection
@section("script")
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script>



    $('document').ready(function(){

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
                        url: '{{route("SuperAdmin.getConstituency")}}',
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
                                url: '{{route("SuperAdmin.getElectral")}}',
                                data: {constituency_id:constituency_id,_token:_token},
                                //dataType: "JSON",
                                success: function (result) {
                                    $('#electoralarea_id')
                                            .append($("<option></option>")
                                                        .attr("value","all")
                                                        .text("Select Electoral Area"));
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
                            url: '{{route("SuperAdmin.getPollingStation")}}',
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
