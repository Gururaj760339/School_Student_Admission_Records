<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Student Admission') · School Student Admission Records</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #14213d;
            --navy-soft: #1f2f52;
            --primary: #1e4fa3;
            --primary-dark: #163c7d;
            --primary-light: #eaf0fa;
            --gold: #c8962e;
            --text-primary: #18202f;
            --text-secondary: #5b6678;
            --border: #dde3ec;
            --border-soft: #eaeef4;
            --surface: #ffffff;
            --page-bg: #f3f5f9;
            --danger: #b42318;
            --danger-bg: #fef3f2;
            --header-h: 72px;
        }

        * { box-sizing: border-box; }

        html { min-height: 100%; scroll-behavior: smooth; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--page-bg);
            color: var(--text-primary);
            font-family: 'Public Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }

        button, input, select, textarea { font: inherit; }

        .container {
            width: min(1180px, calc(100% - 40px));
            margin-inline: auto;
        }

        /* ---------- Header ---------- */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--navy);
            border-bottom: 3px solid var(--gold);
            box-shadow: 0 4px 18px rgba(20, 33, 61, 0.18);
        }

        .header-inner {
            position: relative;
            min-height: var(--header-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 10px;
            background: #ffffff;
            color: var(--navy);
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: inset 0 0 0 2px var(--gold);
        }

        .brand-text {
            color: #ffffff;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.2px;
        }

        /* ---------- Menu button ---------- */
        .menu-toggle {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 10px;
            background: transparent;
            color: #ffffff;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .menu-toggle:hover,
        .menu-toggle[aria-expanded="true"] {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.45);
        }

        .menu-toggle svg {
            width: 22px;
            height: 22px;
        }

        .menu-toggle .menu-icon-close { display: none; }

        .menu-toggle[aria-expanded="true"] .menu-icon-dots { display: none; }

        .menu-toggle[aria-expanded="true"] .menu-icon-close { display: block; }

        /* ---------- Dropdown navigation ---------- */
        .site-nav {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 120;
            display: none;
            width: min(280px, calc(100vw - 32px));
            padding: 8px;
            flex-direction: column;
            align-items: stretch;
            gap: 2px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: 0 20px 48px rgba(20, 33, 61, 0.2);
        }

        .site-nav.is-open { display: flex; }

        .site-nav > a {
            display: flex;
            align-items: center;
            min-height: 44px;
            padding: 10px 14px;
            border-radius: 8px;
            color: #2b3648;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .site-nav > a:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .site-nav form.inline {
            display: block;
            margin: 6px 0 0;
            padding-top: 6px;
            border-top: 1px solid var(--border-soft);
        }

        .link-button {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #2b3648;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .link-button:hover {
            background: var(--danger-bg);
            color: var(--danger);
        }

        .menu-toggle:focus-visible,
        .site-nav > a:focus-visible,
        .link-button:focus-visible {
            outline: 3px solid rgba(30, 79, 163, 0.35);
            outline-offset: 2px;
        }

        .brand:focus-visible {
            outline: 3px solid rgba(255, 255, 255, 0.6);
            outline-offset: 4px;
            border-radius: 8px;
        }

        /* ---------- Main content ---------- */
        .main-content {
            flex: 1;
            padding-top: 36px;
            padding-bottom: 56px;
        }

        .main-content .card {
            overflow-x: auto;
            padding: 28px;
            border: 1px solid var(--border);
            border-top: 3px solid var(--primary);
            border-radius: 10px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(20, 33, 61, 0.04), 0 8px 24px rgba(20, 33, 61, 0.05);
        }

        .main-content h1 {
            margin-top: 0;
            color: var(--navy);
            font-size: clamp(22px, 3vw, 28px);
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.5px;
        }

        .main-content h2 {
            color: var(--navy);
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .main-content label {
            font-weight: 600;
            color: #2b3648;
        }

        .main-content .muted,
        .muted { color: var(--text-secondary); }

        .main-content input:not([type="checkbox"]):not([type="radio"]),
        .main-content select,
        .main-content textarea {
            max-width: 100%;
            padding: 9px 12px;
            border: 1px solid #c3ccda;
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--text-primary);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .main-content input:not([type="checkbox"]):not([type="radio"]):focus,
        .main-content select:focus,
        .main-content textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 79, 163, 0.15);
        }

        .main-content button:not(.link-button) {
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .main-content table {
            width: 100%;
            border-collapse: collapse;
        }

        .main-content th {
            background: #f6f8fb;
            color: #3d495c;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-align: left;
            border-bottom: 2px solid var(--border);
        }

        .main-content th,
        .main-content td { padding: 12px 14px; }

        .main-content td { border-bottom: 1px solid var(--border-soft); }

        .main-content tbody tr:hover { background: #f8faff; }

        /* ---------- Footer ---------- */
        .site-footer {
            margin-top: auto;
            border-top: 1px solid var(--border);
            background: var(--surface);
        }

        .site-footer .container {
            padding-top: 18px;
            padding-bottom: 18px;
            text-align: center;
        }

        .site-footer p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 12px;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 600px) {
            :root { --header-h: 64px; }

            .container { width: calc(100% - 28px); }

            .brand { gap: 10px; }

            .brand-mark {
                width: 38px;
                height: 38px;
                font-size: 13px;
            }

            .brand-text { font-size: 14px; }

            .main-content {
                padding-top: 22px;
                padding-bottom: 36px;
            }

            .main-content .card {
                padding: 18px;
                border-radius: 8px;
            }

            .main-content th,
            .main-content td { padding: 10px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>

<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('admission.select') }}">
                <span class="brand-text">School Student Admission Records</span>
            </a>

            @auth
                <button
                    type="button"
                    class="menu-toggle"
                    id="navigationMenuToggle"
                    aria-label="Open navigation menu"
                    aria-controls="siteNavigation"
                    aria-expanded="false"
                >
                    <svg class="menu-icon menu-icon-dots" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <circle cx="12" cy="5" r="1.8"></circle>
                        <circle cx="12" cy="12" r="1.8"></circle>
                        <circle cx="12" cy="19" r="1.8"></circle>
                    </svg>

                    <svg class="menu-icon menu-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            @endauth

            <nav class="site-nav" id="siteNavigation">
                @auth
                    <a href="{{ route('admission.select') }}">New Admission</a>
                    <a href="{{ route('admin.students.index') }}">Manage Students</a>
                    <a href="{{ route('admin.classes.index') }}">Classes &amp; Sections</a>
                    <a href="{{ route('branding.logo.edit') }}">Upload Logo</a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="link-button">
                            Log out ({{ auth()->user()->name }})
                        </button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    <main class="container main-content">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <p class="muted">
                Student records are stored in Google Sheets. No student data is kept in the database.
            </p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('navigationMenuToggle');
            const navigationMenu = document.getElementById('siteNavigation');

            if (!menuToggle || !navigationMenu) return;

            function openMenu() {
                navigationMenu.classList.add('is-open');
                menuToggle.setAttribute('aria-expanded', 'true');
                menuToggle.setAttribute('aria-label', 'Close navigation menu');
            }

            function closeMenu() {
                navigationMenu.classList.remove('is-open');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', 'Open navigation menu');
            }

            menuToggle.addEventListener('click', function (event) {
                event.stopPropagation();

                const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';

                if (isOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            navigationMenu.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            document.addEventListener('click', function () {
                closeMenu();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                    menuToggle.focus();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
