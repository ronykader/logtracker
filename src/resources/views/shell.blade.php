<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $config['i18n']['audit_panel'] ?? 'Audit Panel' }} | LogTracker</title>
    @if(file_exists(public_path('vendor/logtracker/app.css')))
        <link rel="stylesheet" href="{{ asset('vendor/logtracker/app.css') }}">
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; }
        #logtracker-root { height: 100vh; }
        #lt-error {
            display: none; height: 100vh;
            align-items: center; justify-content: center; flex-direction: column;
            background: #f8fafc; gap: 16px;
        }
        #lt-error.show { display: flex; }
        #lt-error h2 { font-size: 20px; font-weight: 800; color: #1e293b; }
        #lt-error p { font-size: 14px; color: #64748b; max-width: 480px; text-align: center; line-height: 1.6; }
        #lt-error code { font-family: monospace; background: #f1f5f9; padding: 2px 7px; border-radius: 5px; font-size: 13px; color: #6366f1; }
    </style>
</head>
<body>
    <script>
        window.LogtrackerConfig = {!! json_encode($config) !!};
    </script>

    <div id="logtracker-root"></div>

    {{-- Fallback shown if app.js fails to mount --}}
    <div id="lt-error">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        <h2>LogTracker Assets Not Found</h2>
        <p>The compiled React assets are missing. Please run the following commands in your terminal:</p>
        <div style="background:#1e1b4b;color:#a5b4fc;padding:16px 20px;border-radius:12px;font-family:monospace;font-size:13px;line-height:2;text-align:left;width:100%;max-width:580px;">
            <div>cd /path/to/logtracker-package</div>
            <div>npm install &amp;&amp; npm run build</div>
            <div style="margin-top:8px">php artisan vendor:publish --tag=logtracker-assets --force</div>
        </div>
    </div>

    @if(file_exists(public_path('vendor/logtracker/app.js')))
        <script src="{{ asset('vendor/logtracker/app.js') }}"></script>
    @else
        <script>document.getElementById('lt-error').classList.add('show');</script>
    @endif
</body>
</html>
