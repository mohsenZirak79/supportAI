<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>پنل پشتیبانی مناطق آزاد تجاری - سیستم مدیریت تیکت‌ها و گفت‌وگوها</title>
    
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
    
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/user.css', 'resources/js/app.js'])
    <script>
        // Initialize direction from localStorage before page renders (prevent flash)
        (function() {
            const RTL_LOCALES = ['fa', 'ar'];
            const stored = localStorage.getItem('app_language') || 'fa';
            const dir = RTL_LOCALES.includes(stored) ? 'rtl' : 'ltr';
            document.documentElement.lang = stored;
            document.documentElement.dir = dir;
            document.documentElement.classList.add(dir);
        })();
    </script>
    <style>
        /* ============================================
           CSS CUSTOM PROPERTIES (Motion System)
           ============================================ */
        :root {
            /* Colors */
            --color-primary: #0e7490;
            --color-primary-light: #0891b2;
            --color-primary-lighter: #06b6d4;
            --color-primary-dark: #0c5c72;
            --color-accent: #22d3ee;
            --color-surface: #ffffff;
            --color-surface-elevated: #f8fafb;
            --color-text: #1f2937;
            --color-text-muted: #6b7280;
            --color-text-subtle: #9ca3af;
            --color-border: rgba(229, 231, 235, 0.6);
            --color-dark: #0f172a;
            --color-dark-elevated: #1e293b;
            
            /* Motion - Durations */
            --duration-instant: 100ms;
            --duration-fast: 200ms;
            --duration-normal: 400ms;
            --duration-slow: 600ms;
            --duration-slower: 800ms;
            --duration-slowest: 1000ms;
            
            /* Motion - Easings */
            --ease-smooth: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --ease-expo: cubic-bezier(0.19, 1, 0.22, 1);
            --ease-sine: cubic-bezier(0.37, 0, 0.63, 1);
            --ease-decelerate: cubic-bezier(0, 0, 0.2, 1);
            
            /* Spacing */
            --space-xs: 0.5rem;
            --space-sm: 0.75rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            --space-3xl: 4rem;
            --space-4xl: 6rem;
            --space-5xl: 8rem;
            
            /* Typography */
            --font-family-rtl: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif;
            --font-family-ltr: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --font-family: var(--font-family-rtl);
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;
            --font-size-5xl: 3rem;
            --font-size-6xl: 3.75rem;
            --font-size-7xl: 4.5rem;
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 28px;
            --radius-full: 9999px;
            
            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 16px 50px rgba(0, 0, 0, 0.15);
            --shadow-glow: 0 0 40px rgba(14, 116, 144, 0.3);
            --shadow-glow-intense: 0 0 60px rgba(14, 116, 144, 0.5);
        }

        /* ============================================
           REDUCED MOTION SUPPORT
           ============================================ */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            
            .hero-blob,
            .animated-gradient,
            .noise-overlay,
            .parallax-element {
                animation: none !important;
                transform: none !important;
            }
        }

        /* ============================================
           RESET & BASE STYLES
           ============================================ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-family);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: var(--color-text);
            line-height: 1.6;
            overflow-x: hidden;
            background: var(--color-surface);
        }

        /* LTR font-family override */
        html[dir="ltr"] body {
            font-family: var(--font-family-ltr);
        }

        html[dir="rtl"] body {
            font-family: var(--font-family-rtl);
        }

        /* ============================================
           ANIMATED BACKGROUND ELEMENTS
           ============================================ */
        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            opacity: 0.015;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        }

        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: orbFloat 20s ease-in-out infinite;
            will-change: transform;
        }

        .gradient-orb-1 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.3) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            animation-delay: 0s;
        }

        .gradient-orb-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(8, 145, 178, 0.25) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            animation-delay: -7s;
            animation-duration: 25s;
        }

        .gradient-orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.2) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            animation-delay: -14s;
            animation-duration: 30s;
        }

        @keyframes orbFloat {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(30px, -30px) scale(1.05);
            }
            50% {
                transform: translate(-20px, 20px) scale(0.95);
            }
            75% {
                transform: translate(20px, 30px) scale(1.02);
            }
        }

        /* ============================================
           HEADER / NAVBAR - Premium Glass Effect
           ============================================ */
        .landing-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: var(--space-md);
            transition: all var(--duration-normal) var(--ease-smooth);
            pointer-events: none;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md) var(--space-xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: var(--shadow-md);
            border-radius: var(--radius-xl);
            pointer-events: auto;
            opacity: 0;
            transform: translateY(-20px);
            animation: navSlideDown var(--duration-slower) var(--ease-expo) forwards;
            animation-delay: 200ms;
        }

        .landing-header.scrolled .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow-lg), 0 0 0 1px rgba(14, 116, 144, 0.05);
            transform: scale(0.98);
        }

        .landing-header.hidden .navbar {
            transform: translateY(-100%);
            opacity: 0;
        }

        @keyframes navSlideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            text-decoration: none;
            color: var(--color-primary);
            font-size: var(--font-size-lg);
            font-weight: 700;
            transition: all var(--duration-normal) var(--ease-smooth);
            position: relative;
        }

        .navbar-brand:hover {
            transform: translateX(-4px);
        }

        .navbar-brand:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 4px;
            border-radius: var(--radius-sm);
        }

        .navbar-brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: var(--font-size-xl);
            box-shadow: 0 4px 12px rgba(14, 116, 144, 0.3);
            transition: all var(--duration-normal) var(--ease-spring);
            overflow: hidden;
        }

        .navbar-brand-icon svg {
            width: 100%;
            height: 100%;
            padding: 8px;
        }

        .navbar-brand:hover .navbar-brand-icon {
            transform: rotate(8deg) scale(1.1);
            box-shadow: 0 8px 24px rgba(14, 116, 144, 0.4);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: var(--space-sm);
            flex-wrap: nowrap;
        }
        
        .navbar-actions .btn {
            white-space: nowrap;
            height: 38px;
            display: inline-flex;
            align-items: center;
        }
        
        .navbar-actions .lang-pills {
            height: 38px;
            display: flex;
            align-items: center;
        }

        /* Language Switcher */
        .lang-switcher {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .lang-switcher__select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #1f2937;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 100px;
            font-family: inherit;
        }

        [dir="ltr"] .lang-switcher__select {
            padding: 0.5rem 0.75rem 0.5rem 2rem;
        }

        .lang-switcher__select:hover {
            background: rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 0, 0, 0.15);
        }

        .lang-switcher__select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(14, 116, 144, 0.2);
        }

        .lang-switcher__select option {
            background: #ffffff;
            color: #1f2937;
        }

        .lang-switcher__icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            width: 16px;
            height: 16px;
            opacity: 0.5;
            color: #1f2937;
        }

        [dir="rtl"] .lang-switcher__icon {
            left: 0.75rem;
        }

        [dir="ltr"] .lang-switcher__icon {
            right: 0.75rem;
        }

        /* ============================================
           LANGUAGE PILLS - Modern Pill Buttons
           ============================================ */
        .lang-pills {
            display: flex;
            gap: 3px;
            padding: 3px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .lang-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 600;
            font-size: 0.75rem;
        }

        .lang-pill:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .lang-pill.active {
            background: var(--color-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(14, 116, 144, 0.4);
        }

        .lang-pill__text {
            pointer-events: none;
        }

        /* Scrolled state - dark pills */
        .navbar.scrolled .lang-pills {
            background: rgba(14, 116, 144, 0.08);
            border-color: rgba(14, 116, 144, 0.15);
        }

        .navbar.scrolled .lang-pill {
            color: var(--color-primary);
        }

        .navbar.scrolled .lang-pill:hover {
            background: rgba(14, 116, 144, 0.1);
            color: var(--color-primary-dark);
        }

        .navbar.scrolled .lang-pill.active {
            background: var(--color-primary);
            color: white;
        }

        /* ============================================
           BUTTONS - Premium Micro-interactions
           ============================================ */
        .btn {
            position: relative;
            padding: var(--space-sm) var(--space-lg);
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: var(--font-size-sm);
            text-decoration: none;
            border: 2px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            overflow: hidden;
            transition: all var(--duration-normal) var(--ease-smooth);
            -webkit-tap-highlight-color: transparent;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, transparent 50%);
            opacity: 0;
            transition: opacity var(--duration-fast);
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        /* Ripple effect */
        .btn-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple var(--duration-slow) linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .btn-outline {
            color: var(--color-primary);
            border-color: var(--color-primary);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--color-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14, 116, 144, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 14px rgba(14, 116, 144, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(14, 116, 144, 0.4);
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary-lighter) 100%);
        }

        .btn-lg {
            padding: var(--space-md) var(--space-2xl);
            font-size: var(--font-size-lg);
            border-radius: var(--radius-lg);
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.95);
            color: var(--color-primary);
            border: 2px solid rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .btn-hero-secondary:hover {
            background: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.2);
        }

        /* Button icon animation */
        .btn-icon {
            transition: transform var(--duration-normal) var(--ease-spring);
        }

        .btn:hover .btn-icon {
            transform: translateX(-4px);
        }

        /* ============================================
           HERO SECTION - Cinematic Entrance
           ============================================ */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-5xl) var(--space-xl);
            overflow: hidden;
        }

        /* Hero Background Slider */
        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transform: scale(1.1);
            transition: opacity 1.5s var(--ease-smooth), transform 8s var(--ease-smooth);
        }

        .hero-slide.active {
            opacity: 1;
            transform: scale(1);
        }

        /* Hero Overlay with animated gradient */
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(14, 116, 144, 0.85) 0%,
                rgba(8, 145, 178, 0.75) 50%,
                rgba(6, 182, 212, 0.7) 100%
            );
            z-index: 1;
        }

        /* Animated gradient mesh */
        .hero-gradient-mesh {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            opacity: 0.4;
            background: 
                radial-gradient(ellipse at 20% 80%, rgba(34, 211, 238, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(14, 116, 144, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(8, 145, 178, 0.2) 0%, transparent 60%);
            animation: meshShift 15s ease-in-out infinite;
        }

        @keyframes meshShift {
            0%, 100% {
                opacity: 0.4;
                transform: scale(1) translate(0, 0);
            }
            50% {
                opacity: 0.6;
                transform: scale(1.05) translate(-2%, 2%);
            }
        }

        /* Floating particles */
        .hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: particleFloat 20s linear infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 25s; }
        .particle:nth-child(2) { left: 20%; animation-delay: -5s; animation-duration: 20s; }
        .particle:nth-child(3) { left: 30%; animation-delay: -10s; animation-duration: 28s; }
        .particle:nth-child(4) { left: 40%; animation-delay: -15s; animation-duration: 22s; }
        .particle:nth-child(5) { left: 50%; animation-delay: -3s; animation-duration: 24s; }
        .particle:nth-child(6) { left: 60%; animation-delay: -8s; animation-duration: 26s; }
        .particle:nth-child(7) { left: 70%; animation-delay: -12s; animation-duration: 21s; }
        .particle:nth-child(8) { left: 80%; animation-delay: -6s; animation-duration: 27s; }
        .particle:nth-child(9) { left: 90%; animation-delay: -18s; animation-duration: 23s; }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        /* Hero Content */
        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 900px;
            text-align: center;
        }

        /* Staggered entrance animations */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-xs) var(--space-md);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-full);
            color: white;
            font-size: var(--font-size-sm);
            font-weight: 500;
            margin-bottom: var(--space-xl);
            opacity: 0;
            transform: translateY(20px);
            animation: heroFadeUp var(--duration-slower) var(--ease-expo) forwards;
            animation-delay: 400ms;
        }

        .hero-badge-dot {
            width: 8px;
            height: 8px;
            background: var(--color-accent);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .hero h1 {
            font-size: clamp(var(--font-size-2xl), 5vw, var(--font-size-4xl));
            font-weight: 700;
            color: white;
            text-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            margin-bottom: var(--space-xl);
            letter-spacing: -0.03em;
            line-height: 1.1;
            opacity: 0;
            transform: translateY(30px);
            animation: heroFadeUp var(--duration-slower) var(--ease-expo) forwards;
            animation-delay: 600ms;
        }

        .hero p {
            font-size: clamp(var(--font-size-base), 2.5vw, var(--font-size-xl));
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            margin-bottom: var(--space-2xl);
            line-height: 1.8;
            opacity: 0;
            transform: translateY(30px);
            animation: heroFadeUp var(--duration-slower) var(--ease-expo) forwards;
            animation-delay: 800ms;
        }

        .hero-cta {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(30px);
            animation: heroFadeUp var(--duration-slower) var(--ease-expo) forwards;
            animation-delay: 1000ms;
        }

        @keyframes heroFadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scroll indicator */
        .scroll-indicator {
            position: absolute;
            bottom: var(--space-2xl);
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-xs);
            color: rgba(255, 255, 255, 0.7);
            font-size: var(--font-size-sm);
            opacity: 0;
            animation: heroFadeUp var(--duration-slower) var(--ease-expo) forwards;
            animation-delay: 1400ms;
            cursor: pointer;
            transition: color var(--duration-fast);
        }

        .scroll-indicator:hover {
            color: white;
        }

        .scroll-indicator-mouse {
            width: 24px;
            height: 36px;
            border: 2px solid currentColor;
            border-radius: 12px;
            position: relative;
        }

        .scroll-indicator-wheel {
            width: 4px;
            height: 8px;
            background: currentColor;
            border-radius: 2px;
            position: absolute;
            top: 6px;
            left: 50%;
            transform: translateX(-50%);
            animation: scrollWheel 2s ease-in-out infinite;
        }

        @keyframes scrollWheel {
            0%, 100% { opacity: 1; transform: translateX(-50%) translateY(0); }
            50% { opacity: 0.3; transform: translateX(-50%) translateY(10px); }
        }

        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features {
            position: relative;
            padding: var(--space-5xl) var(--space-xl);
            background: linear-gradient(180deg, var(--color-surface) 0%, var(--color-surface-elevated) 50%, var(--color-surface) 100%);
            overflow: hidden;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* Section Header */
        .section-header {
            text-align: center;
            margin-bottom: var(--space-4xl);
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-xs) var(--space-md);
            background: linear-gradient(135deg, rgba(14, 116, 144, 0.1) 0%, rgba(8, 145, 178, 0.1) 100%);
            border: 1px solid rgba(14, 116, 144, 0.2);
            border-radius: var(--radius-full);
            color: var(--color-primary);
            font-size: var(--font-size-sm);
            font-weight: 600;
            margin-bottom: var(--space-lg);
            opacity: 0;
            transform: translateY(20px);
            transition: all var(--duration-slow) var(--ease-smooth);
        }

        .section-badge.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .section-title {
            font-size: clamp(var(--font-size-3xl), 5vw, var(--font-size-5xl));
            font-weight: 800;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 50%, var(--color-primary-lighter) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: var(--space-md);
            letter-spacing: -0.03em;
            position: relative;
            display: inline-block;
            opacity: 0;
            transform: translateY(20px);
            transition: all var(--duration-slow) var(--ease-smooth);
            transition-delay: 100ms;
        }

        .section-title.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .section-subtitle {
            font-size: var(--font-size-xl);
            color: var(--color-text-muted);
            max-width: 600px;
            margin: 0 auto;
            opacity: 0;
            transform: translateY(20px);
            transition: all var(--duration-slow) var(--ease-smooth);
            transition-delay: 200ms;
        }

        .section-subtitle.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: var(--space-xl);
        }

        /* Feature Card */
        .feature-card {
            position: relative;
            background: var(--color-surface);
            border-radius: var(--radius-2xl);
            padding: var(--space-2xl);
            border: 1px solid var(--color-border);
            transition: all var(--duration-normal) var(--ease-smooth);
            overflow: hidden;
            opacity: 0;
            transform: translateY(40px);
        }

        .feature-card.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Card glow effect on hover */
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-primary-light), var(--color-accent));
            transform: scaleX(0);
            transform-origin: right;
            transition: transform var(--duration-normal) var(--ease-smooth);
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: -100%;
            right: -100%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.08) 0%, transparent 50%);
            opacity: 0;
            transition: opacity var(--duration-slow);
            pointer-events: none;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: rgba(14, 116, 144, 0.2);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        .feature-card:hover::after {
            opacity: 1;
        }

        .feature-card:focus-within {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        .feature-icon-wrapper {
            position: relative;
            width: 72px;
            height: 72px;
            margin-bottom: var(--space-lg);
        }

        .feature-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--duration-normal) var(--ease-spring);
            box-shadow: 0 8px 24px rgba(14, 116, 144, 0.25);
            position: relative;
            z-index: 1;
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            inset: -4px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border-radius: calc(var(--radius-lg) + 4px);
            opacity: 0;
            z-index: -1;
            transition: opacity var(--duration-normal);
        }

        .feature-card:hover .feature-icon {
            transform: rotate(8deg) scale(1.1);
            box-shadow: 0 12px 32px rgba(14, 116, 144, 0.35);
        }

        .feature-card:hover .feature-icon::before {
            opacity: 0.3;
        }

        .feature-icon svg {
            width: 32px;
            height: 32px;
            stroke: white;
            fill: none;
            stroke-width: 2;
            transition: transform var(--duration-normal);
        }

        .feature-card:hover .feature-icon svg {
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: var(--space-sm);
            transition: color var(--duration-fast);
        }

        .feature-card:hover h3 {
            color: var(--color-primary-light);
        }

        .feature-card p {
            color: var(--color-text-muted);
            line-height: 1.8;
            font-size: var(--font-size-base);
        }

        /* ============================================
           STATS SECTION
           ============================================ */
        .stats {
            position: relative;
            padding: var(--space-4xl) var(--space-xl);
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            overflow: hidden;
        }

        .stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 30% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 80%, rgba(34, 211, 238, 0.1) 0%, transparent 40%);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-2xl);
            position: relative;
            z-index: 1;
        }

        .stat-item {
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
        }

        .stat-item.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-number {
            font-size: var(--font-size-5xl);
            font-weight: 800;
            color: white;
            line-height: 1;
            margin-bottom: var(--space-xs);
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
        }

        .stat-label {
            font-size: var(--font-size-lg);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            position: relative;
            background: linear-gradient(135deg, var(--color-dark) 0%, var(--color-dark-elevated) 50%, var(--color-dark) 100%);
            color: #e5e7eb;
            padding: var(--space-4xl) var(--space-xl) var(--space-2xl);
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--color-primary), var(--color-primary-light), transparent);
        }

        .footer-glow {
            position: absolute;
            top: -200px;
            right: -200px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.15) 0%, transparent 60%);
            border-radius: 50%;
            animation: orbFloat 30s ease-in-out infinite;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .footer-main {
            text-align: center;
            margin-bottom: var(--space-3xl);
            opacity: 0;
            transform: translateY(30px);
        }

        .footer-main.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .footer-brand {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
        }

        .footer-brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(14, 116, 144, 0.4);
        }

        .footer-brand-icon svg {
            width: 100%;
            height: 100%;
            padding: 10px;
        }

        .footer-brand h3 {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-description {
            color: #94a3b8;
            font-size: var(--font-size-lg);
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto;
        }

        .footer-bottom {
            padding-top: var(--space-xl);
            border-top: 1px solid rgba(55, 65, 81, 0.5);
            text-align: center;
            color: #64748b;
            font-size: var(--font-size-sm);
        }

        /* ============================================
           RESPONSIVE STYLES
           ============================================ */
        @media (max-width: 900px) {
            .navbar {
                padding: var(--space-sm) var(--space-md);
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .navbar-brand span {
                font-size: var(--font-size-sm);
            }
            
            .navbar-actions {
                gap: 6px;
            }
            
            .btn {
                padding: 8px 14px;
                font-size: 0.8rem;
            }
            
            .lang-pill {
                width: 28px;
                height: 28px;
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 768px) {
            .landing-header {
                padding: var(--space-sm);
            }
            
            .navbar {
                padding: 10px 14px;
                margin: 0 var(--space-xs);
                border-radius: var(--radius-lg);
            }

            .navbar-brand span {
                font-size: 0.8rem;
                max-width: 120px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .navbar-brand-icon {
                width: 36px;
                height: 36px;
            }

            .navbar-actions {
                gap: 4px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            
            .navbar-actions .btn {
                padding: 6px 10px;
                font-size: 0.75rem;
            }
            
            .lang-pills {
                padding: 2px;
            }
            
            .lang-pill {
                width: 26px;
                height: 26px;
                font-size: 0.65rem;
            }

            .hero {
                padding: var(--space-4xl) var(--space-md);
                min-height: auto;
                padding-top: 110px;
            }
            
            .hero-badge {
                font-size: 0.75rem;
                padding: 6px 12px;
            }

            .hero-cta {
                flex-direction: column;
                width: 100%;
                padding: 0 var(--space-md);
            }

            .hero-cta .btn {
                width: 100%;
            }

            .scroll-indicator {
                display: none;
            }

            .features {
                padding: var(--space-4xl) var(--space-md);
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: var(--space-lg);
            }

            .feature-card {
                padding: var(--space-xl);
            }
            
            .section-title {
                font-size: var(--font-size-2xl);
            }
            
            .section-subtitle {
                font-size: var(--font-size-base);
            }

            .stats {
                padding: var(--space-3xl) var(--space-md);
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--space-xl);
            }

            .stat-number {
                font-size: var(--font-size-4xl);
            }

            .footer {
                padding: var(--space-3xl) var(--space-md) var(--space-xl);
            }
            
            .footer-brand h3 {
                font-size: var(--font-size-lg);
            }
            
            .footer-description {
                font-size: var(--font-size-base);
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 8px 12px;
            }
            
            .navbar-brand span {
                display: none;
            }
            
            .navbar-brand-icon {
                width: 32px;
                height: 32px;
            }
            
            .navbar-actions .btn {
                padding: 5px 8px;
                font-size: 0.7rem;
            }
            
            .lang-pill {
                width: 24px;
                height: 24px;
                font-size: 0.6rem;
            }
            
            .hero {
                padding-top: 90px;
                padding-left: var(--space-sm);
                padding-right: var(--space-sm);
            }
            
            .hero-badge {
                font-size: 0.7rem;
            }
            
            .hero-cta .btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
            
            .features {
                padding: var(--space-3xl) var(--space-sm);
            }
            
            .feature-card {
                padding: var(--space-lg);
            }
            
            .feature-icon-wrapper {
                width: 56px;
                height: 56px;
            }
            
            .feature-icon {
                width: 56px;
                height: 56px;
            }
            
            .feature-card h3 {
                font-size: var(--font-size-lg);
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .stat-number {
                font-size: var(--font-size-3xl);
            }
            
            .footer-brand {
                flex-direction: column;
                gap: 8px;
            }
        }
        
        @media (max-width: 360px) {
            .navbar-actions .btn {
                padding: 4px 6px;
                font-size: 0.65rem;
            }
            
            .lang-pill {
                width: 22px;
                height: 22px;
            }
        }
    </style>
</head>
<body>
    <!-- Subtle noise texture overlay -->
    <div class="noise-overlay" aria-hidden="true"></div>

    <!-- Header / Navbar -->
    <header class="landing-header" id="header" role="banner">
        <nav class="navbar" role="navigation" aria-label="Main navigation">
            <a href="/" class="navbar-brand" aria-label="صفحه اصلی - پنل پشتیبانی مناطق آزاد تجاری">
                <div class="navbar-brand-icon">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M0,60 Q25,50 50,60 T100,60 L100,100 L0,100 Z" fill="rgba(255,255,255,0.3)"/>
                        <path d="M0,70 Q25,60 50,70 T100,70 L100,100 L0,100 Z" fill="rgba(255,255,255,0.2)"/>
                        <path d="M30,50 Q40,40 50,50 Q60,40 70,50 L70,100 L30,100 Z" fill="rgba(255,255,255,0.4)"/>
                        <circle cx="50" cy="35" r="12" fill="white" opacity="0.9"/>
                        <path d="M42,35 Q50,30 58,35 Q50,40 42,35" fill="white" opacity="0.9"/>
                    </svg>
                </div>
                <span data-i18n="landing.brandName">پنل پشتیبانی مناطق آزاد تجاری</span>
            </a>
            <div class="navbar-actions">
                <!-- Beautiful Language Switcher -->
                <div class="lang-pills" role="group" aria-label="انتخاب زبان">
                    <button class="lang-pill" data-lang="fa" title="فارسی">
                        <span class="lang-pill__text">فا</span>
                    </button>
                    <button class="lang-pill" data-lang="en" title="English">
                        <span class="lang-pill__text">EN</span>
                    </button>
                    <button class="lang-pill" data-lang="ar" title="العربية">
                        <span class="lang-pill__text">ع</span>
                    </button>
                </div>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" data-i18n="landing.goToDashboard">
                        <span>ورود به داشبورد</span>
                        <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline" data-i18n="nav.login">ورود</a>
                    <a href="{{ route('register') }}" class="btn btn-primary" data-i18n="nav.register">ثبت‌نام</a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" role="region" aria-label="معرفی پنل پشتیبانی">
        <!-- Background Image Slider -->
        <div class="hero-slider" aria-hidden="true">
            <div class="hero-slide active" style="background-image: url('/images/1.jpg');"></div>
            <div class="hero-slide" style="background-image: url('/images/2.jpg');"></div>
            <div class="hero-slide" style="background-image: url('/images/3.jpg');"></div>
        </div>
        
        <!-- Overlay -->
        <div class="hero-overlay" aria-hidden="true"></div>
        
        <!-- Animated gradient mesh -->
        <div class="hero-gradient-mesh" aria-hidden="true"></div>
        
        <!-- Floating particles -->
        <div class="hero-particles" aria-hidden="true">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot" aria-hidden="true"></span>
                <span data-i18n="landing.advancedPlatform">پلتفرم پیشرفته پشتیبانی</span>
            </div>
            <h1 data-i18n="landing.heroTitle">سیستم مدیریت پشتیبانی مناطق آزاد تجاری</h1>
            <p data-i18n="landing.heroSubtitle">پلتفرم جامع و هوشمند برای مدیریت تیکت‌ها، گفت‌وگوها و ارتباط مؤثر با کاربران</p>
            <div class="hero-cta">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg" data-i18n="landing.goToDashboard">
                        <span>ورود به داشبورد</span>
                        <svg class="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg" data-i18n="landing.getStarted">
                        <svg class="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        <span>شروع کنید</span>
                        
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-hero-secondary btn-lg" data-i18n="landing.loginToAccount">
                        ورود به حساب کاربری
                    </a>
                @endauth
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="scroll-indicator" onclick="document.querySelector('.features').scrollIntoView({ behavior: 'smooth' })" role="button" aria-label="اسکرول به پایین">
            <div class="scroll-indicator-mouse">
                <div class="scroll-indicator-wheel"></div>
            </div>
            <span data-i18n="landing.scrollDown">اسکرول کنید</span>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" role="region" aria-label="ویژگی‌های پلتفرم">
        <!-- Background orbs -->
        <div class="gradient-orb gradient-orb-1" aria-hidden="true"></div>
        <div class="gradient-orb gradient-orb-2" aria-hidden="true"></div>
        
        <div class="features-container">
            <header class="section-header">
                <div class="section-badge" data-reveal>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <span data-i18n="landing.topFeatures">ویژگی‌های برتر</span>
                </div>
                <h2 class="section-title" data-reveal data-i18n="landing.featuresTitle">ویژگی‌های پلتفرم</h2>
                <p class="section-subtitle" data-reveal data-i18n="landing.featuresSubtitle">ابزارهای قدرتمند برای مدیریت بهتر پشتیبانی</p>
            </header>

            <div class="features-grid">
                <article class="feature-card" data-reveal tabindex="0">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 data-i18n="landing.smartChat">گفت‌وگوی هوشمند</h3>
                    <p data-i18n="landing.smartChatDesc">سیستم چت پیشرفته با پشتیبانی از هوش مصنوعی برای پاسخگویی سریع و دقیق به کاربران</p>
                </article>

                <article class="feature-card" data-reveal tabindex="0">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </div>
                    </div>
                    <h3 data-i18n="landing.ticketManagement">مدیریت تیکت‌ها</h3>
                    <p data-i18n="landing.ticketManagementDesc">سیستم کامل مدیریت تیکت‌های پشتیبانی با امکان پیگیری، اولویت‌بندی و پاسخگویی</p>
                </article>

                <article class="feature-card" data-reveal tabindex="0">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                    </div>
                    <h3 data-i18n="landing.supportTeam">تیم پشتیبانی</h3>
                    <p data-i18n="landing.supportTeamDesc">مدیریت تیم‌های پشتیبانی با سیستم نقش‌ها و دسترسی‌های پیشرفته</p>
                </article>

                <article class="feature-card" data-reveal tabindex="0">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <line x1="18" y1="20" x2="18" y2="10"/>
                                <line x1="12" y1="20" x2="12" y2="4"/>
                                <line x1="6" y1="20" x2="6" y2="14"/>
                            </svg>
                        </div>
                    </div>
                    <h3 data-i18n="landing.reporting">گزارش‌گیری</h3>
                    <p data-i18n="landing.reportingDesc">داشبورد تحلیلی برای بررسی عملکرد و آمار تیکت‌ها و گفت‌وگوها</p>
                </article>

                <article class="feature-card" data-reveal tabindex="0">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                    </div>
                    <h3 data-i18n="landing.security">امنیت بالا</h3>
                    <p data-i18n="landing.securityDesc">سیستم امنیتی پیشرفته با احراز هویت چندمرحله‌ای و مدیریت دسترسی‌ها</p>
                </article>

                <article class="feature-card" data-reveal tabindex="0">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                        </div>
                    </div>
                    <h3 data-i18n="landing.performance">عملکرد سریع</h3>
                    <p data-i18n="landing.performanceDesc">پلتفرم بهینه‌شده با سرعت بالا و تجربه کاربری روان و حرفه‌ای</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats" role="region" aria-label="آمار و ارقام">
        <div class="stats-container">
            <div class="stat-item" data-reveal>
                <div class="stat-number" data-stat="activeUsers">+۱۰۰۰</div>
                <div class="stat-label" data-i18n="landing.activeUsers">کاربر فعال</div>
            </div>
            <div class="stat-item" data-reveal>
                <div class="stat-number" data-stat="ticketsAnswered">+۵۰۰۰</div>
                <div class="stat-label" data-i18n="landing.ticketsAnswered">تیکت پاسخ داده شده</div>
            </div>
            <div class="stat-item" data-reveal>
                <div class="stat-number" data-stat="satisfaction">99%</div>
                <div class="stat-label" data-i18n="landing.satisfaction">رضایت کاربران</div>
            </div>
            <div class="stat-item" data-reveal>
                <div class="stat-number" data-stat="support247">24/7</div>
                <div class="stat-label" data-i18n="landing.support247">پشتیبانی آنلاین</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="footer-glow" aria-hidden="true"></div>
        
        <div class="footer-content">
            <div class="footer-main" data-reveal>
                <div class="footer-brand">
                    <div class="footer-brand-icon">
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M0,60 Q25,50 50,60 T100,60 L100,100 L0,100 Z" fill="rgba(255,255,255,0.3)"/>
                            <path d="M0,70 Q25,60 50,70 T100,70 L100,100 L0,100 Z" fill="rgba(255,255,255,0.2)"/>
                            <path d="M30,50 Q40,40 50,50 Q60,40 70,50 L70,100 L30,100 Z" fill="rgba(255,255,255,0.4)"/>
                            <circle cx="50" cy="35" r="12" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <h3 data-i18n="landing.brandName">پنل پشتیبانی مناطق آزاد تجاری</h3>
                </div>
                <p class="footer-description" data-i18n="landing.footerDesc">
                    راه‌حل جامع و پیشرفته برای مدیریت ارتباط با کاربران و ارائه خدمات پشتیبانی حرفه‌ای با استفاده از تکنولوژی‌های روز دنیا
                </p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} <span data-i18n="landing.copyright">پنل پشتیبانی مناطق آزاد تجاری. تمام حقوق محفوظ است.</span></p>
        </div>
    </footer>

    <script>
        // ============================================
        // MOTION SYSTEM - Inline Implementation
        // ============================================
        
        (function() {
            'use strict';
            
            // Configuration
            const MOTION = {
                duration: {
                    fast: 200,
                    normal: 400,
                    slow: 600,
                    slower: 800,
                },
                easing: {
                    smooth: 'cubic-bezier(0.16, 1, 0.3, 1)',
                    spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                    expo: 'cubic-bezier(0.19, 1, 0.22, 1)',
                },
                stagger: 100,
            };
            
            // Check for reduced motion preference
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            // ============================================
            // NAVBAR SCROLL BEHAVIOR
            // ============================================
            const header = document.getElementById('header');
            let lastScrollY = window.scrollY;
            let ticking = false;
            
            function updateNavbar() {
                const currentScrollY = window.scrollY;
                
                // Add/remove scrolled class
                if (currentScrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                // Hide/show on scroll direction (optional - disabled for now)
                // if (currentScrollY > lastScrollY && currentScrollY > 200) {
                //     header.classList.add('hidden');
                // } else {
                //     header.classList.remove('hidden');
                // }
                
                lastScrollY = currentScrollY;
                ticking = false;
            }
            
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    requestAnimationFrame(updateNavbar);
                    ticking = true;
                }
            }, { passive: true });
            
            // ============================================
            // HERO IMAGE SLIDER
            // ============================================
            const heroSlides = document.querySelectorAll('.hero-slide');
            let currentSlide = 0;
            const slideInterval = 7000; // 7 seconds
            
            function nextSlide() {
                if (heroSlides.length <= 1) return;
                
                heroSlides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % heroSlides.length;
                heroSlides[currentSlide].classList.add('active');
            }
            
            if (heroSlides.length > 1 && !prefersReducedMotion) {
                setInterval(nextSlide, slideInterval);
            }
            
            // ============================================
            // SCROLL REVEAL ANIMATIONS
            // ============================================
            const revealElements = document.querySelectorAll('[data-reveal]');
            
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        const staggerIndex = Array.from(revealElements).indexOf(element);
                        const delay = prefersReducedMotion ? 0 : Math.min(staggerIndex, 6) * MOTION.stagger;
                        
                        setTimeout(() => {
                            element.classList.add('revealed');
                        }, delay);
                        
                        revealObserver.unobserve(element);
                    }
                });
            }, {
                threshold: 0.15,
                rootMargin: '0px 0px -60px 0px'
            });
            
            revealElements.forEach(el => revealObserver.observe(el));
            
            // ============================================
            // BUTTON RIPPLE EFFECT
            // ============================================
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (prefersReducedMotion) return;
                    
                    const rect = this.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    ripple.className = 'btn-ripple';
                    ripple.style.left = (e.clientX - rect.left) + 'px';
                    ripple.style.top = (e.clientY - rect.top) + 'px';
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });
            
            // ============================================
            // FEATURE CARD TILT EFFECT
            // ============================================
            if (!prefersReducedMotion) {
                document.querySelectorAll('.feature-card').forEach(card => {
                    const maxTilt = 8;
                    
                    card.addEventListener('mousemove', function(e) {
                        const rect = this.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        
                        const centerX = rect.width / 2;
                        const centerY = rect.height / 2;
                        
                        const rotateX = ((y - centerY) / centerY) * -maxTilt;
                        const rotateY = ((x - centerX) / centerX) * maxTilt;
                        
                        this.style.transform = `
                            perspective(1000px)
                            translateY(-8px)
                            rotateX(${rotateX}deg)
                            rotateY(${rotateY}deg)
                        `;
                    });
                    
                    card.addEventListener('mouseleave', function() {
                        this.style.transform = '';
                    });
                });
            }
            
            // ============================================
            // SMOOTH SCROLL FOR ANCHOR LINKS
            // ============================================
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: prefersReducedMotion ? 'auto' : 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // ============================================
            // PARALLAX FOR GRADIENT ORBS
            // ============================================
            if (!prefersReducedMotion) {
                const orbs = document.querySelectorAll('.gradient-orb');
                
                window.addEventListener('scroll', function() {
                    const scrollY = window.scrollY;
                    orbs.forEach((orb, index) => {
                        const speed = 0.1 + (index * 0.05);
                        orb.style.transform = `translateY(${scrollY * speed}px)`;
                    });
                }, { passive: true });
            }
            
            // ============================================
            // STATS COUNTER ANIMATION
            // ============================================
            const statNumberElements = document.querySelectorAll('.stat-number');
            
            const statsObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !prefersReducedMotion) {
                        // Stats are already displayed with Persian numerals
                        // Just trigger the reveal animation
                        statsObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            statNumberElements.forEach(stat => statsObserver.observe(stat));
            
            // ============================================
            // KEYBOARD NAVIGATION ENHANCEMENT
            // ============================================
            document.querySelectorAll('.feature-card').forEach(card => {
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        // Could add modal or expand functionality here
                    }
                });
            });
            
            // Log initialization
            console.log('🎬 Landing page motion system initialized');
            if (prefersReducedMotion) {
                console.log('⚡ Reduced motion mode active');
            }
            
            // ============================================
            // I18N LANGUAGE SWITCHER
            // ============================================
            const RTL_LOCALES = ['fa', 'ar'];
            const STORAGE_KEY = 'app_language';
            
            // Stat numbers for each language
            const statTexts = {
                fa: { activeUsers: '+۱۰۰۰', ticketsAnswered: '+۵۰۰۰', satisfaction: '۹۹٪', support247: '۲۴/۷' },
                en: { activeUsers: '1000+', ticketsAnswered: '5000+', satisfaction: '99%', support247: '24/7' },
                ar: { activeUsers: '١٠٠٠+', ticketsAnswered: '٥٠٠٠+', satisfaction: '٩٩٪', support247: '٢٤/٧' }
            };
            
            const translations = {
                fa: {
                    'nav.login': 'ورود',
                    'nav.register': 'ثبت‌نام',
                    'landing.goToDashboard': 'ورود به داشبورد',
                    'landing.heroTitle': 'سیستم مدیریت پشتیبانی مناطق آزاد تجاری',
                    'landing.heroSubtitle': 'پلتفرم جامع و هوشمند برای مدیریت تیکت‌ها، گفت‌وگوها و ارتباط مؤثر با کاربران',
                    'landing.advancedPlatform': 'پلتفرم پیشرفته پشتیبانی',
                    'landing.getStarted': 'شروع کنید',
                    'landing.loginToAccount': 'ورود به حساب کاربری',
                    'landing.scrollDown': 'اسکرول کنید',
                    'landing.featuresTitle': 'ویژگی‌های پلتفرم',
                    'landing.featuresSubtitle': 'ابزارهای قدرتمند برای مدیریت بهتر پشتیبانی',
                    'landing.topFeatures': 'ویژگی‌های برتر',
                    'landing.smartChat': 'گفت‌وگوی هوشمند',
                    'landing.smartChatDesc': 'سیستم چت پیشرفته با پشتیبانی از هوش مصنوعی برای پاسخگویی سریع و دقیق به کاربران',
                    'landing.ticketManagement': 'مدیریت تیکت‌ها',
                    'landing.ticketManagementDesc': 'سیستم کامل مدیریت تیکت‌های پشتیبانی با امکان پیگیری، اولویت‌بندی و پاسخگویی',
                    'landing.supportTeam': 'تیم پشتیبانی',
                    'landing.supportTeamDesc': 'مدیریت تیم‌های پشتیبانی با سیستم نقش‌ها و دسترسی‌های پیشرفته',
                    'landing.reporting': 'گزارش‌گیری',
                    'landing.reportingDesc': 'داشبورد تحلیلی برای بررسی عملکرد و آمار تیکت‌ها و گفت‌وگوها',
                    'landing.security': 'امنیت بالا',
                    'landing.securityDesc': 'سیستم امنیتی پیشرفته با احراز هویت چندمرحله‌ای و مدیریت دسترسی‌ها',
                    'landing.performance': 'عملکرد سریع',
                    'landing.performanceDesc': 'پلتفرم بهینه‌شده با سرعت بالا و تجربه کاربری روان و حرفه‌ای',
                    'landing.activeUsers': 'کاربر فعال',
                    'landing.ticketsAnswered': 'تیکت پاسخ داده شده',
                    'landing.satisfaction': 'رضایت کاربران',
                    'landing.support247': 'پشتیبانی آنلاین',
                    'landing.footerDesc': 'راه‌حل جامع و پیشرفته برای مدیریت ارتباط با کاربران و ارائه خدمات پشتیبانی حرفه‌ای با استفاده از تکنولوژی‌های روز دنیا',
                    'landing.copyright': 'پنل پشتیبانی مناطق آزاد تجاری. تمام حقوق محفوظ است.',
                    'landing.brandName': 'پنل پشتیبانی مناطق آزاد تجاری'
                },
                en: {
                    'nav.login': 'Login',
                    'nav.register': 'Register',
                    'landing.goToDashboard': 'Go to Dashboard',
                    'landing.heroTitle': 'Free Trade Zone Support Management System',
                    'landing.heroSubtitle': 'A comprehensive and intelligent platform for managing tickets, conversations, and effective user communication',
                    'landing.advancedPlatform': 'Advanced Support Platform',
                    'landing.getStarted': 'Get Started',
                    'landing.loginToAccount': 'Login to Account',
                    'landing.scrollDown': 'Scroll down',
                    'landing.featuresTitle': 'Platform Features',
                    'landing.featuresSubtitle': 'Powerful tools for better support management',
                    'landing.topFeatures': 'Top Features',
                    'landing.smartChat': 'Smart Chat',
                    'landing.smartChatDesc': 'Advanced chat system with AI support for quick and accurate responses to users',
                    'landing.ticketManagement': 'Ticket Management',
                    'landing.ticketManagementDesc': 'Complete ticket management system with tracking, prioritization, and response capabilities',
                    'landing.supportTeam': 'Support Team',
                    'landing.supportTeamDesc': 'Manage support teams with advanced roles and permissions system',
                    'landing.reporting': 'Reporting',
                    'landing.reportingDesc': 'Analytical dashboard for reviewing performance and statistics of tickets and conversations',
                    'landing.security': 'High Security',
                    'landing.securityDesc': 'Advanced security system with multi-factor authentication and access management',
                    'landing.performance': 'Fast Performance',
                    'landing.performanceDesc': 'Optimized platform with high speed and smooth, professional user experience',
                    'landing.activeUsers': 'Active Users',
                    'landing.ticketsAnswered': 'Tickets Answered',
                    'landing.satisfaction': 'User Satisfaction',
                    'landing.support247': '24/7 Support',
                    'landing.footerDesc': 'Comprehensive and advanced solution for user communication management and professional support services using cutting-edge technologies',
                    'landing.copyright': 'Free Trade Zone Support Panel. All rights reserved.',
                    'landing.brandName': 'Free Trade Zone Support Panel'
                },
                ar: {
                    'nav.login': 'تسجيل الدخول',
                    'nav.register': 'إنشاء حساب',
                    'landing.goToDashboard': 'الذهاب للوحة التحكم',
                    'landing.heroTitle': 'نظام إدارة دعم المنطقة التجارية الحرة',
                    'landing.heroSubtitle': 'منصة شاملة وذكية لإدارة التذاكر والمحادثات والتواصل الفعال مع المستخدمين',
                    'landing.advancedPlatform': 'منصة دعم متقدمة',
                    'landing.getStarted': 'ابدأ الآن',
                    'landing.loginToAccount': 'تسجيل الدخول',
                    'landing.scrollDown': 'مرر للأسفل',
                    'landing.featuresTitle': 'ميزات المنصة',
                    'landing.featuresSubtitle': 'أدوات قوية لإدارة دعم أفضل',
                    'landing.topFeatures': 'أفضل الميزات',
                    'landing.smartChat': 'المحادثة الذكية',
                    'landing.smartChatDesc': 'نظام محادثة متقدم بدعم الذكاء الاصطناعي للرد السريع والدقيق على المستخدمين',
                    'landing.ticketManagement': 'إدارة التذاكر',
                    'landing.ticketManagementDesc': 'نظام إدارة تذاكر كامل مع التتبع وتحديد الأولويات والرد',
                    'landing.supportTeam': 'فريق الدعم',
                    'landing.supportTeamDesc': 'إدارة فرق الدعم بنظام أدوار وصلاحيات متقدم',
                    'landing.reporting': 'التقارير',
                    'landing.reportingDesc': 'لوحة تحليلية لمراجعة الأداء وإحصائيات التذاكر والمحادثات',
                    'landing.security': 'أمان عالي',
                    'landing.securityDesc': 'نظام أمان متقدم مع مصادقة متعددة العوامل وإدارة الوصول',
                    'landing.performance': 'أداء سريع',
                    'landing.performanceDesc': 'منصة محسنة بسرعة عالية وتجربة مستخدم سلسة واحترافية',
                    'landing.activeUsers': 'مستخدم نشط',
                    'landing.ticketsAnswered': 'تذكرة تم الرد عليها',
                    'landing.satisfaction': 'رضا المستخدمين',
                    'landing.support247': 'دعم على مدار الساعة',
                    'landing.footerDesc': 'حل شامل ومتقدم لإدارة التواصل مع المستخدمين وتقديم خدمات الدعم الاحترافية باستخدام أحدث التقنيات',
                    'landing.copyright': 'لوحة دعم المنطقة التجارية الحرة. جميع الحقوق محفوظة.',
                    'landing.brandName': 'لوحة دعم المنطقة التجارية الحرة'
                }
            };
            
            function t(key, locale) {
                return translations[locale]?.[key] || translations['fa'][key] || key;
            }
            
            function applyTranslations(locale) {
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.dataset.i18n;
                    const text = t(key, locale);
                    // For elements with child spans, update the span
                    const span = el.querySelector('span');
                    if (span) {
                        span.textContent = text;
                    } else {
                        el.textContent = text;
                    }
                });
                
                // Update stat numbers
                document.querySelectorAll('[data-stat]').forEach(el => {
                    const key = el.dataset.stat;
                    if (statTexts[locale] && statTexts[locale][key]) {
                        el.textContent = statTexts[locale][key];
                    }
                });
            }
            
            function setLanguage(locale) {
                if (!['fa', 'en', 'ar'].includes(locale)) return;
                
                const dir = RTL_LOCALES.includes(locale) ? 'rtl' : 'ltr';
                
                // Update document
                document.documentElement.lang = locale;
                document.documentElement.dir = dir;
                document.documentElement.classList.remove('rtl', 'ltr');
                document.documentElement.classList.add(dir);
                
                // Persist
                localStorage.setItem(STORAGE_KEY, locale);
                
                // Apply translations
                applyTranslations(locale);
                
                // Emit event for other components
                window.dispatchEvent(new CustomEvent('localechange', { 
                    detail: { locale, direction: dir } 
                }));
            }
            
            // Initialize language pills
            const langPills = document.querySelectorAll('.lang-pill');
            if (langPills.length) {
                // Set initial value
                const stored = localStorage.getItem(STORAGE_KEY) || 'fa';
                
                // Update active state
                langPills.forEach(pill => {
                    pill.classList.toggle('active', pill.dataset.lang === stored);
                });
                
                // Apply initial translations
                applyTranslations(stored);
                
                // Handle clicks
                langPills.forEach(pill => {
                    pill.addEventListener('click', function() {
                        const lang = this.dataset.lang;
                        
                        // Update active state
                        langPills.forEach(p => p.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Set language
                        setLanguage(lang);
                    });
                });
            }
            
            console.log('🌐 i18n language system initialized');
            
        })();
    </script>
</body>
</html>
