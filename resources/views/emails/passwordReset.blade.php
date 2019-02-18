<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reset Password</title>
    </head>
    <body>
        <p>Hello {{ $user->email }}.</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>

        <p><a href="{{ $resetUrl }}">Reset password</a></p>
        <p>
            {{ $resetUrl }}
        </p>

        <p>If you did not request a password reset, no further action is required.</p>
    </body>
</html>
