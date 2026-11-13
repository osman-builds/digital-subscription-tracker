<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') — @yield('title') | SubShield</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .error-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
            max-width: 460px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .brand-mark {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
        }
        .status-badge {
            display: inline-block;
            padding: .25rem .75rem;
            border-radius: 9999px;
            background: rgba(99, 102, 241, .12);
            color: #4f46e5;
            font-size: .8125rem;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: .75rem;
        }
        h1 {
            margin: 0 0 .75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }
        p {
            margin: 0 0 1.75rem;
            font-size: .95rem;
            line-height: 1.6;
            color: #475569;
        }
        .btn-home {
            display: inline-block;
            padding: .65rem 1.4rem;
            border-radius: 10px;
            background: #6366f1;
            color: #ffffff;
            text-decoration: none;
            font-size: .9375rem;
            font-weight: 600;
            transition: background-color .15s ease;
        }
        .btn-home:hover {
            background: #4f46e5;
        }
    </style>
</head>
<body>
    <main class="error-card" role="main">
        <img src="/favicon.svg" alt="SubShield" class="brand-mark">
        <div>
            <span class="status-badge">ERROR @yield('code')</span>
        </div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="/" class="btn-home">Return to SubShield</a>
    </main>
</body>
</html>
