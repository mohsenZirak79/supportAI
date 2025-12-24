<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('پنل مدیریت'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-192.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo-192.png') }}">

    <!-- Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0e7490">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="پشتیبانی مناطق آزاد تجاری">

    @vite(['resources/css/admin.css', 'resources/js/admin.js', 'resources/js/register.js'])
    @stack('styles')
    <style>
        :root {
            --admin-primary: rgba(15, 23, 42, 0.92);
            --admin-primary-contrast: #f8fafc;
            --admin-accent: #22d3ee;
            --admin-accent-soft: rgba(34, 211, 238, 0.18);
            --admin-muted: #cbd5f5;
            --admin-bg: #f4f6fb;
            --admin-bg-gradient: radial-gradient(circle at 15% 20%, rgba(14, 165, 233, 0.35), transparent 45%) fixed;
            --admin-surface: #ffffff;
            --admin-border: rgba(15, 23, 42, 0.08);
            --admin-text: #0f172a;
            --admin-muted-text: #475569;
            --admin-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
        }

        body {
            margin: 0;
            font-family: 'IRANSans', 'Vazirmatn', sans-serif;
            background-color: var(--admin-bg);
            background-image: var(--admin-bg-gradient);
            background-attachment: fixed;
            color: var(--admin-text);
            transition: background-color .3s ease, color .3s ease;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(120deg, transparent 15%, rgba(255, 255, 255, 0.08), transparent 85%);
            opacity: .4;
        }

        .admin-layout {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .admin-navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1.25rem;
            padding: 1rem 1.75rem;
            background: var(--admin-primary);
            color: var(--admin-primary-contrast);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom-left-radius: 18px;
            border-bottom-right-radius: 18px;
            box-shadow: var(--admin-shadow);
            backdrop-filter: blur(14px);
        }

        .admin-navbar__brand {
            display: flex;
            align-items: center;
            gap: .8rem;
            color: inherit;
            text-decoration: none;
            position: relative;
        }

        .admin-navbar__logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--admin-accent), rgba(255, 255, 255, 0.65));
            color: #020617;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            letter-spacing: .05em;
            box-shadow: 0 12px 28px rgba(34, 211, 238, 0.45);
            overflow: hidden;
        }

        .admin-navbar__logo img,
        .admin-navbar__logo svg {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .admin-navbar__title strong {
            display: block;
            font-size: 1rem;
        }

        .admin-navbar__title small {
            display: block;
            margin-top: -2px;
            font-size: .75rem;
            opacity: .85;
        }

        .admin-navbar__glow {
            position: absolute;
            inset: 0;
            border-radius: 24px;
            background: var(--admin-accent-soft);
            opacity: 0;
            filter: blur(18px);
            transition: opacity .35s ease;
            z-index: -1;
        }

        .admin-navbar__brand:hover .admin-navbar__glow,
        .admin-navbar__brand:focus-visible .admin-navbar__glow {
            opacity: 1;
        }

        .admin-navbar__actions {
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .admin-navbar__toggle {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: var(--admin-primary-contrast);
            border-radius: 10px;
            display: none;
            cursor: pointer;
            padding: .45rem .55rem;
            transition: border-color .2s ease, background .2s ease;
        }

        .admin-navbar__toggle:hover {
            border-color: rgba(255, 255, 255, 0.65);
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-navbar__toggle span,
        .admin-navbar__toggle span::before,
        .admin-navbar__toggle span::after {
            display: block;
            width: 1.45rem;
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
            gap: 0.6rem;
            margin-inline-start: auto;
            margin-left: auto;
        }

        .admin-navbar__link {
            color: var(--admin-muted);
            text-decoration: none;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            font-size: 0.95rem;
            transition: color .25s ease, background .25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .admin-navbar__link:hover,
        .admin-navbar__link:focus-visible {
            color: var(--admin-primary-contrast);
            background: rgba(255, 255, 255, 0.12);
        }

        .admin-navbar__link--active {
            color: var(--admin-primary);
            background: var(--admin-primary-contrast);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.18);
        }

        .admin-navbar__link::after {
            content: '';
            position: absolute;
            inset-inline-start: -30%;
            top: 0;
            width: 220%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            opacity: 0;
            transform: translateX(-25%);
            transition: opacity .35s ease, transform .35s ease;
        }

        .admin-navbar__link:hover::after,
        .admin-navbar__link:focus-visible::after {
            opacity: 1;
            transform: translateX(25%);
        }

        .admin-content {
            flex: 1;
            width: min(1200px, 100%);
            margin: 1.75rem auto 2rem;
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
            border-radius: 18px;
            margin: 0 1rem 1.5rem;
            box-shadow: var(--admin-shadow);
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
                top: 70px;
                background: var(--admin-primary);
                flex-direction: column;
                padding: 1rem;
                gap: .25rem;
                transform-origin: top center;
                transform: scaleY(0);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.2s ease, opacity 0.2s ease;
                margin-inline-start: 0;
                backdrop-filter: blur(20px);
            }

            .admin-navbar__menu.is-open {
                transform: scaleY(1);
                opacity: 1;
                pointer-events: auto;
            }

            .admin-navbar__link {
                width: 100%;
                justify-content: center;
                border-radius: 0.85rem;
            }

            .admin-content {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body class="admin-layout">
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
        <span class="admin-navbar__glow"></span>
        <span class="admin-navbar__logo">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <!-- Wave pattern representing sea -->
                <path d="M0,60 Q25,50 50,60 T100,60 L100,100 L0,100 Z" fill="rgba(0,0,0,0.2)"/>
                <path d="M0,70 Q25,60 50,70 T100,70 L100,100 L0,100 Z" fill="rgba(0,0,0,0.15)"/>
                <!-- Island shape -->
                <path d="M30,50 Q40,40 50,50 Q60,40 70,50 L70,100 L30,100 Z" fill="rgba(0,0,0,0.25)"/>
                <!-- Support symbol (chat bubble) -->
                <circle cx="50" cy="35" r="12" fill="currentColor" opacity="0.9"/>
                <path d="M42,35 Q50,30 58,35 Q50,40 42,35" fill="currentColor" opacity="0.9"/>
            </svg>
        </span>
        <span class="admin-navbar__title">
            <strong>Support AI</strong>
            <small>{{ __('پنل مدیریت') }}</small>
        </span>
    </a>
    <div class="admin-navbar__actions">
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
        const menu = document.querySelector('[data-menu]');
        const toggles = document.querySelectorAll('[data-menu-toggle]');

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
