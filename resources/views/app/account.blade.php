@extends('layouts.app')

@section('content')
<div class="row">
    <div class="column">
        <h2>Account</h2>

        <p>{{ auth()->user()->username }}</p>
    </div>
</div>
@endsection
