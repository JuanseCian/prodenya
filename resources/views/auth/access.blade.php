<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($showRegister ?? false) ? 'Crear cuenta' : 'Iniciar sesión' }} | PRODENYA</title>

    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --violet: #7c74d4;
            --violet-dark: #5e56b0;
            --blue: #69c9e7;
            --ink: #26324d;
            --muted: #6d7b99;
            --line: #dce3f0;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { margin: 0; min-height: 100%; }

        body {
            background: linear-gradient(135deg, #655fc1 0%, #8179d6 54%, #73cbe8 100%);
            background-attachment: fixed;
            color: var(--ink);
            display: flex;
            flex-direction: column;
            font-family: Poppins, Inter, ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            border: 1px solid rgba(255, 255, 255, 0.17);
            border-radius: 50%;
            content: "";
            pointer-events: none;
            position: fixed;
        }

        body::before { height: 620px; right: -270px; top: -260px; width: 620px; }
        body::after { bottom: -330px; height: 670px; left: -320px; width: 670px; }

        a { color: var(--violet); text-decoration: none; }
        a:hover { color: var(--violet-dark); }

        .access-section {
            align-items: center;
            display: flex;
            flex: 1;
            justify-content: center;
            padding: 44px 16px;
            position: relative;
            z-index: 1;
        }

        .scene { max-width: 980px; perspective: 1400px; width: 100%; }

        .flip-card-inner {
            display: grid;
            min-height: 600px;
            transform-style: preserve-3d;
            transition: transform 0.75s cubic-bezier(0.4, 0.2, 0.2, 1);
        }

        .flip-card-inner.is-flipped { transform: rotateY(180deg); }

        .flip-card-front,
        .flip-card-back {
            backface-visibility: hidden;
            grid-area: 1 / 1;
            min-width: 0;
        }

        .flip-card-back { transform: rotateY(180deg); }

        .auth-card {
            background: #fff;
            border: none;
            border-radius: 22px;
            box-shadow: 0 26px 70px rgba(48, 42, 123, 0.27);
            height: 100%;
            overflow: hidden;
        }

        .panel-img {
            align-items: center;
            background: linear-gradient(155deg, rgba(76, 67, 160, 0.91), rgba(105, 201, 231, 0.77));
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 600px;
            overflow: hidden;
            padding: 48px 34px;
            position: relative;
            text-align: center;
        }

        .panel-img::before,
        .panel-img::after {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            content: "";
            position: absolute;
        }

        .panel-img::before { height: 390px; right: -185px; top: -130px; width: 390px; }
        .panel-img::after { bottom: -170px; height: 390px; left: -190px; width: 390px; }

        .brand-mark {
            align-items: center;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 50%;
            box-shadow: 0 8px 20px rgba(46, 40, 118, 0.18);
            display: flex;
            font-size: 1.75rem;
            font-weight: 800;
            height: 74px;
            justify-content: center;
            letter-spacing: -0.1em;
            margin-bottom: 19px;
            position: relative;
            width: 74px;
            z-index: 1;
        }

        .panel-img h2,
        .panel-img p,
        .dot-row { position: relative; z-index: 1; }
        .panel-img h2 { font-size: 1.7rem; font-weight: 700; letter-spacing: -0.035em; margin: 0; }
        .panel-img p { color: rgba(255, 255, 255, 0.8); font-size: 0.85rem; line-height: 1.65; margin: 11px 0 0; max-width: 250px; }
        .dot-row { display: flex; gap: 8px; margin-top: 24px; }
        .dot-row span { background: rgba(255, 255, 255, 0.38); border-radius: 50%; display: inline-block; height: 8px; width: 8px; }
        .dot-row span.active { background: #fff; box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.12); }

        .panel-form {
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 600px;
            padding: 48px 52px;
        }

        .form-wrap { margin: 0 auto; max-width: 390px; width: 100%; }
        .form-title { color: #27334f; font-size: 1.55rem; font-weight: 700; letter-spacing: -0.035em; margin-bottom: 4px; }
        .form-sub { color: var(--muted); font-size: 0.79rem; line-height: 1.6; margin-bottom: 25px; }
        .field { margin-bottom: 16px; position: relative; }
        .field label { color: #4d5a73; display: block; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.07em; margin-bottom: 7px; text-transform: uppercase; }
        .input-wrap { align-items: center; display: flex; position: relative; }
        .ico-left { color: #9baabf; display: flex; left: 13px; pointer-events: none; position: absolute; z-index: 1; }
        .input-wrap input { background: #f8faff; border: 1.5px solid var(--line); border-radius: 10px; color: #1f2b47; font: inherit; font-size: 0.83rem; min-height: 44px; outline: none; padding: 10px 41px 10px 38px; transition: border-color 0.2s, background 0.2s, box-shadow 0.2s; width: 100%; }
        .input-wrap input::placeholder { color: #a4afc1; }
        .input-wrap input:focus { background: #fff; border-color: var(--violet); box-shadow: 0 0 0 3px rgba(124, 116, 212, 0.13); }
        .input-wrap input.is-error { border-color: #ef4444; }
        .eye-btn { align-items: center; background: none; border: none; border-radius: 6px; color: #9baabf; cursor: pointer; display: flex; padding: 5px; position: absolute; right: 8px; transition: color 0.2s; }
        .eye-btn:hover { color: var(--violet-dark); }
        .forgot-link { color: var(--violet-dark); font-size: 0.75rem; font-weight: 600; }
        .forgot-link:hover { color: #4c45a0; text-decoration: underline; }
        .remember-row { align-items: center; color: var(--muted); display: flex; font-size: 0.75rem; gap: 7px; margin: 4px 0 20px; }
        .remember-row input { accent-color: var(--violet); height: 15px; width: 15px; }

        .btn-main { background: linear-gradient(135deg, var(--violet-dark), var(--blue)); border: none; border-radius: 10px; box-shadow: 0 8px 17px rgba(93, 86, 176, 0.2); color: #fff; cursor: pointer; font: inherit; font-size: 0.88rem; font-weight: 700; letter-spacing: 0.01em; min-height: 47px; padding: 11px 12px; transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s; width: 100%; }
        .btn-main:hover { box-shadow: 0 11px 23px rgba(93, 86, 176, 0.28); opacity: 0.92; transform: translateY(-1px); }
        .btn-main:active { transform: translateY(0); }
        #registerForm .btn-main { margin-top: 8px; }
        .switch-link { color: var(--muted); font-size: 0.78rem; margin-top: 21px; text-align: center; }
        .switch-link button { background: none; border: 0; color: var(--violet-dark); cursor: pointer; font: inherit; font-weight: 700; padding: 0; }
        .switch-link button:hover { text-decoration: underline; }
        .form-note { color: #a0aabd; font-size: 0.69rem; line-height: 1.55; margin: 22px 0 0; text-align: center; }

        .alert-danger-custom,
        .alert-success-custom,
        .alert-invitation { border-radius: 10px; font-size: 0.77rem; line-height: 1.5; margin-bottom: 17px; padding: 10px 13px; }
        .alert-danger-custom { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-success-custom { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
        .alert-invitation { background: #f2f0ff; border: 1px solid #dad5ff; color: var(--violet-dark); }
        .alert-danger-custom ul { margin: 0; padding-left: 17px; }
        .field-error { color: #c24141; font-size: 0.7rem; font-weight: 600; margin: 5px 0 0; }

        .pwd-rules { background: #fff; border: 1px solid var(--line); border-radius: 11px; box-shadow: 0 10px 28px rgba(40, 47, 85, 0.1); display: flex; flex-direction: column; gap: 5px; left: calc(100% + 14px); opacity: 0; padding: 12px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); transition: opacity 0.25s, left 0.25s; visibility: hidden; width: 220px; z-index: 10; }
        .pwd-rules::before { background: #fff; border-bottom: 1px solid var(--line); border-left: 1px solid var(--line); content: ""; height: 9px; left: -5px; position: absolute; top: 50%; transform: translateY(-50%) rotate(45deg); width: 9px; }
        .pwd-rules.show { left: calc(100% + 9px); opacity: 1; pointer-events: auto; visibility: visible; }
        .rule { align-items: center; color: #9baabf; display: flex; font-size: 0.68rem; gap: 7px; transition: color 0.2s; }
        .rule.ok { color: #16a34a; }
        .rule-dot { align-items: center; border: 1px solid #d0d9ee; border-radius: 50%; color: transparent; display: flex; flex-shrink: 0; font-size: 0.57rem; height: 16px; justify-content: center; width: 16px; }
        .rule.ok .rule-dot { background: #16a34a; border-color: #16a34a; color: #fff; }
        .match-msg { align-items: center; display: none; font-size: 0.7rem; gap: 5px; margin: 6px 0 14px; }
        .match-msg.ok { color: #16a34a; display: flex; }
        .match-msg.no { color: #dc2626; display: flex; }

        .site-footer { background: rgba(34, 36, 83, 0.72); color: rgba(255, 255, 255, 0.78); padding: 15px 16px; position: relative; z-index: 1; }
        .site-footer-inner { align-items: center; display: flex; gap: 18px; justify-content: space-between; margin: 0 auto; max-width: 980px; }
        .site-footer small { font-size: 0.7rem; }
        .site-footer strong { color: #fff; }

        .welcome-overlay { align-items: center; background: linear-gradient(135deg, #4d489d 0%, #6572c7 50%, #4daeca 100%); display: flex; flex-direction: column; inset: 0; justify-content: center; opacity: 0; pointer-events: none; position: fixed; transition: opacity 0.35s ease, visibility 0.35s ease; visibility: hidden; z-index: 9999; }
        .welcome-overlay.active { opacity: 1; pointer-events: auto; visibility: visible; }
        .welcome-wrapper { color: #fff; text-align: center; transform: scale(0.9); transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .welcome-overlay.active .welcome-wrapper { transform: scale(1); }
        .welcome-logo { align-items: center; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 50%; display: flex; font-size: 2.4rem; font-weight: 800; height: 100px; justify-content: center; letter-spacing: -0.1em; margin: 0 auto 23px; width: 100px; }
        .welcome-label { font-size: 0.83rem; letter-spacing: 0.08em; margin-bottom: 22px; }
        .welcome-spinner { animation: spin 0.8s linear infinite; border: 3px solid rgba(255, 255, 255, 0.18); border-radius: 50%; border-top-color: #fff; height: 42px; margin: 0 auto; width: 42px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 991px) {
            .access-section { padding: 28px 12px; }
            .panel-form { padding: 34px 28px; }
            .pwd-rules { left: 0; margin-top: 9px; position: relative; top: auto; transform: none; width: 100%; }
            .pwd-rules::before { display: none; }
            .pwd-rules.show { left: 0; }
        }

        @media (max-width: 767px) {
            .flip-card-inner { min-height: 0; }
            .panel-img { min-height: 185px; padding: 28px 20px; }
            .panel-img h2 { font-size: 1.4rem; }
            .panel-img p { font-size: 0.76rem; margin-top: 7px; }
            .brand-mark { height: 56px; margin-bottom: 11px; width: 56px; }
            .dot-row { margin-top: 14px; }
            .panel-form { min-height: 0; padding: 28px 21px 32px; }
            .site-footer-inner { align-items: center; flex-direction: column; gap: 4px; text-align: center; }
        }

        @media (max-width: 420px) {
            .access-section { padding: 12px 8px; }
            .auth-card { border-radius: 16px; }
            .panel-img { min-height: 160px; padding: 22px 14px; }
            .panel-form { padding: 24px 16px 28px; }
            .form-title { font-size: 1.35rem; }
            .input-wrap input { font-size: 0.95rem; min-height: 48px; }
            .btn-main { min-height: 50px; }
        }
    </style>
</head>

<body>
    @php
        $showRegister = $showRegister ?? false;
        $teamInvitation = $teamInvitation ?? null;
    @endphp

    <div class="welcome-overlay" id="welcomeOverlay" aria-hidden="true">
        <div class="welcome-wrapper">
            <div class="welcome-logo">P</div>
            <div class="welcome-label">Ingresando a PRODENYA</div>
            <div class="welcome-spinner"></div>
        </div>
    </div>

    <section class="access-section">
        <div class="container scene">
            <div class="flip-card-inner {{ $showRegister ? 'is-flipped' : '' }}" id="cardInner">
                <div class="flip-card-front">
                    <div class="auth-card">
                        <div class="row g-0">
                            <div class="col-md-5 panel-img">
                                <div class="brand-mark">P</div>
                                <h2>Bienvenido/a</h2>
                                <p>Plataforma de promoción y protección de derechos.</p>
                                <div class="dot-row"><span class="active"></span><span></span><span></span></div>
                            </div>

                            <div class="col-md-7 panel-form">
                                <div class="form-wrap">
                                    @if ($teamInvitation)
                                        <div class="alert-invitation"><i class="bi bi-shield-check me-1"></i> Invitación para participar en <strong>{{ $teamInvitation['teamName'] }}</strong>.</div>
                                    @endif

                                    @if (session('status'))
                                        <div class="alert-success-custom"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
                                    @endif

                                    @if ($errors->has('email') || $errors->has('password'))
                                        <div class="alert-danger-custom"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first('email') ?: $errors->first('password') }}</div>
                                    @endif

                                    <div class="form-title">Iniciar sesión</div>
                                    <div class="form-sub">Ingresá tus credenciales para continuar.</div>

                                    <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                                        @csrf
                                        <div class="field">
                                            <label for="login-email">Correo electrónico</label>
                                            <div class="input-wrap">
                                                <span class="ico-left"><i class="bi bi-envelope"></i></span>
                                                <input type="email" id="login-email" name="email" value="{{ old('email') }}" placeholder="nombre@organismo.gob.ar" autocomplete="username" required autofocus>
                                            </div>
                                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="field">
                                            <label for="login-password">Contraseña</label>
                                            <div class="input-wrap">
                                                <span class="ico-left"><i class="bi bi-lock"></i></span>
                                                <input type="password" id="login-password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                                                <button class="eye-btn" type="button" data-target="login-password" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                                            </div>
                                            @error('password') <p class="field-error">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <label class="remember-row mb-0"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Recordarme</label>
                                            @if ($canResetPassword ?? true)
                                                <a class="forgot-link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                                            @endif
                                        </div>

                                        <button class="btn-main" type="submit">Ingresar</button>
                                    </form>

                                    <div class="switch-link">¿No tenés cuenta? <button type="button" data-flip="register">Registrate</button></div>
                                    <p class="form-note">Acceso exclusivo para equipos autorizados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flip-card-back">
                    <div class="auth-card">
                        <div class="row g-0">
                            <div class="col-md-5 panel-img">
                                <div class="brand-mark"><i class="bi bi-person-plus"></i></div>
                                <h2>Unite al sistema</h2>
                                <p>Creá tu cuenta para comenzar a trabajar en la plataforma.</p>
                                <div class="dot-row"><span></span><span class="active"></span><span></span></div>
                            </div>

                            <div class="col-md-7 panel-form">
                                <div class="form-wrap">
                                    @if ($teamInvitation)
                                        <div class="alert-invitation"><i class="bi bi-shield-check me-1"></i> Tu registro quedará asociado a <strong>{{ $teamInvitation['teamName'] }}</strong>.</div>
                                    @endif

                                    @if ($showRegister && $errors->any())
                                        <div class="alert-danger-custom">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="form-title">Crear cuenta</div>
                                    <div class="form-sub">Completá el formulario para registrarte.</div>

                                    <form method="POST" action="{{ route('register.store') }}" id="registerForm">
                                        @csrf
                                        <div class="field">
                                            <label for="register-name">Nombre completo</label>
                                            <div class="input-wrap">
                                                <span class="ico-left"><i class="bi bi-person"></i></span>
                                                <input type="text" id="register-name" name="name" value="{{ old('name') }}" placeholder="Nombre y apellido" autocomplete="name" required>
                                            </div>
                                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="field">
                                            <label for="register-email">Correo electrónico</label>
                                            <div class="input-wrap">
                                                <span class="ico-left"><i class="bi bi-envelope"></i></span>
                                                <input type="email" id="register-email" name="email" value="{{ old('email') }}" placeholder="nombre@organismo.gob.ar" autocomplete="email" required>
                                            </div>
                                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="field mb-0">
                                                    <label for="register-dni">DNI</label>
                                                    <div class="input-wrap">
                                                        <span class="ico-left"><i class="bi bi-card-text"></i></span>
                                                        <input type="text" id="register-dni" name="dni" value="{{ old('dni') }}" placeholder="Sin puntos" inputmode="numeric" autocomplete="off" required>
                                                    </div>
                                                    @error('dni') <p class="field-error">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="field mb-0">
                                                    <label for="register-telefono">Teléfono</label>
                                                    <div class="input-wrap">
                                                        <span class="ico-left"><i class="bi bi-telephone"></i></span>
                                                        <input type="tel" id="register-telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Opcional" autocomplete="tel">
                                                    </div>
                                                    @error('telefono') <p class="field-error">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="field mt-3">
                                            <label for="register-profesion">Profesión</label>
                                            <div class="input-wrap">
                                                <span class="ico-left"><i class="bi bi-briefcase"></i></span>
                                                <input type="text" id="register-profesion" name="profesion" value="{{ old('profesion') }}" placeholder="Opcional" autocomplete="organization-title">
                                            </div>
                                            @error('profesion') <p class="field-error">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="row g-2 mb-0">
                                            <div class="col-6">
                                                <div class="field mb-0">
                                                    <label for="register-password">Contraseña</label>
                                                    <div class="input-wrap">
                                                        <span class="ico-left"><i class="bi bi-lock"></i></span>
                                                        <input type="password" id="register-password" name="password" placeholder="••••••••" autocomplete="new-password">
                                                        <button class="eye-btn" type="button" data-target="register-password" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                                                        <div class="pwd-rules" id="pwd-rules-box">
                                                            <div class="rule" id="r-len"><span class="rule-dot">✓</span> Mínimo 8 caracteres</div>
                                                            <div class="rule" id="r-num"><span class="rule-dot">✓</span> Al menos un número</div>
                                                            <div class="rule" id="r-low"><span class="rule-dot">✓</span> Una minúscula</div>
                                                            <div class="rule" id="r-spe"><span class="rule-dot">✓</span> Un carácter especial</div>
                                                        </div>
                                                    </div>
                                                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="field mb-0">
                                                    <label for="register-password-confirmation">Confirmar</label>
                                                    <div class="input-wrap">
                                                        <span class="ico-left"><i class="bi bi-lock-fill"></i></span>
                                                        <input type="password" id="register-password-confirmation" name="password_confirmation" placeholder="••••••••" autocomplete="new-password">
                                                        <button class="eye-btn" type="button" data-target="register-password-confirmation" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match-msg" id="match-msg"></div>

                                        <button class="btn-main" type="submit">Crear cuenta</button>
                                    </form>

                                    <div class="switch-link">¿Ya tenés cuenta? <button type="button" data-flip="login">Ingresar</button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="site-footer-inner">
            <small><strong>PRODENYA</strong> · Promoción y Protección de Derechos</small>
            <small>© {{ date('Y') }} · Uso institucional</small>
        </div>
    </footer>

    <script>
        (() => {
            const card = document.getElementById('cardInner');
            const invitation = window.location.search;

            document.querySelectorAll('[data-flip]').forEach((button) => {
                button.addEventListener('click', () => {
                    const register = button.dataset.flip === 'register';
                    card?.classList.toggle('is-flipped', register);
                    window.history.replaceState({}, '', register ? `{{ route('register') }}${invitation}` : `{{ route('login') }}${invitation}`);
                });
            });

            const eyeOpen = 'bi-eye';
            const eyeClosed = 'bi-eye-slash';

            document.querySelectorAll('[data-target]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.target);
                    const icon = button.querySelector('i');

                    if (!input || !icon) return;

                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    icon.classList.toggle(eyeOpen, visible);
                    icon.classList.toggle(eyeClosed, !visible);
                    button.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                });
            });

            const password = document.getElementById('register-password');
            const confirmation = document.getElementById('register-password-confirmation');
            const rules = document.getElementById('pwd-rules-box');
            const match = document.getElementById('match-msg');

            const setRule = (id, valid) => document.getElementById(id)?.classList.toggle('ok', valid);

            const checkRules = () => {
                if (!password || !rules) return;

                const value = password.value;
                const valid = {
                    length: value.length >= 8,
                    number: /\d/.test(value),
                    lowercase: /[a-z]/.test(value),
                    special: /[.,!/@#$%^&*()\-_+=?;:'"`~<>[\]{}|\\]/.test(value),
                };

                setRule('r-len', valid.length);
                setRule('r-num', valid.number);
                setRule('r-low', valid.lowercase);
                setRule('r-spe', valid.special);
                rules.classList.toggle('show', value.length > 0 && !Object.values(valid).every(Boolean));
            };

            const checkMatch = () => {
                if (!password || !confirmation || !match) return;

                if (!confirmation.value) {
                    match.className = 'match-msg';
                    match.textContent = '';
                    return;
                }

                const same = password.value === confirmation.value;
                match.className = `match-msg ${same ? 'ok' : 'no'}`;
                match.innerHTML = same
                    ? '<i class="bi bi-check-circle"></i> Las contraseñas coinciden'
                    : '<i class="bi bi-x-circle"></i> Las contraseñas no coinciden';
            };

            password?.addEventListener('input', () => { checkRules(); checkMatch(); });
            password?.addEventListener('focus', checkRules);
            password?.addEventListener('blur', () => rules?.classList.remove('show'));
            confirmation?.addEventListener('input', checkMatch);

            document.getElementById('loginForm')?.addEventListener('submit', (event) => {
                if (event.currentTarget.checkValidity()) {
                    document.getElementById('welcomeOverlay')?.classList.add('active');
                }
            });
        })();
    </script>
</body>

</html>
