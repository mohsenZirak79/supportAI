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

    <title data-i18n="auth.loginTitle">ورود کاربر</title>
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

        .login-page {
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
        .login-form-wrap { order: 1; min-width: 0; }
        html[dir="rtl"] .login-form-wrap { order: 2; }
        .login-visual {
            order: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        html[dir="rtl"] .login-visual { order: 1; }
        .login-visual img {
            width: 100%;
            max-width: 420px;
            height: auto;
            object-fit: contain;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            margin-inline-start: 0;
            margin-inline-end: auto;
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
            overflow-wrap: break-word;
        }
        html[dir="rtl"] .login-card { margin-inline-start: auto; margin-inline-end: 0; }

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
        .login-title { color: #0f172a; font-size: 1.35rem; font-weight: 700; margin-bottom: 0.35rem; }
        .login-subtitle { color: #64748b; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; color: #334155; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.4rem; }
        .form-input {
            width: 100%; max-width: 100%; padding: 0.75rem 1rem;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            color: #0f172a; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s;
            outline: none; box-sizing: border-box;
        }
        .form-input::placeholder { color: #94a3b8; }
        .form-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.12); }
        html[dir="ltr"] .form-input { direction: ltr; }
        html[dir="rtl"] .form-input { direction: rtl; }
        html[dir="rtl"] .form-input[type="tel"],
        html[dir="rtl"] .form-input[name="phone"],
        html[dir="rtl"] .form-input[name="otp"] { direction: ltr; text-align: right; }
        .submit-btn {
            width: 100%; padding: 0.875rem; background: var(--color-primary); border: none;
            border-radius: 10px; color: #fff; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s, transform 0.15s;
        }
        .submit-btn:hover { background: var(--color-primary-light); transform: translateY(-1px); }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .error-msg {
            color: #dc2626; font-size: 0.875rem; margin-top: 1rem; text-align: center;
            padding: 0.75rem; background: #fef2f2; border-radius: 8px; display: none;
        }
        .error-msg.show { display: block; }
        .card-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0; }
        .card-footer p { color: #64748b; font-size: 0.875rem; }
        .card-footer a { color: var(--color-primary); text-decoration: none; font-weight: 600; }
        .card-footer a:hover { text-decoration: underline; }
        .form-section { transition: opacity 0.3s ease; }
        .form-section.hidden { display: none; }
        .form-section.fade-out { opacity: 0; }
        .form-section.fade-in { opacity: 1; }
        .success-alert {
            position: fixed; top: max(2rem, env(safe-area-inset-top)); left: 50%;
            transform: translateX(-50%); background: #059669; color: #fff;
            padding: 1rem 1.25rem; margin: 0 1rem; max-width: calc(100vw - 2rem);
            border-radius: 10px; z-index: 1000;
        }
        @media (max-width: 900px) {
            .login-page { grid-template-columns: 1fr; padding: 1.5rem; padding-top: 3.5rem; }
            .login-visual { order: 0; }
            .login-visual img { max-width: 280px; }
            .login-form-wrap { order: 1; }
            html[dir="rtl"] .login-visual { order: 0; }
            html[dir="rtl"] .login-form-wrap { order: 1; }
            .login-card { max-width: 100%; margin-inline: 0; }
        }
        @media (max-width: 576px) {
            .login-page { padding: 1rem; padding-top: 3.5rem; }
            .login-card { padding: 1.5rem; border-radius: 14px; }
            .login-title { font-size: 1.2rem; }
            .login-subtitle { font-size: 0.85rem; }
            .login-visual img { max-width: 220px; }
        }
        @media (max-width: 400px) {
            .login-card { padding: 1.25rem; }
            .login-title { font-size: 1.1rem; }
        }
    </style>
</head>

<body>
    <div class="lang-switcher">
        <button class="lang-btn" data-lang="fa">فارسی</button>
        <button class="lang-btn" data-lang="en">English</button>
        <button class="lang-btn" data-lang="ar">العربية</button>
    </div>

    @if(session('success'))
        <div class="success-alert" id="successAlert">{{ session('success') }}</div>
    @endif

    <div class="login-page">
        <div class="login-form-wrap">
            <div class="login-card">
        <!-- Logo -->
        <div class="logo-container">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <h1 class="login-title" data-i18n="auth.welcome">خوش آمدید</h1>
            <p class="login-subtitle" data-i18n="auth.enterPhone">شماره تلفن همراه خود را وارد کنید.</p>
                            </div>

        <!-- Phone Form -->
        <form id="loginForm" class="form-section">
                                    @csrf
            <div class="form-group">
                <label class="form-label" data-i18n="auth.phone">شماره تلفن</label>
                <input type="tel" class="form-input" name="phone"
                       data-i18n-placeholder="auth.phonePlaceholder"
                                               placeholder="شماره تلفن (مانند 09123456789)" required>
                                    </div>
            <button type="submit" class="submit-btn" data-i18n="auth.sendOtp">
                                        ارسال کد تایید
                                    </button>
                                </form>

        <!-- OTP Form -->
        <form id="otpForm" class="form-section hidden">
                                    @csrf
                                    <input type="hidden" name="phone">
            <div class="form-group">
                <label class="form-label" data-i18n="auth.otpPlaceholder">کد تایید</label>
                <input type="text" class="form-input" name="otp"
                       data-i18n-placeholder="auth.otpPlaceholder"
                       placeholder="کد تایید" required>
            </div>
            <button type="submit" class="submit-btn" data-i18n="auth.verifyOtp">
                                        تایید کد
                                    </button>
                                </form>

        <div id="error" class="error-msg"></div>

        <!-- Footer -->
                <div class="card-footer">
                    <p>
                        <span data-i18n="auth.noAccount">حساب کاربری ندارید؟</span>
                        <a href="{{ route('register') }}" data-i18n="nav.register">ثبت‌نام</a>
                    </p>
                </div>
            </div>
        </div>
        <div class="login-visual" aria-hidden="true">
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
            'auth.loginTitle': 'ورود کاربر',
            'auth.enterPhone': 'شماره تلفن همراه خود را وارد کنید.',
            'auth.phone': 'شماره تلفن',
            'auth.phonePlaceholder': 'شماره تلفن (مانند 09123456789)',
            'auth.sendOtp': 'ارسال کد تایید',
            'auth.otpPlaceholder': 'کد تایید',
            'auth.verifyOtp': 'تایید کد',
            'auth.noAccount': 'حساب کاربری ندارید؟',
            'auth.phoneError': 'خطا در ارسال شماره',
            'auth.otpError': 'خطا در تأیید کد',
            'auth.phoneNotRegistered': 'شماره تلفن یافت نشد.',
            'auth.invalidPhone': 'شماره تلفن نامعتبر است.',
            'auth.invalidOtp': 'کد تأیید نادرست است.',
            'auth.otpExpired': 'کد تأیید منقضی شده است.',
            'auth.pleaseWait': 'لطفا صبر کنید.',
            'auth.tooManyRequests': 'تعداد درخواست بیش از حد مجاز.',
            'nav.register': 'ثبت‌نام'
        },
        en: {
            'auth.welcome': 'Welcome',
            'auth.loginTitle': 'User Login',
            'auth.enterPhone': 'Enter your mobile phone number.',
            'auth.phone': 'Phone Number',
            'auth.phonePlaceholder': 'Phone number (e.g., 09123456789)',
            'auth.sendOtp': 'Send Verification Code',
            'auth.otpPlaceholder': 'Verification Code',
            'auth.verifyOtp': 'Verify Code',
            'auth.noAccount': "Don't have an account?",
            'auth.phoneError': 'Error sending phone number',
            'auth.otpError': 'Error verifying code',
            'auth.phoneNotRegistered': 'Phone number not found.',
            'auth.invalidPhone': 'Invalid phone number.',
            'auth.invalidOtp': 'Invalid verification code.',
            'auth.otpExpired': 'Verification code has expired.',
            'auth.pleaseWait': 'Please wait.',
            'auth.tooManyRequests': 'Too many requests.',
            'nav.register': 'Register'
        },
        ar: {
            'auth.welcome': 'مرحباً بك',
            'auth.loginTitle': 'تسجيل الدخول',
            'auth.enterPhone': 'أدخل رقم هاتفك المحمول.',
            'auth.phone': 'رقم الهاتف',
            'auth.phonePlaceholder': 'رقم الهاتف (مثال: 09123456789)',
            'auth.sendOtp': 'إرسال رمز التحقق',
            'auth.otpPlaceholder': 'رمز التحقق',
            'auth.verifyOtp': 'تأكيد الرمز',
            'auth.noAccount': 'ليس لديك حساب؟',
            'auth.phoneError': 'خطأ في إرسال رقم الهاتف',
            'auth.otpError': 'خطأ في التحقق من الرمز',
            'auth.phoneNotRegistered': 'رقم الهاتف غير موجود.',
            'auth.invalidPhone': 'رقم الهاتف غير صالح.',
            'auth.invalidOtp': 'رمز التحقق غير صحيح.',
            'auth.otpExpired': 'انتهت صلاحية رمز التحقق.',
            'auth.pleaseWait': 'يرجى الانتظار.',
            'auth.tooManyRequests': 'طلبات كثيرة جداً.',
            'nav.register': 'إنشاء حساب'
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
        if (translations[locale]['auth.loginTitle']) {
            document.title = translations[locale]['auth.loginTitle'];
        }

        // Update active button
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === locale);
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

    // ==========================================
    // FORM HANDLING
    // ==========================================
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        const normalizeDigits = (value = '') => {
            const persianZero = 1776;
            const arabicZero = 1632;
            return value.replace(/[۰-۹٠-٩]/g, (char) => {
                const code = char.charCodeAt(0);
                if (code >= persianZero && code <= persianZero + 9) {
                    return String(code - persianZero);
                }
                if (code >= arabicZero && code <= arabicZero + 9) {
                    return String(code - arabicZero);
                }
                return char;
            });
        };

        const phoneInput = document.querySelector('#loginForm input[name="phone"]');
        const otpInput = document.querySelector('#otpForm input[name="otp"]');
    const loginForm = document.getElementById('loginForm');
    const otpForm = document.getElementById('otpForm');
    const errorDiv = document.getElementById('error');

    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
    }

    function hideError() {
        errorDiv.classList.remove('show');
    }

    function getErrorText(type) {
        const locale = getStoredLocale();
        return translations[locale][type] || type;
    }

    // Translate common API error messages
    function translateApiError(message) {
        const locale = getStoredLocale();
        const errorMap = {
            // Persian error messages from API - all variations
            'شماره تلفن یافت نشد': translations[locale]['auth.phoneNotRegistered'],
            'این شماره تلفن ثبت نشده است': translations[locale]['auth.phoneNotRegistered'],
            'شماره تلفن ثبت نشده': translations[locale]['auth.phoneNotRegistered'],
            'کاربری با این شماره یافت نشد': translations[locale]['auth.phoneNotRegistered'],
            'کاربر یافت نشد': translations[locale]['auth.phoneNotRegistered'],
            'شماره تلفن نامعتبر': translations[locale]['auth.invalidPhone'],
            'فرمت شماره تلفن نادرست': translations[locale]['auth.invalidPhone'],
            'کد تأیید نادرست است': translations[locale]['auth.invalidOtp'],
            'کد تأیید نادرست': translations[locale]['auth.invalidOtp'],
            'کد اشتباه است': translations[locale]['auth.invalidOtp'],
            'کد نادرست': translations[locale]['auth.invalidOtp'],
            'کد تأیید منقضی شده است': translations[locale]['auth.otpExpired'],
            'کد منقضی شده': translations[locale]['auth.otpExpired'],
            'کد تایید منقضی': translations[locale]['auth.otpExpired'],
            'لطفا صبر کنید': translations[locale]['auth.pleaseWait'],
            'تعداد درخواست بیش از حد': translations[locale]['auth.tooManyRequests'],
        };

        // Check if message matches any known error
        for (const [key, value] of Object.entries(errorMap)) {
            if (message && message.includes(key)) {
                return value || message;
            }
        }
        return message;
    }

    // Phone form submit
    loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
        hideError();

            const phone = normalizeDigits(phoneInput.value.trim());
            phoneInput.value = phone;
            if (!phone) return;

        const btn = loginForm.querySelector('.submit-btn');
        btn.disabled = true;

            try {
                const res = await axios.post('/api/v1/auth/login', { phone });

            // Transition to OTP form
            loginForm.classList.add('fade-out');
                setTimeout(() => {
                loginForm.classList.add('hidden');
                otpForm.classList.remove('hidden');
                otpForm.classList.add('fade-in');
                otpForm.querySelector('input[name="phone"]').value = phone;
                otpInput.focus();
            }, 300);

                if (res.data.otp) alert('Test OTP: ' + res.data.otp);
            } catch (err) {
            const apiMsg = err?.response?.data?.message;
            showError(apiMsg ? translateApiError(apiMsg) : getErrorText('auth.phoneError'));
        } finally {
            btn.disabled = false;
            }
        });

    // OTP form submit
    otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
        hideError();

        const phone = otpForm.querySelector('input[name="phone"]').value;
            const otp = normalizeDigits(otpInput.value.trim());
            otpInput.value = otp;

        const btn = otpForm.querySelector('.submit-btn');
        btn.disabled = true;

            try {
                const res = await axios.post('/login', { phone, otp });
                window.location.href = res.data.redirect_url;
            } catch (err) {
            const apiMsg = err?.response?.data?.error?.message || err?.response?.data?.message;
            showError(apiMsg ? translateApiError(apiMsg) : getErrorText('auth.otpError'));
        } finally {
            btn.disabled = false;
        }
    });

    // Auto-hide success alert
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 300);
        }, 3000);
    }
    });
</script>
</body>
</html>
