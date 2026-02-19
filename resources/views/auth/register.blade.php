<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
            min-height: 100dvh;
            min-height: -webkit-fill-available;
            background: #f8fafc;
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .lang-switcher {
            position: fixed;
            top: max(1rem, env(safe-area-inset-top));
            right: max(1rem, env(safe-area-inset-right));
            left: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            z-index: 100;
            max-width: calc(100vw - 2rem);
            justify-content: flex-end;
        }
        html[dir="ltr"] .lang-switcher { right: auto; left: max(1rem, env(safe-area-inset-left)); }

        .lang-btn {
            padding: 0.4rem 0.75rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .lang-btn:hover { background: #f1f5f9; color: #0e7490; border-color: #cbd5e1; }
        .lang-btn.active { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }

        .register-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: max(4rem, calc(2rem + env(safe-area-inset-top)));
            padding-left: max(2rem, env(safe-area-inset-left));
            padding-right: max(2rem, env(safe-area-inset-right));
            padding-bottom: max(2rem, env(safe-area-inset-bottom));
        }
        .register-form-wrap { order: 1; min-width: 0; }
        html[dir="rtl"] .register-form-wrap { order: 2; }
        .register-visual {
            order: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        html[dir="rtl"] .register-visual { order: 1; }
        .register-visual img {
            width: 100%;
            max-width: 420px;
            height: auto;
            object-fit: contain;
        }

        .register-card {
            width: 100%;
            max-width: 520px;
            margin-inline-start: 0;
            margin-inline-end: auto;
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
            overflow-wrap: break-word;
        }
        html[dir="rtl"] .register-card { margin-inline-start: auto; margin-inline-end: 0; }

        .logo-container { text-align: center; margin-bottom: 1.5rem; }
        .logo-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            background: var(--color-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-icon svg { width: 26px; height: 26px; fill: #fff; }
        .register-title { color: #0f172a; font-size: 1.35rem; font-weight: 700; margin-bottom: 0.35rem; }
        .register-subtitle { color: #64748b; font-size: 0.9rem; }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            min-width: 0;
        }
        .form-group { margin-bottom: 1rem; min-width: 0; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-label { display: block; color: #334155; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.4rem; }
        .form-input, .form-textarea {
            width: 100%; max-width: 100%; padding: 0.75rem 1rem;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            color: #0f172a; font-size: 0.95rem; transition: border-color 0.2s, box-shadow 0.2s;
            outline: none; box-sizing: border-box;
        }
        .form-input::placeholder, .form-textarea::placeholder { color: #94a3b8; }
        .form-input:focus, .form-textarea:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.12);
        }
        .form-textarea { min-height: 80px; resize: vertical; }
        html[dir="ltr"] .form-input, html[dir="ltr"] .form-textarea { direction: ltr; }
        html[dir="rtl"] .form-input, html[dir="rtl"] .form-textarea { direction: rtl; }
        html[dir="rtl"] .form-input[type="email"],
        html[dir="rtl"] .form-input[type="tel"],
        html[dir="rtl"] .form-input[name="phone"],
        html[dir="rtl"] .form-input[name="national_id"],
        html[dir="rtl"] .form-input[name="postal_code"] { direction: ltr; text-align: right; }
        .submit-btn {
            width: 100%; padding: 0.875rem; background: var(--color-primary); border: none;
            border-radius: 10px; color: #fff; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s, transform 0.15s; margin-top: 0.5rem;
        }
        .submit-btn:hover { background: var(--color-primary-light); transform: translateY(-1px); }
        .submit-btn:active { transform: translateY(0); }
        .error-alert {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px;
            padding: 1rem; margin-bottom: 1.5rem; max-width: 100%; overflow-wrap: break-word;
        }
        .error-alert ul { list-style: none; color: #dc2626; font-size: 0.875rem; }
        .error-alert li { margin-bottom: 0.25rem; }
        .error-alert li:last-child { margin-bottom: 0; }
        .card-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0; }
        .card-footer p { color: #64748b; font-size: 0.875rem; }
        .card-footer a { color: var(--color-primary); text-decoration: none; font-weight: 600; }
        .card-footer a:hover { text-decoration: underline; }
        .jdp-container { direction: rtl; }

        @media (max-width: 900px) {
            .register-page { grid-template-columns: 1fr; padding: 1.5rem; padding-top: 3.5rem; }
            .register-visual { order: 0; }
            .register-visual img { max-width: 280px; margin: 0 auto; }
            .register-form-wrap { order: 1; }
            html[dir="rtl"] .register-visual { order: 0; }
            html[dir="rtl"] .register-form-wrap { order: 1; }
            .register-card { max-width: 100%; margin-inline: 0; }
        }
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .register-page { padding: 1rem; padding-top: 3.5rem; }
            .register-card { padding: 1.5rem; border-radius: 14px; }
            .register-title { font-size: 1.2rem; }
            .register-subtitle { font-size: 0.85rem; }
            .register-visual img { max-width: 220px; }
        }
        @media (max-width: 400px) {
            .register-card { padding: 1.25rem; }
            .register-title { font-size: 1.1rem; }
        }
    </style>
</head>

<body>
    <div class="lang-switcher">
        <button class="lang-btn" data-lang="fa">فارسی</button>
        <button class="lang-btn" data-lang="en">English</button>
        <button class="lang-btn" data-lang="ar">العربية</button>
    </div>

    <div class="register-page">
        <div class="register-form-wrap">
            <div class="register-card">
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
                           data-i18n-placeholder="auth.firstName" placeholder="نام" required>
                </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.lastName">نام خانوادگی</label>
                    <input type="text" class="form-input" name="family"
                           data-i18n-placeholder="auth.lastName" placeholder="نام خانوادگی" required>
                                    </div>
                                    </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.email">ایمیل</label>
                    <input type="email" class="form-input" name="email"
                           data-i18n-placeholder="auth.emailPlaceholder" placeholder="ایمیل" required>
                                    </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.phone">شماره تلفن</label>
                    <input type="tel" class="form-input" name="phone"
                           data-i18n-placeholder="auth.phonePlaceholder" placeholder="شماره تلفن (...09)" required>
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
                           data-i18n-placeholder="auth.nationalId" placeholder="کد ملی">
                                    </div>
                <div class="form-group">
                    <label class="form-label" data-i18n="auth.postalCode">کد پستی</label>
                    <input type="text" class="form-input" name="postal_code"
                           data-i18n-placeholder="auth.postalCode" placeholder="کد پستی">
                                    </div>
                                    </div>

            <div class="form-group">
                <label class="form-label" data-i18n="auth.birthDate">تاریخ تولد (شمسی)</label>
                <input id="birth_date" name="birth_date" type="text" class="form-input"
                       data-i18n-placeholder="auth.birthDatePlaceholder" placeholder="مثلاً 1404/07/18"
                       autocomplete="off" dir="ltr"
                       data-jdp data-jdp-only-date="true"
                       data-jdp-config='{"selector":"#birth_date","dateFormat":"YYYY/MM/DD","autoShow":true}'>
                                    </div>

            <div class="form-group">
                <label class="form-label" data-i18n="auth.address">آدرس</label>
                <textarea class="form-input form-textarea" name="address"
                          data-i18n-placeholder="auth.address" placeholder="آدرس"></textarea>
            </div>

            <button type="submit" class="submit-btn" data-i18n="nav.register">
                                        ثبت‌نام
                                    </button>
                                </form>

                <div class="card-footer">
                    <p>
                        <span data-i18n="auth.hasAccount">قبلاً حساب کاربری دارید؟</span>
                        <a href="{{ route('login') }}" data-i18n="nav.login">ورود</a>
                    </p>
                </div>
            </div>
        </div>
        <div class="register-visual" aria-hidden="true">
            <img src="{{ asset('images/Artificial intelligence-amico.svg') }}" alt="">
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
