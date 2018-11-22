@extends('layouts.app')

@section('content')
<div class="container">
        <br><br><br>
    <div class="row justify-content-center">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-primary text-center">{{ __('Reset Password') }}</div>

                <form method="POST" action="{{ route('password.email') }}">

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                        @csrf

                        @if ($errors->has('email'))
                                    <div class="alert alert-danger">
                                            {{ $errors->first('email') }}
                                    </div>
                                @endif
                        <div class="input-group">

                          <div class="input-group-prepend">
                            <span class="input-group-text">
                              <i class="material-icons">mail</i>
                            </span>

                          </div>


                          <input id="email" type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus placeholder="Email...">
                        </div>

                        {{-- <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                             <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif

                        </div> --}}

                        {{-- <div class="form-group row mb-0"> --}}
                            {{-- <div class="col-md-6 offset-md-4"> --}}
                               {{--  <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button> --}}
                            {{-- </div> --}}
                        {{-- </div> --}}

                        <div class="footer text-center">
                                <button  class="btn btn-primary btn-link btn-wd btn-lg" type="submit">Submit</button>
                        </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
