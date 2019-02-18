<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Confirm Email Address</title>
    </head>
    <body>
        <p>Hello {{ $user->email }}.</p>
        <p>Please confirm your email address.</p>

        <p><a href="{{ $confirmUrl }}">Confirm email address</a></p>
        <p>
            {{ $confirmUrl }}
        </p>
    </body>
</html>
