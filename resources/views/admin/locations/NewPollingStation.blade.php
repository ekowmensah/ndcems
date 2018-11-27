@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Add Polling Station<small> </small></h3>
        </div>

    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-8 col-md-offset-2">
        <div class="x_panel">
          <div class="x_title">
            <div class="clearfix"></div>
          </div>
          <div class="x_content ">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <form id="demo-form2" method="POST" action="{{route('SuperAdmin.New.PollingStation')}}" data-parsley-validate="" class="form-horizontal form-label-left" >
                        @csrf
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Polling Station Name <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input name="name" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Polling Station ID <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="polling_station_id" type="text" id="polling_station_id" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Total Voter <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="total_voters" type="text" id="total_voters" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Total Candidates <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="total_candidates" type="text" id="total_candidates" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Country  <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                        <select class="form-control" name="country_id" id="country_id" required>
                                            <option value="sec" >Select Country<option>
                                           @foreach ($countries as $country)
                                                <option value="{{$country->id}}"  >{{$country->name}}<option>
                                             @endforeach
                                           </select>
                                </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Region  <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                        <select class="form-control" name="region_id" id="region_id"  required>

                                           </select>
                                </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Constituency  <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select class="form-control" name="constituency_id" id="constituency_id"  required>

                                       </select>
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Electral Area  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                                <select class="form-control" name="electoralarea_id" id="electoralarea_id"  required>

                                   </select>
                        </div>
                </div>



                        <div class="ln_solid"></div>
                        <div class="form-group">
                          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button class="btn btn-primary" type="reset">Reset</button>
                            <button type="submit" class="btn btn-success">Submit</button>
                          </div>
                        </div>

                      </form>


          </div>
        </div>
      </div>
</div>
@endsection

@section("script")
<script src="{{ asset('js/bootstrap-validator/validator.min.js') }}"></script>
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
                                                .attr("value","Select")
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

                // new

                $("#country_id").on('change', '', function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    $("#region_id").empty();
                    var country_id = $("#country_id").val()
                    // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                            var _token = $('input[name="_token"]').val();
                        $.ajax({
                                type: "POST",
                                url: '{{route("SuperAdmin.getRegion")}}',
                                data: {country_id:country_id,_token:_token},
                                //dataType: "JSON",
                                success: function (result) {
                                    $('#region_id')
                                            .append($("<option></option>")
                                                        .attr("value","Select")
                                                        .text("Select Region"));
                                    $.each(result, function(key, value) {
                                        $('#region_id')
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
                                                        .attr("value","Select")
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
        });
</script>
        @endsection
