<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>@yield('title')</title>
    </head>
    <body>
        @if(Auth::guest())
            <a href="{{ route('login') }}">Please log-in</a>
        @else
            Hello {{ Auth::user()->tag }}. <a href="{{ route('logout') }}">Want to logout?</a>
        @endif

        @yield('content')
    </body>
</html>
