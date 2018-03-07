@extends('layouts.auth')

@section('content')
<p class="text-center" style="padding-bottom: 5px">RESET PASSWORD</p>
<form id="login-form" action="{{ route('password.request') }}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email">Email</label>
        <input type="email" class="form-control underlined" name="email" id="email" placeholder="Enter email address" value="{{ $email or old('email') }}" required>
        @if ($errors->has('email'))
            <span id="email-error" class="has-error">
                {{ $errors->first('email') }}
            </span>
        @endif
    </div>
    <div class="form-group{{ $errors->has('password') ? ' has-error' : $errors->has('password_confirmation') ? ' has-error' : '' }}">
        <label for="password">Password</label>
        <div class="row">
            <div class="col-sm-6">
                <input type="password" class="form-control underlined" name="password" id="password" placeholder="New password" required>
                @if ($errors->has('password'))
                    @if (!str_contains($errors->first('password'), 'password confirmation'))
                        <span id="password-error" class="has-error">
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                @endif 
            </div>
            <div class="col-sm-6">
                <input type="password" class="form-control underlined" name="password_confirmation" id="retype_password" placeholder="Re-type password" required>
                @if ($errors->has('password'))
                    @if (str_contains($errors->first('password'), 'password confirmation'))
                        <span id="password-error" class="has-error">
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                @endif
                @if ($errors->has('password_confirmation'))
                    <span class="has-error">
                        {{ $errors->first('password_confirmation') }}
                    </span>
                @endif 
            </div>
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-block btn-primary">Reset password</button>
    </div>
    <div class="form-group">
        <p class="text-muted text-center">Do not have an account?
            <a href="{{ route('register') }}">Sign Up!</a>
        </p>
    </div>
</form>
@endsection
