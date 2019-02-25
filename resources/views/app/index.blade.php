@extends('layouts.app')

@section('content')
<div class="row">
    <div class="column">
        <h2>Welcome</h2>
        <h3>Quick links</h3>
        <p><a href="/auth/login">Login</a></p>
        <p><a href="/auth/forgot">Forgot</a></p>
        <p><a href="/auth/register">Register</a></p>

        <p><a href="/account">Account</a></p>
    </div>
</div>
@endsection
