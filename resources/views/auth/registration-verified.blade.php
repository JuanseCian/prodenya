<!DOCTYPE html>
<html lang="es" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo verificado | PRODENYA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/SN.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Resolve the stored preference before the stylesheet paints the page. --}}
    <script>
        (function() {
            let stored = null;
            try {
                stored = localStorage.getItem('appearance');
            } catch (e) {}
            const cookie = document.cookie.split('; ').find((entry) => entry.startsWith('appearance='));
            const cookieAppearance = cookie ? decodeURIComponent(cookie.slice('appearance='.length)) : null;
            const appearance = ['light', 'dark', 'system'].includes(stored)
                ? stored
                : (['light', 'dark', 'system'].includes(cookieAppearance) ? cookieAppearance : '{{ $appearance ?? "system" }}');

            document.documentElement.classList.toggle(
                'dark',
                appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches),
            );
        })();
    </script>

    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        html.dark { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { align-items: center; background: #f4f7fb; color: #173247; display: flex; justify-content: center; margin: 0; min-height: 100vh; padding: 24px; }
        html.dark body { background: #101a20; color: #edf6fb; }
        main { background: #fff; border: 1px solid #dce7df; border-radius: 24px; box-shadow: 0 20px 55px rgba(23, 50, 71, 0.1); max-width: 480px; padding: 42px 34px; text-align: center; width: 100%; }
        html.dark main { background: #172832; border-color: #304753; box-shadow: 0 20px 55px rgba(0, 0, 0, 0.45); }
        .icon { align-items: center; background: #eaf3ed; border-radius: 50%; color: #397461; display: inline-flex; font-size: 1.8rem; height: 64px; justify-content: center; width: 64px; }
        html.dark .icon { background: #1d3328; color: #8bc39a; }
        h1 { font-size: 1.6rem; letter-spacing: -0.04em; margin: 22px 0 10px; }
        p { color: #697586; font-size: 0.92rem; line-height: 1.65; margin: 0 auto; max-width: 370px; }
        html.dark p { color: #a9bdc7; }
        a { background: #173247; border-radius: 9px; color: #fff; display: inline-block; font-size: 0.85rem; font-weight: 700; margin-top: 25px; padding: 11px 17px; text-decoration: none; }
        html.dark a { background: #8bc39a; color: #10251c; }
    </style>
</head>
<body>
    <main>
        <div class="icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></div>
        <h1>Correo verificado</h1>
        <p>Gracias, {{ $user->name }}. Tu solicitud quedó lista para revisión. Todavía no podrás ingresar hasta que un administrador la apruebe.</p>
        <a href="{{ route('login') }}">Ir al inicio de sesión</a>
    </main>
</body>
</html>
