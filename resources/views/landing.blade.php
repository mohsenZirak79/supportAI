<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>پنل پشتیبانی کیش - سیستم مدیریت تیکت‌ها و گفت‌وگوها</title>
    
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
    <meta name="apple-mobile-web-app-title" content="پشتیبانی کیش">
    
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: #1f2937;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ============================================
           HEADER / NAVBAR
           ============================================ */
        .landing-header {
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
            padding: 0;
        }

        .landing-header.scrolled {
            padding: 0;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.6s ease-out;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
            border-radius: 0 0 20px 20px;
            transition: all 0.3s ease;
        }

        .landing-header.scrolled .navbar {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.98);
        }

        @keyframes slideDown {
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
            gap: 0.75rem;
            text-decoration: none;
            color: #0e7490;
            font-size: 1.5rem;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -4px;
            right: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #0e7490, #0891b2);
            transition: width 0.3s ease;
        }

        .navbar-brand:hover::after {
            width: 100%;
        }

        .navbar-brand:hover {
            transform: translateX(-3px);
        }

        .navbar-brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(14, 116, 144, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .navbar-brand-icon img,
        .navbar-brand-icon svg {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .navbar-brand:hover .navbar-brand-icon {
            transform: rotate(5deg) scale(1.05);
            box-shadow: 0 6px 20px rgba(14, 116, 144, 0.4);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-nav {
            padding: 0.75rem 1.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn-nav::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-nav:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-nav-outline {
            color: #0e7490;
            border-color: #0e7490;
            background: transparent;
        }

        .btn-nav-outline:hover {
            background: #0e7490;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 116, 144, 0.3);
        }

        .btn-nav-primary {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            color: white;
            border: none;
        }

        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14, 116, 144, 0.4);
        }

        /* ============================================
           HERO SECTION
           ============================================ */
        .hero {
            padding: 8rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

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
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Overlay برای خوانایی متن */
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(14, 116, 144, 0.7) 0%, rgba(8, 145, 178, 0.6) 100%);
            z-index: 1;
        }

        /* دایره‌های شناور برای افکت بصری */
        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 25s ease-in-out infinite reverse;
            z-index: 1;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }
            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-size: 4.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 4px 30px rgba(0, 0, 0, 0.4), 0 2px 10px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
            letter-spacing: -0.03em;
            line-height: 1.1;
            animation: fadeInUp 1s ease-out 0.2s both;
            position: relative;
        }

        .hero p {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.98);
            text-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 3.5rem;
            line-height: 1.9;
            animation: fadeInUp 1s ease-out 0.4s both;
            font-weight: 400;
        }

        .hero-cta {
            display: flex;
            gap: 1.25rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .btn-hero {
            padding: 1.125rem 2.75rem;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.125rem;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }

        .btn-hero::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-hero:hover::before {
            width: 400px;
            height: 400px;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(14, 116, 144, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-hero-primary:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 12px 35px rgba(14, 116, 144, 0.5);
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.98);
            color: #0e7490;
            border: 2px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            font-weight: 700;
        }

        .btn-hero-secondary:hover {
            background: white;
            color: #0891b2;
            border-color: white;
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features {
            padding: 10rem 2rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafb 50%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }

        .features::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 30s ease-in-out infinite;
        }

        .features::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(8, 145, 178, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 35s ease-in-out infinite reverse;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .section-title {
            text-align: center;
            margin-bottom: 6rem;
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .section-title.visible {
            animation-delay: 0.2s;
        }

        .section-title h2 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            letter-spacing: -0.03em;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #0e7490, #0891b2);
            border-radius: 2px;
        }

        .section-title p {
            font-size: 1.375rem;
            color: #6b7280;
            font-weight: 400;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 3rem;
        }

        .feature-card {
            background: white;
            border-radius: 28px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(229, 231, 235, 0.6);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(40px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.08) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #0e7490, #0891b2);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.6s ease;
        }

        .feature-card.visible {
            opacity: 1;
            transform: translateY(0);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .feature-card:hover {
            transform: translateY(-16px) scale(1.03);
            box-shadow: 0 25px 50px rgba(14, 116, 144, 0.2);
            border-color: rgba(8, 145, 178, 0.3);
        }

        .feature-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2.5rem;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(14, 116, 144, 0.25);
            position: relative;
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 28px;
            background: linear-gradient(135deg, #0e7490, #0891b2);
            opacity: 0;
            transition: opacity 0.6s ease;
            z-index: -1;
        }

        .feature-icon svg {
            width: 44px;
            height: 44px;
            stroke: white;
            fill: none;
            stroke-width: 2.5;
            transition: transform 0.6s ease;
        }

        .feature-card:hover .feature-icon {
            transform: rotate(12deg) scale(1.15);
            box-shadow: 0 15px 35px rgba(14, 116, 144, 0.35);
        }

        .feature-card:hover .feature-icon::before {
            opacity: 0.3;
        }

        .feature-card:hover .feature-icon svg {
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 1.25rem;
            transition: all 0.4s ease;
            position: relative;
        }

        .feature-card:hover h3 {
            color: #0891b2;
            transform: translateY(-2px);
        }

        .feature-card p {
            color: #6b7280;
            line-height: 2;
            font-size: 1.1rem;
            transition: color 0.4s ease;
        }

        .feature-card:hover p {
            color: #4b5563;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e5e7eb;
            padding: 5rem 2rem 3rem;
            margin-top: 8rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(14, 116, 144, 0.5), rgba(8, 145, 178, 0.5), transparent);
        }

        .footer::after {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 40s ease-in-out infinite;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .footer-brand {
            margin-bottom: 3rem;
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .footer-brand h3 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .footer-brand p {
            color: #94a3b8;
            font-size: 1.125rem;
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 3rem auto 0;
            padding-top: 3rem;
            border-top: 1px solid rgba(55, 65, 81, 0.6);
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .footer-bottom p {
            margin: 0;
            opacity: 0.8;
        }

        /* ============================================
           SCROLL ANIMATIONS
           ============================================ */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-wrap: wrap;
            }

            .navbar-actions {
                width: 100%;
                margin-top: 1rem;
                justify-content: flex-end;
            }

            .hero {
                padding: 5rem 1.5rem;
                min-height: 80vh;
            }

            .hero h1 {
                font-size: 2.75rem;
            }

            .hero p {
                font-size: 1.125rem;
            }

            .hero-cta {
                flex-direction: column;
            }

            .btn-hero {
                width: 100%;
                justify-content: center;
            }

            .features {
                padding: 5rem 1.5rem;
            }

            .section-title h2 {
                font-size: 2.25rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .feature-card {
                padding: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header / Navbar -->
    <header class="landing-header" id="header">
        <nav class="navbar">
            <a href="/" class="navbar-brand">
                <div class="navbar-brand-icon">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <!-- Wave pattern representing sea -->
                        <path d="M0,60 Q25,50 50,60 T100,60 L100,100 L0,100 Z" fill="rgba(255,255,255,0.3)"/>
                        <path d="M0,70 Q25,60 50,70 T100,70 L100,100 L0,100 Z" fill="rgba(255,255,255,0.2)"/>
                        <!-- Island shape -->
                        <path d="M30,50 Q40,40 50,50 Q60,40 70,50 L70,100 L30,100 Z" fill="rgba(255,255,255,0.4)"/>
                        <!-- Support symbol (chat bubble) -->
                        <circle cx="50" cy="35" r="12" fill="white" opacity="0.9"/>
                        <path d="M42,35 Q50,30 58,35 Q50,40 42,35" fill="white" opacity="0.9"/>
                    </svg>
                </div>
                <span>پنل پشتیبانی کیش</span>
            </a>
            <div class="navbar-actions">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn-nav btn-nav-primary">
                        ورود به داشبورد
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav btn-nav-outline">
                        ورود
                    </a>
                    <a href="{{ route('register') }}" class="btn-nav btn-nav-primary">
                        ثبت‌نام
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Background Image Slider -->
        <div class="hero-slider">
            <div class="hero-slide active" style="background-image: url('/images/1.jpg');"></div>
            <div class="hero-slide" style="background-image: url('/images/2.jpg');"></div>
            <div class="hero-slide" style="background-image: url('/images/3.jpg');"></div>
        </div>
        
        <div class="hero-content">
            <h1>سیستم مدیریت پشتیبانی کیش</h1>
            <p>پلتفرم جامع برای مدیریت تیکت‌ها، گفت‌وگوها و ارتباط با کاربران</p>
            <div class="hero-cta">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn-hero btn-hero-primary">
                        ورود به داشبورد
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-primary">
                        شروع کنید
                    </a>
                    <a href="{{ route('login') }}" class="btn-hero btn-hero-secondary">
                        ورود به حساب کاربری
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-container">
            <div class="section-title visible">
                <h2>ویژگی‌های پلتفرم</h2>
                <p>ابزارهای قدرتمند برای مدیریت بهتر پشتیبانی</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3>گفت‌وگوی هوشمند</h3>
                    <p>سیستم چت پیشرفته با پشتیبانی از هوش مصنوعی برای پاسخگویی سریع و دقیق به کاربران</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>مدیریت تیکت‌ها</h3>
                    <p>سیستم کامل مدیریت تیکت‌های پشتیبانی با امکان پیگیری، اولویت‌بندی و پاسخگویی</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h3>تیم پشتیبانی</h3>
                    <p>مدیریت تیم‌های پشتیبانی با سیستم نقش‌ها و دسترسی‌های پیشرفته</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                    </div>
                    <h3>گزارش‌گیری</h3>
                    <p>داشبورد تحلیلی برای بررسی عملکرد و آمار تیکت‌ها و گفت‌وگوها</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <h3>امنیت بالا</h3>
                    <p>سیستم امنیتی پیشرفته با احراز هویت چندمرحله‌ای و مدیریت دسترسی‌ها</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                    </div>
                    <h3>عملکرد سریع</h3>
                    <p>پلتفرم بهینه‌شده با سرعت بالا و تجربه کاربری روان و حرفه‌ای</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-brand">
                <h3>پنل پشتیبانی کیش</h3>
                <p>راه‌حل جامع و پیشرفته برای مدیریت ارتباط با کاربران و ارائه خدمات پشتیبانی حرفه‌ای با استفاده از تکنولوژی‌های روز دنیا</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} پنل پشتیبانی کیش. تمام حقوق محفوظ است.</p>
        </div>
    </footer>

    <script>
        // Header scroll effect
        let lastScroll = 0;
        const header = document.getElementById('header');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });

        // Hero Image Slider
        const heroSlides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;
        const slideInterval = 6000; // 6 seconds (slower)

        function nextSlide() {
            // Remove active class from current slide
            heroSlides[currentSlide].classList.remove('active');
            
            // Move to next slide
            currentSlide = (currentSlide + 1) % heroSlides.length;
            
            // Add active class to next slide
            heroSlides[currentSlide].classList.add('active');
        }

        // Start slider
        if (heroSlides.length > 0) {
            setInterval(nextSlide, slideInterval);
        }

        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe feature cards
        document.querySelectorAll('.feature-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            observer.observe(card);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
