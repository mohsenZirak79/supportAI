<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('پنل مدیریت'))</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-192.png') }}">
    <meta name="theme-color" content="#0e7490">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="پشتیبانی مناطق آزاد تجاری">

    @vite(['resources/css/admin.css', 'resources/js/admin.js', 'resources/js/register.js'])
    @stack('styles')
    <style>
        /* ─── فاز ۱: رنگ‌های پروژه (بدون تغییر primary/accent) ─── */
        :root {
            --admin-primary: rgba(15, 23, 42, 0.92);
            --admin-primary-contrast: #f8fafc;
            --admin-accent: #22d3ee;
            --admin-accent-soft: rgba(34, 211, 238, 0.18);
            --admin-muted: #94a3b8;
            --admin-bg: #f4f6fb;
            --admin-bg-gradient: radial-gradient(circle at 15% 20%, rgba(14, 165, 233, 0.12), transparent 45%) fixed;
            --admin-surface: #ffffff;
            --admin-border: rgba(15, 23, 42, 0.08);
            --admin-text: #0f172a;
            --admin-muted-text: #475569;
            --admin-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
            --admin-shadow-soft: 0 2px 8px rgba(15, 23, 42, 0.08);
            --admin-sidebar-width: 256px;
        }

        body {
            margin: 0;
            font-family: 'Vazirmatn', 'Vazir', sans-serif;
            background-color: var(--admin-bg);
            background-image: var(--admin-bg-gradient);
            background-attachment: fixed;
            color: var(--admin-text);
            min-height: 100vh;
        }

        /* Overlay موبایل هنگام باز بودن سایدبار */
        .admin-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            z-index: 35;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .admin-overlay.is-open {
            display: block;
            opacity: 1;
        }
        @media (min-width: 993px) {
            .admin-overlay { display: none !important; }
        }

        /* سایدبار ثابت سمت راست */
        .admin-sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--admin-sidebar-width);
            min-height: 100vh;
            z-index: 40;
            display: flex;
            flex-direction: column;
            background: linear-gradient(to bottom, #f8fafc, #fff 30%, rgba(34, 211, 238, 0.04));
            border-left: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: -4px 0 24px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
        }
        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(100%);
            }
            .admin-sidebar.is-open {
                transform: translateX(0);
            }
        }

        .admin-sidebar__brand {
            padding: 1.25rem 1.25rem;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            border-radius: 0 0 0 1rem;
        }
        .admin-sidebar__logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--admin-accent), rgba(255, 255, 255, 0.7));
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(34, 211, 238, 0.35);
            overflow: hidden;
        }
        .admin-sidebar__logo svg {
            width: 24px;
            height: 24px;
        }
        .admin-sidebar__title strong { display: block; font-size: 1rem; color: var(--admin-text); }
        .admin-sidebar__title small { display: block; font-size: 0.75rem; color: var(--admin-muted-text); margin-top: 2px; }

        .admin-sidebar__close {
            display: none;
            margin-right: auto;
            padding: 0.5rem;
            border: none;
            background: transparent;
            color: var(--admin-text);
            border-radius: 0.5rem;
            cursor: pointer;
        }
        @media (max-width: 992px) {
            .admin-sidebar__close { display: inline-flex; }
        }

        .admin-sidebar__nav {
            flex: 1;
            padding: 0.75rem;
            overflow-y: auto;
        }
        .admin-sidebar__nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 12px;
            color: var(--admin-text);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .admin-sidebar__nav-item:hover {
            background: var(--admin-accent-soft);
            color: var(--admin-primary);
        }
        .admin-sidebar__nav-item.is-active {
            background: var(--admin-primary);
            color: var(--admin-primary-contrast);
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
        }
        .admin-sidebar__nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .admin-sidebar__nav-item.is-active svg { opacity: 1; }

        .admin-sidebar__footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid var(--admin-border);
            text-align: center;
        }
        .admin-sidebar__footer a {
            font-size: 0.75rem;
            color: var(--admin-muted-text);
            text-decoration: none;
        }
        .admin-sidebar__footer a:hover { color: var(--admin-accent); }

        /* ناحیه اصلی + هدر */
        .admin-main {
            min-height: 100vh;
            margin-right: 0;
            transition: margin-right 0.3s ease;
        }
        @media (min-width: 993px) {
            .admin-main { margin-right: var(--admin-sidebar-width); }
        }

        .admin-header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--admin-border);
            box-shadow: 0 1px 12px rgba(0, 0, 0, 0.05);
        }
        @media (min-width: 768px) {
            .admin-header { padding: 0.875rem 2rem; }
        }

        .admin-header__menu-btn {
            display: flex;
            padding: 0.5rem;
            border: none;
            background: transparent;
            color: var(--admin-text);
            border-radius: 0.75rem;
            cursor: pointer;
        }
        .admin-header__menu-btn:hover { background: var(--admin-accent-soft); }
        @media (min-width: 993px) {
            .admin-header__menu-btn { display: none; }
        }

        .admin-header__right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        @media (min-width: 768px) {
            .admin-header__right { gap: 1rem; }
        }
        .admin-header__logout {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--admin-muted-text);
            text-decoration: none;
            padding: 0.5rem 0.85rem;
            border-radius: 0.5rem;
            transition: color 0.2s, background 0.2s;
        }
        .admin-header__logout:hover {
            color: #dc2626;
            background: rgba(220, 38, 38, 0.08);
        }
        @media (max-width: 480px) {
            .admin-header__logout { font-size: 0.8125rem; padding: 0.4rem 0.6rem; }
        }

        /* نوتیفیکیشن (همان ساختار قبلی برای admin.js) */
        .admin-notifications { position: relative; }
        .admin-notifications__trigger {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid var(--admin-border);
            background: rgba(15, 23, 42, 0.04);
            color: var(--admin-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }
        .admin-notifications__trigger:hover {
            background: var(--admin-accent-soft);
            transform: translateY(-1px);
        }
        .admin-notifications__badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .admin-notifications__dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: min(360px, 92vw);
            background: var(--admin-surface);
            border-radius: 1rem;
            box-shadow: var(--admin-shadow);
            border: 1px solid var(--admin-border);
            overflow: hidden;
            display: none;
            z-index: 60;
        }
        .admin-notifications__dropdown.is-open { display: block; }
        .admin-notifications__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: rgba(15, 23, 42, 0.04);
            font-weight: 600;
            color: var(--admin-text);
        }
        .admin-notifications__actions { display: inline-flex; gap: 6px; }
        .admin-notifications__action {
            border: 1px solid var(--admin-border);
            background: #fff;
            color: var(--admin-text);
            border-radius: 8px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 0.78rem;
        }
        .admin-notifications__body { max-height: 360px; overflow-y: auto; }
        .admin-notifications__empty,
        .admin-notifications__loading {
            padding: 20px;
            text-align: center;
            color: var(--admin-muted-text);
            font-size: 0.9rem;
        }
        .admin-notifications__list { list-style: none; margin: 0; padding: 0; }
        .admin-notifications__item { border-bottom: 1px solid var(--admin-border); }
        .admin-notifications__item:last-child { border-bottom: none; }
        .admin-notifications__link {
            width: 100%;
            text-align: right;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        .admin-notifications__item.unread .admin-notifications__link {
            background: var(--admin-accent-soft);
        }
        .admin-notifications__content strong { display: block; color: var(--admin-text); font-size: 0.9rem; }
        .admin-notifications__content p { margin: 4px 0 0; color: var(--admin-muted-text); font-size: 0.82rem; }
        .admin-notifications__timestamp { font-size: 0.75rem; color: var(--admin-muted-text); white-space: nowrap; }

        /* کاربر + dropdown */
        .admin-user {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.5rem;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .admin-user:hover { background: var(--admin-accent-soft); }
        .admin-user__avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--admin-accent-soft), rgba(34, 211, 238, 0.08));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--admin-accent);
            font-weight: 700;
            font-size: 0.9rem;
        }
        @media (min-width: 768px) {
            .admin-user__avatar { width: 40px; height: 40px; }
        }
        .admin-user__name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--admin-text);
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 767px) {
            .admin-user__name { display: none; }
        }
        .admin-user__dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            background: var(--admin-surface);
            border-radius: 1rem;
            box-shadow: var(--admin-shadow);
            border: 1px solid var(--admin-border);
            padding: 0.5rem;
            display: none;
            z-index: 50;
        }
        .admin-user__dropdown.is-open { display: block; }
        .admin-user__dropdown a,
        .admin-user__dropdown button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: none;
            background: transparent;
            color: var(--admin-text);
            text-decoration: none;
            font-size: 0.875rem;
            text-align: right;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .admin-user__dropdown a:hover,
        .admin-user__dropdown button:hover {
            background: rgba(15, 23, 42, 0.06);
        }
        .admin-user__dropdown button.logout { color: #dc2626; }
        .admin-user__dropdown button.logout:hover { background: rgba(220, 38, 38, 0.08); }
        .admin-user__dropdown svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* محتوای اصلی */
        .admin-content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 2rem;
        }
        @media (min-width: 768px) {
            .admin-content { padding: 2rem; }
        }

    </style>
</head>
<body class="admin-layout">
@php
    $menuItems = [
        ['label' => 'داشبورد', 'route' => 'admin.dashboard', 'matches' => ['admin.dashboard'], 'icon' => 'dashboard'],
        ['label' => 'مدیریت کاربران', 'route' => 'admin.users', 'ability' => 'read-user', 'matches' => ['admin.users'], 'icon' => 'users'],
        ['label' => 'نقش‌ها', 'route' => 'admin.roles', 'ability' => 'read-role', 'matches' => ['admin.roles'], 'icon' => 'roles'],
        ['label' => 'تیکت‌ها', 'route' => 'admin.tickets', 'ability' => 'read-ticket', 'matches' => ['admin.tickets', 'admin.tickets.show'], 'icon' => 'ticket'],
        ['label' => 'گفت‌وگوها', 'route' => 'admin.chats', 'ability' => 'read-chat', 'matches' => ['admin.chats', 'admin.chats.detail'], 'icon' => 'chats'],
        ['label' => 'پروفایل', 'route' => 'profile', 'matches' => ['profile', 'profile.update'], 'icon' => 'profile'],
    ];
@endphp

    <!-- Overlay موبایل -->
    <div class="admin-overlay" id="adminOverlay" aria-hidden="true"></div>

    <!-- سایدبار -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar__brand">
            <a href="{{ route('admin.dashboard') }}" style="display:flex;align-items:center;gap:0.75rem;text-decoration:none;color:inherit;">
                <span class="admin-sidebar__logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <span class="admin-sidebar__title">
                    <strong>Support AI</strong>
                    <small>{{ __('پنل مدیریت') }}</small>
                </span>
            </a>
            <button type="button" class="admin-sidebar__close" id="adminSidebarClose" aria-label="بستن منو">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="admin-sidebar__nav">
            @foreach($menuItems as $item)
                @php
                    $canView = empty($item['ability'] ?? null) || (auth()->user()?->can($item['ability']));
                    $isActive = request()->routeIs($item['matches'] ?? $item['route']);
                @endphp
                @if($canView)
                    <a href="{{ route($item['route']) }}" class="admin-sidebar__nav-item {{ $isActive ? 'is-active' : '' }}">
                        @if(($item['icon'] ?? '') === 'dashboard')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        @elseif(($item['icon'] ?? '') === 'users')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        @elseif(($item['icon'] ?? '') === 'roles')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @elseif(($item['icon'] ?? '') === 'ticket')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V9z"/><path d="M6 9h.01M18 9h.01M6 15h.01M18 15h.01"/></svg>
                        @elseif(($item['icon'] ?? '') === 'chats')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        @endif
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="admin-sidebar__footer">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">خروج</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <button type="button" class="admin-header__menu-btn" id="adminMenuBtn" aria-label="باز کردن منو">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="admin-header__right">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();" class="admin-header__logout" aria-label="خروج از حساب کاربری">خروج</a>
                <div class="admin-notifications" data-admin-notifications>
                    <button class="admin-notifications__trigger" type="button" data-admin-notifications-trigger aria-haspopup="true" aria-expanded="false">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a5 5 0 0 0-5 5v3.1c0 .58-.2 1.14-.57 1.58L5 14.4h14l-.43-2.72A2.5 2.5 0 0 1 18 10.1V7a5 5 0 0 0-5-5zm0 18a3 3 0 0 0 2.83-2H9.17A3 3 0 0 0 12 20z"/></svg>
                        <span class="admin-notifications__badge" data-admin-notifications-badge style="display:none;">0</span>
                    </button>
                    <div class="admin-notifications__dropdown" data-admin-notifications-dropdown>
                        <div class="admin-notifications__header">
                            <span data-admin-notifications-title>نوتیفیکیشن‌ها</span>
                            <div class="admin-notifications__actions">
                                <button type="button" class="admin-notifications__action" data-admin-notifications-refresh>⟳</button>
                                <button type="button" class="admin-notifications__action" data-admin-notifications-mark-all>خوانده‌شده همه</button>
                            </div>
                        </div>
                        <div class="admin-notifications__body">
                            <div class="admin-notifications__loading" data-admin-notifications-loading style="display:none;">در حال بارگذاری…</div>
                            <div class="admin-notifications__empty" data-admin-notifications-empty style="display:none;">نوتیفیکیشنی ثبت نشده.</div>
                            <ul class="admin-notifications__list" data-admin-notifications-list></ul>
                        </div>
                    </div>
                </div>
                <div class="admin-user" id="adminUserMenu" aria-label="منوی کاربر" aria-haspopup="true" aria-expanded="false">
                    <div class="admin-user__avatar">{{ mb_substr(auth()->user()->name ?? 'ا', 0, 1) }}</div>
                    <span class="admin-user__name">{{ auth()->user()->name ?? 'کاربر' }}</span>
                    <div class="admin-user__dropdown" id="adminUserDropdown">
                        <a href="{{ route('admin.dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            داشبورد
                        </a>
                        <button type="button" class="logout" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            خروج از حساب کاربری
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <script>window.__adminFlash = { type: 'success', message: @json(session('success')) };</script>
            @elseif(session('error'))
                <script>window.__adminFlash = { type: 'error', message: @json(session('error')) };</script>
            @endif
            @yield('content')
        </div>
    </main>

    <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

    @include('admin.partials.confirm-modal')

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.getElementById('adminOverlay');
        var menuBtn = document.getElementById('adminMenuBtn');
        var sidebarClose = document.getElementById('adminSidebarClose');
        var userMenu = document.getElementById('adminUserMenu');
        var userDropdown = document.getElementById('adminUserDropdown');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-open');
        }
        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-open');
        }

        if (menuBtn) menuBtn.addEventListener('click', openSidebar);
        if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        /* فاز ۷.۲: نمایش پیام موفقیت/خطای session با toast یکسان */
        if (window.__adminFlash && window.toast) {
            var f = window.__adminFlash;
            if (f.type === 'success') window.toast.success(f.message);
            else if (f.type === 'error') window.toast.error(f.message);
            window.__adminFlash = null;
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 993) closeSidebar();
        });

        if (userMenu && userDropdown) {
            userMenu.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = userDropdown.classList.toggle('is-open');
                userMenu.setAttribute('aria-expanded', isOpen);
            });
            document.addEventListener('click', function () {
                userDropdown.classList.remove('is-open');
                userMenu.setAttribute('aria-expanded', 'false');
            });
            userDropdown.addEventListener('click', function (e) { e.stopPropagation(); });
        }

        /* فاز ۴: حالت loading برای فرم‌های معمولی (غیر AJAX) */
        document.querySelectorAll('.admin-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                if (form.dataset.ajaxForm === '1') return;
                var btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('is-loading')) {
                    btn.classList.add('is-loading');
                    btn.dataset.originalText = btn.textContent.trim();
                    btn.textContent = 'در حال ذخیره...';
                }
            });
        });

        /* فاز ۶.۲: مودال تأیید برای حذف */
        var confirmModalEl = document.getElementById('adminConfirmModal');
        var confirmBodyEl = document.getElementById('adminConfirmModalBody');
        var confirmSubmitBtn = document.getElementById('adminConfirmModalSubmit');
        var confirmCallback = null;

        if (confirmModalEl && confirmBodyEl && confirmSubmitBtn && window.bootstrap) {
            var confirmModal = new bootstrap.Modal(confirmModalEl);
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-confirm-form]');
                if (!btn) return;
                e.preventDefault();
                var formId = btn.getAttribute('data-confirm-form');
                var form = document.getElementById(formId) || document.querySelector(formId);
                if (!form) return;
                var body = btn.getAttribute('data-confirm-body') || 'آیا از انجام این عملیات مطمئن هستید؟';
                var title = btn.getAttribute('data-confirm-title');
                var btnText = btn.getAttribute('data-confirm-btn') || 'بله، حذف شود';
                if (title) {
                    var titleEl = confirmModalEl.querySelector('.modal-title');
                    if (titleEl) titleEl.textContent = title;
                }
                confirmBodyEl.textContent = body;
                confirmSubmitBtn.textContent = btnText;
                confirmCallback = function () { form.submit(); };
                confirmModal.show();
            });
            confirmSubmitBtn.addEventListener('click', function () {
                if (typeof confirmCallback === 'function') confirmCallback();
                confirmModal.hide();
                confirmCallback = null;
            });
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
