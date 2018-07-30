@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-6 col-sm-12">
                                <form id="setting-form" action="{{ route('setting') }}" method="POST">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> Settings </p>
                                        </div>
                                    </div>
                                    <div class="card-block" style="padding: 10px 30px 30px 30px">
                                    @if (session('message'))
                                        <div class="alert alert-success">
                                            {{ session('message') }}
                                        </div>
                                    @endif
                                    {{ csrf_field() }}
                                    <div class="form-group{{ $errors->has('sendmail') ? ' has-error' : '' }}">
                                        <label class="col-md-4 col-xs-4 align-top"><b>Sendmail</b></label>
                                        <span class="col-md-5">
                                            <label class="radio-inline">
                                              <input type="radio" name="sendmail" value="1" {{ Auth::user()->setting()->get()->first()->sendmail ? 'checked' : '' }}>On
                                            </label>
                                            <label class="radio-inline" style="padding-left: 20px">
                                              <input type="radio" name="sendmail" value="0" {{ Auth::user()->setting()->get()->first()->sendmail ? '' : 'checked' }}>Off
                                            </label>
                                        </span>
                                        @if ($errors->has('sendmail'))
                                            <span id="sendmail-error" class="has-error">
                                                {{ $errors->first('sendmail') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('notify') ? ' has-error' : '' }}">
                                        <label class="col-md-4 col-xs-4 align-top"><b>Notification</b></label>
                                        <span class="col-md-5">
                                            <label class="radio-inline">
                                              <input type="radio" name="notify" value="1" {{ Auth::user()->setting()->get()->first()->notify ? 'checked' : '' }}>On
                                            </label>
                                            <label class="radio-inline" style="padding-left: 20px">
                                              <input type="radio" name="notify" value="0" {{ Auth::user()->setting()->get()->first()->notify ? '' : 'checked' }}>Off
                                            </label>
                                        </span>  
                                        @if ($errors->has('notify'))
                                            <span id="notify-error" class="has-error">
                                                {{ $errors->first('notify') }}
                                            </span>
                                        @endif                         
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
