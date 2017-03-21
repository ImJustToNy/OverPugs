<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{{ config('app.name') }} - Best place to find your teammates</title>

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script>
            window.overwatchLounge = {!! json_encode([
                'csrfToken' => csrf_token(),
                'serverTime' => $serverTime,
                'user' => $frontendParametrs,
                'match' => $matchParametrs
            ]) !!}
        </script>
    </head>
    <body>

        <div id="app">
            <layout></layout>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
