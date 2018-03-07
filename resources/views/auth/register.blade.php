@extends('layouts.auth')


@section('content')
<p class="text-center" style="padding-bottom: 5px">SIGNUP TO GET INSTANT ACCESS</p>
<form id="signup-form" action="{{ route('register') }}" method="POST">
    {{ csrf_field() }}
    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
        <label for="name">Name</label>
        <input type="text" class="form-control underlined" name="name" id="name" placeholder="Enter name" value="{{ old('name') }}" required> 
        @if ($errors->has('name'))
            <span id="name-error" class="has-error">
                {{ $errors->first('name') }}
            </span>
        @endif
    </div>
    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email">Email</label>
        <input type="email" class="form-control underlined" name="email" id="email" placeholder="Enter email address" value="{{ old('email') }}" required>
        @if ($errors->has('email'))
            <span id="email-error" class="has-error">
                {{ $errors->first('email') }}
            </span>
        @endif
    </div>
    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
        <label for="username">Username</label>
        <input type="text" class="form-control underlined" name="username" id="username" placeholder="Enter username" value="{{ old('username') }}" required> 
        @if ($errors->has('username'))
            <span id="username-error" class="has-error">
                {{ $errors->first('username') }}
            </span>
        @endif
    </div>
    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password">Password</label>
        <div class="row">
            <div class="col-sm-6">
                <input type="password" class="form-control underlined" name="password" id="password" placeholder="Enter password" required>
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
            </div>
        </div>
    </div>
    {{-- <div class="form-group">
        <label for="agree">
            <input class="checkbox" name="agree" id="agree" type="checkbox" required="">
            <span>Agree the terms and
                <a href="#">policy</a>
            </span>
        </label>
    </div> --}}
    <div class="form-group">
        <button type="submit" class="btn btn-block btn-primary">Sign Up</button>
    </div>
    <div class="form-group">
        <p class="text-muted text-center"><em class="fa fa-sign-in"></em> Already have an account?
            <a href="{{ route('login') }}"> Login!</a>
        </p>
    </div>
</form>
@endsection
