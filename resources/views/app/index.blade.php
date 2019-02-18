@extends('layouts.app')

@section('content')
<div class="row">
    <div class="column">
        <h2>Welcome</h2>
        <h3>Quick links</h3>
        <p><a href="{{ url('/') }}">Home</a></p>
        <p><a href="{{ url('/account') }}">Account</a></p>
        <p><a href="{{ url('/login') }}">Login</a></p>
        <p><a href="{{ url('/forgot') }}">Forgot</a></p>
        <p><a href="{{ url('/register') }}">Register</a></p>
    </div>
</div>
@endsection
