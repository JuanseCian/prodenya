<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | PRODENYA</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { align-items: center; background: #f4f7fb; color: #173247; display: flex; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; justify-content: center; margin: 0; min-height: 100vh; padding: 24px 16px; }
        .error-card { animation: error-enter 520ms cubic-bezier(0.22, 1, 0.36, 1) both; background: #fff; border: 1px solid #dce7ed; border-radius: 24px; box-shadow: 0 24px 60px rgba(23, 50, 71, 0.1); max-width: 520px; overflow: hidden; text-align: center; width: 100%; }
        .error-logo { border-bottom: 1px solid #e6eef2; padding: 28px 32px 22px; }
        .error-logo img { display: block; height: auto; margin: 0 auto; max-width: 260px; width: 100%; }
        .error-body { padding: 30px 32px 34px; }
        .error-code { color: #173247; font-size: calc(4.5rem + 2px); font-weight: 800; letter-spacing: -0.06em; line-height: 1; margin: 0; }
        .error-code span { color: #397461; }
        .error-eyebrow { color: #397461; font-size: calc(0.68rem + 2px); font-weight: 800; letter-spacing: 0.16em; margin: 14px 0 0; text-transform: uppercase; }
        .error-title { font-size: calc(1.35rem + 2px); font-weight: 700; letter-spacing: -0.03em; margin: 8px 0 0; }
        .error-text { color: #5d727e; font-size: calc(0.9rem + 2px); line-height: 1.55; margin: 10px auto 0; max-width: 380px; }
        .error-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 26px; }
        .error-button { align-items: center; border-radius: 12px; cursor: pointer; display: inline-flex; font: inherit; font-size: calc(0.85rem + 2px); font-weight: 700; gap: 8px; padding: 11px 18px; text-decoration: none; transition: background-color 160ms ease, box-shadow 160ms ease, transform 160ms ease; }
        .error-button:hover { transform: translateY(-1px); }
        .error-button:focus-visible { outline: 3px solid rgba(107, 143, 113, 0.45); outline-offset: 3px; }
        .error-button--primary { background: #173247; border: 0; box-shadow: 0 12px 24px rgba(23, 50, 71, 0.18); color: #fff; }
        .error-button--primary:hover { background: #244357; }
        .error-button--ghost { background: #eaf3ed; border: 1px solid #cfe3d6; color: #397461; }
        .error-button--ghost:hover { background: #dcece2; }
        .error-footer { background: #f7fbf8; border-top: 1px solid #e6eef2; color: #6a7d86; font-size: calc(0.72rem + 2px); padding: 14px 24px; }
        @keyframes error-enter { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 420px) {
            .error-logo, .error-body { padding-left: 20px; padding-right: 20px; }
            .error-code { font-size: calc(3.6rem + 2px); }
            .error-button { justify-content: center; width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .error-card { animation: none; }
            .error-button { transition: none; }
        }
    </style>
</head>
<body>
    <main class="error-card">
        <div class="error-logo">
            <img src="{{ asset('images/escudo-municipalidad.png') }}" alt="Municipalidad de San Nicolás de los Arroyos">
        </div>

        <div class="error-body">
            <p class="error-code">@yield('code')</p>
            <p class="error-eyebrow">@yield('title')</p>
            <h1 class="error-title">@yield('heading')</h1>
            <p class="error-text">@yield('message')</p>

            <div class="error-actions">
                @yield('actions')
            </div>
        </div>

        <div class="error-footer">
            PRODENYA · Promoción y Protección de Derechos
        </div>
    </main>
</body>
</html>
