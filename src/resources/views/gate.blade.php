<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logtracker — Access Required</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
        }
        .gate-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .gate-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
        }
        h1 { font-size: 22px; font-weight: 800; color: #f8fafc; margin-bottom: 8px; }
        p { font-size: 13px; color: #94a3b8; line-height: 1.6; margin-bottom: 28px; }
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(99, 102, 241, 0.3);
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.6);
            color: #f8fafc;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="password"]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        input::placeholder { color: #475569; }
        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        button:active { transform: translateY(0); }
        .error-msg {
            color: #f43f5e;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
            display: none;
        }
        .footer {
            margin-top: 28px;
            font-size: 11px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="gate-card">
        <div class="gate-icon">🔒</div>
        <h1>Logtracker Access</h1>
        <p>This audit panel is protected. Enter the access key configured in your <code style="background:rgba(99,102,241,0.15);padding:2px 6px;border-radius:4px;font-size:12px;color:#a5b4fc;">.env</code> file to continue.</p>
        
        <form id="gate-form" method="GET">
            <div class="input-group">
                <input type="password" name="secret" id="secret-input" placeholder="Enter access key..." autocomplete="off" autofocus>
            </div>
            <button type="submit">Unlock Dashboard</button>
        </form>
        
        <p class="error-msg" id="error-msg">Invalid access key. Please try again.</p>
        
        <div class="footer">
            Set <code style="color:#64748b;">LOGTRACKER_ACCESS_SECRET</code> in your .env
        </div>
    </div>

    <script>
        // Show error if returning from a failed attempt
        if (window.location.search.includes('error=1')) {
            document.getElementById('error-msg').style.display = 'block';
        }
    </script>
</body>
</html>
