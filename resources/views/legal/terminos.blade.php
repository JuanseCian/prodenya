<!DOCTYPE html>
<html lang="es" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y condiciones de uso | PRODENYA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/SN.png') }}">

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
        body { background: linear-gradient(145deg, #eef5f1, #f5f7fb); color: #173247; margin: 0; min-height: 100vh; padding: 32px 20px 56px; }
        html.dark body { background: linear-gradient(145deg, #101a20, #0d1620); color: #edf6fb; }
        main { background: #fff; border: 1px solid #dce7df; border-radius: 24px; box-shadow: 0 20px 55px rgba(23, 50, 71, 0.1); margin: 0 auto; max-width: 720px; padding: 42px clamp(24px, 5vw, 56px); }
        html.dark main { background: #172832; border-color: #304753; box-shadow: 0 20px 55px rgba(0, 0, 0, 0.45); }
        .eyebrow { color: #397461; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; }
        html.dark .eyebrow { color: #8bc39a; }
        h1 { font-size: 1.9rem; letter-spacing: -0.03em; margin: 10px 0 6px; }
        .updated { color: #697586; font-size: 0.82rem; margin: 0 0 30px; }
        html.dark .updated { color: #a9bdc7; }
        h2 { border-top: 1px solid #e5ece8; color: #173247; font-size: 1.05rem; letter-spacing: -0.01em; margin: 30px 0 12px; padding-top: 24px; }
        html.dark h2 { border-color: #304753; color: #edf6fb; }
        main > h2:first-of-type { border-top: 0; margin-top: 0; padding-top: 0; }
        p, li { color: #3d4b53; font-size: 0.92rem; line-height: 1.7; }
        html.dark p, html.dark li { color: #c3d2d9; }
        ul { margin: 10px 0; padding-left: 20px; }
        li + li { margin-top: 6px; }
        strong { color: #173247; font-weight: 700; }
        html.dark strong { color: #edf6fb; }
        a { color: #397461; font-weight: 700; text-decoration: none; }
        a:hover { text-decoration: underline; }
        html.dark a { color: #8bc39a; }
        .back { display: inline-block; margin-top: 36px; }
    </style>
</head>
<body>
    <main>
        <span class="eyebrow">PRODENYA · Municipalidad de San Nicolás de los Arroyos</span>
        <h1>Términos y condiciones de uso</h1>
        <p class="updated">Última actualización: {{ \Illuminate\Support\Facades\Date::now()->translatedFormat('d \d\e F \d\e Y') }}</p>

        <h2>1. Objeto</h2>
        <p>
            PRODENYA es el sistema de gestión institucional de la Municipalidad de San Nicolás de los Arroyos
            destinado a acompañar la promoción y protección integral de los derechos de niñas, niños y
            adolescentes. Estos términos y condiciones regulan el acceso y el uso del sistema por parte de las
            personas usuarias autorizadas.
        </p>

        <h2>2. Aceptación</h2>
        <p>
            Al crear una cuenta o utilizar el sistema, la persona usuaria declara haber leído, comprendido y
            aceptado estos términos. Si no está de acuerdo con alguno de sus puntos, debe abstenerse de
            utilizar el sistema.
        </p>

        <h2>3. Acceso y cuentas de usuario</h2>
        <ul>
            <li>El acceso está reservado a personal autorizado por la institución, cuya cuenta debe ser aprobada por un administrador antes de habilitarse.</li>
            <li>Cada persona usuaria es responsable de la confidencialidad de sus credenciales y de toda actividad realizada desde su cuenta.</li>
            <li>El uso del sistema debe limitarse a las funciones y secciones habilitadas según el rol asignado.</li>
        </ul>

        <h2>4. Confidencialidad y protección de datos personales</h2>
        <p>
            El sistema procesa datos personales y, en muchos casos, datos sensibles de niñas, niños,
            adolescentes y sus familias. Su tratamiento se rige por la Ley N.º 25.326 de Protección de Datos
            Personales y por la Ley N.º 26.061 de Protección Integral de los Derechos de las Niñas, Niños y
            Adolescentes. La persona usuaria se compromete a:
        </p>
        <ul>
            <li>Utilizar la información exclusivamente para las finalidades institucionales que motivan su acceso.</li>
            <li>No divulgar, copiar ni compartir legajos, comunicaciones o datos personales fuera de los canales institucionales autorizados.</li>
            <li>Mantener el deber de confidencialidad incluso después de dejar de prestar funciones en la institución.</li>
        </ul>

        <h2>5. Uso responsable del sistema</h2>
        <p>
            Queda prohibido utilizar el sistema para fines ajenos a la gestión institucional, intentar vulnerar
            sus medidas de seguridad, o acceder a información de legajos, personas o módulos para los que no
            se cuenta con autorización.
        </p>

        <h2>6. Propiedad intelectual</h2>
        <p>
            El software, el diseño y los contenidos del sistema son propiedad de la Municipalidad de San
            Nicolás de los Arroyos o de sus proveedores, y no pueden reproducirse ni distribuirse sin
            autorización.
        </p>

        <h2>7. Disponibilidad y modificaciones</h2>
        <p>
            El sistema puede presentar interrupciones por tareas de mantenimiento o causas ajenas a la
            institución. Estos términos pueden actualizarse para reflejar cambios normativos o funcionales;
            el uso continuado del sistema luego de una actualización implica su aceptación.
        </p>

        <h2>8. Legislación aplicable</h2>
        <p>
            Estos términos se rigen por las leyes de la República Argentina. Cualquier controversia derivada
            de su interpretación o aplicación se someterá a los tribunales competentes del Departamento
            Judicial de San Nicolás.
        </p>

        <h2>9. Contacto</h2>
        <p>
            Ante dudas sobre estos términos o sobre el tratamiento de datos personales en el sistema, la
            persona usuaria puede contactar al equipo administrador institucional de PRODENYA.
        </p>

        <a class="back" href="{{ route('login') }}">← Volver al inicio de sesión</a>
    </main>
</body>
</html>
