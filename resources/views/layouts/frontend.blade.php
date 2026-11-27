<!DOCTYPE html>
<html lang="es" @class(['dark' => ($appearance ?? 'system') === 'dark'])>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <title>@yield('title', 'PRODENYA')</title>
    <meta name="description" content="@yield('description', 'Sistema de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes')">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/SN.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @vite('resources/css/app.css')
        @viteReactRefresh
        @if (isset($page))
            @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
            <x-inertia::head />
        @endif
        @vite('resources/js/site-header.tsx')
        @vite('resources/js/site-footer.tsx')

    <style>
         :root {
             --brand: #2c4a5e;
             --brand-dark: #173247;
             --brand-deep: #1f394b;
             --ink: #2b2f36;
             --muted: #697586;
             --surface: #ffffff;
             --page: #f4f6f5;
             --line: #dce3df;
             --shadow: 0 16px 40px rgba(23, 50, 71, 0.1);
             --site-header-height: 75px;
         }

         @media (max-width: 920px) {
             :root { --site-header-height: 65px; }
         }

         html.dark {
             --ink: #edf6fb;
             --muted: #a9bdc7;
             --surface: #172832;
             --page: #101a20;
             --line: #304753;
             --shadow: 0 16px 40px rgba(0, 0, 0, 0.28);
         }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background: var(--page);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .site-shell { min-height: 100vh; }

        .site-header {
            animation: site-header-enter 480ms cubic-bezier(0.22, 1, 0.36, 1) both;
            background: var(--brand-dark);
            box-shadow: 0 5px 22px rgba(57, 51, 119, 0.2);
            color: #fff;
            position: relative;
            z-index: 10;
        }

        @keyframes site-header-enter {
            from { opacity: 0; transform: translate3d(0, -8px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }

        .site-header-inner {
            align-items: center;
            display: flex;
            gap: 28px;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1240px;
            min-height: 82px;
            padding: 10px 24px;
        }

        .brand-lockup {
            align-items: center;
            color: #fff;
            display: inline-flex;
            flex-shrink: 0;
            gap: 12px;
            text-decoration: none;
        }

        .brand-divider { background: rgba(255, 255, 255, 0.3); height: 42px; width: 1px; }

        .brand-mark {
            align-items: center;
            background: rgba(255, 255, 255, 0.17);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 15px;
            box-shadow: 0 6px 16px rgba(38, 33, 94, 0.2);
            display: flex;
            font-size: calc(1.35rem + 2px);
            font-weight: 800;
            height: 50px;
            justify-content: center;
            letter-spacing: -0.08em;
            width: 50px;
        }

        .brand-mark-image { object-fit: contain; padding: 7px; }

        .brand-name {
            font-size: calc(1.15rem + 2px);
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1.1;
        }

        .brand-caption {
            color: rgba(255, 255, 255, 0.75);
            font-size: calc(0.7rem + 2px);
            letter-spacing: 0.02em;
            margin-top: 4px;
        }

        .site-user-avatar {
            align-items: center;
            background: #fff;
            border-radius: 50%;
            color: var(--brand-dark);
            display: flex;
            font-size: calc(0.75rem + 2px);
            font-weight: 800;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        .site-user-avatar-image { object-fit: cover; }

        .site-account { position: relative; }

        .site-account-trigger {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 12px;
            color: #fff;
            cursor: pointer;
            display: flex;
            gap: 9px;
            list-style: none;
            padding: 5px 7px;
        }

        .site-account-trigger::-webkit-details-marker { display: none; }
        .site-account-trigger:hover, .site-account[open] .site-account-trigger { background: rgba(255, 255, 255, 0.13); }
        .site-account-chevron { font-size: calc(0.8rem + 2px); transition: transform 180ms ease; }
        .site-account[open] .site-account-chevron { transform: rotate(180deg); }

        .site-account-menu {
            background: #fff;
            border: 1px solid #e2e5ed;
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(35, 33, 76, 0.18);
            color: var(--ink);
            min-width: 278px;
            padding: 8px;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            z-index: 30;
        }

        .site-account-menu-header { border-bottom: 1px solid #edf0f5; padding: 10px 10px 13px; }
        .site-account-menu-name { color: #2d3348; font-size: calc(0.9rem + 2px); font-weight: 800; }
        .site-account-menu-email { color: #778096; font-size: calc(0.75rem + 2px); margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-account-menu-meta { color: #6673a9; font-size: calc(0.7rem + 2px); font-weight: 700; margin-top: 8px; }
        .site-account-menu-link, .site-account-menu-logout { align-items: center; border-radius: 9px; display: flex; font-size: calc(0.82rem + 2px); font-weight: 600; gap: 10px; padding: 10px; text-decoration: none; width: 100%; }
        .site-account-menu-link { color: #3e455d; }
        .site-account-menu-link:hover { background: #f4f5fb; color: var(--brand-dark); }
        .site-account-menu-logout { background: transparent; border: 0; color: #b14657; cursor: pointer; text-align: left; }
        .site-account-menu-logout:hover { background: #fff1f2; }
        .site-account-menu-separator { border-top: 1px solid #edf0f5; margin: 6px 2px; }

        .logout-button {
            background: rgba(43, 38, 91, 0.32);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            font-size: calc(0.78rem + 2px);
            font-weight: 700;
            padding: 8px 13px;
            transition: background-color 180ms ease;
        }

        .logout-button:hover { background: rgba(43, 38, 91, 0.55); }

        .site-main { min-height: calc(100vh - 164px); }

        .site-mobile-bottom-nav { display: none; }

        @media (max-width: 560px) {
            .site-header-inner { padding-left: 16px; padding-right: 16px; }
            .site-account-menu { max-width: calc(100vw - 32px); min-width: 250px; }
        }

        .site-shell { background: var(--page); position: relative; }
        .site-page { display: flex; flex-direction: column; min-height: 100vh; margin-left: 0; }
         .site-sidebar {
             background: #173247;
             bottom: 0;
             box-shadow: -12px 0 32px rgba(23, 50, 71, 0.18);
            color: #fff;
            display: flex;
            flex-direction: column;
            left: auto;
            overflow-y: auto;
            padding: 22px 15px 16px;
            position: fixed;
            top: 0;
            right: 0;
            width: 276px;
            z-index: 40;
        }
        .site-sidebar-brand { align-items: center; display: flex; gap: 11px; padding: 3px 10px 24px; }
        .site-sidebar-brand .brand-mark { flex-shrink: 0; height: 44px; width: 44px; }
        .site-sidebar-brand .brand-name { font-size: calc(1.02rem + 2px); }
        .site-sidebar-brand .brand-caption { color: rgba(255, 255, 255, 0.6); font-size: calc(0.64rem + 2px); }
        .site-sidebar-rule { background: rgba(255, 255, 255, 0.15); height: 1px; margin: 0 10px 23px; }
        .site-sidebar-label { color: rgba(255, 255, 255, 0.45); font-size: calc(0.64rem + 2px); font-weight: 800; letter-spacing: 0.15em; padding: 0 12px 9px; text-transform: uppercase; }
        .site-sidebar-nav { display: flex; flex-direction: column; gap: 5px; }
        .site-sidebar-link {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.72);
            display: flex;
            font-size: calc(0.86rem + 2px);
            font-weight: 650;
            gap: 12px;
            padding: 12px 13px;
            text-decoration: none;
            transition: background-color 180ms ease, color 180ms ease, transform 180ms ease;
        }
        .site-sidebar-link i { color: rgba(255, 255, 255, 0.56); font-size: calc(1.03rem + 2px); width: 20px; }
        .site-sidebar-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; transform: translateX(2px); }
        .site-sidebar-link:hover i, .site-sidebar-link.is-active i { color: #fff; }
        .site-sidebar-link.is-active { background: rgba(255, 255, 255, 0.14); border-color: rgba(255, 255, 255, 0.1); box-shadow: inset 3px 0 0 #9ce0bf; color: #fff; }
        .site-sidebar-link.is-admin { background: rgba(156, 224, 191, 0.12); color: #dff8e9; margin-top: 6px; }
        .site-sidebar-link.is-admin i { color: #9ce0bf; }
        .site-sidebar-link.is-admin:hover { background: rgba(156, 224, 191, 0.2); }
        .site-sidebar-spacer { flex: 1; min-height: 28px; }
        .site-header { position: sticky; top: 0; }
        .site-header-inner { max-width: none; }
         .site-sidebar-backdrop { background: rgba(15, 23, 32, 0.25); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity 360ms cubic-bezier(0.22, 1, 0.36, 1); z-index: 35; }

         @media (max-width: 1020px) {
             .site-page { margin-left: 0; }
             .site-sidebar { transform: translateX(105%); transition: transform 220ms ease; }
             .site-shell.sidebar-open .site-sidebar { transform: translateX(0); }
         .site-shell.sidebar-open .site-sidebar-backdrop { opacity: 1; pointer-events: auto; }
         }

         /* Header: one restrained system for identity, navigation and account tools. */
         .site-header { background: #173247; box-shadow: 0 1px 0 rgba(255, 255, 255, 0.08); }
          .site-header-inner { align-items: center; display: flex; gap: 28px; min-height: 76px; padding: 12px 28px; }
         .site-mobile-nav-toggle { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: #fff; cursor: pointer; display: none; flex: 0 0 44px; height: 44px; justify-content: center; padding: 0; }
         .site-mobile-nav-toggle:hover, .site-mobile-nav-toggle:focus-visible { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.25); outline: none; }
         .site-mobile-nav-toggle i { font-size: calc(1.25rem + 2px); }
         .site-header-brand { align-items: center; color: #fff; display: inline-flex; flex: 0 1 auto; gap: 12px; min-width: 236px; text-decoration: none; }
          .site-header-brand .brand-mark { background: transparent; border: 0; border-radius: 0; box-shadow: none; flex: 0 0 42px; height: 42px; padding: 0; width: 42px; }
         .site-header-brand .brand-divider { background: rgba(255, 255, 255, 0.26); height: 36px; }
         .site-header-brand .brand-name { color: #fff; font-size: calc(1.18rem + 2px); font-weight: 700; letter-spacing: 0.085em; }
         .site-header-brand .brand-caption { color: rgba(255, 255, 255, 0.68); font-size: calc(0.75rem + 2px); font-weight: 400; letter-spacing: 0; margin-top: 4px; }
         .site-primary-nav { align-items: center; display: flex; flex: 1 1 auto; gap: 4px; min-width: 0; }
         .site-primary-link { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: rgba(255, 255, 255, 0.78); display: inline-flex; font-size: calc(0.875rem + 2px); font-weight: 550; gap: 8px; justify-content: center; min-height: 44px; padding: 9px 12px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; white-space: nowrap; }
         .site-primary-link i { color: rgba(255, 255, 255, 0.62); font-size: calc(1.05rem + 2px); }
         .site-primary-link:hover, .site-primary-link:focus-visible { background: rgba(255, 255, 255, 0.09); border-color: rgba(255, 255, 255, 0.16); color: #fff; outline: none; }
         .site-primary-link:hover i, .site-primary-link:focus-visible i { color: #fff; }
         .site-primary-link.is-active { background: rgba(255, 255, 255, 0.14); color: #fff; font-weight: 650; }
         .site-primary-link.is-active::after { background: #9ce0bf; border-radius: 999px; content: ''; height: 3px; margin-left: 1px; width: 3px; }
         .site-disclosure { position: relative; }
         .site-disclosure-trigger { cursor: pointer; list-style: none; }
         .site-disclosure-trigger::-webkit-details-marker { display: none; }
         .site-disclosure-trigger .site-disclosure-chevron { color: rgba(255, 255, 255, 0.54); font-size: calc(0.7rem + 2px); margin-left: 1px; transition: transform 150ms ease; width: auto; }
         .site-disclosure[open] .site-disclosure-chevron { transform: rotate(180deg); }
         .site-header-more-panel { animation: none; background: #fff; border: 1px solid #dce3df; border-radius: 9px; box-shadow: 0 12px 28px rgba(23, 50, 71, 0.16); min-width: 248px; padding: 6px; position: absolute; right: 0; top: calc(100% + 8px); }
         .site-header-more-link { border-radius: 6px; color: #2c4a5e; font-size: calc(0.84rem + 2px); font-weight: 550; gap: 10px; min-height: 42px; padding: 9px 10px; transition: background-color 150ms ease, color 150ms ease; }
         .site-header-more-link:hover, .site-header-more-link:focus-visible { background: #eef5f1; color: #173247; outline: none; transform: none; }
         .site-header-more-link i { color: #6b8f71; }
         .site-header-tools { align-items: center; display: flex; flex: 0 0 auto; gap: 6px; margin-left: auto; }
         .site-message-link, .site-admin-link { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: rgba(255, 255, 255, 0.78); display: inline-flex; font-size: calc(0.84rem + 2px); font-weight: 550; gap: 8px; min-height: 44px; padding: 9px 11px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; white-space: nowrap; }
         .site-message-link:hover, .site-message-link:focus-visible, .site-admin-link:hover, .site-admin-link:focus-visible { background: rgba(255, 255, 255, 0.09); border-color: rgba(255, 255, 255, 0.16); color: #fff; outline: none; }
         .site-message-link > i, .site-admin-link > i { color: rgba(255, 255, 255, 0.64); font-size: calc(1.05rem + 2px); }
         .site-message-badge { background: #9ce0bf; border: 2px solid #173247; color: #244d3b; font-size: calc(0.62rem + 2px); height: 18px; min-width: 18px; }
         .site-message-badge.is-empty { display: none; }
         .site-message-link.is-notified .site-message-icon-wrap, .site-message-link.is-notified::after { animation: none; }
         .site-header-account { margin-left: 6px; }
         .site-header-account::before { background: rgba(255, 255, 255, 0.2); content: ''; height: 28px; left: -7px; position: absolute; top: 8px; width: 1px; }
         .site-header-account .site-account-trigger { border-radius: 7px; gap: 8px; min-height: 44px; padding: 4px 7px; }
         .site-header-account .site-account-trigger:hover, .site-header-account[open] .site-account-trigger, .site-header-account .site-account-trigger:focus-visible { background: rgba(255, 255, 255, 0.09); outline: none; }
         .site-header-account .site-user-avatar { height: 36px; width: 36px; }
         .site-account-visible-name { color: rgba(255, 255, 255, 0.86); font-size: calc(0.82rem + 2px); max-width: 132px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
         .site-header-account .site-account-chevron { color: rgba(255, 255, 255, 0.62); font-size: calc(0.7rem + 2px); }
         .site-header-account .site-account-menu { min-width: 272px; right: 0; top: calc(100% + 8px); }
         .site-header-account .site-account-menu-link, .site-header-account .site-account-menu-logout { min-height: 42px; }
         .site-sidebar-link:hover { transform: none; }
         .site-sidebar-link:focus-visible, .site-account-menu-link:focus-visible, .site-account-menu-logout:focus-visible { outline: 2px solid #9ce0bf; outline-offset: 2px; }
         @media (max-width: 1100px) {
             .site-header-inner { gap: 18px; padding-left: 24px; padding-right: 24px; }
             .site-header-brand { min-width: 210px; }
             .site-header-brand .brand-caption { display: none; }
             .site-account-visible-name { max-width: 92px; }
         }
         @media (max-width: 920px) {
              .site-header-inner { align-items: center; flex-wrap: nowrap; gap: 10px; min-height: 64px; padding: 8px 16px; }
             .site-mobile-nav-toggle { display: inline-flex; }
             .site-header-brand { flex: 1 1 auto; gap: 9px; min-width: 0; }
             .site-header-brand .brand-mark { flex-basis: 38px; height: 38px; width: 38px; }
             .site-header-brand .brand-divider { height: 28px; }
              .site-header-brand .brand-name { font-size: calc(0.98rem + 2px); }
              .site-header-brand .brand-caption { display: none; }
              .site-primary-nav { display: none; }
              .site-header-tools { gap: 2px; }
             .site-admin-link { display: none; }
              .site-account-visible-name { display: none; }
              .site-header-account { margin-left: 2px; }
              .site-header-account::before { display: none; }
               .site-header-account .site-account-menu { position: fixed; right: 16px; top: 64px; }
              .site-main { padding-bottom: calc(78px + env(safe-area-inset-bottom)); }
              .site-mobile-bottom-nav {
                  align-items: stretch;
                   backdrop-filter: none;
                   background: #173247;
                  border-top: 1px solid rgba(255, 255, 255, 0.14);
                  bottom: 0;
                  box-shadow: 0 -10px 28px rgba(23, 50, 71, 0.18);
                  display: grid;
                  gap: 3px;
                  grid-auto-columns: minmax(0, 1fr);
                  grid-auto-flow: column;
                  left: 0;
                  padding: 7px max(8px, env(safe-area-inset-right)) calc(7px + env(safe-area-inset-bottom)) max(8px, env(safe-area-inset-left));
                  position: fixed;
                  right: 0;
                  z-index: 39;
              }
              .site-mobile-bottom-link {
                  align-items: center;
                  border: 1px solid transparent;
                  border-radius: 13px;
                  color: rgba(255, 255, 255, 0.66);
                  display: flex;
                  flex-direction: column;
                  font-size: calc(0.65rem + 2px);
                  font-weight: 650;
                  gap: 4px;
                  justify-content: center;
                  min-height: 54px;
                  min-width: 0;
                  padding: 6px 4px;
                  text-decoration: none;
                  transition: background-color 180ms ease, color 180ms ease, transform 180ms ease;
              }
              .site-mobile-bottom-link i { font-size: calc(1.12rem + 2px); line-height: 1; }
              .site-mobile-bottom-link:hover, .site-mobile-bottom-link:focus-visible { background: rgba(255, 255, 255, 0.1); color: #fff; outline: none; }
              .site-mobile-bottom-link:active { transform: scale(0.96); }
              .site-mobile-bottom-link.is-active { background: rgba(156, 224, 191, 0.15); color: #b9e8cc; }
              .site-mobile-bottom-link.is-active i { color: #9ce0bf; }
              .site-mobile-bottom-link--more { background: transparent; cursor: pointer; font: inherit; }
          }
         @media (max-width: 480px) {
             .site-header-inner { padding-left: 12px; padding-right: 12px; }
             .site-message-label { display: none; }
             .site-message-link, .site-admin-link, .site-header-account .site-account-trigger, .site-mobile-nav-toggle { min-height: 44px; min-width: 44px; padding: 8px; }
             .site-header-brand .brand-divider { display: none; }
             .site-header-brand .brand-name { font-size: calc(0.92rem + 2px); letter-spacing: 0.07em; }
             .site-account-menu { max-width: calc(100vw - 24px); min-width: 248px; }
         }
        @media (prefers-reduced-motion: reduce) {
            .site-header {
                animation: none;
            }

              *, *::before, *::after { scroll-behavior: auto !important; transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; }
         }

         .site-page { margin-left: 0; transition: margin-left 220ms ease; }
           .site-sidebar { background: #fff; border-left: 1px solid #dce3df; box-shadow: -10px 0 28px rgba(23, 50, 71, 0.12); color: #2b2f36; max-height: 100dvh; overscroll-behavior: contain; padding: 0; transform: translate3d(105%, 0, 0); transition: transform 380ms cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 380ms; visibility: hidden; width: 344px; will-change: transform; }
          .site-shell.sidebar-open .site-sidebar { transform: translate3d(0, 0, 0); transition-delay: 0s; visibility: visible; }
         .site-shell.sidebar-open .site-sidebar-backdrop { opacity: 1; pointer-events: auto; }
          .site-sidebar-header { align-items: center; border-bottom: 1px solid #e5ebe7; display: flex; justify-content: space-between; min-height: 72px; padding: 14px 20px; }
         .site-sidebar-header h2 { color: #173247; font-size: calc(1.05rem + 2px); font-weight: 700; letter-spacing: -0.01em; margin: 0; }
         .site-sidebar-close { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: #60727b; cursor: pointer; display: inline-flex; height: 44px; justify-content: center; width: 44px; }
         .site-sidebar-close:hover, .site-sidebar-close:focus-visible { background: #f1f5f3; border-color: #d5e0da; color: #173247; outline: none; }
         .site-sidebar-nav { display: flex; flex: 0 0 auto; flex-direction: column; gap: 0; padding: 18px 16px 10px; }
         .site-sidebar-group + .site-sidebar-group { border-top: 1px solid #edf1ef; margin-top: 16px; padding-top: 16px; }
          .site-sidebar-label { color: #819197; font-size: calc(0.7rem + 2px); font-weight: 700; letter-spacing: 0.04em; padding: 0 10px 7px; }
          .site-sidebar-link { align-items: center; border: 1px solid transparent; border-radius: 7px; color: #49616d; display: flex; font-size: calc(0.875rem + 2px); font-weight: 550; gap: 10px; min-height: 46px; padding: 10px 10px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; }
         .site-sidebar-link i { color: #77918d; font-size: calc(1rem + 2px); width: 20px; }
         .site-sidebar-link:hover, .site-sidebar-link:focus-visible { background: #f1f6f3; border-color: #dce9e1; color: #173247; outline: none; }
         .site-sidebar-link.is-active { background: #eaf3ed; border-color: #d5e6da; box-shadow: inset 3px 0 0 #6b8f71; color: #173247; font-weight: 700; }
         .site-sidebar-link.is-active i, .site-sidebar-link:hover i, .site-sidebar-link:focus-visible i { color: #397461; }
          .site-sidebar-account { border-top: 1px solid #e5ebe7; margin-top: auto; padding: 14px 20px 16px; }
          .site-sidebar-account-trigger { align-items: center; border-radius: 8px; cursor: pointer; display: flex; gap: 10px; list-style: none; min-width: 0; padding: 4px; }
          .site-sidebar-account-trigger::-webkit-details-marker { display: none; }
          .site-sidebar-account-trigger:hover, .site-sidebar-account-trigger:focus-visible { background: #f1f6f3; outline: none; }
         .site-sidebar-account-heading { align-items: center; display: flex; gap: 10px; min-width: 0; }
         .site-sidebar-account .site-user-avatar { background: #eaf3ed; color: #397461; flex: 0 0 36px; height: 36px; width: 36px; }
         .site-sidebar-account-copy { min-width: 0; }
          .site-sidebar-account-copy h2 { color: #173247; font-size: calc(0.82rem + 2px); font-weight: 700; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
          .site-sidebar-account-copy p { color: #71828a; font-size: calc(0.7rem + 2px); margin: 3px 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
          .site-sidebar-account-copy small { color: #6b8f71; display: block; font-size: calc(0.68rem + 2px); font-weight: 650; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
          .site-sidebar-account-chevron { color: #819197; font-size: calc(0.75rem + 2px); margin-left: auto; transition: transform 150ms ease; }
          .site-sidebar-account[open] .site-sidebar-account-chevron { transform: rotate(180deg); }
          .site-sidebar-account-options { border-top: 1px solid #edf1ef; margin-top: 12px; padding-top: 8px; }
          .site-sidebar-account-email { color: #71828a; font-size: calc(0.72rem + 2px); margin: 0 10px 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
         .site-sidebar-account-link { align-items: center; background: transparent; border: 0; border-radius: 7px; color: #49616d; display: flex; font: inherit; font-size: calc(0.8rem + 2px); font-weight: 550; gap: 10px; min-height: 42px; padding: 9px 10px; text-align: left; text-decoration: none; width: 100%; }
         .site-sidebar-account-link:hover, .site-sidebar-account-link:focus-visible { background: #f1f6f3; color: #173247; outline: none; }
         .site-sidebar-account-link i { color: #77918d; font-size: calc(0.95rem + 2px); width: 18px; }
         .site-sidebar-logout-form { border-top: 1px solid #edf1ef; margin-top: 6px; padding-top: 6px; }
         .site-sidebar-logout { color: #9b5964; cursor: pointer; }
         .site-sidebar-logout:hover, .site-sidebar-logout:focus-visible { background: #fcf1f3; color: #8c4c58; }
         .site-sidebar-logout i { color: currentColor; }
         .site-header-quick-link, .site-menu-toggle { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: rgba(255, 255, 255, 0.8); display: inline-flex; font-size: calc(0.84rem + 2px); font-weight: 550; gap: 8px; min-height: 44px; padding: 9px 11px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; white-space: nowrap; }
         .site-header-quick-link:hover, .site-header-quick-link:focus-visible, .site-menu-toggle:hover, .site-menu-toggle:focus-visible { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2); color: #fff; outline: none; }
         .site-header-quick-link > i, .site-menu-toggle > i { color: rgba(255, 255, 255, 0.66); font-size: calc(1.05rem + 2px); }
         .site-header-quick-link.is-active { background: rgba(255, 255, 255, 0.13); color: #fff; font-weight: 650; }
         .site-header-quick-link.is-active::after { background: #9ce0bf; border-radius: 999px; content: ''; height: 3px; margin-left: 1px; width: 3px; }
         .site-menu-toggle { background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.18); color: #fff; cursor: pointer; }
         .site-menu-toggle[aria-expanded="true"] { background: rgba(255, 255, 255, 0.16); }
         .site-menu-toggle > i { color: #b9e8cc; }
         .site-header-tools { gap: 5px; }
         @media (max-width: 920px) {
             .site-header-inner { gap: 8px; }
             .site-header-brand { flex: 1 1 auto; min-width: 0; }
             .site-header-quick-link > span:not(.site-message-badge), .site-menu-toggle > span { display: none; }
             .site-header-quick-link, .site-menu-toggle { min-height: 44px; min-width: 44px; padding: 8px; }
             .site-sidebar { width: min(344px, calc(100vw - 20px)); }
         }
          @media (max-width: 480px) {
              .site-header-inner { padding-left: 12px; padding-right: 12px; }
              .site-header-brand .brand-divider { display: none; }
              .site-header-brand .brand-name { font-size: calc(0.92rem + 2px); letter-spacing: 0.07em; }
           }

         /* Modern visual layer for the institutional header and navigation drawer. */
         .site-header {
             background: #173247;
             border-bottom: 1px solid rgba(255, 255, 255, 0.12);
             box-shadow: 0 8px 24px rgba(16, 44, 62, 0.14);
             z-index: 30;
         }
         .site-header-inner { gap: 24px; min-height: 76px; padding: 12px clamp(18px, 3vw, 36px); }
         .site-header-brand { gap: 13px; min-width: 246px; }
         .site-header-brand .brand-mark {
             background: rgba(255, 255, 255, 0.1);
             border: 1px solid rgba(255, 255, 255, 0.2);
             border-radius: 12px;
             box-shadow: 0 6px 16px rgba(5, 24, 36, 0.16);
             flex-basis: 44px;
             height: 44px;
             padding: 5px;
             width: 44px;
         }
         .site-header-brand .brand-divider { background: rgba(255, 255, 255, 0.24); height: 34px; }
         .site-header-brand .brand-name { font-size: calc(1.06rem + 2px); letter-spacing: 0.13em; }
         .site-header-brand .brand-caption { color: rgba(235, 248, 242, 0.72); font-size: calc(0.72rem + 2px); letter-spacing: 0.01em; margin-top: 5px; }
          .site-header-tools { gap: 8px; }
          .site-header-quick-link, .site-menu-toggle {
             border-radius: 12px;
             font-size: calc(0.84rem + 2px);
             font-weight: 650;
             min-height: 46px;
             padding: 10px 13px;
             transition: background-color 160ms ease, border-color 160ms ease, box-shadow 160ms ease, color 160ms ease, transform 160ms ease;
         }
         .site-header-quick-link:hover, .site-header-quick-link:focus-visible, .site-menu-toggle:hover, .site-menu-toggle:focus-visible {
             box-shadow: 0 6px 16px rgba(5, 24, 36, 0.14);
             transform: translateY(-1px);
         }
         .site-header-quick-link.is-active { background: rgba(255, 255, 255, 0.16); }
         .site-message-badge { background: #a8e8c7; border-color: #173c50; box-shadow: 0 2px 7px rgba(5, 24, 36, 0.18); color: #174b3a; }
         .site-menu-toggle {
             background: rgba(255, 255, 255, 0.08);
             border-color: rgba(255, 255, 255, 0.18);
             color: #fff;
             box-shadow: none;
             flex: 0 0 48px;
             height: 48px;
             min-height: 48px;
             min-width: 48px;
             padding: 5px;
         }
         .site-menu-toggle:hover, .site-menu-toggle:focus-visible { background: rgba(255, 255, 255, 0.14); color: #fff; }
         .site-menu-toggle > i { color: rgba(255, 255, 255, 0.84); }
         .site-menu-toggle .site-user-avatar {
             background: rgba(255, 255, 255, 0.12);
             border: 2px solid rgba(255, 255, 255, 0.36);
             color: #fff;
             flex: 0 0 36px;
             font-size: calc(0.68rem + 2px);
             height: 36px;
             width: 36px;
         }
         .site-menu-toggle .site-user-avatar-image { object-fit: cover; }

          .site-sidebar-backdrop { background: rgba(10, 29, 40, 0.25); backdrop-filter: none; }
          .site-sidebar {
              background: #ffffff;
             border: 1px solid #dbe9e1;
             border-radius: 22px;
             bottom: 14px;
             box-shadow: -18px 12px 48px rgba(18, 52, 67, 0.18);
             max-height: calc(100dvh - 28px);
             right: 14px;
             top: 14px;
             transform: translateX(calc(100% + 28px));
             width: 344px;
         }
         .site-shell.sidebar-open .site-sidebar { transform: translateX(0); }
         .site-sidebar-header {
              background: #f7faf8;
             border-bottom-color: #e1ece6;
             border-radius: 22px 22px 0 0;
             min-height: 78px;
             padding: 16px 20px 15px 24px;
         }
         .site-sidebar-header h2 { font-size: calc(1.1rem + 2px); letter-spacing: -0.02em; }
         .site-sidebar-header h2::before { color: #6b8f71; content: 'PRODENYA'; display: block; font-size: calc(0.62rem + 2px); font-weight: 800; letter-spacing: 0.13em; margin-bottom: 4px; }
         .site-sidebar-close { border-radius: 11px; }
         .site-sidebar-nav { gap: 0; padding: 22px 16px 12px; }
         .site-sidebar-group + .site-sidebar-group { margin-top: 20px; padding-top: 19px; }
         .site-sidebar-label { align-items: center; color: #5f737d; display: flex; gap: 8px; font-size: calc(0.65rem + 2px); font-weight: 800; letter-spacing: 0.13em; padding: 0 10px 9px; }
         .site-sidebar-label::before { background: #8fc8a8; border-radius: 999px; content: ''; height: 6px; width: 6px; }
         .site-sidebar-link {
             border-color: transparent;
             border-radius: 13px;
             color: #4a626c;
             font-size: calc(0.88rem + 2px);
             font-weight: 600;
             gap: 12px;
             min-height: 52px;
             padding: 8px 12px;
             transition: background-color 160ms ease, border-color 160ms ease, box-shadow 160ms ease, color 160ms ease, transform 160ms ease;
         }
         .site-sidebar-link i {
             align-items: center;
             background: #edf5f1;
             border: 1px solid #e1eee7;
             border-radius: 10px;
             color: #62917d;
             display: inline-flex;
             flex: 0 0 34px;
             height: 34px;
             justify-content: center;
             width: 34px;
         }
         .site-sidebar-link:hover, .site-sidebar-link:focus-visible { background: #f0f8f4; border-color: #d8e9df; box-shadow: 0 5px 12px rgba(49, 111, 83, 0.07); color: #173247; transform: translateX(3px); }
         .site-sidebar-link:hover i, .site-sidebar-link:focus-visible i { background: #e3f2e9; border-color: #cde3d5; color: #397461; }
          .site-sidebar-link.is-active { background: #eaf3ed; border-color: #cbe4d4; box-shadow: inset 4px 0 0 #5b9d7b; color: #173247; }
         .site-sidebar-link.is-active i { background: #cfead9; border-color: #b8ddc6; color: #31765b; }
         .site-sidebar-account { background: rgba(235, 247, 240, 0.74); border: 1px solid #d5e8dc; border-radius: 16px; margin: auto 16px 16px; padding: 8px; }
         .site-sidebar-account-trigger { border-radius: 11px; min-height: 58px; padding: 7px 8px; }
         .site-sidebar-account-trigger:hover, .site-sidebar-account-trigger:focus-visible { background: rgba(255, 255, 255, 0.72); }
         .site-sidebar-account .site-user-avatar { background: #d7eee0; border: 1px solid #c1e2ce; flex-basis: 38px; height: 38px; width: 38px; }
         .site-sidebar-account-copy h2 { font-size: calc(0.84rem + 2px); }
         .site-sidebar-account-copy small { color: #4c8b6c; font-size: calc(0.69rem + 2px); margin-top: 5px; }
         .site-sidebar-account-chevron { margin-right: 3px; }
         .site-sidebar-account-options { border-top-color: #d5e8dc; margin-top: 7px; padding: 9px 4px 2px; }
         .site-sidebar-account-email { color: #5d707a; margin: 0 8px 6px; }
          .site-sidebar-account-link { border-radius: 10px; min-height: 40px; }
          .site-sidebar-account-link:hover, .site-sidebar-account-link:focus-visible { background: rgba(255, 255, 255, 0.82); }
          .site-sidebar-logout-form { border-top-color: #d5e8dc; }
         @media (max-width: 1100px) {
             .site-header-inner { gap: 16px; }
             .site-header-brand { min-width: 220px; }
         }
          @media (max-width: 920px) {
              .site-header-inner { min-height: 66px; padding-bottom: 9px; padding-top: 9px; }
              .site-header-brand .brand-mark { flex-basis: 38px; height: 38px; width: 38px; }
              .site-sidebar { bottom: 8px; max-height: calc(100dvh - 16px); right: 8px; top: 8px; width: min(344px, calc(100vw - 16px)); }
          }
          @media (max-width: 480px) {
              .site-header-brand { gap: 9px; }
              .site-header-brand .brand-name { font-size: calc(0.91rem + 2px); }
               .site-sidebar { border-radius: 18px; }
               .site-sidebar-header { border-radius: 18px 18px 0 0; }
           }
         .dark .site-sidebar {
              background: #152532;
             border-color: #294252;
             color: #edf6fb;
         }
          .dark .site-sidebar-header { background: #1b303f; border-color: #294252; }
         .dark .site-sidebar-header h2 { color: #edf6fb; }
         .dark .site-sidebar-header h2::before { color: #9ce0bf; }
         .dark .site-sidebar-close { color: #a8bbc5; }
         .dark .site-sidebar-close:hover, .dark .site-sidebar-close:focus-visible { background: #203746; border-color: #3a596b; color: #fff; }
         .dark .site-sidebar-group + .site-sidebar-group { border-color: #294252; }
         .dark .site-sidebar-label { color: #a8bbc5; }
         .dark .site-sidebar-label::before { background: #9ce0bf; }
         .dark .site-sidebar-link { color: #c2d1d8; }
         .dark .site-sidebar-link i { background: #1c3544; border-color: #315063; color: #9ccfb2; }
         .dark .site-sidebar-link:hover, .dark .site-sidebar-link:focus-visible { background: #1c3544; border-color: #315063; color: #fff; }
         .dark .site-sidebar-link:hover i, .dark .site-sidebar-link:focus-visible i { background: #285143; border-color: #40775f; color: #b9e8cc; }
          .dark .site-sidebar-link.is-active { background: #214537; border-color: #40775f; color: #fff; }
         .dark .site-sidebar-link.is-active i { background: #2c6249; border-color: #4a916f; color: #c5f0d6; }
         .dark .site-sidebar-account { background: rgba(28, 53, 68, 0.85); border-color: #315063; }
         .dark .site-sidebar-account-trigger:hover, .dark .site-sidebar-account-trigger:focus-visible { background: rgba(255, 255, 255, 0.08); }
         .dark .site-sidebar-account .site-user-avatar { background: #285143; border-color: #40775f; color: #c5f0d6; }
         .dark .site-sidebar-account-copy h2 { color: #edf6fb; }
          .dark .site-sidebar-account-copy small, .dark .site-sidebar-account-email { color: #a8caba; }
          .dark .site-sidebar-account-chevron { color: #a8bbc5; }
          .dark .site-sidebar-account-options, .dark .site-sidebar-logout-form { border-color: #315063; }
         .dark .site-sidebar-account-link { color: #c2d1d8; }
          .dark .site-sidebar-account-link:hover, .dark .site-sidebar-account-link:focus-visible { background: rgba(255, 255, 255, 0.08); color: #fff; }
           .dark .site-sidebar-account-link i { color: #9ccfb2; }

          /* Final institutional navigation layer: restrained surfaces, compact rhythm, no decorative blur. */
          .site-header {
              background: #173247;
              box-shadow: 0 5px 18px rgba(16, 44, 62, 0.12);
          }
          .site-header-inner { gap: 20px; min-height: 72px; padding: 10px clamp(16px, 3vw, 36px); }
          .site-header-brand { gap: 12px; min-width: 238px; }
          .site-header-brand .brand-mark { background: rgba(255, 255, 255, 0.08); border-radius: 10px; box-shadow: none; }
          .site-header-tools { gap: 4px; }
          .site-header-quick-link, .site-menu-toggle { border-radius: 8px; min-height: 44px; padding: 9px 11px; transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease; }
          .site-header-quick-link:hover, .site-header-quick-link:focus-visible, .site-menu-toggle:hover, .site-menu-toggle:focus-visible { box-shadow: none; transform: none; }
          .site-menu-toggle { flex: 0 0 auto; height: 44px; min-height: 44px; min-width: 44px; padding: 8px 11px; }
          .site-header-account { position: relative; }
          .site-account-header-copy { display: flex; flex-direction: column; min-width: 0; text-align: left; }
          .site-account-visible-role { color: rgba(235, 248, 242, 0.58); font-size: calc(0.68rem + 2px); line-height: 1.2; margin-top: 2px; }
          .site-header-account .site-account-trigger { min-height: 44px; }
          .site-header-account .site-account-menu { right: 0; top: calc(100% + 8px); }
          .site-sidebar-backdrop { background: rgba(10, 29, 40, 0.36); backdrop-filter: none; transition-duration: 180ms; }
          .site-sidebar { background: #fff; border-left: 1px solid #dbe9e1; border-radius: 0; bottom: 0; box-shadow: -18px 0 42px rgba(18, 52, 67, 0.16); max-height: 100dvh; right: 0; top: 0; transform: translateX(100%); width: 360px; }
          .site-sidebar-header { background: #f7faf8; border-radius: 0; min-height: 70px; padding: 12px 16px 12px 20px; }
          .site-sidebar-nav { padding: 18px 14px 12px; }
          .site-sidebar-group + .site-sidebar-group { margin-top: 14px; padding-top: 14px; }
          .site-sidebar-link { border-radius: 8px; font-size: calc(0.86rem + 2px); gap: 11px; min-height: 44px; padding: 7px 10px; transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease; }
          .site-sidebar-link i { background: transparent; border: 0; flex-basis: 22px; height: 22px; width: 22px; }
          .site-sidebar-link:hover, .site-sidebar-link:focus-visible { box-shadow: none; transform: none; }
          .site-sidebar-link.is-active { background: #eaf3ed; box-shadow: inset 3px 0 0 #6b8f71; }
          .site-sidebar-link:hover i, .site-sidebar-link:focus-visible i, .site-sidebar-link.is-active i { background: transparent; border-color: transparent; }
          .dark .site-sidebar { background: #152532; border-color: #294252; }
          .dark .site-sidebar-header { background: #1b303f; border-color: #294252; }
          .dark .site-sidebar-link i { background: transparent; border-color: transparent; }
          @media (max-width: 920px) {
              .site-header-inner { min-height: 64px; padding-bottom: 8px; padding-top: 8px; }
              .site-header-brand { min-width: 0; }
              .site-header-brand .brand-caption, .site-account-visible-role { display: none; }
              .site-header-quick-link > span, .site-menu-toggle > span, .site-account-visible-name { display: none; }
              .site-header-quick-link, .site-menu-toggle, .site-header-account .site-account-trigger { min-height: 44px; min-width: 44px; padding: 8px; }
              .site-sidebar { width: min(360px, 100vw); }
          }
           @media (max-width: 480px) {
               .site-header-inner { padding-left: 12px; padding-right: 12px; }
               .site-header-brand .brand-divider { display: none; }
               .site-header-brand .brand-name { font-size: calc(0.91rem + 2px); }
               .site-sidebar { width: 100vw; }
           }

           /* Header controls: one rhythm for notifications, links, profile and drawer. */
           .site-header {
               background: #173247;
               box-shadow: 0 1px 0 rgba(255, 255, 255, 0.12);
           }
           .site-header-inner { min-height: 74px; }
           .site-header-brand .brand-mark { background: transparent; border: 0; box-shadow: none; }
           .site-header-tools { gap: 6px; }
           .site-header-quick-link,
           .site-profile-link,
           .site-menu-toggle {
               min-height: 44px;
               transition: background-color 170ms ease, border-color 170ms ease, color 170ms ease;
           }
           .site-header-quick-link:hover,
           .site-header-quick-link:focus-visible,
           .site-profile-link:hover,
           .site-profile-link:focus-visible,
           .site-menu-toggle:hover,
           .site-menu-toggle:focus-visible {
               box-shadow: none;
               outline: none;
               transform: none;
           }
           .site-profile-link {
               align-items: center;
               background: transparent;
               border: 1px solid transparent;
               border-radius: 8px;
               display: inline-flex;
               justify-content: center;
               padding: 8px;
           }
           .site-profile-link.is-active,
           .site-profile-link:hover,
           .site-profile-link:focus-visible {
               background: rgba(255, 255, 255, 0.1);
               border-color: rgba(255, 255, 255, 0.16);
           }
           .site-profile-link .site-user-avatar {
               background: rgba(255, 255, 255, 0.12);
               border: 2px solid rgba(255, 255, 255, 0.36);
               color: #fff;
               flex: 0 0 34px;
               font-size: calc(0.67rem + 2px);
               height: 34px;
               width: 34px;
           }
           .site-menu-toggle {
               background: transparent;
               border-color: rgba(255, 255, 255, 0.18);
               display: inline-flex;
               gap: 8px;
               justify-content: center;
               min-width: 44px;
               padding: 8px 11px;
           }
           .site-menu-toggle > i { color: #d1e7dc; font-size: calc(1.2rem + 2px); }
           .site-menu-toggle[aria-expanded='true'] { background: rgba(255, 255, 255, 0.14); }
           @media (max-width: 920px) {
               .site-header-quick-link,
               .site-profile-link,
               .site-menu-toggle,
               .site-header-account .site-account-trigger { min-height: 44px; min-width: 44px; padding: 8px; }
               .site-profile-link .site-user-avatar { flex-basis: 30px; height: 30px; width: 30px; }
           }

           /* Final institutional layer: compact, opaque and easy to scan. */
          .site-sidebar-backdrop { background: rgba(10, 29, 40, 0.34); backdrop-filter: none; -webkit-backdrop-filter: none; }
          .site-sidebar {
              background: #ffffff;
              border: 1px solid #dbe7e0;
              border-radius: 16px;
              bottom: 12px;
              box-shadow: -14px 10px 34px rgba(18, 52, 67, 0.14);
              max-height: calc(100dvh - 24px);
              right: 12px;
              top: 12px;
              transform: translateX(calc(100% + 24px));
              width: 352px;
          }
          .site-sidebar-header { background: #f7faf8; border-bottom-color: #e1ebe5; border-radius: 16px 16px 0 0; min-height: 68px; }
          .site-sidebar-nav { padding: 18px 16px 12px; }
          .site-sidebar-group + .site-sidebar-group { margin-top: 16px; padding-top: 15px; }
          .site-sidebar-link { border-radius: 12px; min-height: 46px; }
          .site-sidebar-link i { background: #edf5f1; border: 1px solid #e1eee7; border-radius: 9px; flex-basis: 30px; height: 30px; width: 30px; }
          .site-sidebar-link:hover i, .site-sidebar-link:focus-visible i { background: #e3f2e9; border-color: #c5e1cf; }
          .site-sidebar-link.is-active { background: #eaf3ed; border-color: #c8e0d0; box-shadow: inset 3px 0 #5b9d7b; }
          .site-sidebar-link.is-active i { background: #cfead9; border-color: #b8ddc6; }
          .site-sidebar-link-admin { background: #f1f6fa; border-color: #d9e6ee; }
          .site-sidebar-link-admin i { background: #e4eef5; border-color: #c6dce9; color: #35627a; }
          .site-sidebar-account { background: #f4faf6; border: 1px solid #d5e8dc; border-radius: 14px; margin: auto 16px 16px; padding: 8px; }
          .site-sidebar-account-trigger { min-height: 56px; }
          .site-sidebar-account-options { border-top-color: rgba(207, 229, 216, 0.9); }
          .site-menu-toggle { gap: 8px; }
          .site-menu-toggle .site-user-avatar { background: rgba(255, 255, 255, 0.13); border: 2px solid rgba(255, 255, 255, 0.42); }
          .site-menu-toggle .site-user-avatar-image { border-radius: 999px; object-fit: cover; }
          .dark .site-sidebar { background: #152532; border-color: #294252; }
          .dark .site-sidebar-header { background: #1b303f; border-color: #294252; }
          .dark .site-sidebar-link i { background: #1c3544; border-color: #315063; }
          .dark .site-sidebar-link.is-active { background: #214537; border-color: #40775f; }
          .dark .site-sidebar-account { background: #1c3544; border-color: #315063; }
          @media (max-width: 920px) {
              .site-sidebar { bottom: 8px; max-height: calc(100dvh - 16px); right: 8px; top: 8px; width: min(360px, calc(100vw - 16px)); }
          }
          @media (max-width: 480px) {
              .site-sidebar { border-radius: 20px; bottom: 8px; right: 8px; top: 8px; width: calc(100vw - 16px); }
              .site-sidebar-header { border-radius: 20px 20px 0 0; }
          }

        /* Navigation shell: left menu button, docked sidebar on desktop and account menu on the avatar. */
        .site-count-badge { align-items: center; background: #c2414f; border-radius: 999px; color: #fff; display: inline-flex; font-size: 11px; font-weight: 700; height: 18px; justify-content: center; line-height: 1; margin-left: auto; min-width: 18px; padding: 0 5px; }
        .site-count-badge[hidden] { display: none; }

        .site-user-menu { position: relative; }
        .site-user-menu-trigger { align-items: center; border-radius: 10px; color: #fff; cursor: pointer; display: flex; gap: 12px; list-style: none; min-height: 48px; padding: 4px 8px 4px 0; transition: background-color 160ms ease; }
        .site-user-menu-trigger::-webkit-details-marker { display: none; }
        .site-user-menu-trigger:hover, .site-user-menu-trigger:focus-visible, .site-user-menu[open] .site-user-menu-trigger { background: rgba(255, 255, 255, 0.08); outline: none; }
        .site-user-menu-trigger:focus-visible { box-shadow: 0 0 0 2px #9ce0bf; }
        .site-user-menu-divider { align-self: center; background: rgba(255, 255, 255, 0.26); height: 36px; margin-right: 4px; width: 1px; }
        .site-user-menu-identity { display: flex; flex-direction: column; line-height: 1.1; max-width: 170px; text-align: right; }
        .site-user-menu-trigger-name { color: #fff; font-size: 14px; font-weight: 700; letter-spacing: 0.02em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-user-menu-trigger-role { color: rgba(255, 255, 255, 0.68); font-size: 12px; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-user-menu-avatar { flex-shrink: 0; position: relative; }
        .site-user-menu .site-user-avatar { background: #fff; color: #173247; font-weight: 800; letter-spacing: 0.04em; }
        .site-user-menu-avatar .site-user-avatar { box-shadow: 0 0 0 2px rgba(156, 224, 191, 0.55); height: 38px; width: 38px; }
        .site-user-menu-dot { border: 2px solid #173247; font-size: 10px; height: 18px; margin: 0; min-width: 18px; padding: 0 4px; position: absolute; right: -6px; top: -5px; }
        .site-user-menu-chevron { color: rgba(255, 255, 255, 0.62); font-size: 11px; transition: transform 160ms ease; }
        .site-user-menu[open] .site-user-menu-chevron { transform: rotate(180deg); }

        .site-user-menu-panel { animation: site-user-menu-in 140ms ease-out both; background: #fff; border: 1px solid #e3e9e6; border-radius: 12px; box-shadow: 0 12px 32px rgba(18, 52, 67, 0.14); color: #2b2f36; padding: 6px; position: absolute; right: 0; top: calc(100% + 8px); width: 220px; z-index: 45; }
        @keyframes site-user-menu-in { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        .site-user-menu-name { color: #8a9aa1; font-size: 12px; font-weight: 600; margin: 0; overflow: hidden; padding: 8px 10px 6px; text-overflow: ellipsis; white-space: nowrap; }
        .site-user-menu-links { display: flex; flex-direction: column; }
        .site-user-menu-link { align-items: center; background: transparent; border: 0; border-radius: 8px; color: #2c4a5e; cursor: pointer; display: flex; font: inherit; font-size: 14px; font-weight: 500; gap: 10px; min-height: 36px; padding: 6px 10px; text-align: left; text-decoration: none; transition: background-color 120ms ease; width: 100%; }
        .site-user-menu-link i { color: #8a9aa1; font-size: 15px; width: 16px; }
        .site-user-menu-link:hover, .site-user-menu-link:focus-visible { background: #f2f6f4; outline: none; }
        .site-user-menu-link:hover i, .site-user-menu-link:focus-visible i { color: #397461; }
        .site-user-menu-logout-form { border-top: 1px solid #edf1ef; margin-top: 4px; padding-top: 4px; }
        .site-user-menu-logout, .site-user-menu-logout i { color: #9b5964; }
        .site-user-menu-logout:hover, .site-user-menu-logout:focus-visible { background: #fcf3f4; }
        .site-user-menu-logout:hover i { color: #9b5964; }

        .dark .site-user-menu-panel { background: #152532; border-color: #294252; }
        .dark .site-user-menu-name { color: #8fa4ae; }
        .dark .site-user-menu-link { color: #dbe7ee; }
        .dark .site-user-menu-link i { color: #8fa4ae; }
        .dark .site-user-menu-link:hover, .dark .site-user-menu-link:focus-visible { background: #1c3544; }
        .dark .site-user-menu-link:hover i { color: #9ccfb2; }
        .dark .site-user-menu-logout-form { border-color: #294252; }
        .dark .site-user-menu-logout, .dark .site-user-menu-logout i { color: #e8a3ae; }
        .dark .site-user-menu-logout:hover { background: #2a2228; }

        @media (prefers-reduced-motion: reduce) {
            .site-user-menu-panel { animation: none; }
        }

        /* Brand doubles as the menu button. */
        .site-brand-toggle { background: transparent; border: 0; border-radius: 12px; cursor: pointer; font: inherit; margin-left: -10px; padding: 6px 12px 6px 10px; text-align: left; transition: background-color 180ms ease; }
        .site-brand-toggle:hover, .site-brand-toggle:focus-visible { background: rgba(255, 255, 255, 0.07); outline: none; }
        .site-brand-toggle:focus-visible { box-shadow: 0 0 0 2px #9ce0bf; }
        .site-brand-indicator { align-items: center; background: rgba(255, 255, 255, 0.08); border-radius: 999px; color: rgba(255, 255, 255, 0.75); display: inline-flex; flex: 0 0 22px; font-size: 11px; height: 22px; justify-content: center; margin-left: 2px; transform: rotate(-90deg); transition: transform 320ms cubic-bezier(0.22, 1, 0.36, 1), background-color 180ms ease; }
        .site-brand-toggle[aria-expanded='true'] .site-brand-indicator { transform: rotate(0deg); }
        .site-brand-toggle:hover .site-brand-indicator { background: rgba(156, 224, 191, 0.2); color: #d8f5e4; }

        /* Sidebar: same navy surface as the header, so both read as one frame. */
        .site-sidebar, .dark .site-sidebar { background: #2c4a5e; border: 0; box-shadow: none; color: #fff; left: 0; right: auto; }
        .site-sidebar-header, .dark .site-sidebar-header { background: transparent; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .site-sidebar-header h2, .dark .site-sidebar-header h2 { color: #fff; }
        .site-sidebar-header h2::before, .dark .site-sidebar-header h2::before { color: #9ce0bf; }
        .site-sidebar-close, .dark .site-sidebar-close { color: rgba(255, 255, 255, 0.75); }
        .site-sidebar-close:hover, .site-sidebar-close:focus-visible, .dark .site-sidebar-close:hover { background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.14); color: #fff; }
        .site-sidebar-nav { padding: 14px 12px 20px; }
        .site-sidebar-group + .site-sidebar-group, .dark .site-sidebar-group + .site-sidebar-group { border-top: 1px solid rgba(255, 255, 255, 0.08); margin-top: 12px; padding-top: 14px; }
        .site-sidebar-label, .dark .site-sidebar-label { color: rgba(255, 255, 255, 0.5); }
        .site-sidebar-label::before, .dark .site-sidebar-label::before { background: #9ce0bf; }
        .site-sidebar-link, .site-sidebar-link-admin, .dark .site-sidebar-link { background: transparent; border-color: transparent; border-radius: 10px; color: rgba(255, 255, 255, 0.76); min-height: 42px; }
        .site-sidebar-link i, .site-sidebar-link-admin i, .dark .site-sidebar-link i { background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 9px; color: #9ccfb2; flex-basis: 30px; height: 30px; width: 30px; }
        .site-sidebar-link:hover, .site-sidebar-link:focus-visible, .dark .site-sidebar-link:hover, .dark .site-sidebar-link:focus-visible { background: rgba(255, 255, 255, 0.07); border-color: rgba(255, 255, 255, 0.08); color: #fff; }
        .site-sidebar-link:hover i, .site-sidebar-link:focus-visible i, .dark .site-sidebar-link:hover i { background: rgba(156, 224, 191, 0.16); border-color: rgba(156, 224, 191, 0.24); color: #c5f0d6; }
        .site-sidebar-link.is-active, .dark .site-sidebar-link.is-active { background: rgba(156, 224, 191, 0.13); border-color: rgba(156, 224, 191, 0.2); box-shadow: inset 3px 0 0 #9ce0bf; color: #fff; font-weight: 700; }
        .site-sidebar-link.is-active i, .dark .site-sidebar-link.is-active i { background: rgba(156, 224, 191, 0.24); border-color: rgba(156, 224, 191, 0.34); color: #d8f5e4; }
        .site-sidebar-link:focus-visible { outline: 2px solid #9ce0bf; outline-offset: 1px; }
        .site-sidebar-badge { border: 0; margin-left: auto; }
        .site-sidebar::-webkit-scrollbar { width: 8px; }
        .site-sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.14); border-radius: 999px; }

        /* Items slide in one after another when the menu opens. */
        .site-sidebar-label, .site-sidebar-link { opacity: 0; transform: translateX(-10px); transition: opacity 160ms ease, transform 200ms ease, background-color 160ms ease, border-color 160ms ease, color 160ms ease; }
        .site-shell.sidebar-open .site-sidebar-label, .site-shell.sidebar-open .site-sidebar-link,
        .site-shell.sidebar-docked .site-sidebar-label, .site-shell.sidebar-docked .site-sidebar-link {
            opacity: 1;
            transform: none;
            transition: opacity 300ms ease, transform 380ms cubic-bezier(0.22, 1, 0.36, 1), background-color 160ms ease, border-color 160ms ease, color 160ms ease;
            transition-delay: calc(90ms + var(--item-index, 0) * 24ms), calc(90ms + var(--item-index, 0) * 24ms), 0s, 0s, 0s;
        }
        .site-shell:not(.sidebar-ready) .site-sidebar,
        .site-shell:not(.sidebar-ready) .site-sidebar-label,
        .site-shell:not(.sidebar-ready) .site-sidebar-link,
        .site-shell:not(.sidebar-ready) .site-main,
        .site-shell:not(.sidebar-ready) [data-site-footer] { transition: none !important; }

        @media (min-width: 1021px) {
            .site-sidebar, .dark .site-sidebar {
                border-radius: 0;
                bottom: 0;
                max-height: none;
                top: var(--site-header-height);
                transform: translateX(-100%);
                transition: transform 380ms cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 380ms;
                visibility: hidden;
                width: 264px;
                z-index: 9;
            }
            .site-shell.sidebar-docked .site-sidebar { transform: none; transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), visibility 0s; visibility: visible; }
            .site-sidebar-header, .site-sidebar-backdrop { display: none; }
            .site-main, [data-site-footer] { transition: margin-left 420ms cubic-bezier(0.22, 1, 0.36, 1), border-radius 420ms ease; }
            .site-shell.sidebar-docked .site-page { background: #2c4a5e; }
            .site-shell.sidebar-docked .site-header { box-shadow: none; z-index: 20; }
            .site-shell.sidebar-docked .site-main { background: var(--background, var(--page)); border-top-left-radius: 22px; margin-left: 264px; overflow: clip; }
            .site-shell.sidebar-docked [data-site-footer] { margin-left: 264px; }
        }

        @media (max-width: 1020px) {
            .site-sidebar, .dark .site-sidebar {
                border-radius: 18px;
                bottom: 8px;
                box-shadow: 18px 0 44px rgba(10, 29, 40, 0.32);
                left: 8px;
                max-height: calc(100dvh - 16px);
                top: 8px;
                transform: translateX(calc(-100% - 16px));
                transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 420ms;
                width: min(320px, calc(100vw - 16px));
            }
            .site-shell.sidebar-open .site-sidebar { transform: none; transition: transform 460ms cubic-bezier(0.22, 1, 0.36, 1), visibility 0s; visibility: visible; }
            .site-sidebar-backdrop { backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px); background: rgba(10, 29, 40, 0.42); transition: opacity 320ms ease; }
        }

        @media (max-width: 920px) {
            .site-brand-toggle { margin-left: -6px; padding: 4px 8px 4px 6px; }
            .site-user-menu-trigger { gap: 0; padding: 4px; }
            .site-user-menu-divider, .site-user-menu-identity, .site-user-menu-chevron { display: none; }
            .site-user-menu-avatar .site-user-avatar { height: 34px; width: 34px; }
            .site-user-menu-panel { position: fixed; right: 12px; top: calc(var(--site-header-height) + 4px); width: min(240px, calc(100vw - 24px)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .site-sidebar, .site-sidebar-label, .site-sidebar-link, .site-main, [data-site-footer], .site-brand-indicator { transition: none !important; }
            .site-sidebar-label, .site-sidebar-link { opacity: 1; transform: none; }
        }
      </style>

    @stack('styles')
</head>

<body>
    @php
        $mensajesSinLeer = auth()->check()
            ? (int) (isset($page) ? data_get($page, 'props.comunicacionNoLeidos', 0) : auth()->user()->mensajesSinLeer())
            : 0;
    @endphp
    <div class="site-shell" id="site-shell">
        <script>
            (() => {
                let preferencia = null;
                try { preferencia = localStorage.getItem('prodenya.sidebar'); } catch (e) {}
                if (window.matchMedia('(min-width: 1021px)').matches && preferencia !== 'cerrado') {
                    document.getElementById('site-shell').classList.add('sidebar-docked');
                }
            })();
        </script>
        <aside class="site-sidebar" id="site-sidebar" role="dialog" aria-modal="true" aria-labelledby="site-menu-title" aria-hidden="true" inert>
            <div class="site-sidebar-header">
                <h2 id="site-menu-title">Menú</h2>
                <button type="button" class="site-sidebar-close" data-sidebar-close aria-label="Cerrar menú de navegación">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            @auth
                <nav class="site-sidebar-nav" aria-label="Módulos del sistema">
                    <div class="site-sidebar-group">
                        <a href="{{ route('home') }}" class="site-sidebar-link {{ request()->routeIs('home') ? 'is-active' : '' }}" @if (request()->routeIs('home')) aria-current="page" @endif>
                            <i class="bi bi-house" aria-hidden="true"></i>
                            Inicio
                        </a>
                    </div>
                    <div class="site-sidebar-group">
                        <p class="site-sidebar-label">Gestión</p>
                        @if (Route::has('legajos.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('legajos.index') }}" class="site-sidebar-link {{ request()->routeIs('legajos.*') ? 'is-active' : '' }}" @if (request()->routeIs('legajos.*')) aria-current="page" @endif>
                                <i class="bi bi-folder2-open" aria-hidden="true"></i>
                                Legajos
                            </a>
                        @endif
                        @if (Route::has('actas.index') && auth()->user()->tieneRol(...\App\Models\Acta::ROLES))
                            <a href="{{ route('actas.index') }}" class="site-sidebar-link {{ request()->routeIs('actas.*') ? 'is-active' : '' }}" @if (request()->routeIs('actas.*')) aria-current="page" @endif>
                                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                                Actas
                            </a>
                        @endif
                        @if (Route::has('tareas.index') && auth()->user()->tieneRol('admin', 'operador', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('tareas.index') }}" class="site-sidebar-link {{ request()->routeIs('tareas.*') ? 'is-active' : '' }}" @if (request()->routeIs('tareas.*')) aria-current="page" @endif>
                                <i class="bi bi-check2-square" aria-hidden="true"></i>
                                Tareas
                            </a>
                        @endif
                        @if (Route::has('estadisticas') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('estadisticas') }}" class="site-sidebar-link {{ request()->routeIs('estadisticas*') ? 'is-active' : '' }}" @if (request()->routeIs('estadisticas*')) aria-current="page" @endif>
                                <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                                Estadísticas
                            </a>
                        @endif
                    </div>


                    @if ((Route::has('admin.home') || Route::has('admin.auditoria.index')) && auth()->user()->tieneRol('admin'))
                        <div class="site-sidebar-group">
                            <p class="site-sidebar-label">Administración</p>
                            @if (Route::has('admin.home'))
                                <a href="{{ route('admin.home') }}" class="site-sidebar-link site-sidebar-link-admin {{ request()->routeIs('admin.home') ? 'is-active' : '' }}" @if (request()->routeIs('admin.home')) aria-current="page" @endif>
                                    <i class="bi bi-speedometer2" aria-hidden="true"></i>
                                    Panel administrativo
                                </a>
                            @endif
                            @if (Route::has('admin.auditoria.index'))
                                <a href="{{ route('admin.auditoria.index') }}" class="site-sidebar-link site-sidebar-link-admin {{ request()->routeIs('admin.auditoria.*') ? 'is-active' : '' }}" @if (request()->routeIs('admin.auditoria.*')) aria-current="page" @endif>
                                    <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                                    Auditoría
                                </a>
                            @endif
                        </div>
                    @endif

                    @if (Route::has('manual.download'))
                        <div class="site-sidebar-group">
                            <p class="site-sidebar-label">Ayuda</p>
                            <a href="{{ route('manual.download') }}" class="site-sidebar-link">
                                <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                                Manual de usuario
                            </a>
                        </div>
                    @endif
                </nav>


            @endauth
        </aside>
        <div class="site-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>
        <div class="site-page">
        <header class="site-header">
            <div class="site-header-inner">
                @auth
                    <button type="button" class="brand-lockup site-header-brand site-brand-toggle" data-sidebar-toggle aria-expanded="false" aria-controls="site-sidebar" aria-label="PRODENYA: mostrar u ocultar el menú">
                    <img src="{{ asset('assets/img/SN.png') }}" class="brand-mark brand-mark-image" alt="San Nicolás">
                    <span class="brand-divider" aria-hidden="true"></span>
                    <span>
                        <span class="brand-name block">PRODENYA</span>
                        <span class="brand-caption block">Promoción y Protección de Derechos</span>
                    </span>
                        <i class="bi bi-chevron-down site-brand-indicator" aria-hidden="true"></i>
                    </button>
                @else
                    <a href="{{ route('home') }}" class="brand-lockup site-header-brand" aria-label="Ir al inicio de Prodenya">
                    <img src="{{ asset('assets/img/SN.png') }}" class="brand-mark brand-mark-image" alt="San Nicolás">
                    <span class="brand-divider" aria-hidden="true"></span>
                    <span>
                        <span class="brand-name block">PRODENYA</span>
                        <span class="brand-caption block">Promoción y Protección de Derechos</span>
                    </span>
                    </a>
                @endauth

                 <div class="site-header-tools">
                    @auth
                        <div
                            data-site-header-extras
                            data-mode="{{ isset($page) ? 'inertia' : 'standalone' }}"
                            data-passkey-enabled="{{ (isset($page) ? data_get($page, 'props.showPasskeyNotice', false) : ($showPasskeyNotice ?? false)) ? '1' : '0' }}"
                            data-passkey-password-confirmed="{{ (isset($page) ? data_get($page, 'props.passkeyPasswordConfirmed', false) : ($passkeyPasswordConfirmed ?? false)) ? '1' : '0' }}"
                        ></div>
                        @php
                            $headerUser = auth()->user();
                            $headerInitials = collect(explode(' ', trim($headerUser->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->join('');
                        @endphp
                        @if (Route::has('mailbox.index') && $headerUser->tieneRol('admin', 'oficios', 'coordinador'))
                            <a href="{{ route('mailbox.index') }}" class="site-header-quick-link {{ request()->routeIs('mailbox.*') ? 'is-active' : '' }}" aria-label="Correo institucional" @if (request()->routeIs('mailbox.*')) aria-current="page" @endif>
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <span>Correo</span>
                            </a>
                        @endif
                        @php
                            $headerRole = $headerUser->rol?->nombre;
                            $headerCanAgenda = Route::has('agenda.index') && $headerUser->tieneRol('admin', 'coordinador', 'profesional', 'administrativo');
                            $headerCanMessages = Route::has('comunicacion.index') && $headerUser->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo');
                        @endphp
                        <details class="site-user-menu" data-disclosure>
                            <summary class="site-user-menu-trigger" aria-label="Abrir el menú de {{ $headerUser->name }}" aria-expanded="false">
                                <span class="site-user-menu-divider" aria-hidden="true"></span>
                                <span class="site-user-menu-identity">
                                    <span class="site-user-menu-trigger-name">{{ $headerUser->name }}</span>
                                    @if ($headerRole)
                                        <span class="site-user-menu-trigger-role">{{ ucfirst($headerRole) }}</span>
                                    @endif
                                </span>
                                <span class="site-user-menu-avatar">
                                    @if ($headerUser->avatar)
                                        <img src="{{ $headerUser->avatar }}" class="site-user-avatar site-user-avatar-image" alt="">
                                    @else
                                        <span class="site-user-avatar" aria-hidden="true">{{ $headerInitials }}</span>
                                    @endif
                                    <span class="site-count-badge site-user-menu-dot" data-unread-messages @if (! $mensajesSinLeer) hidden @endif>{{ $mensajesSinLeer }}</span>
                                </span>
                                <i class="bi bi-chevron-down site-user-menu-chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="site-user-menu-panel">
                                <p class="site-user-menu-name">{{ $headerUser->name }}</p>
                                <nav class="site-user-menu-links" aria-label="Opciones de la cuenta">
                                    @if (Route::has('profile.edit'))
                                        <a href="{{ route('profile.edit') }}" class="site-user-menu-link"><i class="bi bi-person" aria-hidden="true"></i>Mi perfil</a>
                                    @endif
                                    @if ($headerCanMessages)
                                        <a href="{{ route('comunicacion.index') }}" class="site-user-menu-link"><i class="bi bi-chat" aria-hidden="true"></i>Mensajes<span class="site-count-badge" data-unread-messages @if (! $mensajesSinLeer) hidden @endif>{{ $mensajesSinLeer }}</span></a>
                                    @endif
                                    @if ($headerCanAgenda)
                                        <a href="{{ route('agenda.index') }}" class="site-user-menu-link"><i class="bi bi-calendar3" aria-hidden="true"></i>Agenda</a>
                                    @endif
                                    @if (Route::has('security.edit'))
                                        <a href="{{ route('security.edit') }}" class="site-user-menu-link"><i class="bi bi-shield-lock" aria-hidden="true"></i>Seguridad</a>
                                    @endif
                                    @if (Route::has('appearance.edit'))
                                        <a href="{{ route('appearance.edit') }}" class="site-user-menu-link"><i class="bi bi-sliders2" aria-hidden="true"></i>Preferencias</a>
                                    @endif
                                </nav>
                                <form method="POST" action="{{ route('logout') }}" class="site-user-menu-logout-form">
                                    @csrf
                                    <button type="submit" class="site-user-menu-link site-user-menu-logout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Cerrar sesión</button>
                                </form>
                            </div>
                        </details>
                    @else
                        <a href="{{ route('login') }}" class="site-header-quick-link">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            Ingresar
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="site-main flex-1">
            @if (isset($page))
                <x-inertia::app />
            @else
                @yield('content')
            @endif
        </main>

          <div
              data-site-footer
          ></div>
         @auth
             <nav class="site-mobile-bottom-nav" aria-label="Navegación principal móvil">
                 <a href="{{ route('home') }}" class="site-mobile-bottom-link {{ request()->routeIs('home') ? 'is-active' : '' }}" @if (request()->routeIs('home')) aria-current="page" @endif>
                     <i class="bi bi-house" aria-hidden="true"></i>
                     <span>Inicio</span>
                 </a>
                 @if (Route::has('legajos.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                     <a href="{{ route('legajos.index') }}" class="site-mobile-bottom-link {{ request()->routeIs('legajos.*', 'mayores18.*') ? 'is-active' : '' }}" @if (request()->routeIs('legajos.*', 'mayores18.*')) aria-current="page" @endif>
                         <i class="bi bi-folder2-open" aria-hidden="true"></i>
                         <span>Legajos</span>
                     </a>
                 @endif
                 @if (Route::has('tareas.index') && auth()->user()->tieneRol('admin', 'operador', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                     <a href="{{ route('tareas.index') }}" class="site-mobile-bottom-link {{ request()->routeIs('tareas.*') ? 'is-active' : '' }}" @if (request()->routeIs('tareas.*')) aria-current="page" @endif>
                         <i class="bi bi-check2-square" aria-hidden="true"></i>
                         <span>Tareas</span>
                     </a>
                 @endif
                 @if (Route::has('agenda.index') && auth()->user()->tieneRol('admin', 'coordinador', 'profesional', 'administrativo'))
                     <a href="{{ route('agenda.index') }}" class="site-mobile-bottom-link {{ request()->routeIs('agenda.*') ? 'is-active' : '' }}" @if (request()->routeIs('agenda.*')) aria-current="page" @endif>
                         <i class="bi bi-calendar3" aria-hidden="true"></i>
                         <span>Agenda</span>
                     </a>
                 @endif
                 <button type="button" class="site-mobile-bottom-link site-mobile-bottom-link--more" data-sidebar-toggle aria-expanded="false" aria-controls="site-sidebar">
                     <i class="bi bi-grid-1x2" aria-hidden="true"></i>
                     <span>Más</span>
                 </button>
             </nav>
         @endauth
         </div>
    </div>

    @stack('scripts')
    <script>
        (() => {
            const shell = document.getElementById('site-shell');
            const sidebar = document.getElementById('site-sidebar');
            const header = document.querySelector('.site-header');
            const toggles = [...document.querySelectorAll('[data-sidebar-toggle]')];
            const closeButtons = [...document.querySelectorAll('[data-sidebar-close]')];

            if (!shell || !sidebar || !toggles.length) return;

            const desktop = window.matchMedia('(min-width: 1021px)');
            const storageKey = 'prodenya.sidebar';
            const focusableSelector = 'a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])';
            let lastFocus = null;
            let previousBodyOverflow = '';

            const savePreference = (value) => {
                try { localStorage.setItem(storageKey, value); } catch (e) {}
            };
            const readPreference = () => {
                try { return localStorage.getItem(storageKey); } catch (e) { return null; }
            };
            const syncHeaderHeight = () => {
                if (header) document.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight}px`);
            };
            const syncToggles = (expanded) => {
                toggles.forEach((toggle) => {
                    toggle.setAttribute('aria-expanded', String(expanded));
                    toggle.setAttribute('aria-label', expanded ? 'Ocultar el menú' : 'Mostrar el menú');
                });
            };

            /** Desktop: the sidebar is part of the page and only shows or hides. */
            const setDocked = (docked, remember = false) => {
                shell.classList.toggle('sidebar-docked', docked);
                sidebar.removeAttribute('role');
                sidebar.removeAttribute('aria-modal');
                sidebar.setAttribute('aria-hidden', String(!docked));
                sidebar.toggleAttribute('inert', !docked);
                syncToggles(docked);
                if (remember) savePreference(docked ? 'abierto' : 'cerrado');
            };

            /** Mobile: the sidebar is a modal drawer above the page. */
            const setDrawer = (isOpen) => {
                shell.classList.toggle('sidebar-open', isOpen);
                sidebar.setAttribute('role', 'dialog');
                sidebar.setAttribute('aria-modal', 'true');
                sidebar.setAttribute('aria-hidden', String(!isOpen));
                sidebar.toggleAttribute('inert', !isOpen);
                syncToggles(isOpen);
                if (isOpen) {
                    lastFocus = document.activeElement;
                    previousBodyOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    window.requestAnimationFrame(() => sidebar.querySelector(focusableSelector)?.focus());
                } else if (document.body.style.overflow === 'hidden') {
                    document.body.style.overflow = previousBodyOverflow;
                    if (lastFocus instanceof HTMLElement) lastFocus.focus();
                    lastFocus = null;
                }
            };

            const applyMode = () => {
                syncHeaderHeight();
                if (desktop.matches) {
                    if (shell.classList.contains('sidebar-open')) setDrawer(false);
                    setDocked(readPreference() !== 'cerrado');
                } else {
                    shell.classList.remove('sidebar-docked');
                    setDrawer(false);
                }
            };

            toggles.forEach((toggle) => toggle.addEventListener('click', () => {
                if (desktop.matches) {
                    setDocked(!shell.classList.contains('sidebar-docked'), true);
                } else {
                    setDrawer(!shell.classList.contains('sidebar-open'));
                }
            }));
            closeButtons.forEach((button) => button.addEventListener('click', () => setDrawer(false)));
            sidebar.addEventListener('click', (event) => {
                if (!desktop.matches && event.target.closest('a, button[type="submit"]')) setDrawer(false);
            });
            document.addEventListener('keydown', (event) => {
                if (desktop.matches || !shell.classList.contains('sidebar-open')) return;
                if (event.key === 'Escape') {
                    setDrawer(false);
                    return;
                }
                if (event.key !== 'Tab') return;

                const focusable = [...sidebar.querySelectorAll(focusableSelector)].filter((element) => element.offsetParent !== null);
                if (!focusable.length) return;

                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            });

            sidebar.querySelectorAll('.site-sidebar-label, .site-sidebar-link').forEach((item, index) => {
                item.style.setProperty('--item-index', String(Math.min(index, 14)));
            });

            desktop.addEventListener('change', applyMode);
            window.addEventListener('resize', syncHeaderHeight, { passive: true });
            applyMode();
            window.requestAnimationFrame(() => window.requestAnimationFrame(() => shell.classList.add('sidebar-ready')));
        })();

        /** Inertia changes pages without reloading this layout, so active links and counters are refreshed here. */
        (() => {
            const groups = [
                document.querySelector('.site-sidebar-nav'),
                document.querySelector('.site-header-tools'),
                document.querySelector('.site-mobile-bottom-nav'),
            ].filter(Boolean);

            const refreshActiveLinks = (pathname) => {
                groups.forEach((group) => {
                    const links = [...group.querySelectorAll('a[href]')].filter((link) => link.origin === window.location.origin);
                    let best = null;
                    links.forEach((link) => {
                        const path = link.pathname.replace(/\/$/, '') || '/';
                        const matches = path === '/' ? pathname === '/' : pathname === path || pathname.startsWith(`${path}/`);
                        if (matches && (!best || path.length > best.path.length)) best = { link, path };
                    });
                    links.forEach((link) => {
                        const active = best?.link === link;
                        link.classList.toggle('is-active', active);
                        if (active) link.setAttribute('aria-current', 'page');
                        else link.removeAttribute('aria-current');
                    });
                });
            };

            const refreshUnread = (count) => {
                if (typeof count !== 'number') return;
                document.querySelectorAll('[data-unread-messages]').forEach((badge) => {
                    badge.textContent = count > 99 ? '99+' : String(count);
                    badge.hidden = count === 0;
                });
            };

            document.addEventListener('prodenya:navigated', (event) => {
                const detail = event.detail ?? {};
                refreshActiveLinks(new URL(detail.url ?? window.location.href, window.location.origin).pathname.replace(/\/$/, '') || '/');
                refreshUnread(detail.comunicacionNoLeidos);
                document.querySelectorAll('.site-user-menu[open]').forEach((menu) => { menu.open = false; });
            });
        })();

        (() => {
            const disclosures = [...document.querySelectorAll('[data-disclosure]')];
            if (!disclosures.length) return;

            const setExpanded = (disclosure, expanded) => {
                disclosure.querySelector('summary')?.setAttribute('aria-expanded', String(expanded));
            };
            const closeDisclosure = (disclosure, restoreFocus = false) => {
                if (!disclosure.open) return;
                disclosure.open = false;
                setExpanded(disclosure, false);
                if (restoreFocus) disclosure.querySelector('summary')?.focus();
            };

            disclosures.forEach((disclosure) => {
                disclosure.addEventListener('toggle', () => {
                    setExpanded(disclosure, disclosure.open);
                    if (disclosure.open) {
                        disclosures
                            .filter((other) => other !== disclosure)
                            .forEach((other) => closeDisclosure(other));
                    }
                });
            });

            document.addEventListener('click', (event) => {
                disclosures.forEach((disclosure) => {
                    if (disclosure.open && !disclosure.contains(event.target)) closeDisclosure(disclosure);
                });
            });
            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                const openDisclosure = disclosures.find((disclosure) => disclosure.open);
                if (openDisclosure) closeDisclosure(openDisclosure, true);
            });
        })();
    </script>
</body>

</html>
