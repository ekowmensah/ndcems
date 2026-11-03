@extends('admin.layouts.app')

@section('page_title', 'Confirm Your Identity')
@section('page_description', 'Sensitive administrative actions require a recent password confirmation. Confirmation remains valid for 15 minutes.')

@section('content')
<div class="row"><div class="col-md-6 col-md-offset-3"><div class="x_panel"><div class="x_title"><h2>Security checkpoint</h2><div class="clearfix"></div></div><div class="x_content">
    <div class="alert alert-warning"><i class="fa fa-lock"></i> Enter your administrator password to continue to the requested action.</div>
    @if(isset($errors) && $errors->any())<div class="alert alert-danger">{{ $errors->first('password') }}</div>@endif
    <form method="POST" action="{{ route('SuperAdmin.security.confirm.store') }}">@csrf
        <div class="form-group"><label for="admin-confirm-password">Administrator password</label><input id="admin-confirm-password" type="password" name="password" class="form-control" required autofocus autocomplete="current-password"></div>
        <button class="btn btn-primary" type="submit"><i class="fa fa-shield"></i> Confirm and continue</button>
        <a class="btn btn-default" href="{{ route('SuperAdmin.home') }}">Cancel</a>
    </form>
</div></div></div></div>
@endsection
