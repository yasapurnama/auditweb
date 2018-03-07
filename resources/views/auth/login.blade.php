@extends('layouts.auth')

@section('content')
<p class="text-center" style="padding-bottom: 5px">LOGIN TO CONTINUE</p>
<form id="login-form" action="{{ route('login') }}" method="POST">
    {{ csrf_field() }}
    <div id="form-username" class="form-group">
        <div class="input-group">
            <span class="input-group-addon addon-inside bg-gray">
                <i class="fa fa-user icon"></i>
            </span>
            <input type="text" class="form-control underlined" name="username" id="username" placeholder="Your username" value="{{ old('username') }}" required> 
        </div>
    </div>
    <div id="form-password" class="form-group{{ $errors->has('password') ? ' has-error' : $errors->has('username') ? ' has-error' : '' }}">
        <div class="input-group">
            <span class="input-group-addon addon-inside bg-gray">
                <i class="fa fa-unlock-alt"></i>
            </span>
            <input type="password" class="form-control underlined" name="password" id="password" placeholder="Your password" required> 
        </div>
        @if ($errors->has('username'))
            <span id="username-error" class="has-error">
                {{ $errors->first('username') }}
            </span>
        @endif
        @if ($errors->has('password'))
            <span id="password-error" class="has-error">
                {{ $errors->first('password') }}
            </span>
        @endif
    </div>
    <div class="form-group">
        <label for="remember" class="arrange-left">
            <input class="checkbox" id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <span>Remember me</span>
        </label>
        <a href="{{ route('password.request') }}" class="forgot-btn pull-right">Forgot password?</a>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-block btn-primary">Login</button>
    </div>
    <div class="form-group">
        <p class="text-muted text-center">Do not have an account?
            <a href="{{ route('register') }}">Sign Up!</a>
        </p>
    </div>
</form>
@endsection
