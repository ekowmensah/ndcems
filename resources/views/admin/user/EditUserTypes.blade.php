@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Create New Manager Type <small> E.g Polling Agent</small></h3>
        </div>

    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-8 col-md-offset-2">
        <div class="x_panel">
          <div class="x_title">
            <h2>Manager Types </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">
                <form id="demo-form2" method="POST" action="{{route('SuperAdmin.Edit.UserTypes')}}" data-parsley-validate="" class="form-horizontal form-label-left" >
                        @csrf
                        <input type="hidden" name="id" value="{{$UserTypes->id}}">
                        <input type="hidden" name="oldName" value="{{$UserTypes->name}}">
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Manager Type Name <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input name="name" value="{{$UserTypes->name}}" type="text" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3">Parent Type</label>
                            <div class="col-md-6">
                               <select class="form-control" name="parent" required>
                                <option value="0">Select</option>
                                @foreach ($AllUserTypes as $UserType)
                                    @if($UserType->id == $UserTypes->id)
                                        @continue
                                    @endif
                                   <option value="{{$UserType->id}}" @if($UserType->id == $UserTypes->parent) selected @endif>{{$UserType->name}}<option>
                                @endforeach
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


       });
</script>
        @endsection
