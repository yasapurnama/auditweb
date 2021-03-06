@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-6 col-sm-12">
                                <form id="profile-edit" action="{{ route('editprofile') }}" method="POST">
                                {{ csrf_field() }}
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> {{ Auth::user()->role == 2 ? "Admin" : "User" }} Profile </p>
                                        </div>
                                    </div>
                                    <div class="card-block" style="padding: 10px 50px 30px 50px">
                                    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                        <label for="username">Username</label>
                                        <input type="text" class="form-control" name="username" id="username" placeholder="Enter username" value="{{ Auth::user()->username }}" readonly="readonly"> 
                                        @if ($errors->has('username'))
                                            <span id="username-error" class="has-error">
                                                {{ $errors->first('username') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" name="name" id="name" placeholder="Enter name" value="{{ old('name') ? old('name') : Auth::user()->name }}" required> 
                                        @if ($errors->has('name'))
                                            <span id="name-error" class="has-error">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter email address" value="{{ old('email') ? old('email') : Auth::user()->email }}" required>
                                        @if ($errors->has('email'))
                                            <span id="email-error" class="has-error">
                                                {{ $errors->first('email') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label for="password">Password</label>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <input type="password" class="form-control" name="password" id="password" placeholder="Enter password" value="{{ old('password') ? old('password') : '' }}">
                                                @if ($errors->has('password'))
                                                    @if (!str_contains($errors->first('password'), 'password confirmation'))
                                                        <span id="password-error" class="has-error">
                                                            {{ $errors->first('password') }}
                                                        </span>
                                                    @endif
                                                @endif 
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="password" class="form-control" name="password_confirmation" id="retype_password" placeholder="Re-type password" value="{{ old('password_confirmation') ? old('password_confirmation') : '' }}">
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
                                    </div>
                                    <div class="card-footer"> 
                                        <div class="pull-right">
                                            <div class="form-group">
                                                <input class="btn btn-primary" type="submit" value="Submit">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </section>

@endsection
