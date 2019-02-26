<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- CSRF Token -->
    <!--<meta name="csrf-token" content="{{ csrf_token() }}">-->

    <!-- Fonts -->
    {{--<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">--}}

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>

    <div class="header">
        <h1>{{ config('app.name') }}</h1>
    </div>

    <div class="topnav">
        <a href="/">Home</a>
        @if (auth()->check())
            <a href="/account">Account</a>
            <a>
            <form action="/auth/logout" method="post">
                @csrf
                <button type="submit">Logout ({{ auth()->user()->email }})</button>
            </form>
            </a>
        @else
            <a href="/auth/login">Login</a>
            <a href="/auth/register">Register</a>
        @endif

    </div>

    <div id="content">
        @yield('content')
    </div>

    <?php if (app()->has('debugbar') && app()->get('debugbar')->isEnabled()): ?>
        <link rel='stylesheet' type='text/css' property='stylesheet' href='/css/debugbar.css'>
        <script type='text/javascript' src="/js/debugbar.js"></script>
        @if (app('router')->has('debugbar.openhandler'))
            {{ debugbar()->getJavascriptRenderer()->setOpenHandlerUrl(route('debugbar.openhandler'))->render() }}
        @endif
    <?php endif; ?>
</body>
</html>
