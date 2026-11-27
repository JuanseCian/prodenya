<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

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

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: #f3f7f5;
            }

            html.dark {
                background-color: #101a20;
            }
        </style>
        <link rel="icon" type="image/png" href="{{ asset('assets/img/SN.png') }}">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        
        <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Prodenya') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <div
            id="initial-app-loader"
            role="status"
            aria-live="polite"
            aria-label="Cargando aplicacion"
            style="align-items:center;background:#f3f7f5;color:#39795b;display:flex;inset:0;justify-content:center;position:fixed;z-index:99999;"
        >
            <div style="align-items:center;display:flex;flex-direction:column;gap:14px;">
                <div style="align-items:center;border:1px solid rgba(57,121,91,.2);border-radius:16px;display:flex;height:76px;justify-content:center;padding:14px;width:76px;">
                    <img src="{{ asset('assets/img/SN.png') }}" alt="" style="height:100%;object-fit:contain;width:100%;">
                </div>
                <span style="font-size: calc(11px + 2px);font-weight:700;letter-spacing:.16em;text-transform:uppercase;">PRODENYA</span>
            </div>
        </div>
        <x-inertia::app />
    </body>
</html>
