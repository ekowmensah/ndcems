@extends('layouts.app_director')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Edit Candidate </h3>
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
                <form id="demo-form2" method="POST" action="{{route('Director.candidateEditPost',$candidate->id)}}" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" >
                        @csrf
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Election Type <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="alert alert-success alert-dismissible fade in" style="margin: 0 !important;" role="alert">
                                    {{$type->name }}
                                  </div>
                            </div>
                          </div>
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">First Name <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input name="first_name" value="{{$candidate->first_name}}" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Last Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="last_name" value="{{$candidate->last_name}}" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">ID Card No <span class="required"></span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="id_no" value="{{$candidate->id_no}}" type="text" id="first-name"  class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Phone No <span class="required"></span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="phone" value="{{$candidate->phone}}" type="text" id="first-name"  class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>

                          {{-- <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Date Of Birth <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="dob" type="text" value="{{$candidate->dob}}" required id="dob" placeholder="DD-MM-YYYY" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div> --}}
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Elections  <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select class="form-control " data-live-search="true" name="election_start_up_id" id="election_start_up_id"  required>

                                        <option  >Select Elections<option>
                                            @foreach ($electionStartupDetail as $electionType)
                                                    <option value="{{$electionType->id}}" @if($candidate->election_start_up_id == $electionType->id) selected  @endif >{{$electionType->election_name}}<option>
                                                @endforeach
                                    </select>
                            </div>
                        </div>
                          <input name="country_id" id="country_id" value="{{$country[0]['id']}}" type="hidden" >

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Political Party  <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select class="form-control .searchabled selectpicker" data-live-search="true" name="party_id" id="party_id"  required>

                                        <option value="s" disabled >Select Political Party<option>
                                            @foreach ($PoliticalParties as $electionType)
                                                    <option value="{{$electionType->id}}" @if($candidate->party_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                                @endforeach
                                       </select>
                            </div>
                            </div>
                            <input type="hidden" name="election_id" value="{{$type->id}}">
                         {{--  <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Election  <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select class="form-control "  name="election_id" id="election_id"  required>

                                        <option value="s" disabled selected>Select Election Type<option>
                                            @foreach ($electionTypes as $electionType)
                                                    <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                                                @endforeach
                                       </select>
                            </div>
                            </div> --}}
                                @if($NewElectionTypes[0])

                                @endif
                                @if(isset($NewElectionTypes[1]) && $NewElectionTypes[1] && !isset($NewElectionTypes[2]))
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Region  <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                            <select class="form-control .searchabled selectpicker" data-live-search="true" name="region_id" id="region_id"  required>
                                                    <option value="s" disabled >Select Region<option>
                                                            @foreach ($regions as $electionType)
                                                        <option value="{{$electionType->id}}" @if($candidate->region_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                                    @endforeach
                                            </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Constituency  <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                            <select class="form-control" name="constituency_id" id="constituency_id"  required>
                                                    @foreach ($Constituencies as $electionType)
                                                    <option value="{{$electionType->id}}" @if($candidate->constituency_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                                @endif
                                @if(isset($NewElectionTypes[2]) && $NewElectionTypes[2])
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Region  <span class="required">*</span>
                                        </label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                <select class="form-control "  name="region_id" id="region_id"  required>
                                                        @foreach ($regions as $electionType)
                                                        <option value="{{$electionType->id}}" @if($candidate->region_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                                    @endforeach
                                                </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Constituency  <span class="required">*</span>
                                        </label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                <select class="form-control" name="constituency_id" id="constituency_id"  required>
                                                        @foreach ($Constituencies as $electionType)
                                                        <option value="{{$electionType->id}}" @if($candidate->constituency_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                                    @endforeach
                                                </select>
                                        </div>
                                    </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Electoral Area  <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                         <select class="form-control" name="electoral_area_id" id="electoralarea_id"  required>
                                                        @foreach ($ElectoralAreas as $electionType)
                                                        <option value="{{$electionType->id}}" @if($candidate->electoral_area_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                                    @endforeach
                                                </select>

                                </div>
                            </div>
                            {{-- @endif
                            @if(isset($NewUserTypes[4])) --}}
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Polling Station  <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                        <select class="form-control" name="polling_station_id" id="polling_station_id"  required>
                                            @foreach ($pollings as $electionType)
                                                <option value="{{$electionType->id}}" @if($candidate->polling_station_id == $electionType->id) selected  @endif>{{$electionType->name}}<option>
                                            @endforeach
                                           </select>
                                </div>
                            </div>
                            @endif
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Profile Photo <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="file" name="photo"   class="form-control col-md-7 col-xs-12">
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Personal <span class="required"></span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <textarea class="form-control" name="personal" rows="5" id="comment">{{$candidate->personal}}</textarea>
                                </div>
                              </div>

                        {{-- <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Political Party ID <span class="required"></span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="party_id" type="text" id="first-name"  class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Party Initials <span class="required"></span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="party_initial" type="text" id="first-name"  class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">File Attachment <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="file" name="logo"  required class="form-control col-md-7 col-xs-12">
                            </div>

                        </div> --}}

                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Order Positioning  <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">

                            <input type="number" name="ordering_position" value="{{$candidate->ordering_position}}" id="ordering_position" class="form-control " data-live-search="true" required>

                            </div>

                            <span id="ordering_position_panel">

                            </span>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" />
<script>
    $('document').ready(function(){
        $("#ordering_position_panel").empty();
        $("#ordering_position_panel").append(`<span style="color:red">Must Be Unique<span>` )
        $('select option')
        .filter(function() {
            return !this.value && $.trim(this.value).length == 0 && $.trim(this.text).length == 0;
        })
        .remove();

         $('button[type="submit"]').attr('disabled','disabled');
    });
    $("#ordering_position").keyup(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        $("#ordering_position_panel").empty();
        $("#ordering_position_panel").append(`<span style="color:red">Must be unique.<span>` )
        $('button[type="submit"]').attr('disabled','disabled');
        var value = $('#ordering_position').val();
        var election_start_up_id = $('#election_start_up_id').val();
        var constituency_id = $('#constituency_id').val();

        if(value.length >0 && election_start_up_id != "all"){

            var _token = $('input[name="_token"]').val();
            $("#ordering_position_panel").empty();
            $("#ordering_position_panel").append(`<span class="fa fa-spinner"><span>` )
            $.ajax({
                    type: "POST",
                    url: '{{route("Director.VerifyPositioningOrdering")}}',
                    data: {ordering_position:value,_token:_token,election_start_up_id:election_start_up_id,constituency_id:constituency_id},
                    //dataType: "JSON",
                    success: function (result) {
                        $("#ordering_position_panel").empty();
                        if(result == "faund"){
                            $("#ordering_position_panel").append(`<span class="fa fa-close" style="color:red"><span>` )

                        }else{
                            $('button[type="submit"]').removeAttr('disabled');
                            $("#ordering_position_panel").append(`<span class="fa fa-check" style="color:green"><span>` )

                        }
                    }
                });
        }

    })
</script>
<script>
     $('document').ready(function(){
            $('select option')
            .filter(function() {
                return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
            })
            .remove();
            $(function() {
                $('.searchabled').selectpicker();
            });

            //$("#country_id").on('change', '', function (e) {
                    /* $.ajaxSetup({
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
                            }); */
                       // });

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
                                                        .attr("value","Select")
                                                        .text("Select Polling Station"));
                                    $.each(result, function(key, value) {
                                        $('#polling_station_id')
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
