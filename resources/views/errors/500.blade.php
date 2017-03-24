<div class="content">
    <div class="title">Something went wrong.</div>
    @unless(empty($sentryID))
        <script src="https://cdn.ravenjs.com/3.3.0/raven.min.js"></script>

        <script>
        Raven.showReportDialog({
            eventId: '{{ $sentryID }}',

            dsn: 'https://bc01834581ec4fc9b2069d8bce9cbded@sentry.io/149810'
        });
        </script>
    @endunless
</div>
