<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{{ config('app.name') }} - Best place to find your teammates</title>

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script>
            window.OverPugs = {!! json_encode([
                'csrfToken' => csrf_token(),
                'sentry_dsn' => env('SENTRY_PUBLIC_DSN'),
                'pusherKey' => config('broadcasting.connections.pusher.key'),
                'languages' => trans('languages'),
                'user' => $user,
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
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-52946216-8', 'auto');
            ga('send', 'pageview');
        </script>
    </body>
</html>
