@extends('layout.registrationlayout')

@section('title', 'Login')

@section('content')

<form class="form-material" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
        <input id="email" type="email" class="form-control form-control-line @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        <label for="example-email">Email</label>
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <input id="password" type="password" class="form-control form-control-line @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
        <label>Password</label>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <button class="btn btn-block btn-lg btn-color-scheme ripple" type="submit">{{ __('Login') }}</button>
    </div>
    <div class="form-group no-gutters mb-0">
        <div class="col-md-12 d-flex">
            <div class="checkbox checkbox-info mr-auto">
                <label class="d-flex">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"{{old('remember') ? 'checked' : '' }}> <span class="label-text">{{ __('Remember Me') }}</span>
                </label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" id="to-recover" class="my-auto pb-2 text-right">
                    <i class="fa fa-lock mr-1"></i>{{ __('Forgot Password') }}?
                </a>
            @endif
        </div>
        <!-- /.col-md-12 -->
    </div>
    <!-- /.form-group -->
</form>
<!-- /.form-material -->

<div class="container" style="display: none;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
