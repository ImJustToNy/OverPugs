<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>@yield('title')</title>

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <script>
            window.overwatchLounge = {!! json_encode(['csrfToken' => csrf_token(), 'user' => Auth::user()]) !!}
        </script>
    </head>
    <body>

        <div id="app">
            <layout></layout>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
