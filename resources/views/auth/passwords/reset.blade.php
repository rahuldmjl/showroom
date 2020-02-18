@extends('layout.registrationlayout')

@section('title', 'Reset Password')

@section('content')

<form method="POST" action="{{ route('password.update') }}">
    @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group row">
            <label for="email">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
               @error('email')
                   <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
        </div>
        <div class="form-group row">
            <label for="password" >{{ __('Password') }}</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
        </div>
        <div class="form-group row">
            <label for="password-confirm">{{ __('Confirm Password') }}</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
        </div>
        <div class="form-group row mb-0">

                <button type="submit" class="btn btn-block btn-color-scheme ripple">
                    {{ __('Reset Password') }}
                </button>

       </div>

@endsection
