@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Edit Political Party </h3>
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
                <form id="demo-form2" method="POST" action="{{route('SuperAdmin.Edit.editPoliticalPartyPost',$PoliticalParty->id)}}" enctype="multipart/form-data" data-parsley-validate="" class="form-horizontal form-label-left" >
                        @csrf
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Political Party Name <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input name="name" type="text" value="{{$PoliticalParty->name}}" id="first-name" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Political Party ID <span class="required"></span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="party_id" value="{{$PoliticalParty->party_id}}" type="text" id="first-name"  class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Party Initials <span class="required"></span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input name="party_initial" type="text" value="{{$PoliticalParty->party_initial}}" id="first-name"  class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">File Attachment <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="file" name="logo" class="form-control col-md-7 col-xs-12">
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
