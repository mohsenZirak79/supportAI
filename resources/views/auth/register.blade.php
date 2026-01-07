<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <title data-i18n="auth.registerTitle">ثبت‌نام کاربر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>

    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/user.css', 'resources/js/app.js'])

    <script>
        // Initialize direction from localStorage before page renders
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
        :root {
            --color-primary: #0e7490;
            --color-primary-light: #0891b2;
            --color-primary-lighter: #22d3ee;
            --color-primary-dark: #0c5c72;
            --font-family-rtl: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif;
            --font-family-ltr: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html[dir="rtl"] body { font-family: var(--font-family-rtl); }
        html[dir="ltr"] body { font-family: var(--font-family-ltr); }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-gradient-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }

        .bg-gradient-orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.4) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        .bg-gradient-orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(8, 145, 178, 0.3) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation-delay: -7s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.05); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(10px, 30px) scale(1.02); }
        }

        /* Language Switcher */
        .lang-switcher {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            display: flex;
            gap: 0.5rem;
            z-index: 100;
        }

        html[dir="ltr"] .lang-switcher {
            right: auto;
            left: 1.5rem;
        }

        .lang-btn {
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .lang-btn.active {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }

        /* Card */
        .register-card {
            position: relative;
            width: 100%;
            max-width: 600px;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: cardEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
            margin: 4rem 0;
        }

        @keyframes cardEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-primary-lighter), var(--color-primary));
            border-radius: 24px 24px 0 0;
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-lighter));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(14, 116, 144, 0.3);
        }

        .logo-icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        .register-title {
            color: white;
            font-size: 1.375rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .register-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Form */
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0.4rem;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder, .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-input:focus, .form-textarea:focus {
            border-color: var(--color-primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.2);
        }

        .form-textarea {
            min-height: 80px;
            resize: vertical;
        }

        .captcha-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .captcha-image img {
            height: 44px;
            border-radius: 8px;
        }

        .captcha-refresh {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.9);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .captcha-refresh svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .captcha-refresh:hover {
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(255, 255, 255, 0.24);
            transform: translateY(-1px);
        }

        html[dir="ltr"] .form-input,
        html[dir="ltr"] .form-textarea {
            direction: ltr;
        }

        html[dir="rtl"] .form-input,
        html[dir="rtl"] .form-textarea {
            direction: rtl;
        }

        html[dir="rtl"] .form-input[type="email"],
        html[dir="rtl"] .form-input[type="tel"],
        html[dir="rtl"] .form-input[name="phone"],
        html[dir="rtl"] .form-input[name="national_id"],
        html[dir="rtl"] .form-input[name="postal_code"] {
            direction: ltr;
            text-align: right;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(14, 116, 144, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Error Message */
        .error-alert {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .error-alert ul {
            list-style: none;
            color: #f87171;
            font-size: 0.875rem;
        }

        .error-alert li {
            margin-bottom: 0.25rem;
        }

        .error-alert li:last-child {
            margin-bottom: 0;
        }

        /* Footer */
        .card-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-footer p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }

        .card-footer a {
            color: var(--color-primary-lighter);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .card-footer a:hover {
            color: white;
        }

        /* Date picker overrides */
        .jdp-container {
            direction: rtl;
        }

        /* ============================================
           RESPONSIVE STYLES
           ============================================ */
        /* Tablet */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
                overflow-y: auto;
            }

            .register-card {
                padding: 2rem;
                margin: 3rem 0 2rem;
                max-width: 100%;
            }

            .lang-switcher {
                position: fixed;
                top: 0.75rem;
                right: 0.75rem;
                gap: 0.25rem;
            }

            html[dir="ltr"] .lang-switcher {
                left: 0.75rem;
            }

            .lang-btn {
                padding: 0.4rem 0.65rem;
                font-size: 0.75rem;
            }

            .bg-gradient-orb-1 {
                width: 350px;
                height: 350px;
            }

            .bg-gradient-orb-2 {
                width: 280px;
                height: 280px;
            }
        }

        /* Mobile */
        @media (max-width: 576px) {
            body {
                padding: 0.75rem;
                align-items: flex-start;
                padding-top: 3rem;
            }

            .register-card {
                padding: 1.5rem;
                border-radius: 18px;
                margin-top: 1rem;
                margin-bottom: 1.5rem;
            }

            .logo-container {
                margin-bottom: 1.5rem;
            }

            .logo-icon {
                width: 48px;
                height: 48px;
                margin-bottom: 0.75rem;
            }

            .logo-icon svg {
                width: 26px;
                height: 26px;
            }

            .register-title {
                font-size: 1.2rem;
            }

            .register-subtitle {
                font-size: 0.85rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-group {
                margin-bottom: 0.875rem;
            }

            .form-label {
                font-size: 0.8rem;
                margin-bottom: 0.35rem;
            }

            .form-input, .form-textarea {
                padding: 0.7rem 0.875rem;
                font-size: 0.95rem;
                border-radius: 10px;
            }

            .form-textarea {
                min-height: 70px;
            }

            .submit-btn {
                padding: 0.875rem;
                font-size: 0.95rem;
                margin-top: 0.5rem;
            }

            .card-footer {
                margin-top: 1.25rem;
                padding-top: 1.25rem;
            }

            .card-footer p {
                font-size: 0.85rem;
            }

            .bg-gradient-orb-1 {
                width: 250px;
                height: 250px;
                top: -80px;
                right: -80px;
            }

            .bg-gradient-orb-2 {
                width: 200px;
                height: 200px;
                bottom: -60px;
                left: -60px;
            }
        }

        /* Small mobile */
        @media (max-width: 400px) {
            body {
                padding: 0.5rem;
            }

            .lang-switcher {
                top: 0.5rem;
                right: 0.5rem;
                gap: 0.2rem;
            }

            html[dir="ltr"] .lang-switcher {
                left: 0.5rem;
            }

            .lang-btn {
                padding: 0.3rem 0.5rem;
                font-size: 0.7rem;
            }

            .register-card {
                padding: 1.25rem;
                border-radius: 14px;
            }

            .logo-icon {
                width: 42px;
                height: 42px;
            }

            .register-title {
                font-size: 1.1rem;
            }

            .register-subtitle {
                font-size: 0.8rem;
            }

            .form-label {
                font-size: 0.75rem;
            }

            .form-input, .form-textarea {
                padding: 0.6rem 0.75rem;
                font-size: 0.9rem;
            }

            .submit-btn {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Very small */
        @media (max-width: 320px) {
            .register-card {
                padding: 1rem;
            }

            .register-title {
                font-size: 1rem;
            }

            .lang-btn {
                padding: 0.25rem 0.4rem;
                font-size: 0.65rem;
            }
        }
    </style>
</head>

<body>
    <!-- Background Orbs -->
    <div class="bg-gradient-orb bg-gradient-orb-1"></div>
    <div class="bg-gradient-orb bg-gradient-orb-2"></div>

    <!-- Language Switcher -->
    <div class="lang-switcher">
        <button class="lang-btn" data-lang="fa">فارسی</button>
        <button class="lang-btn" data-lang="en">English</button>
        <button class="lang-btn" data-lang="ar">العربية</button>
</div>

    <!-- Register Card -->
    <div class="register-card">
        <!-- Logo -->
        <div class="logo-container">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <h1 class="register-title" data-i18n="auth.welcome">خوش آمدید</h1>
            <p class="register-subtitle" data-i18n="auth.enterInfo">لطفا اطلاعات خود را وارد کنید.</p>
                            </div>

                            @if ($errors->any())
            <div class="error-alert" id="errorAlert">
                <ul>
                                        @foreach ($errors->all() as $error)
                                            <li data-error-text="{{ $error }}">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                                <form id="registerForm" method="POST" action="{{ route('register') }}">
                                    @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.firstName">نام</label>
                    <input type="text" class="form-input" name="name"
                           data-i18n-placeholder="auth.firstName" placeholder="نام" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.lastName">نام خانوادگی</label>
                    <input type="text" class="form-input" name="family"
                           data-i18n-placeholder="auth.lastName" placeholder="نام خانوادگی" value="{{ old('family') }}" required>
                                    </div>
                                    </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.email">ایمیل</label>
                    <input type="email" class="form-input" name="email"
                           data-i18n-placeholder="auth.emailPlaceholder" placeholder="ایمیل" value="{{ old('email') }}" required>
                                    </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.phone">شماره تلفن</label>
                    <input type="tel" class="form-input" name="phone"
                           data-i18n-placeholder="auth.phonePlaceholder" placeholder="شماره تلفن (...09)" value="{{ old('phone') }}" required>
                                    </div>
                                    </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.password">رمز عبور</label>
                    <input type="password" class="form-input" name="password"
                           data-i18n-placeholder="auth.password" placeholder="رمز عبور" required>
                                        </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.confirmPassword">تکرار رمز عبور</label>
                    <input type="password" class="form-input" name="password_confirmation"
                           data-i18n-placeholder="auth.confirmPassword" placeholder="تکرار رمز عبور" required>
                                        </div>
                                    </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.nationalId">کد ملی</label>
                    <input type="text" class="form-input" name="national_id"
                           data-i18n-placeholder="auth.nationalId" placeholder="کد ملی" value="{{ old('national_id') }}">
                                    </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.postalCode">کد پستی</label>
                    <input type="text" class="form-input" name="postal_code"
                           data-i18n-placeholder="auth.postalCode" placeholder="کد پستی" value="{{ old('postal_code') }}">
                                    </div>
                                    </div>

            <div class="form-group">
                <label class="form-label" data-i18n="auth.birthDate">تاریخ تولد (شمسی)</label>
                <input id="birth_date" name="birth_date" type="text" class="form-input"
                       data-i18n-placeholder="auth.birthDatePlaceholder" placeholder="مثلاً 1404/07/18"
                       value="{{ old('birth_date') }}"
                       autocomplete="off" dir="ltr"
                       data-jdp data-jdp-only-date="true"
                       data-jdp-config='{"selector":"#birth_date","dateFormat":"YYYY/MM/DD","autoShow":true}'>
                                    </div>

            <div class="form-group">
                <label class="form-label" data-i18n="auth.address">آدرس</label>
                <textarea class="form-input form-textarea" name="address"
                          data-i18n-placeholder="auth.address" placeholder="آدرس">{{ old('address') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" data-i18n="auth.captcha">کد کپچا</label>
                <div class="captcha-row">
                    @if (function_exists('captcha_img'))
                        <div class="captcha-image">{!! captcha_img() !!}</div>
                        <button type="button" class="captcha-refresh" aria-label="Refresh captcha">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M21 12a9 9 0 1 1-2.64-6.36"/>
                                <path d="M21 3v6h-6"/>
                            </svg>
                        </button>
                    @endif
                </div>
                <input type="text" class="form-input" name="captcha"
                       data-i18n-placeholder="auth.captchaPlaceholder" placeholder="کد کپچا"
                       value="{{ old('captcha') }}" required>
            </div>

            <button type="submit" class="submit-btn" data-i18n="nav.register">
                                        ثبت‌نام
                                    </button>
                                </form>

        <!-- Footer -->
        <div class="card-footer">
            <p>
                <span data-i18n="auth.hasAccount">قبلاً حساب کاربری دارید؟</span>
                <a href="{{ route('login') }}" data-i18n="nav.login">ورود</a>
            </p>
                        </div>
                    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // TRANSLATIONS
    // ==========================================
    const translations = {
        fa: {
            'auth.welcome': 'خوش آمدید',
            'auth.registerTitle': 'ثبت‌نام کاربر',
            'auth.enterInfo': 'لطفا اطلاعات خود را وارد کنید.',
            'auth.firstName': 'نام',
            'auth.lastName': 'نام خانوادگی',
            'auth.email': 'ایمیل',
            'auth.emailPlaceholder': 'ایمیل',
            'auth.phone': 'شماره تلفن',
            'auth.phonePlaceholder': 'شماره تلفن (...09)',
            'auth.password': 'رمز عبور',
            'auth.confirmPassword': 'تکرار رمز عبور',
            'auth.nationalId': 'کد ملی',
            'auth.postalCode': 'کد پستی',
            'auth.birthDate': 'تاریخ تولد (شمسی)',
            'auth.birthDatePlaceholder': 'مثلاً 1404/07/18',
            'auth.address': 'آدرس',
            'auth.captcha': 'کد کپچا',
            'auth.captchaPlaceholder': 'کد کپچا',
            'auth.hasAccount': 'قبلاً حساب کاربری دارید؟',
            'nav.register': 'ثبت‌نام',
            'nav.login': 'ورود'
        },
        en: {
            'auth.welcome': 'Welcome',
            'auth.registerTitle': 'Create Account',
            'auth.enterInfo': 'Please enter your information.',
            'auth.firstName': 'First Name',
            'auth.lastName': 'Last Name',
            'auth.email': 'Email',
            'auth.emailPlaceholder': 'Email',
            'auth.phone': 'Phone Number',
            'auth.phonePlaceholder': 'Phone number (09...)',
            'auth.password': 'Password',
            'auth.confirmPassword': 'Confirm Password',
            'auth.nationalId': 'National ID',
            'auth.postalCode': 'Postal Code',
            'auth.birthDate': 'Birth Date (Solar)',
            'auth.birthDatePlaceholder': 'e.g. 1404/07/18',
            'auth.address': 'Address',
            'auth.captcha': 'Captcha Code',
            'auth.captchaPlaceholder': 'Enter captcha',
            'auth.hasAccount': 'Already have an account?',
            'nav.register': 'Register',
            'nav.login': 'Login'
        },
        ar: {
            'auth.welcome': 'مرحباً بك',
            'auth.registerTitle': 'إنشاء حساب',
            'auth.enterInfo': 'يرجى إدخال معلوماتك.',
            'auth.firstName': 'الاسم الأول',
            'auth.lastName': 'اسم العائلة',
            'auth.email': 'البريد الإلكتروني',
            'auth.emailPlaceholder': 'البريد الإلكتروني',
            'auth.phone': 'رقم الهاتف',
            'auth.phonePlaceholder': 'رقم الهاتف (09...)',
            'auth.password': 'كلمة المرور',
            'auth.confirmPassword': 'تأكيد كلمة المرور',
            'auth.nationalId': 'الرقم الوطني',
            'auth.postalCode': 'الرمز البريدي',
            'auth.birthDate': 'تاريخ الميلاد (شمسي)',
            'auth.birthDatePlaceholder': 'مثال: 1404/07/18',
            'auth.address': 'العنوان',
            'auth.captcha': 'رمز التحقق',
            'auth.captchaPlaceholder': 'أدخل رمز التحقق',
            'auth.hasAccount': 'لديك حساب بالفعل؟',
            'nav.register': 'إنشاء حساب',
            'nav.login': 'تسجيل الدخول'
        }
    };

    // Error message translations (Persian -> other languages)
    const errorTranslations = {
        fa: {}, // Keep original Persian
        en: {
            'فیلد نام الزامی است': 'The name field is required.',
            'فیلد نام خانوادگی الزامی است': 'The last name field is required.',
            'فیلد ایمیل الزامی است': 'The email field is required.',
            'ایمیل باید یک آدرس ایمیل معتبر باشد': 'The email must be a valid email address.',
            'ایمیل قبلا ثبت شده است': 'The email has already been taken.',
            'فیلد شماره تلفن الزامی است': 'The phone number field is required.',
            'شماره تلفن قبلا ثبت شده است': 'The phone number has already been taken.',
            'شماره تلفن باید ۱۱ رقم باشد': 'The phone number must be 11 digits.',
            'فرمت شماره تلفن نادرست است': 'The phone number format is invalid.',
            'فیلد رمز عبور الزامی است': 'The password field is required.',
            'رمز عبور باید حداقل ۶ کاراکتر باشد': 'The password must be at least 6 characters.',
            'رمز عبور و تکرار آن مطابقت ندارند': 'The password confirmation does not match.',
            'کد ملی الزامی است': 'The national ID is required.',
            'کد ملی باید ۱۰ رقم باشد': 'The national ID must be 10 digits.',
            'کد پستی الزامی است': 'The postal code is required.',
            'کد پستی باید ۱۰ رقم باشد': 'The postal code must be 10 digits.',
            'تاریخ تولد الزامی است': 'The birth date is required.',
            'وارد کردن آدرس الزامی است': 'The address is required.',
            'آدرس الزامی است': 'The address is required.',
        },
        ar: {
            'فیلد نام الزامی است': 'حقل الاسم مطلوب.',
            'فیلد نام خانوادگی الزامی است': 'حقل اسم العائلة مطلوب.',
            'فیلد ایمیل الزامی است': 'حقل البريد الإلكتروني مطلوب.',
            'ایمیل باید یک آدرس ایمیل معتبر باشد': 'يجب أن يكون البريد الإلكتروني صالحاً.',
            'ایمیل قبلا ثبت شده است': 'البريد الإلكتروني مستخدم بالفعل.',
            'فیلد شماره تلفن الزامی است': 'حقل رقم الهاتف مطلوب.',
            'شماره تلفن قبلا ثبت شده است': 'رقم الهاتف مستخدم بالفعل.',
            'شماره تلفن باید ۱۱ رقم باشد': 'يجب أن يكون رقم الهاتف ۱۱ رقماً.',
            'فرمت شماره تلفن نادرست است': 'تنسيق رقم الهاتف غير صالح.',
            'فیلد رمز عبور الزامی است': 'حقل كلمة المرور مطلوب.',
            'رمز عبور باید حداقل ۶ کاراکتر باشد': 'يجب أن تكون كلمة المرور ۶ أحرف على الأقل.',
            'رمز عبور و تکرار آن مطابقت ندارند': 'تأكيد كلمة المرور غير متطابق.',
            'کد ملی الزامی است': 'الرقم الوطني مطلوب.',
            'کد ملی باید ۱۰ رقم باشد': 'يجب أن يكون الرقم الوطني ۱۰ أرقام.',
            'کد پستی الزامی است': 'الرمز البريدي مطلوب.',
            'کد پستی باید ۱۰ رقم باشد': 'يجب أن يكون الرمز البريدي ۱۰ أرقام.',
            'تاریخ تولد الزامی است': 'تاريخ الميلاد مطلوب.',
            'وارد کردن آدرس الزامی است': 'العنوان مطلوب.',
            'آدرس الزامی است': 'العنوان مطلوب.',
        }
    };

    const RTL_LOCALES = ['fa', 'ar'];

    function getStoredLocale() {
        return localStorage.getItem('app_language') || 'fa';
    }

    function setLocale(locale) {
        if (!translations[locale]) return;

        localStorage.setItem('app_language', locale);

        // Update direction
        const dir = RTL_LOCALES.includes(locale) ? 'rtl' : 'ltr';
        document.documentElement.lang = locale;
        document.documentElement.dir = dir;
        document.documentElement.classList.remove('rtl', 'ltr');
        document.documentElement.classList.add(dir);

        // Update all translatable elements
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (translations[locale][key]) {
                el.textContent = translations[locale][key];
            }
        });

        // Update placeholders
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (translations[locale][key]) {
                el.placeholder = translations[locale][key];
            }
        });

        // Update title
        if (translations[locale]['auth.registerTitle']) {
            document.title = translations[locale]['auth.registerTitle'];
        }

        // Update active button
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === locale);
        });

        // Translate error messages
        translateErrors(locale);
    }

    function translateErrors(locale) {
        document.querySelectorAll('[data-error-text]').forEach(el => {
            const originalText = el.dataset.errorText;
            if (locale === 'fa') {
                el.textContent = originalText;
            } else {
                // Try to find a translation
                const errMap = errorTranslations[locale] || {};
                let translated = originalText;
                for (const [key, value] of Object.entries(errMap)) {
                    if (originalText.includes(key)) {
                        translated = value;
                        break;
                    }
                }
                el.textContent = translated;
            }
        });
    }

    function refreshCaptcha(container = document) {
        const img = container.querySelector('.captcha-image img');
        if (!img) return;
        const baseSrc = img.getAttribute('src').split('?')[0];
        img.setAttribute('src', `${baseSrc}?${Date.now()}`);
    }

    document.querySelectorAll('.captcha-refresh').forEach((btn) => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.captcha-row') || document;
            refreshCaptcha(row);
        });
    });

    // Initialize language
    const currentLocale = getStoredLocale();
    setLocale(currentLocale);

    // Language button click handlers
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            setLocale(btn.dataset.lang);
        });
    });

    // Initialize date picker
    if (typeof jalaliDatepicker !== 'undefined') {
        jalaliDatepicker.startWatch();
        }
    });
</script>
</body>
</html>
