@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-6 col-sm-12">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> {{ Auth::user()->role == 2 ? "Admin" : "User" }} Profile </p>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                    @if (session('message'))
                                        <div class="alert alert-success">
                                            {{ session('message') }}
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label class="col-md-3 col-xs-3 align-top"><b>Username</b></label>
                                        <label class="col-md-6">: {{ Auth::user()->username }}</label>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 col-xs-3 align-top"><b>Nama</b></label>
                                        <label class="col-md-6">: {{ Auth::user()->name }}</label>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 col-xs-3 align-top"><b>Email</b></label>
                                        <label class="col-md-6">: {{ Auth::user()->email }}</label>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 col-xs-3 align-top"><b>Password</b></label>
                                        <label class="col-md-6">: **********</label>
                                    </div>
                                    </div>
                                    <div class="card-footer"> 
                                        <div class="pull-right">
                                            <a class="btn btn-primary" href="{{ route('editprofile') }}"><em class="fa fa-edit"></em> Edit</a> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

@endsection
