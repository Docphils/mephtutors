<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0e7490">
    <title>Offline | {{ config('app.name', 'MephEd') }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(160deg, #ecfeff, #e0f2fe);
            font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
        }

        .card {
            width: min(92vw, 460px);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.12);
            padding: 1.5rem;
            text-align: center;
        }

        .logo {
            width: 96px;
            height: auto;
        }

        h1 {
            margin: 0.75rem 0 0.5rem;
            font-size: 1.25rem;
        }

        p {
            margin: 0;
            line-height: 1.5;
            color: #334155;
            font-size: 0.95rem;
        }

        a {
            display: inline-block;
            margin-top: 1rem;
            border-radius: 999px;
            background: #0e7490;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.6rem 1rem;
        }
    </style>
</head>

<body>
    <article class="card">
        <img src="{{ asset('images/MephEd.png') }}" alt="MephEd" class="logo">
        <h1>You Are Offline</h1>
        <p>You can continue browsing already visited pages. Reconnect to access new requests and live updates.</p>
        <a href="{{ url('/') }}">Return Home</a>
    </article>
</body>

</html>
