@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-8 mx-auto">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> Website Audit </p>
                                        </div>
                                    </div>
                                    <div class="card-block" style="padding: 10px 80px 30px 80px">
                                    <form id="login-form" action="{{ route('scan') }}" method="POST">
                                        {{ csrf_field() }}
                                        <div id="form-scan" class="form-group{{ $errors->has('domain') ? ' has-error' : '' }}">
                                            <p class="text-center">Input a website domain here to scan:</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="domain" placeholder="Input website" style="padding-left: 15px;">
                                                <span class="input-group-btn">
                                                    <input class="btn btn-primary" type="submit" value="Scan" onclick="this.disabled=true;this.value='Scanning..';this.form.submit();" style="height: 38px;">
                                                </span>
                                            </div>
                                            @if ($errors->has('domain'))
                                                <span id="domain-error" class="has-error">
                                                    {{ $errors->first('domain') }}
                                                </span>
                                            @endif
                                            <p class="text-center">Example: testweb.com </p>
                                        </div>
                                    </form>
                                    </div>
                                    <div class="card-footer"> 
                                        <div class="pull-right">
                                            <a href="{{ route('history') }}"><i class="fa fa-history"></i> History</a> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

@endsection
