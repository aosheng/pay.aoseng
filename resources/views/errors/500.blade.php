// 500.blade.php
<div class="content">
    <div class="title">Something went wrong.</div>
    @unless(empty($sentryID))

        <!-- Sentry JS SDK 2.1.+ required -->
        <script src="https://cdn.ravenjs.com/3.3.0/raven.min.js"></script>

        <script>
        Raven.showReportDialog({
            eventId: '{{ $sentryID }}',

            // use the public DSN (dont include your secret!)
            dsn: 'https://1ed67d8e59ae46db81e0c8407c807d8b@sentry.io/191059'
        });
        </script>
    @endunless
</div>