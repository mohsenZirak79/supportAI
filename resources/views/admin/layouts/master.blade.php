<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('پنل مدیریت'))</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin.js', 'resources/js/register.js'])
    @stack('styles')
    <style>
        :root {
            --admin-primary: #0f172a;
            --admin-primary-contrast: #ffffff;
            --admin-accent: #22d3ee;
            --admin-muted: #cbd5f5;
            --admin-bg: #f4f6fb;
            --admin-surface: #ffffff;
            --admin-border: rgba(15, 23, 42, 0.08);
            --admin-text: #0f172a;
            --admin-muted-text: #475569;
        }

        body[data-theme='dark'] {
            --admin-primary: #020617;
            --admin-primary-contrast: #f8fafc;
            --admin-accent: #38bdf8;
            --admin-muted: #94a3b8;
            --admin-bg: #0f172a;
            --admin-surface: #111827;
            --admin-border: rgba(226, 232, 240, 0.18);
            --admin-text: #e2e8f0;
            --admin-muted-text: #94a3b8;
        }

        body {
            margin: 0;
            font-family: 'IRANSans', 'Vazirmatn', sans-serif;
            background-color: var(--admin-bg);
            color: var(--admin-text);
            transition: background-color .3s ease, color .3s ease;
        }

        .admin-layout {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--admin-primary);
            color: var(--admin-primary-contrast);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1rem;
            padding: 0.85rem 1.5rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.25);
        }

        .admin-navbar__brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-weight: 600;
            font-size: 1rem;
            color: var(--admin-primary-contrast);
            text-decoration: none;
        }

        .admin-navbar__actions {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .admin-navbar__toggle {
            background: transparent;
            border: none;
            color: var(--admin-primary-contrast);
            display: none;
            cursor: pointer;
            padding: .35rem;
        }

        .admin-navbar__toggle span,
        .admin-navbar__toggle span::before,
        .admin-navbar__toggle span::after {
            display: block;
            width: 1.5rem;
            height: 2px;
            background: currentColor;
            border-radius: 999px;
            position: relative;
            transition: transform .2s ease, opacity .2s ease;
        }

        .admin-navbar__toggle span::before,
        .admin-navbar__toggle span::after {
            content: '';
            position: absolute;
            inset-inline-start: 0;
        }

        .admin-navbar__toggle span::before {
            transform: translateY(-6px);
        }

        .admin-navbar__toggle span::after {
            transform: translateY(6px);
        }

        .admin-navbar__menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-inline-start: auto;
            margin-left: auto;
        }

        .admin-navbar__link {
            color: var(--admin-muted);
            text-decoration: none;
            padding: 0.5rem 0.9rem;
            border-radius: 999px;
            font-size: 0.95rem;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .admin-navbar__link:hover,
        .admin-navbar__link:focus {
            color: var(--admin-primary-contrast);
            background: rgba(255, 255, 255, 0.08);
        }

        .admin-navbar__link--active {
            color: var(--admin-primary);
            background: var(--admin-primary-contrast);
        }

        .theme-toggle {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: var(--admin-primary-contrast);
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            cursor: pointer;
            transition: background .2s ease, color .2s ease, border-color .2s ease;
            font-size: .9rem;
        }

        .theme-toggle:hover,
        .theme-toggle:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.45);
        }

        body[data-theme='dark'] .theme-toggle {
            border-color: rgba(148, 163, 184, 0.4);
        }

        .admin-content {
            flex: 1;
            width: min(1200px, 100%);
            margin: 1.5rem auto;
            padding: 0 1rem 2rem;
            transition: background-color .3s ease;
        }

        .admin-footer {
            background: var(--admin-surface);
            border-top: 1px solid var(--admin-border);
            padding: 1rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            color: var(--admin-muted-text);
        }

        .admin-footer a {
            color: var(--admin-primary);
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .admin-navbar {
                flex-wrap: wrap;
            }

            .admin-navbar__actions {
                order: 2;
                width: 100%;
                justify-content: space-between;
                margin-inline-start: 0;
            }

            .admin-navbar__toggle {
                display: block;
            }

            .admin-navbar__menu {
                order: 3;
                flex-basis: 100%;
                position: fixed;
                inset-inline-start: 0;
                inset-inline-end: 0;
                top: 56px;
                background: var(--admin-primary);
                flex-direction: column;
                padding: 1rem;
                gap: 0;
                transform-origin: top center;
                transform: scaleY(0);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.2s ease, opacity 0.2s ease;
                margin-inline-start: 0;
            }

            .admin-navbar__menu.is-open {
                transform: scaleY(1);
                opacity: 1;
                pointer-events: auto;
            }

            .admin-navbar__link {
                width: 100%;
                justify-content: center;
                border-radius: 0.75rem;
            }

            .admin-content {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body class="admin-layout" data-theme="light">
@php
    $menuItems = [
        ['label' => 'داشبورد', 'route' => 'admin.dashboard', 'matches' => ['admin.dashboard']],
        ['label' => 'مدیریت کاربران', 'route' => 'admin.users', 'ability' => 'read-user', 'matches' => ['admin.users']],
        ['label' => 'نقش‌ها', 'route' => 'admin.roles', 'ability' => 'read-role', 'matches' => ['admin.roles']],
        ['label' => 'تیکت‌ها', 'route' => 'admin.tickets', 'ability' => 'read-ticket', 'matches' => ['admin.tickets', 'admin.tickets.*']],
        ['label' => 'گفت‌وگوها', 'route' => 'admin.chats', 'ability' => 'read-chat', 'matches' => ['admin.chats', 'admin.chats.*']],
        ['label' => 'پروفایل', 'route' => 'profile', 'matches' => ['profile', 'profile.*']],
    ];
@endphp

<header class="admin-navbar">
    <a href="{{ route('admin.dashboard') }}" class="admin-navbar__brand">
        <span>Support AI</span>
        <small style="opacity: .8; font-weight: 400;">{{ __('پنل مدیریت') }}</small>
    </a>
    <div class="admin-navbar__actions">
        <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
            <span data-theme-icon>🌙</span>
            <span data-theme-label>{{ __('حالت تیره') }}</span>
        </button>
        <button class="admin-navbar__toggle" type="button" aria-label="{{ __('باز و بسته کردن منو') }}" data-menu-toggle>
            <span></span>
        </button>
    </div>
    <nav class="admin-navbar__menu" data-menu>
        @foreach($menuItems as $item)
            @php
                $canView = empty($item['ability'] ?? null) || (auth()->user()?->can($item['ability']));
                $isActive = request()->routeIs($item['matches'] ?? $item['route']);
            @endphp
            @if($canView)
                <a href="{{ route($item['route']) }}"
                   class="admin-navbar__link {{ $isActive ? 'admin-navbar__link--active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
</header>

<main class="admin-content">
    @yield('content')
</main>

<footer class="admin-footer">
    <span>&copy; {{ now()->year }} Support AI</span>
    <div>
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
            {{ __('خروج از حساب کاربری') }}
        </a>
        <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        const menu = document.querySelector('[data-menu]');
        const toggles = document.querySelectorAll('[data-menu-toggle]');
        const themeToggle = document.querySelector('[data-theme-toggle]');
        const themeIcon = document.querySelector('[data-theme-icon]');
        const themeLabel = document.querySelector('[data-theme-label]');
        const THEME_KEY = 'admin-theme';

        const applyTheme = (theme) => {
            body.setAttribute('data-theme', theme);
            if (themeToggle && themeIcon && themeLabel) {
                const isDark = theme === 'dark';
                themeIcon.textContent = isDark ? '☀️' : '🌙';
                themeLabel.textContent = isDark ? '{{ __('حالت روشن') }}' : '{{ __('حالت تیره') }}';
                themeToggle.setAttribute('aria-pressed', String(isDark));
            }
        };

        const getSavedTheme = () => {
            try {
                return localStorage.getItem(THEME_KEY);
            } catch (e) {
                return null;
            }
        };

        const saveTheme = (theme) => {
            try {
                localStorage.setItem(THEME_KEY, theme);
            } catch (e) {
                // ignore write errors
            }
        };

        const preferred = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        applyTheme(getSavedTheme() || preferred);

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const nextTheme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                applyTheme(nextTheme);
                saveTheme(nextTheme);
            });
        }

        if (menu) {
            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    menu.classList.toggle('is-open');
                });
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 992) {
                    menu.classList.remove('is-open');
                }
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
