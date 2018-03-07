@extends('layouts.auth')

@section('content')
<p class="text-center">PASSWORD RECOVER</p>
<p class="text-muted text-center">
    <small>Enter your email address to recover your password.</small>
</p>
@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
<form id="reset-form" action="{{ route('password.email') }}" method="POST">
    {{ csrf_field() }}
    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email1">Email</label>
        <input type="email" class="form-control underlined" name="email" id="email" placeholder="Your email address" value="{{ old('email') }}" required> 
        @if ($errors->has('email'))
            <span class="has-error">
                {{ $errors->first('email') }}
            </span>
        @endif
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-block btn-primary">Reset</button>
    </div>
    <div class="form-group clearfix">
        <div class="pull-left">
            <a class="pull-right" href="{{ route('register') }}"><em class="fa fa-user"></em> Register</a>
        </div>
        <div class="pull-right">
            <a href="{{ route('login') }}"><em class="fa fa-sign-in"></em> Login</a>
        </div>
    </div>
</form>
@endsection
