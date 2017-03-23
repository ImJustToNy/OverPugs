<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{{ config('app.name') }} - Best place to find your teammates</title>

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

        <script>
            window.overwatchLounge = {!! json_encode([
                'csrfToken' => csrf_token(),
                'pusherKey' => config('broadcasting.connections.pusher.key'),
                'user' => $user,
                'match' => $matchParametrs,
                'askedMatch' => session('match')
            ]) !!}
        </script>
    </head>
    <body>

        <div id="app">
            <layout></layout>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>

        <script>
            (adsbygoogle = window.adsbygoogle || []).push({})
        </script>
    </body>
</html>
