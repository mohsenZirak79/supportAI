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
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background */
        .bg-gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            animation: float 20s ease-in-out infinite;
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
        
        .bg-gradient-orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.25) 0%, transparent 70%);
            top: 40%;
            left: 30%;
            animation-delay: -14s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.05); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(10px, 30px) scale(1.02); }
        }
        
        /* Language Switcher */
        .lang-switcher {
            position: absolute;
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
        .login-card {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: cardEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        @keyframes cardEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card::before {
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
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-lighter));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(14, 116, 144, 0.3);
        }
        
        .logo-icon svg {
            width: 36px;
            height: 36px;
            fill: white;
        }
        
        .login-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-input:focus {
            border-color: var(--color-primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.2);
        }
        
        html[dir="ltr"] .form-input {
            direction: ltr;
        }
        
        html[dir="rtl"] .form-input {
            direction: rtl;
        }
        
        html[dir="rtl"] .form-input[type="tel"],
        html[dir="rtl"] .form-input[name="phone"],
        html[dir="rtl"] .form-input[name="otp"] {
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
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(14, 116, 144, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Error Message */
        .error-msg {
            color: #f87171;
            font-size: 0.875rem;
            margin-top: 1rem;
            text-align: center;
            padding: 0.75rem;
            background: rgba(248, 113, 113, 0.1);
            border-radius: 8px;
            display: none;
        }
        
        .error-msg.show {
            display: block;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Footer */
        .card-footer {
            text-align: center;
            margin-top: 2rem;
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
        
        /* Form transitions */
        .form-section {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .form-section.hidden {
            display: none;
        }
        
        .form-section.fade-out {
            opacity: 0;
            transform: translateY(-10px);
        }
        
        .form-section.fade-in {
            animation: fadeIn 0.4s ease forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Success Alert */
        .success-alert {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            z-index: 1000;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
    </style>
</head>

<body>
    <!-- Background Orbs -->
    <div class="bg-gradient-orb bg-gradient-orb-1"></div>
    <div class="bg-gradient-orb bg-gradient-orb-2"></div>
    <div class="bg-gradient-orb bg-gradient-orb-3"></div>
    
    <!-- Language Switcher -->
    <div class="lang-switcher">
        <button class="lang-btn" data-lang="fa">فارسی</button>
        <button class="lang-btn" data-lang="en">English</button>
        <button class="lang-btn" data-lang="ar">العربية</button>
    </div>
    
    @if(session('success'))
        <div class="success-alert" id="successAlert">
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Login Card -->
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
            'auth.otpError': 'خطا در تأیید OTP',
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
            'auth.otpError': 'Error verifying OTP',
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
            showError(err?.response?.data?.message || getErrorText('auth.phoneError'));
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
            showError(err?.response?.data?.error?.message || getErrorText('auth.otpError'));
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
