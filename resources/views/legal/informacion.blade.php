<!DOCTYPE html>
<html lang="es" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | PRODENYA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/SN.png') }}">
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        html.dark { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background: linear-gradient(145deg, #eef5f1, #f5f7fb); color: #173247; margin: 0; min-height: 100vh; padding: 32px 20px 56px; }
        html.dark body { background: linear-gradient(145deg, #101a20, #0d1620); color: #edf6fb; }
        main { background: #fff; border: 1px solid #dce7df; border-radius: 24px; box-shadow: 0 20px 55px rgba(23, 50, 71, 0.1); margin: 0 auto; max-width: 720px; padding: 42px clamp(24px, 5vw, 56px); }
        html.dark main { background: #172832; border-color: #304753; box-shadow: 0 20px 55px rgba(0, 0, 0, 0.45); }
        .eyebrow { color: #397461; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; }
        html.dark .eyebrow { color: #8bc39a; }
        h1 { font-size: 1.9rem; letter-spacing: -0.03em; margin: 10px 0 6px; }
        h2 { border-top: 1px solid #e5ece8; color: #173247; font-size: 1.05rem; letter-spacing: -0.01em; margin: 30px 0 12px; padding-top: 24px; }
        html.dark h2 { border-color: #304753; color: #edf6fb; }
        main > h2:first-of-type { border-top: 0; margin-top: 0; padding-top: 0; }
        p { color: #3d4b53; font-size: 0.92rem; line-height: 1.7; }
        html.dark p { color: #c3d2d9; }
        a { color: #397461; font-weight: 700; text-decoration: none; }
        a:hover { text-decoration: underline; }
        html.dark a { color: #8bc39a; }
        .back { display: inline-block; margin-top: 36px; }
    </style>
</head>
<body>
    <main>
        <span class="eyebrow">{{ $eyebrow }}</span>
        <h1>{{ $title }}</h1>
        <p>{{ $intro }}</p>

        @foreach ($sections as $section)
            <h2>{{ $section['title'] }}</h2>
            <p>{{ $section['body'] }}</p>
        @endforeach

        <a class="back" href="{{ route('login') }}">Volver al inicio de sesión</a>
    </main>
</body>
</html>
