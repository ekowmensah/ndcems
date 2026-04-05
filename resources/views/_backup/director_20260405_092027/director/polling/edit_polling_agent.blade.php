@extends('layouts.app_director')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3></h3>
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

                <form id="demo-form2" method="POST" action="{{route('Director.EditUserPost')}}" data-parsley-validate="" class="form-horizontal form-label-left" >
                        @csrf
                        <input type="hidden" value="{{$User->id}}"  name="id">
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3"> Type</label>
                                <div class="col-md-6">
                                        <div class="alert alert-success alert-dismissible fade in" style="margin: 0 !important;" role="alert">
                                                {{$Type->name }}
                                              </div>
                                    <input type="hidden" name="user_type_id" value="{{$Type->id }}" >
                                   {{-- <select class="form-control" name="user_type_id"  required>

                                    @foreach ($UserTypes as $UserType)
                                       <option value="{{$UserType->id}}"  @if($Type->id == $UserType->id) selected @endif  >{{$UserType->name}}<option>
                                    @endforeach
                                  </select> --}}
                                </div>
                              </div>

                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Manager Name <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                          <input name="name" value="{{$User->name}}" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Manager Email <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="email" value="{{$User->email}}" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                                </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">ID Card No #<span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="username" value="{{$User->username}}"  type="text" id="username" required="required" class="form-control col-md-7 col-xs-12">

                                </div>
                                <span id="username_panel">

                                </span>

                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Mobile Number <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="phoneno" type="text" value="{{$User->phoneno}}" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                                </div>
                        </div>
                       {{--  <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3">Under Constituency</label>
                            <div class="col-md-6">
                               <select class="form-control" name="constituency"  required>
                                   <option></option>

                                @if(count($belongTo)==0 && $Type->id == 0)
                                    <option value="0">Country</option>
                                @endif
                                @foreach ($belongTo as $To)
                                   <option value="{{$To->user_id}}"  >{{$To->user_type_name}} -- {{$To->user_name}}<option>
                                @endforeach
                              </select>
                            </div>
                          </div> --}}

                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Password <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="password" type="text" id="first-name"  class="form-control col-md-7 col-xs-12">
                                </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Gender <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class="radio">
                                                <label>
                                                  <input type="radio" @if($User->gender == "male") checked="" @endif  name="gender"  value="male" id="optionsRadios1" > Male
                                                </label>
                                              </div>
                                              <div class="radio">
                                                    <label>
                                                      <input type="radio" @if($User->gender == "female") checked="" @endif  name="gender"  value="female" id="optionsRadios1"> Female
                                                    </label>
                                                  </div>
                                </div>
                        </div>
                        @if(isset($NewUserTypes[0]))
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Country  <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                        <select class="form-control" name="country_id" id="country_id" required disabled>
                                            <option value="sec" >Select Country<option>
                                        @foreach ($countries as $country)
                                                <option value="{{$country->id}}" @if($country->id == $User->country_id) selected @endif >{{$country->name}}<option>
                                            @endforeach
                                        </select>
                                </div>
                        </div>
                    @endif
                    @if(isset($NewUserTypes[1]))
                    <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Region  <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select class="form-control" name="region_id" id="region_id"  required disabled>
                                            <option value="sec" >Select Region<option>
                                                    @foreach ($regions as $regions)
                                                            <option value="{{$regions->id}}" @if($regions->id == $User->region_id) selected @endif >{{$regions->name}}<option>
                                                        @endforeach
                                    </select>

                            </div>
                    </div>
                    @endif
                    @if(isset($NewUserTypes[2]))
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Constituency  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                                <select class="form-control" name="constituency_id" id="constituency_id"  required disabled>
                                        <option value="sec" >Select Constituency<option>
                                                @foreach ($constituency as $regions)
                                                        <option value="{{$regions->id}}" @if($regions->id == $User->constituency_id) selected @endif >{{$regions->name}}<option>
                                                    @endforeach
                                </select>
                        </div>
                </div>
                @endif
                @if(isset($NewUserTypes[3]))
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Electoral Area  <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                            <select class="form-control" name="electoralarea_id" id="electoralarea_id"  required>
                                    <option value="sec" >Electoral Area<option>
                                            @foreach ($electoralarea as $regions)
                                                    <option value="{{$regions->id}}" @if($regions->id == $User->electoralarea_id) selected @endif >{{$regions->name}}<option>
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
                                    <option value="sec" >Polling Station<option>
                                            @foreach ($pollingstation as $regions)
                                                    <option value="{{$regions->id}}" @if($regions->id == $User->polling_station_id) selected @endif >{{$regions->name}}<option>
                                                @endforeach
                               </select>
                    </div>
                </div>
                @endif


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
            $("#username_panel").empty();
           //$("#username_panel").append(`<span style="color:red">Lenght equall to 10th character<span>` )
            $('select option')
            .filter(function() {
                return !this.value && $.trim(this.value).length == 0 && $.trim(this.text).length == 0;
            })
            .remove();

             //$('button[type="submit"]').attr('disabled','disabled');
        });
        $("#username").keyup(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            $("#username_panel").empty();
            //$("#username_panel").append(`<span style="color:red">Lenght equall to 10th character<span>` )
            //$('button[type="submit"]').attr('disabled','disabled');
            var value = $('#username').val();
            if(value.length == 10){

                var _token = $('input[name="_token"]').val();
                $("#username_panel").empty();
                $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                $.ajax({
                        type: "POST",
                        url: '{{route("Director.VerifyUsername")}}',
                        data: {username:value,_token:_token},
                        //dataType: "JSON",
                        success: function (result) {
                            console.log("dd"+result);
                            $("#username_panel").empty();
                            if(result == "faund"){
                                $("#username_panel").append(`<span class="fa fa-close" style="color:red"><span>` )

                            }else{
                                $('button[type="submit"]').removeAttr('disabled');
                                $("#username_panel").append(`<span class="fa fa-check" style="color:green"><span>` )

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
                        url: '{{route("Director.getConstituency")}}',
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
                                url: '{{route("Director.getRegion")}}',
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
                                url: '{{route("Director.getElectral")}}',
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
                                url: '{{route("Director.getPollingStation")}}',
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
