@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Create New Manager</h3>
        </div>

    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-8 col-md-offset-2">
        <div class="x_panel">
          <div class="x_title">
            <h2>Managers  </h2>
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

                <form id="demo-form2" method="POST" action="{{route('SuperAdmin.New.UserPost')}}" data-parsley-validate="" class="form-horizontal form-label-left" >
                        @csrf
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3">Manager Type</label>
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
                            <input name="name" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Manager Email <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="email" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                                </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">ID Card No #<span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="username" type="text" id="username" required="required" class="form-control col-md-7 col-xs-12">

                                </div>
                                <span id="username_panel">

                                </span>

                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Mobile Number <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="phoneno" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                                </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3">Under Constituency</label>
                            <div class="col-md-6">
                               <select class="form-control" name="constituency"  required>
                                   <option></option>

                                @if(count($belongTo)==0)
                                    <option value="0">This is Main Central Constituency</option>
                                @endif
                                @foreach ($belongTo as $To)
                                   <option value="{{$To->user_id}}"  >{{$To->user_type_name}} -- {{$To->user_name}}<option>
                                @endforeach
                              </select>
                            </div>
                          </div>

                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Password <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input name="password" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                                </div>
                        </div>
                        <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Gender <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class="radio">
                                                <label>
                                                  <input type="radio"  name="gender"  value="male" id="optionsRadios1" > Male
                                                </label>
                                              </div>
                                              <div class="radio">
                                                    <label>
                                                      <input type="radio"  name="gender"  value="female" id="optionsRadios1"> Female
                                                    </label>
                                                  </div>
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
            $("#username_panel").empty();
            $("#username_panel").append(`<span style="color:red">Lenght equall to 10th character<span>` )
            $('select option')
            .filter(function() {
                return !this.value && $.trim(this.value).length == 0 && $.trim(this.text).length == 0;
            })
            .remove();

             $('button[type="submit"]').attr('disabled','disabled');
        });
        $("#username").keyup(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            $("#username_panel").empty();
            $("#username_panel").append(`<span style="color:red">Lenght equall to 10th character<span>` )
            $('button[type="submit"]').attr('disabled','disabled');
            var value = $('#username').val();
            if(value.length == 10){

                var _token = $('input[name="_token"]').val();
                $("#username_panel").empty();
                $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                $.ajax({
                        type: "POST",
                        url: '{{route("SuperAdmin.VerifyUsername")}}',
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
@endsection

{{-- $("#submit").attr("disabled", false)
if (!result.error && result.status != "Error") {

    bootbox.alert("Your Order has been Placed.");
    $("#form").get(0).reset();

} else if (result.status == "success") {

    bootbox.alert("Your Order Placed Successfully.");
    $("#form").get(0).reset();

}
else {

    bootbox.alert(result.error?result.error:"something went wrong");
} --}}
