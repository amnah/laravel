@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Email Confirmation</div>
                <div class="panel-body">
                    @if ($success)
                        <div class="alert alert-success">
                            <p>{!! $success !!}</p>
                            <p><a href="/">Go home</a></p>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            Invalid confirmation
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
