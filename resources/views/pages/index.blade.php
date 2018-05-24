@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center">
        <h1>Welcome To Laravel!</h1>
        <p>This is the laravel application from the 
            'Laravel From Scratch' Youtube series.</p>
        @if (Auth::guest())
            <p><a class="btn btn-primary btn-lg" href="/login"
            role="button">Login</a> <a class="btn btn-success btn-lg"
            href="/register" role="button">Register</a></p>
        @else
            <p>Hello there, {{Auth::user()->name}}</p>
        @endif
    </div>
@endsection
