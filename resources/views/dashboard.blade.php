@extends('layouts.app')

@section('content')
                    @if (Auth::user()->role == 1)
                    <div class="title-block">
                        <center><h3 class="title"> Hallo {{ Auth::user()->name }}, </h3>
                        <p class="title-description"> Welcome to dashboard page, start audit a website now </p></center>
                    </div>
                    {{-- <section class="section text-center">
                        <a href="{{ route('scan') }}" class="btn btn-primary">Get Started <em class="fa fa-play-circle"></em></a>
                    </section> --}}
                    @else
                    <div class="title-block">
                        <center><h3 class="title"> Hallo {{ Auth::user()->name }}, </h3>
                        <p class="title-description"> Welcome to dashboard page, start management now </p></center>
                    </div>
                    @endif


@endsection
