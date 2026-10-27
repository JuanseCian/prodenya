<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PRODENYA')</title>
    <meta name="description" content="@yield('description', 'Sistema de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes')">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @if (isset($page))
            @viteReactRefresh
            @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
            <x-inertia::head />
        @endif

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
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            box-shadow: 0 5px 22px rgba(57, 51, 119, 0.2);
            color: #fff;
            position: relative;
            z-index: 10;
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
            font-size: 1.35rem;
            font-weight: 800;
            height: 50px;
            justify-content: center;
            letter-spacing: -0.08em;
            width: 50px;
        }

        .brand-mark-image { object-fit: contain; padding: 7px; }

        .brand-name {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1.1;
        }

        .brand-caption {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.7rem;
            letter-spacing: 0.02em;
            margin-top: 4px;
        }

        .site-user-avatar {
            align-items: center;
            background: #fff;
            border-radius: 50%;
            color: var(--brand-dark);
            display: flex;
            font-size: 0.75rem;
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
        .site-account-chevron { font-size: 0.8rem; transition: transform 180ms ease; }
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
        .site-account-menu-name { color: #2d3348; font-size: 0.9rem; font-weight: 800; }
        .site-account-menu-email { color: #778096; font-size: 0.75rem; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-account-menu-meta { color: #6673a9; font-size: 0.7rem; font-weight: 700; margin-top: 8px; }
        .site-account-menu-link, .site-account-menu-logout { align-items: center; border-radius: 9px; display: flex; font-size: 0.82rem; font-weight: 600; gap: 10px; padding: 10px; text-decoration: none; width: 100%; }
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
            font-size: 0.78rem;
            font-weight: 700;
            padding: 8px 13px;
            transition: background-color 180ms ease;
        }

        .logout-button:hover { background: rgba(43, 38, 91, 0.55); }

        .site-main { min-height: calc(100vh - 164px); }

        .site-footer {
            background: var(--brand-dark);
            color: rgba(255, 255, 255, 0.78);
            margin-top: 64px;
        }

        .site-footer-inner {
            align-items: center;
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1240px;
            padding: 22px 24px;
        }

        .site-footer strong { color: #fff; }
        .site-footer small { font-size: 0.78rem; }
        .footer-municipal-logo { height: 58px; object-fit: contain; object-position: left center; width: 225px; }

        @media (max-width: 560px) {
            .site-header-inner { padding-left: 16px; padding-right: 16px; }
            .site-account-menu { max-width: calc(100vw - 32px); min-width: 250px; }
            .site-footer-inner { align-items: flex-start; flex-direction: column; padding: 20px 16px; }
        }

        .site-shell { background: var(--page); position: relative; }
        .site-page { display: flex; flex-direction: column; min-height: 100vh; margin-left: 0; }
         .site-sidebar {
             background: linear-gradient(170deg, #173247 0%, #2c4a5e 58%, #41634a 125%);
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
        .site-sidebar-brand .brand-name { font-size: 1.02rem; }
        .site-sidebar-brand .brand-caption { color: rgba(255, 255, 255, 0.6); font-size: 0.64rem; }
        .site-sidebar-rule { background: rgba(255, 255, 255, 0.15); height: 1px; margin: 0 10px 23px; }
        .site-sidebar-label { color: rgba(255, 255, 255, 0.45); font-size: 0.64rem; font-weight: 800; letter-spacing: 0.15em; padding: 0 12px 9px; text-transform: uppercase; }
        .site-sidebar-nav { display: flex; flex-direction: column; gap: 5px; }
        .site-sidebar-link {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.72);
            display: flex;
            font-size: 0.86rem;
            font-weight: 650;
            gap: 12px;
            padding: 12px 13px;
            text-decoration: none;
            transition: background-color 180ms ease, color 180ms ease, transform 180ms ease;
        }
        .site-sidebar-link i { color: rgba(255, 255, 255, 0.56); font-size: 1.03rem; width: 20px; }
        .site-sidebar-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; transform: translateX(2px); }
        .site-sidebar-link:hover i, .site-sidebar-link.is-active i { color: #fff; }
        .site-sidebar-link.is-active { background: rgba(255, 255, 255, 0.14); border-color: rgba(255, 255, 255, 0.1); box-shadow: inset 3px 0 0 #9ce0bf; color: #fff; }
        .site-sidebar-link.is-admin { background: rgba(156, 224, 191, 0.12); color: #dff8e9; margin-top: 6px; }
        .site-sidebar-link.is-admin i { color: #9ce0bf; }
        .site-sidebar-link.is-admin:hover { background: rgba(156, 224, 191, 0.2); }
        .site-sidebar-spacer { flex: 1; min-height: 28px; }
        .site-header { position: sticky; top: 0; }
        .site-header-inner { max-width: none; }
         .site-sidebar-backdrop { background: rgba(15, 23, 32, 0.25); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity 180ms ease; z-index: 35; }

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
         .site-mobile-nav-toggle i { font-size: 1.25rem; }
         .site-header-brand { align-items: center; color: #fff; display: inline-flex; flex: 0 1 auto; gap: 12px; min-width: 236px; text-decoration: none; }
          .site-header-brand .brand-mark { background: transparent; border: 0; border-radius: 0; box-shadow: none; flex: 0 0 42px; height: 42px; padding: 0; width: 42px; }
         .site-header-brand .brand-divider { background: rgba(255, 255, 255, 0.26); height: 36px; }
         .site-header-brand .brand-name { color: #fff; font-size: 1.18rem; font-weight: 700; letter-spacing: 0.085em; }
         .site-header-brand .brand-caption { color: rgba(255, 255, 255, 0.68); font-size: 0.75rem; font-weight: 400; letter-spacing: 0; margin-top: 4px; }
         .site-primary-nav { align-items: center; display: flex; flex: 1 1 auto; gap: 4px; min-width: 0; }
         .site-primary-link { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: rgba(255, 255, 255, 0.78); display: inline-flex; font-size: 0.875rem; font-weight: 550; gap: 8px; justify-content: center; min-height: 44px; padding: 9px 12px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; white-space: nowrap; }
         .site-primary-link i { color: rgba(255, 255, 255, 0.62); font-size: 1.05rem; }
         .site-primary-link:hover, .site-primary-link:focus-visible { background: rgba(255, 255, 255, 0.09); border-color: rgba(255, 255, 255, 0.16); color: #fff; outline: none; }
         .site-primary-link:hover i, .site-primary-link:focus-visible i { color: #fff; }
         .site-primary-link.is-active { background: rgba(255, 255, 255, 0.14); color: #fff; font-weight: 650; }
         .site-primary-link.is-active::after { background: #9ce0bf; border-radius: 999px; content: ''; height: 3px; margin-left: 1px; width: 3px; }
         .site-disclosure { position: relative; }
         .site-disclosure-trigger { cursor: pointer; list-style: none; }
         .site-disclosure-trigger::-webkit-details-marker { display: none; }
         .site-disclosure-trigger .site-disclosure-chevron { color: rgba(255, 255, 255, 0.54); font-size: 0.7rem; margin-left: 1px; transition: transform 150ms ease; width: auto; }
         .site-disclosure[open] .site-disclosure-chevron { transform: rotate(180deg); }
         .site-header-more-panel { animation: none; background: #fff; border: 1px solid #dce3df; border-radius: 9px; box-shadow: 0 12px 28px rgba(23, 50, 71, 0.16); min-width: 248px; padding: 6px; position: absolute; right: 0; top: calc(100% + 8px); }
         .site-header-more-link { border-radius: 6px; color: #2c4a5e; font-size: 0.84rem; font-weight: 550; gap: 10px; min-height: 42px; padding: 9px 10px; transition: background-color 150ms ease, color 150ms ease; }
         .site-header-more-link:hover, .site-header-more-link:focus-visible { background: #eef5f1; color: #173247; outline: none; transform: none; }
         .site-header-more-link i { color: #6b8f71; }
         .site-header-tools { align-items: center; display: flex; flex: 0 0 auto; gap: 6px; margin-left: auto; }
         .site-message-link, .site-admin-link { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: rgba(255, 255, 255, 0.78); display: inline-flex; font-size: 0.84rem; font-weight: 550; gap: 8px; min-height: 44px; padding: 9px 11px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; white-space: nowrap; }
         .site-message-link:hover, .site-message-link:focus-visible, .site-admin-link:hover, .site-admin-link:focus-visible { background: rgba(255, 255, 255, 0.09); border-color: rgba(255, 255, 255, 0.16); color: #fff; outline: none; }
         .site-message-link > i, .site-admin-link > i { color: rgba(255, 255, 255, 0.64); font-size: 1.05rem; }
         .site-message-badge { background: #9ce0bf; border: 2px solid #173247; color: #244d3b; font-size: 0.62rem; height: 18px; min-width: 18px; }
         .site-message-badge.is-empty { display: none; }
         .site-message-link.is-notified .site-message-icon-wrap, .site-message-link.is-notified::after { animation: none; }
         .site-header-account { margin-left: 6px; }
         .site-header-account::before { background: rgba(255, 255, 255, 0.2); content: ''; height: 28px; left: -7px; position: absolute; top: 8px; width: 1px; }
         .site-header-account .site-account-trigger { border-radius: 7px; gap: 8px; min-height: 44px; padding: 4px 7px; }
         .site-header-account .site-account-trigger:hover, .site-header-account[open] .site-account-trigger, .site-header-account .site-account-trigger:focus-visible { background: rgba(255, 255, 255, 0.09); outline: none; }
         .site-header-account .site-user-avatar { height: 36px; width: 36px; }
         .site-account-visible-name { color: rgba(255, 255, 255, 0.86); font-size: 0.82rem; max-width: 132px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
         .site-header-account .site-account-chevron { color: rgba(255, 255, 255, 0.62); font-size: 0.7rem; }
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
             .site-header-brand .brand-name { font-size: 0.98rem; }
             .site-header-brand .brand-caption { display: none; }
             .site-primary-nav { display: none; }
             .site-header-tools { gap: 2px; }
             .site-admin-link { display: none; }
             .site-account-visible-name { display: none; }
             .site-header-account { margin-left: 2px; }
             .site-header-account::before { display: none; }
              .site-header-account .site-account-menu { position: fixed; right: 16px; top: 64px; }
         }
         @media (max-width: 480px) {
             .site-header-inner { padding-left: 12px; padding-right: 12px; }
             .site-message-label { display: none; }
             .site-message-link, .site-admin-link, .site-header-account .site-account-trigger, .site-mobile-nav-toggle { min-height: 44px; min-width: 44px; padding: 8px; }
             .site-header-brand .brand-divider { display: none; }
             .site-header-brand .brand-name { font-size: 0.92rem; letter-spacing: 0.07em; }
             .site-account-menu { max-width: calc(100vw - 24px); min-width: 248px; }
         }
         @media (prefers-reduced-motion: reduce) {
              *, *::before, *::after { scroll-behavior: auto !important; transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; }
         }

         .site-page { margin-left: 0; transition: margin-left 220ms ease; }
          .site-sidebar { background: #fff; border-left: 1px solid #dce3df; box-shadow: -10px 0 28px rgba(23, 50, 71, 0.12); color: #2b2f36; max-height: 100dvh; overscroll-behavior: contain; padding: 0; transform: translateX(105%); transition: transform 220ms ease, visibility 220ms ease; visibility: hidden; width: 344px; }
         .site-shell.sidebar-open .site-sidebar { transform: translateX(0); visibility: visible; }
         .site-shell.sidebar-open .site-sidebar-backdrop { opacity: 1; pointer-events: auto; }
          .site-sidebar-header { align-items: center; border-bottom: 1px solid #e5ebe7; display: flex; justify-content: space-between; min-height: 72px; padding: 14px 20px; }
         .site-sidebar-header h2 { color: #173247; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.01em; margin: 0; }
         .site-sidebar-close { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: #60727b; cursor: pointer; display: inline-flex; height: 44px; justify-content: center; width: 44px; }
         .site-sidebar-close:hover, .site-sidebar-close:focus-visible { background: #f1f5f3; border-color: #d5e0da; color: #173247; outline: none; }
         .site-sidebar-nav { display: flex; flex: 0 0 auto; flex-direction: column; gap: 0; padding: 18px 16px 10px; }
         .site-sidebar-group + .site-sidebar-group { border-top: 1px solid #edf1ef; margin-top: 16px; padding-top: 16px; }
          .site-sidebar-label { color: #819197; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.04em; padding: 0 10px 7px; }
          .site-sidebar-link { align-items: center; border: 1px solid transparent; border-radius: 7px; color: #49616d; display: flex; font-size: 0.875rem; font-weight: 550; gap: 10px; min-height: 46px; padding: 10px 10px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; }
         .site-sidebar-link i { color: #77918d; font-size: 1rem; width: 20px; }
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
          .site-sidebar-account-copy h2 { color: #173247; font-size: 0.82rem; font-weight: 700; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
          .site-sidebar-account-copy p { color: #71828a; font-size: 0.7rem; margin: 3px 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
          .site-sidebar-account-copy small { color: #6b8f71; display: block; font-size: 0.68rem; font-weight: 650; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
          .site-sidebar-account-chevron { color: #819197; font-size: 0.75rem; margin-left: auto; transition: transform 150ms ease; }
          .site-sidebar-account[open] .site-sidebar-account-chevron { transform: rotate(180deg); }
          .site-sidebar-account-options { border-top: 1px solid #edf1ef; margin-top: 12px; padding-top: 8px; }
          .site-sidebar-account-email { color: #71828a; font-size: 0.72rem; margin: 0 10px 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
         .site-sidebar-account-link { align-items: center; background: transparent; border: 0; border-radius: 7px; color: #49616d; display: flex; font: inherit; font-size: 0.8rem; font-weight: 550; gap: 10px; min-height: 42px; padding: 9px 10px; text-align: left; text-decoration: none; width: 100%; }
         .site-sidebar-account-link:hover, .site-sidebar-account-link:focus-visible { background: #f1f6f3; color: #173247; outline: none; }
         .site-sidebar-account-link i { color: #77918d; font-size: 0.95rem; width: 18px; }
         .site-sidebar-logout-form { border-top: 1px solid #edf1ef; margin-top: 6px; padding-top: 6px; }
         .site-sidebar-logout { color: #9b5964; cursor: pointer; }
         .site-sidebar-logout:hover, .site-sidebar-logout:focus-visible { background: #fcf1f3; color: #8c4c58; }
         .site-sidebar-logout i { color: currentColor; }
         .site-header-quick-link, .site-menu-toggle { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 7px; color: rgba(255, 255, 255, 0.8); display: inline-flex; font-size: 0.84rem; font-weight: 550; gap: 8px; min-height: 44px; padding: 9px 11px; text-decoration: none; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; white-space: nowrap; }
         .site-header-quick-link:hover, .site-header-quick-link:focus-visible, .site-menu-toggle:hover, .site-menu-toggle:focus-visible { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2); color: #fff; outline: none; }
         .site-header-quick-link > i, .site-menu-toggle > i { color: rgba(255, 255, 255, 0.66); font-size: 1.05rem; }
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
              .site-header-brand .brand-name { font-size: 0.92rem; letter-spacing: 0.07em; }
           }

         /* Modern visual layer for the institutional header and navigation drawer. */
         .site-header {
             background:
                 radial-gradient(circle at 78% -80%, rgba(123, 213, 180, 0.2), transparent 34%),
                 linear-gradient(112deg, #102c3e 0%, #173c50 58%, #1b5360 100%);
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
         .site-header-brand .brand-name { font-size: 1.06rem; letter-spacing: 0.13em; }
         .site-header-brand .brand-caption { color: rgba(235, 248, 242, 0.72); font-size: 0.72rem; letter-spacing: 0.01em; margin-top: 5px; }
         .site-header-tools { gap: 8px; }
         .site-header-quick-link, .site-menu-toggle {
             border-radius: 12px;
             font-size: 0.84rem;
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
             font-size: 0.68rem;
             height: 36px;
             width: 36px;
         }
         .site-menu-toggle .site-user-avatar-image { object-fit: cover; }

         .site-sidebar-backdrop { background: rgba(10, 29, 40, 0.25); backdrop-filter: blur(2px); }
         .site-sidebar {
             background: linear-gradient(180deg, #ffffff 0%, #f7fbf9 100%);
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
             background: linear-gradient(135deg, #f9fcfa 0%, #eef7f2 100%);
             border-bottom-color: #e1ece6;
             border-radius: 22px 22px 0 0;
             min-height: 78px;
             padding: 16px 20px 15px 24px;
         }
         .site-sidebar-header h2 { font-size: 1.1rem; letter-spacing: -0.02em; }
         .site-sidebar-header h2::before { color: #6b8f71; content: 'PRODENYA'; display: block; font-size: 0.62rem; font-weight: 800; letter-spacing: 0.13em; margin-bottom: 4px; }
         .site-sidebar-close { border-radius: 11px; }
         .site-sidebar-nav { gap: 0; padding: 22px 16px 12px; }
         .site-sidebar-group + .site-sidebar-group { margin-top: 20px; padding-top: 19px; }
         .site-sidebar-label { align-items: center; color: #5f737d; display: flex; gap: 8px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.13em; padding: 0 10px 9px; }
         .site-sidebar-label::before { background: #8fc8a8; border-radius: 999px; content: ''; height: 6px; width: 6px; }
         .site-sidebar-link {
             border-color: transparent;
             border-radius: 13px;
             color: #4a626c;
             font-size: 0.88rem;
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
         .site-sidebar-link.is-active { background: linear-gradient(105deg, #e5f4eb 0%, #f1f8f4 100%); border-color: #cbe4d4; box-shadow: inset 4px 0 0 #5b9d7b, 0 6px 14px rgba(49, 111, 83, 0.08); color: #173247; }
         .site-sidebar-link.is-active i { background: #cfead9; border-color: #b8ddc6; color: #31765b; }
         .site-sidebar-account { background: rgba(235, 247, 240, 0.74); border: 1px solid #d5e8dc; border-radius: 16px; margin: auto 16px 16px; padding: 8px; }
         .site-sidebar-account-trigger { border-radius: 11px; min-height: 58px; padding: 7px 8px; }
         .site-sidebar-account-trigger:hover, .site-sidebar-account-trigger:focus-visible { background: rgba(255, 255, 255, 0.72); }
         .site-sidebar-account .site-user-avatar { background: #d7eee0; border: 1px solid #c1e2ce; flex-basis: 38px; height: 38px; width: 38px; }
         .site-sidebar-account-copy h2 { font-size: 0.84rem; }
         .site-sidebar-account-copy small { color: #4c8b6c; font-size: 0.69rem; margin-top: 5px; }
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
              .site-header-brand .brand-name { font-size: 0.91rem; }
              .site-sidebar { border-radius: 18px; }
              .site-sidebar-header { border-radius: 18px 18px 0 0; }
          }
         .dark .site-sidebar {
             background: linear-gradient(180deg, #152532 0%, #10202b 100%);
             border-color: #294252;
             color: #edf6fb;
         }
         .dark .site-sidebar-header { background: linear-gradient(135deg, #1b303f 0%, #152a37 100%); border-color: #294252; }
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
         .dark .site-sidebar-link.is-active { background: linear-gradient(105deg, #214537 0%, #1c3544 100%); border-color: #40775f; color: #fff; }
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
     </style>

    @stack('styles')
</head>

<body>
    <div class="site-shell" id="site-shell">
        <aside class="site-sidebar" id="site-sidebar" role="dialog" aria-modal="true" aria-labelledby="site-menu-title" aria-hidden="true">
            <div class="site-sidebar-header">
                <h2 id="site-menu-title">Menú</h2>
                <button type="button" class="site-sidebar-close" data-sidebar-close aria-label="Cerrar menú de navegación">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            @auth
                <nav class="site-sidebar-nav" aria-label="Módulos del sistema">
                    <div class="site-sidebar-group">
                        <p class="site-sidebar-label">Gestión</p>
                        @if (Route::has('legajos.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('legajos.index') }}" class="site-sidebar-link {{ request()->routeIs('legajos.*') ? 'is-active' : '' }}" @if (request()->routeIs('legajos.*')) aria-current="page" @endif>
                                <i class="bi bi-folder2-open" aria-hidden="true"></i>
                                Legajos
                            </a>
                        @endif
                        @if (Route::has('mayores18.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('mayores18.index') }}" class="site-sidebar-link {{ request()->routeIs('mayores18.*') ? 'is-active' : '' }}" @if (request()->routeIs('mayores18.*')) aria-current="page" @endif>
                                <i class="bi bi-person-check" aria-hidden="true"></i>
                                Mayores de 18
                            </a>
                        @endif
                        @if (Route::has('tareas.index') && auth()->user()->tieneRol('admin', 'operador', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('tareas.index') }}" class="site-sidebar-link {{ request()->routeIs('tareas.*') ? 'is-active' : '' }}" @if (request()->routeIs('tareas.*')) aria-current="page" @endif>
                                <i class="bi bi-check2-square" aria-hidden="true"></i>
                                Tareas
                            </a>
                        @endif
                        @if (Route::has('agenda.index') && auth()->user()->tieneRol('admin', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('agenda.index') }}" class="site-sidebar-link {{ request()->routeIs('agenda.*') ? 'is-active' : '' }}" @if (request()->routeIs('agenda.*')) aria-current="page" @endif>
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                Agenda
                            </a>
                        @endif
                        @if (Route::has('estadisticas') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('estadisticas') }}" class="site-sidebar-link {{ request()->routeIs('estadisticas*', 'dashboard') ? 'is-active' : '' }}" @if (request()->routeIs('estadisticas*', 'dashboard')) aria-current="page" @endif>
                                <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                                Estadísticas
                            </a>
                        @endif
                    </div>

                    <div class="site-sidebar-group">
                        <p class="site-sidebar-label">Comunicación</p>
                        @if (Route::has('comunicacion.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('comunicacion.index') }}" class="site-sidebar-link {{ request()->routeIs('comunicacion.*') ? 'is-active' : '' }}" data-comunicacion-entry @if (request()->routeIs('comunicacion.*')) aria-current="page" @endif>
                                <i class="bi bi-chat-dots" aria-hidden="true"></i>
                                Mensajes
                            </a>
                        @endif
                        @if (Route::has('mailbox.index') && auth()->user()->tieneRol('admin', 'oficios', 'coordinador'))
                            <a href="{{ route('mailbox.index') }}" class="site-sidebar-link {{ request()->routeIs('mailbox.*') ? 'is-active' : '' }}" @if (request()->routeIs('mailbox.*')) aria-current="page" @endif>
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                Correo institucional
                            </a>
                        @endif
                    </div>

                    @if ((Route::has('admin.home') || Route::has('admin.auditoria.index')) && auth()->user()->tieneRol('admin'))
                        <div class="site-sidebar-group">
                            <p class="site-sidebar-label">Administración</p>
                            @if (Route::has('admin.home'))
                                <a href="{{ route('admin.home') }}" class="site-sidebar-link {{ request()->routeIs('admin.home') ? 'is-active' : '' }}" @if (request()->routeIs('admin.home')) aria-current="page" @endif>
                                    <i class="bi bi-speedometer2" aria-hidden="true"></i>
                                    Panel administrativo
                                </a>
                            @endif
                            @if (Route::has('admin.auditoria.index'))
                                <a href="{{ route('admin.auditoria.index') }}" class="site-sidebar-link {{ request()->routeIs('admin.auditoria.*') ? 'is-active' : '' }}" @if (request()->routeIs('admin.auditoria.*')) aria-current="page" @endif>
                                    <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                                    Auditoría
                                </a>
                            @endif
                        </div>
                    @endif
                </nav>

                @php
                    $panelUser = auth()->user();
                    $panelInitials = collect(explode(' ', trim($panelUser->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                        ->join('');
                    $panelTeamRole = $panelUser->currentTeam ? $panelUser->teamRole($panelUser->currentTeam)?->label() : null;
                    $panelTeamName = $panelUser->currentTeam?->name;
                @endphp
                <details class="site-sidebar-account" data-disclosure>
                    <summary class="site-sidebar-account-trigger" aria-expanded="false">
                        @if ($panelUser->avatar)
                            <img src="{{ $panelUser->avatar }}" class="site-user-avatar site-user-avatar-image" alt="">
                        @else
                            <span class="site-user-avatar" aria-hidden="true">{{ $panelInitials }}</span>
                        @endif
                        <div class="site-sidebar-account-copy">
                            <h2 id="site-account-title">{{ $panelUser->name }}</h2>
                            @if ($panelTeamName || $panelTeamRole || $panelUser->rol?->nombre)
                                <small>{{ ucfirst($panelUser->rol?->nombre ?? '') }}{{ $panelTeamName ? ' · '.$panelTeamName : '' }}{{ $panelTeamRole ? ' · '.$panelTeamRole : '' }}</small>
                            @endif
                        </div>
                        <i class="bi bi-chevron-down site-sidebar-account-chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="site-sidebar-account-options">
                        <p class="site-sidebar-account-email">{{ $panelUser->email }}</p>
                        @if (Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="site-sidebar-account-link">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                                Mi perfil
                            </a>
                        @endif
                        @if (Route::has('security.edit'))
                            <a href="{{ route('security.edit') }}" class="site-sidebar-account-link">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                Seguridad y contraseña
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="site-sidebar-logout-form">
                            @csrf
                            <button type="submit" class="site-sidebar-account-link site-sidebar-logout">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </details>
            @endauth
        </aside>
        <div class="site-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>
        <div class="site-page">
        <header class="site-header">
            <div class="site-header-inner">
                <a href="{{ route('home') }}" class="brand-lockup site-header-brand" aria-label="Ir al inicio de Prodenya">
                    <img src="{{ asset('assets/img/SN.png') }}" class="brand-mark brand-mark-image" alt="San Nicolás">
                    <span class="brand-divider" aria-hidden="true"></span>
                    <span>
                        <span class="brand-name block">PRODENYA</span>
                        <span class="brand-caption block">Promoción y Protección de Derechos</span>
                    </span>
                </a>

                <div class="site-header-tools">
                    @auth
                        @if (Route::has('legajos.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            <a href="{{ route('legajos.index') }}" class="site-header-quick-link {{ request()->routeIs('legajos.*', 'mayores18.*') ? 'is-active' : '' }}" aria-label="Legajos" @if (request()->routeIs('legajos.*', 'mayores18.*')) aria-current="page" @endif>
                                <i class="bi bi-folder2-open" aria-hidden="true"></i>
                                <span>Legajos</span>
                            </a>
                        @endif

                        @if (Route::has('comunicacion.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta', 'oficios', 'coordinador', 'profesional', 'administrativo'))
                            @php
                                $comunicacionNoLeidos = isset($page)
                                    ? (int) data_get($page, 'props.comunicacionNoLeidos', 0)
                                    : 0;
                            @endphp
                            <a
                                href="{{ route('comunicacion.index') }}"
                                class="site-header-quick-link site-message-link"
                                data-comunicacion-link
                                data-comunicacion-entry
                                aria-label="Mensajes{{ $comunicacionNoLeidos > 0 ? ', '.$comunicacionNoLeidos.' mensajes sin leer' : '' }}"
                            >
                                <i class="bi bi-chat-square-dots" aria-hidden="true"></i>
                                <span class="site-message-label">Mensajes</span>
                                <span
                                    class="site-message-badge {{ $comunicacionNoLeidos > 0 ? '' : 'is-empty' }}"
                                    data-comunicacion-badge
                                    aria-live="polite"
                                >{{ $comunicacionNoLeidos > 99 ? '99+' : $comunicacionNoLeidos }}</span>
                            </a>
                        @endif

                        @php
                            $headerUser = auth()->user();
                            $headerInitials = collect(explode(' ', trim($headerUser->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->join('');
                        @endphp
                        <button type="button" class="site-menu-toggle" data-sidebar-toggle aria-expanded="false" aria-controls="site-sidebar" aria-label="Abrir menú de navegación">
                            @if ($headerUser->avatar)
                                <img src="{{ $headerUser->avatar }}" class="site-user-avatar site-user-avatar-image" alt="">
                            @else
                                <span class="site-user-avatar" aria-hidden="true">{{ $headerInitials }}</span>
                            @endif
                        </button>
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

        <footer class="site-footer">
            <div class="site-footer-inner">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('assets/img/municipalidad_logo.png') }}" class="footer-municipal-logo" alt="Municipalidad de San Nicolás de los Arroyos">
                    <small><strong>PRODENYA</strong> · Promoción y Protección de Derechos</small>
                </div>
                <small>© {{ date('Y') }} · Uso institucional</small>
            </div>
        </footer>
        </div>
    </div>

    @stack('scripts')
    <script>
        (() => {
            const shell = document.getElementById('site-shell');
            const sidebar = document.getElementById('site-sidebar');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const closeButtons = [...document.querySelectorAll('[data-sidebar-close]')];

            if (!shell || !sidebar || !toggle || !closeButtons.length) return;

             let lastFocus = null;
             let previousBodyOverflow = '';
             const focusableSelector = 'a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])';

            const setSidebarState = (isOpen) => {
                shell.classList.toggle('sidebar-open', isOpen);
                sidebar.setAttribute('aria-hidden', String(!isOpen));
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
                if (isOpen) {
                    lastFocus = document.activeElement;
                    previousBodyOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    window.requestAnimationFrame(() => sidebar.querySelector(focusableSelector)?.focus());
                } else {
                    document.body.style.overflow = previousBodyOverflow;
                    if (lastFocus instanceof HTMLElement) {
                        lastFocus.focus();
                        lastFocus = null;
                    }
                }
            };
            const closeSidebar = () => setSidebarState(false);
            toggle.addEventListener('click', () => setSidebarState(!shell.classList.contains('sidebar-open')));
            closeButtons.forEach((button) => button.addEventListener('click', closeSidebar));
            sidebar.querySelectorAll('a, button[type="submit"]').forEach((link) => link.addEventListener('click', closeSidebar));
            document.addEventListener('keydown', (event) => {
                if (!shell.classList.contains('sidebar-open')) return;
                if (event.key === 'Escape') {
                    closeSidebar();
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
        })();

        (() => {
            const link = document.querySelector('[data-comunicacion-link]');
            const badge = document.querySelector('[data-comunicacion-badge]');

            if (!link || !badge) return;

            const actualizarBadge = (cantidad) => {
                const total = Number(cantidad) || 0;
                badge.textContent = total > 99 ? '99+' : String(total);
                badge.classList.toggle('is-empty', total === 0);
                link.classList.toggle('is-notified', total > 0);
                link.setAttribute(
                    'aria-label',
                    total > 0
                        ? `Mensajes, ${total} mensajes sin leer`
                        : 'Mensajes',
                );
            };

            const consultarMensajes = () => {
                fetch('/comunicacion/no-leidos', {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                })
                    .then((response) => response.ok ? response.json() : null)
                    .then((data) => data && actualizarBadge(data.no_leidos))
                    .catch(() => {});
            };

            consultarMensajes();
            window.setInterval(consultarMensajes, 30000);
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
