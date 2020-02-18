@extends('layout.registrationlayout')

@section('title', 'Forgot Password')

@section('content')

<form class="form-material" method="POST" action="{{ route('password.email') }}">
    <p class="text-center text-muted">Enter your email address and we'll send you an email with instructions to reset your password.</p>
     <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
    @csrf
    
    <div class="form-group">
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        <label for="email">{{ __('E-Mail Address') }}</label>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                    @enderror
    </div>
    
    <div class="form-group row mb-0">

            <button type="submit" class="btn btn-block btn-color-scheme ripple" >
                {{ __('Send Password Reset Link') }}
            </button>
        </div>

    <div>
        <p style="text-align: center;padding-bottom: 0px;padding-top: 26px;">Back to
            <a href="{{url('login')}}" class="text-primary m-l-5">
                <b>Login</b>
            </a>
        </p>
    </div>
    </form>

@endsection