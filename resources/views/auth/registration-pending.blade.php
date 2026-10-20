<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud recibida | PRODENYA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        * { box-sizing: border-box; }
        body { align-items: center; background: linear-gradient(145deg, #eef5f1, #f5f7fb); color: #173247; display: flex; justify-content: center; margin: 0; min-height: 100vh; padding: 24px; }
        main { background: #fff; border: 1px solid #dce7df; border-radius: 24px; box-shadow: 0 20px 55px rgba(23, 50, 71, 0.1); max-width: 480px; padding: 42px 34px; text-align: center; width: 100%; }
        .icon { align-items: center; background: #eaf3ed; border-radius: 50%; color: #397461; display: inline-flex; font-size: 1.8rem; height: 64px; justify-content: center; width: 64px; }
        h1 { font-size: 1.6rem; letter-spacing: -0.04em; margin: 22px 0 10px; }
        p { color: #697586; font-size: 0.92rem; line-height: 1.65; margin: 0 auto; max-width: 370px; }
        a { color: #397461; display: inline-block; font-size: 0.85rem; font-weight: 700; margin-top: 25px; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main>
        <div class="icon"><i class="bi bi-envelope-check" aria-hidden="true"></i></div>
        <h1>Solicitud recibida</h1>
        <p>Te enviamos un correo para verificar tu dirección. Después de verificarla, el equipo administrador revisará tu solicitud y te informará cuando tu cuenta esté aprobada.</p>
        <a href="{{ route('login') }}">Volver al inicio de sesión</a>
    </main>
</body>
</html>
