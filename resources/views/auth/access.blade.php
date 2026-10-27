@php
    $showRegister = $showRegister ?? false;
    $teamInvitation = $teamInvitation ?? null;
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $showRegister ? 'Crear cuenta' : 'Iniciar sesión' }} | PRODENYA</title>

    <style>
        :root {
            --ink: #152d3b;
            --ink-soft: #36505c;
            --muted: #6b7c83;
            --paper: #f3f5f1;
            --surface: #ffffff;
            --line: #dfe7e2;
            --line-strong: #c7d4cc;
            --accent: #2f7d68;
            --accent-dark: #23604f;
            --accent-soft: #e7f1eb;
            --warning-soft: #fff7e6;
            --warning: #9a681e;
            --danger-soft: #fff1ef;
            --danger: #a8493c;
            --shadow: 0 24px 70px rgba(21, 45, 59, 0.15);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            background: var(--paper);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
        }

        body::before {
            background: radial-gradient(circle, rgba(76, 137, 112, 0.12), transparent 68%);
            content: "";
            height: 460px;
            pointer-events: none;
            position: fixed;
            right: -180px;
            top: -210px;
            width: 460px;
        }

        button, input { font: inherit; }

        button, a { -webkit-tap-highlight-color: transparent; }

        a { color: inherit; }

        .skip-link {
            background: var(--ink);
            color: #fff;
            left: 16px;
            padding: 9px 14px;
            position: fixed;
            top: -60px;
            z-index: 10;
        }

        .skip-link:focus { top: 16px; }

        .auth-page {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            padding: 28px;
        }

        .auth-shell {
            background: var(--surface);
            border: 1px solid rgba(199, 212, 204, 0.9);
            border-radius: 26px;
            box-shadow: var(--shadow);
            display: grid;
            grid-template-columns: minmax(300px, 0.78fr) minmax(450px, 1.22fr);
            max-width: 1040px;
            min-height: 690px;
            overflow: hidden;
            position: relative;
            width: 100%;
            z-index: 1;
        }

        .brand-panel {
            background: #173a49;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 36px;
            position: relative;
        }

        .brand-panel::before,
        .brand-panel::after {
            border: 1px solid rgba(197, 232, 214, 0.2);
            border-radius: 50%;
            content: "";
            pointer-events: none;
            position: absolute;
        }

        .brand-panel::before { height: 520px; right: -300px; top: -190px; width: 520px; }
        .brand-panel::after { bottom: -290px; height: 470px; left: -240px; width: 470px; }

        .brand-panel__top,
        .brand-panel__content,
        .brand-panel__footer { position: relative; z-index: 1; }

        .brand {
            align-items: center;
            color: #fff;
            display: inline-flex;
            gap: 12px;
            text-decoration: none;
            width: fit-content;
        }

        .brand__mark {
            align-items: center;
            background: #cce8d7;
            border-radius: 13px;
            color: #1e5143;
            display: inline-flex;
            font-family: Georgia, serif;
            font-size: 1.15rem;
            font-weight: 700;
            height: 42px;
            justify-content: center;
            letter-spacing: -0.12em;
            padding-right: 3px;
            width: 42px;
        }

        .brand__name { font-size: 0.76rem; font-weight: 800; letter-spacing: 0.16em; }
        .brand__descriptor { color: rgba(255, 255, 255, 0.58); font-size: 0.67rem; margin-top: 2px; }

        .brand-panel__content { margin: auto 0; max-width: 300px; padding: 60px 0; }
        .eyebrow { color: #9bd2b3; font-size: 0.68rem; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; }
        .brand-panel h1 { font-family: Georgia, "Times New Roman", serif; font-size: clamp(2.15rem, 4vw, 3.45rem); font-weight: 400; letter-spacing: -0.055em; line-height: 0.98; margin: 16px 0 20px; }
        .brand-panel__lead { color: rgba(255, 255, 255, 0.72); font-size: 0.92rem; line-height: 1.7; margin: 0; max-width: 275px; }

        .trust-list { display: grid; gap: 13px; list-style: none; margin: 30px 0 0; padding: 0; }
        .trust-list li { align-items: center; color: rgba(255, 255, 255, 0.77); display: flex; font-size: 0.76rem; gap: 10px; }
        .trust-list__dot { background: #8bc5a5; border-radius: 50%; box-shadow: 0 0 0 5px rgba(139, 197, 165, 0.12); height: 6px; width: 6px; }
        .brand-panel__footer { border-top: 1px solid rgba(255, 255, 255, 0.14); color: rgba(255, 255, 255, 0.54); font-size: 0.68rem; padding-top: 18px; }

        .auth-panel { display: flex; flex-direction: column; padding: 36px clamp(32px, 5vw, 70px); }
        .auth-panel__header { align-items: center; display: flex; justify-content: space-between; }
        .auth-panel__section { color: var(--muted); font-size: 0.69rem; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; }
        .auth-panel__secure { align-items: center; color: var(--accent); display: inline-flex; font-size: 0.7rem; gap: 6px; }
        .secure-dot { background: var(--accent); border-radius: 50%; height: 6px; width: 6px; }

        .auth-panel__content { margin: auto 0; max-width: 470px; width: 100%; }
        .auth-view { animation: auth-enter 260ms ease both; }
        .auth-view[hidden] { display: none; }
        .auth-view__heading { margin-bottom: 27px; }
        .auth-view__heading h2 { color: var(--ink); font-family: Georgia, "Times New Roman", serif; font-size: clamp(2rem, 4vw, 2.7rem); font-weight: 400; letter-spacing: -0.055em; line-height: 1; margin: 0 0 11px; }
        .auth-view__heading p { color: var(--muted); font-size: 0.86rem; line-height: 1.6; margin: 0; }

        .notice { border-radius: 12px; font-size: 0.78rem; line-height: 1.55; margin-bottom: 18px; padding: 12px 14px; }
        .notice strong { font-weight: 750; }
        .notice--invitation { background: var(--warning-soft); border: 1px solid #f0d49c; color: var(--warning); }
        .notice--success { background: var(--accent-soft); border: 1px solid #b7d8c4; color: var(--accent-dark); }
        .notice--error { background: var(--danger-soft); border: 1px solid #f1c5bf; color: var(--danger); }
        .notice--error ul { margin: 0; padding-left: 18px; }

        .auth-form { display: grid; gap: 17px; }
        .form-grid { display: grid; gap: 17px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .field { min-width: 0; }
        .field label { color: var(--ink-soft); display: block; font-size: 0.74rem; font-weight: 750; margin-bottom: 7px; }
        .field input { background: #fbfcfb; border: 1px solid var(--line-strong); border-radius: 10px; color: var(--ink); min-height: 46px; outline: none; padding: 11px 42px 11px 13px; transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease; width: 100%; }
        .field input::placeholder { color: #9aa9a8; }
        .field input:hover { border-color: #aebeb5; }
        .field input:focus { background: #fff; border-color: var(--accent); box-shadow: 0 0 0 4px rgba(47, 125, 104, 0.13); }
        .field input[aria-invalid="true"] { border-color: var(--danger); }
        .field__control { position: relative; }
        .field__toggle { align-items: center; background: transparent; border: 0; color: #7e918e; cursor: pointer; display: inline-flex; height: 34px; justify-content: center; padding: 0; position: absolute; right: 7px; top: 6px; width: 34px; }
        .field__toggle:hover, .field__toggle:focus-visible { color: var(--accent-dark); }
        .field__toggle svg { height: 17px; width: 17px; }
        .field__error { color: var(--danger); font-size: 0.72rem; line-height: 1.4; margin: 6px 0 0; }
        .field__hint { color: var(--muted); font-size: 0.7rem; line-height: 1.45; margin: 6px 0 0; }

        .form-meta { align-items: center; display: flex; gap: 12px; justify-content: space-between; }
        .check-label { align-items: center; color: var(--muted); display: inline-flex; font-size: 0.75rem; gap: 8px; }
        .check-label input { accent-color: var(--accent); height: 15px; width: 15px; }
        .text-link { color: var(--accent-dark); font-size: 0.75rem; font-weight: 750; text-decoration: none; }
        .text-link:hover { text-decoration: underline; text-underline-offset: 3px; }
        .submit-button { align-items: center; background: var(--accent); border: 1px solid var(--accent); border-radius: 10px; color: #fff; cursor: pointer; display: inline-flex; font-size: 0.83rem; font-weight: 800; justify-content: center; min-height: 48px; padding: 11px 16px; transition: background 180ms ease, box-shadow 180ms ease, transform 180ms ease; width: 100%; }
        .submit-button:hover { background: var(--accent-dark); box-shadow: 0 10px 22px rgba(47, 125, 104, 0.2); transform: translateY(-1px); }
        .submit-button:active { transform: translateY(0); }
        .submit-button:disabled { cursor: wait; opacity: 0.72; }
        .submit-button__spinner { animation: spin 800ms linear infinite; border: 2px solid rgba(255, 255, 255, 0.35); border-radius: 50%; border-top-color: #fff; display: none; height: 15px; margin-right: 8px; width: 15px; }
        .is-submitting .submit-button__spinner { display: inline-block; }
        .switch-copy { color: var(--muted); font-size: 0.78rem; margin: 23px 0 0; text-align: center; }
        .switch-button { background: transparent; border: 0; color: var(--accent-dark); cursor: pointer; font-size: inherit; font-weight: 800; padding: 0; }
        .switch-button:hover { text-decoration: underline; text-underline-offset: 3px; }

        .security-checks { display: grid; gap: 7px; grid-template-columns: repeat(2, minmax(0, 1fr)); list-style: none; margin: 9px 0 0; padding: 0; }
        .security-checks li { align-items: center; color: #879692; display: flex; font-size: 0.68rem; gap: 6px; }
        .security-checks li::before { border: 1px solid #bcc9c2; border-radius: 50%; content: ""; height: 13px; width: 13px; }
        .security-checks li.is-valid { color: var(--accent); }
        .security-checks li.is-valid::before { background: var(--accent); border-color: var(--accent); box-shadow: inset 0 0 0 3px #fff; }
        .match-message { color: var(--danger); font-size: 0.7rem; margin: -7px 0 0; }
        .match-message.is-valid { color: var(--accent); }

        .auth-bottom { color: #80908c; font-size: 0.68rem; line-height: 1.5; margin: auto 0 0; padding-top: 28px; text-align: center; }

        @keyframes auth-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 800px) {
            .auth-page { padding: 14px; }
            .auth-shell { grid-template-columns: 1fr; min-height: 0; }
            .brand-panel { min-height: 255px; padding: 25px 26px; }
            .brand-panel__content { margin: 38px 0 0; padding: 0; }
            .brand-panel h1 { font-size: 2.25rem; margin: 12px 0; }
            .brand-panel__lead { font-size: 0.8rem; max-width: 420px; }
            .trust-list { display: none; }
            .brand-panel__footer { display: none; }
            .auth-panel { padding: 30px 26px 25px; }
            .auth-panel__content { margin: 30px 0 0; max-width: none; }
            .auth-bottom { padding-top: 25px; }
        }

        @media (max-width: 480px) {
            .auth-page { align-items: flex-start; padding: 8px; }
            .auth-shell { border-radius: 18px; }
            .brand-panel { min-height: 218px; padding: 22px 20px; }
            .brand__mark { height: 37px; width: 37px; }
            .brand__name { font-size: 0.68rem; }
            .brand-panel h1 { font-size: 2rem; }
            .brand-panel__lead { font-size: 0.75rem; line-height: 1.5; }
            .auth-panel { padding: 24px 18px 22px; }
            .auth-panel__secure { font-size: 0; gap: 0; }
            .auth-view__heading h2 { font-size: 2rem; }
            .form-grid { gap: 14px; grid-template-columns: 1fr; }
            .security-checks { grid-template-columns: 1fr; }
            .form-meta { align-items: flex-start; flex-direction: column; gap: 12px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; scroll-behavior: auto !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>

<body class="auth-page">
    <a class="skip-link" href="#auth-content">Saltar al formulario</a>

    <main
        id="auth-content"
        class="auth-shell"
        data-auth-shell
        data-mode="{{ $showRegister ? 'register' : 'login' }}"
    >
        <section class="brand-panel" aria-label="Información de PRODENYA">
            <div class="brand-panel__top">
                <a class="brand" href="{{ route('home') }}" aria-label="Ir al inicio de PRODENYA">
                    <span class="brand__mark" aria-hidden="true">PD</span>
                    <span>
                        <span class="brand__name">PRODENYA</span>
                        <span class="brand__descriptor">Gestión institucional</span>
                    </span>
                </a>
            </div>

            <div class="brand-panel__content">
                <span class="eyebrow">Acceso institucional</span>
                <h1>PRODENYA</h1>
                <p class="brand-panel__lead">Promoción y Protección de Derechos.</p>
            </div>

            <div class="brand-panel__footer">Acceso para equipos autorizados</div>
        </section>

        <section class="auth-panel" aria-label="Acceso a PRODENYA">
            <header class="auth-panel__header">
                <span class="auth-panel__section">Cuenta de usuario</span>
                <span class="auth-panel__secure"><span class="secure-dot" aria-hidden="true"></span>Acceso protegido</span>
            </header>

            <div class="auth-panel__content">
                <div class="auth-view" data-auth-view="login" @if ($showRegister) hidden @endif>
                    <div class="auth-view__heading">
                        <h2>Bienvenido/a</h2>
                        <p>Ingresá con tus credenciales.</p>
                    </div>

                    @if ($teamInvitation)
                        <div class="notice notice--invitation" role="status">
                            Tu invitación para participar en <strong>{{ $teamInvitation['teamName'] }}</strong> está lista.
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="notice notice--success" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('email') || $errors->has('password'))
                        <div class="notice notice--error" role="alert">{{ $errors->first('email') ?: $errors->first('password') }}</div>
                    @endif

                    <form class="auth-form" method="POST" action="{{ route('login.store') }}" id="loginForm">
                        @csrf

                        <div class="field">
                            <label for="login-email">Correo electrónico</label>
                            <div class="field__control">
                                <input id="login-email" type="email" name="email" value="{{ old('email') }}" placeholder="nombre@organismo.gob.ar" autocomplete="username" required autofocus @if ($errors->has('email')) aria-invalid="true" @endif>
                            </div>
                            @error('email') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="field">
                            <label for="login-password">Contraseña</label>
                            <div class="field__control">
                                <input id="login-password" type="password" name="password" placeholder="Ingresá tu contraseña" autocomplete="current-password" required @if ($errors->has('password')) aria-invalid="true" @endif>
                                <button class="field__toggle" type="button" data-password-toggle="login-password" aria-label="Mostrar contraseña">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                </button>
                            </div>
                            @error('password') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-meta">
                            <label class="check-label"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Recordarme</label>
                            @if ($canResetPassword ?? true)
                                <a class="text-link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                            @endif
                        </div>

                        <button class="submit-button" type="submit"><span class="submit-button__spinner" aria-hidden="true"></span><span data-submit-label>Ingresar</span></button>
                    </form>

                    <p class="switch-copy">¿No tenés una cuenta? <button class="switch-button" type="button" data-auth-switch="register">Registrate</button></p>
                </div>

                <div class="auth-view" data-auth-view="register" @if (!$showRegister) hidden @endif>
                    <div class="auth-view__heading">
                        <h2>Crear cuenta</h2>
                        <p>Completá tus datos para solicitar acceso.</p>
                    </div>

                    @if ($teamInvitation)
                        <div class="notice notice--invitation" role="status">
                            Tu registro quedará asociado a <strong>{{ $teamInvitation['teamName'] }}</strong>.
                        </div>
                    @endif

                    @if ($showRegister && $errors->any())
                        <div class="notice notice--error" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="auth-form" method="POST" action="{{ route('register.store') }}" id="registerForm">
                        @csrf

                        <div class="field">
                            <label for="register-name">Nombre completo</label>
                            <div class="field__control">
                                <input id="register-name" type="text" name="name" value="{{ old('name') }}" placeholder="Nombre y apellido" autocomplete="name" required>
                            </div>
                            @error('name') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="register-dni">DNI</label>
                                <div class="field__control">
                                    <input id="register-dni" type="text" name="dni" value="{{ old('dni') }}" placeholder="Sin puntos" inputmode="numeric" autocomplete="off" required>
                                </div>
                                @error('dni') <p class="field__error">{{ $message }}</p> @enderror
                            </div>

                            <div class="field">
                                <label for="register-telefono">Teléfono <span class="field__hint">(opcional)</span></label>
                                <div class="field__control">
                                    <input id="register-telefono" type="tel" name="telefono" value="{{ old('telefono') }}" placeholder="Tu teléfono" autocomplete="tel">
                                </div>
                                @error('telefono') <p class="field__error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="register-email">Correo electrónico</label>
                            <div class="field__control">
                                <input id="register-email" type="email" name="email" value="{{ old('email') }}" placeholder="nombre@organismo.gob.ar" autocomplete="email" required>
                            </div>
                            @error('email') <p class="field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="register-password">Contraseña</label>
                                <div class="field__control">
                                    <input id="register-password" type="password" name="password" placeholder="Elegí una contraseña" autocomplete="new-password" required>
                                    <button class="field__toggle" type="button" data-password-toggle="register-password" aria-label="Mostrar contraseña">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="field">
                                <label for="register-password-confirmation">Confirmar contraseña</label>
                                <div class="field__control">
                                    <input id="register-password-confirmation" type="password" name="password_confirmation" placeholder="Repetí la contraseña" autocomplete="new-password" required>
                                    <button class="field__toggle" type="button" data-password-toggle="register-password-confirmation" aria-label="Mostrar contraseña">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <ul class="security-checks" id="password-rules" aria-label="Requisitos de contraseña">
                            <li data-password-rule="length">Mínimo 8 caracteres</li>
                            <li data-password-rule="number">Al menos un número</li>
                            <li data-password-rule="lowercase">Una minúscula</li>
                            <li data-password-rule="special">Un carácter especial</li>
                        </ul>
                        <p class="match-message" id="password-match" role="status" aria-live="polite"></p>

                        <button class="submit-button" type="submit"><span class="submit-button__spinner" aria-hidden="true"></span><span data-submit-label>Crear cuenta</span></button>
                    </form>

                    <p class="switch-copy">¿Ya tenés una cuenta? <button class="switch-button" type="button" data-auth-switch="login">Iniciar sesión</button></p>
                </div>
            </div>

            <p class="auth-bottom">Uso institucional.</p>
        </section>
    </main>

    <script>
        (() => {
            const shell = document.querySelector('[data-auth-shell]');
            const views = [...document.querySelectorAll('[data-auth-view]')];
            const invitation = window.location.search;
            const paths = {
                login: @json(route('login')),
                register: @json(route('register')),
            };

            const setMode = (mode, updateUrl = true) => {
                shell?.setAttribute('data-mode', mode);
                views.forEach((view) => {
                    view.hidden = view.dataset.authView !== mode;
                });

                if (updateUrl) {
                    window.history.replaceState({}, '', `${paths[mode]}${invitation}`);
                }

                const heading = mode === 'register' ? 'Crear cuenta' : 'Iniciar sesión';
                document.title = `${heading} | PRODENYA`;
                document.querySelector(`[data-auth-view="${mode}"] input`)?.focus();
            };

            document.querySelectorAll('[data-auth-switch]').forEach((button) => {
                button.addEventListener('click', () => setMode(button.dataset.authSwitch));
            });

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.passwordToggle);
                    if (!input) return;

                    const isVisible = input.type === 'text';
                    input.type = isVisible ? 'password' : 'text';
                    button.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                });
            });

            const password = document.getElementById('register-password');
            const confirmation = document.getElementById('register-password-confirmation');
            const matchMessage = document.getElementById('password-match');
            const rules = {
                length: (value) => value.length >= 8,
                number: (value) => /\d/.test(value),
                lowercase: (value) => /[a-z]/.test(value),
                special: (value) => /[.,!/@#$%^&*()\-_+=?;:'"`~<>[\]{}|\\]/.test(value),
            };

            const updatePasswordRules = () => {
                if (!password) return;
                const value = password.value;

                Object.entries(rules).forEach(([name, check]) => {
                    document.querySelector(`[data-password-rule="${name}"]`)?.classList.toggle('is-valid', check(value));
                });
            };

            const updatePasswordMatch = () => {
                if (!confirmation || !matchMessage || !confirmation.value) {
                    if (matchMessage) matchMessage.textContent = '';
                    return;
                }

                const matches = password?.value === confirmation.value;
                matchMessage.classList.toggle('is-valid', matches);
                matchMessage.textContent = matches ? 'Las contraseñas coinciden.' : 'Las contraseñas no coinciden.';
            };

            password?.addEventListener('input', () => {
                updatePasswordRules();
                updatePasswordMatch();
            });
            confirmation?.addEventListener('input', updatePasswordMatch);

            document.querySelectorAll('form').forEach((form) => {
                form.addEventListener('submit', () => {
                    if (!form.checkValidity()) return;
                    form.classList.add('is-submitting');
                    const button = form.querySelector('.submit-button');
                    const label = button?.querySelector('[data-submit-label]');
                    if (button) button.disabled = true;
                    if (label) label.textContent = form.id === 'loginForm' ? 'Ingresando...' : 'Creando cuenta...';
                });
            });
        })();
    </script>
</body>

</html>
