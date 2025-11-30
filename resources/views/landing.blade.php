<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>پنل پشتیبانی کیش - سیستم مدیریت تیکت‌ها و گفت‌وگوها</title>
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
        }

        /* ============================================
           HEADER / NAVBAR
           ============================================ */
        .landing-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e5e7eb;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #0e7490;
            font-size: 1.5rem;
            font-weight: 700;
            transition: transform 0.2s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-nav {
            padding: 0.625rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
            box-shadow: 0 4px 12px rgba(14, 116, 144, 0.3);
        }

        .btn-nav-primary {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            color: white;
            border: none;
        }

        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 116, 144, 0.4);
        }

        /* ============================================
           HERO SECTION
           ============================================ */
        .hero {
            background: linear-gradient(135deg, #f8fafb 0%, #e5e7eb 100%);
            padding: 6rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(14, 116, 144, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out;
        }

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

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 2.5rem;
            line-height: 1.8;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 1rem 2.5rem;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 116, 144, 0.3);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(14, 116, 144, 0.4);
        }

        .btn-hero-secondary {
            background: white;
            color: #0e7490;
            border: 2px solid #0e7490;
        }

        .btn-hero-secondary:hover {
            background: #0e7490;
            color: white;
            transform: translateY(-3px);
        }

        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features {
            padding: 6rem 2rem;
            background: white;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.125rem;
            color: #6b7280;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: #f8fafb;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #0891b2;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.8;
        }

        /* ============================================
           AUTH MODALS
           ============================================ */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            animation: fadeIn 0.3s ease-out;
        }

        .modal.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 480px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.3s ease-out;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .modal-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0e7490;
            margin: 0;
        }

        .modal-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 1.25rem;
        }

        .modal-close:hover {
            background: #e2e8f0;
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #0891b2;
            background: white;
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #6b7280;
            cursor: pointer;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(14, 116, 144, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 116, 144, 0.4);
        }

        .text-danger {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #34d399;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            background: #1f2937;
            color: #e5e7eb;
            padding: 3rem 2rem 2rem;
            margin-top: 6rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .footer-section p,
        .footer-section a {
            color: #9ca3af;
            text-decoration: none;
            line-height: 1.8;
            transition: color 0.2s ease;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 2rem;
            border-top: 1px solid #374151;
            text-align: center;
            color: #9ca3af;
            font-size: 0.9rem;
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
                padding: 4rem 1.5rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .hero-cta {
                flex-direction: column;
            }

            .btn-hero {
                width: 100%;
                justify-content: center;
            }

            .features {
                padding: 4rem 1.5rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header / Navbar -->
    <header class="landing-header">
        <nav class="navbar">
            <a href="/" class="navbar-brand">
                <div class="navbar-brand-icon">SA</div>
                <span>پنل پشتیبانی کیش</span>
            </a>
            <div class="navbar-actions">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn-nav btn-nav-primary">
                        ورود به داشبورد
                    </a>
                @else
                    <button onclick="openModal('loginModal')" class="btn-nav btn-nav-outline">
                        ورود
                    </button>
                    <button onclick="openModal('registerModal')" class="btn-nav btn-nav-primary">
                        ثبت‌نام
                    </button>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>سیستم مدیریت پشتیبانی کیش</h1>
            <p>پلتفرم جامع برای مدیریت تیکت‌ها، گفت‌وگوها و ارتباط با کاربران</p>
            <div class="hero-cta">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn-hero btn-hero-primary">
                        ورود به داشبورد
                    </a>
                @else
                    <button onclick="openModal('registerModal')" class="btn-hero btn-hero-primary">
                        شروع کنید
                    </button>
                    <button onclick="openModal('loginModal')" class="btn-hero btn-hero-secondary">
                        ورود به حساب کاربری
                    </button>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-container">
            <div class="section-title">
                <h2>ویژگی‌های پلتفرم</h2>
                <p>ابزارهای قدرتمند برای مدیریت بهتر پشتیبانی</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>گفت‌وگوی هوشمند</h3>
                    <p>سیستم چت پیشرفته با پشتیبانی از هوش مصنوعی برای پاسخگویی سریع و دقیق به کاربران</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎫</div>
                    <h3>مدیریت تیکت‌ها</h3>
                    <p>سیستم کامل مدیریت تیکت‌های پشتیبانی با امکان پیگیری، اولویت‌بندی و پاسخگویی</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>تیم پشتیبانی</h3>
                    <p>مدیریت تیم‌های پشتیبانی با سیستم نقش‌ها و دسترسی‌های پیشرفته</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>گزارش‌گیری</h3>
                    <p>داشبورد تحلیلی برای بررسی عملکرد و آمار تیکت‌ها و گفت‌وگوها</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>امنیت بالا</h3>
                    <p>سیستم امنیتی پیشرفته با احراز هویت چندمرحله‌ای و مدیریت دسترسی‌ها</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>عملکرد سریع</h3>
                    <p>پلتفرم بهینه‌شده با سرعت بالا و تجربه کاربری روان و حرفه‌ای</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>درباره ما</h3>
                <p>پلتفرم پشتیبانی کیش، راه‌حل جامع برای مدیریت ارتباط با کاربران و ارائه خدمات پشتیبانی حرفه‌ای</p>
            </div>
            <div class="footer-section">
                <h3>لینک‌های مفید</h3>
                <p><a href="#">راهنما</a></p>
                <p><a href="#">مستندات</a></p>
                <p><a href="#">تماس با ما</a></p>
            </div>
            <div class="footer-section">
                <h3>پشتیبانی</h3>
                <p><a href="#">مرکز راهنمایی</a></p>
                <p><a href="#">سوالات متداول</a></p>
                <p><a href="#">گزارش مشکل</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} پنل پشتیبانی کیش. تمام حقوق محفوظ است.</p>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal" onclick="closeModalOnBackdrop(event, 'loginModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>ورود</h2>
                <button class="modal-close" onclick="closeModal('loginModal')">&times;</button>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any() && (session('login_errors') || request()->routeIs('landing.login')))
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-right: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('landing.login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="login_email">ایمیل</label>
                    <input
                        type="email"
                        id="login_email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="example@email.com"
                    >
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="login_password">رمز عبور</label>
                    <input
                        type="password"
                        id="login_password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        placeholder="••••••••"
                    >
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="form-check-input"
                        value="1"
                    >
                    <label class="form-check-label" for="remember">
                        مرا به خاطر بسپار
                    </label>
                </div>

                <button type="submit" class="btn-primary">
                    ورود
                </button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal" onclick="closeModalOnBackdrop(event, 'registerModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>ثبت‌نام</h2>
                <button class="modal-close" onclick="closeModal('registerModal')">&times;</button>
            </div>

            @if($errors->any() && request()->routeIs('landing.register'))
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-right: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('landing.register') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="register_name">نام</label>
                    <input
                        type="text"
                        id="register_name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        placeholder="نام خود را وارد کنید"
                    >
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="register_email">ایمیل</label>
                    <input
                        type="email"
                        id="register_email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        placeholder="example@email.com"
                    >
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="register_password">رمز عبور</label>
                    <input
                        type="password"
                        id="register_password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        placeholder="حداقل 6 کاراکتر"
                    >
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="register_password_confirmation">تکرار رمز عبور</label>
                    <input
                        type="password"
                        id="register_password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        required
                        placeholder="رمز عبور را دوباره وارد کنید"
                    >
                </div>

                <button type="submit" class="btn-primary">
                    ثبت‌نام
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeModalOnBackdrop(event, modalId) {
            if (event.target.id === modalId) {
                closeModal(modalId);
            }
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
        });

        // Auto-open modal if there are errors
        @if(session('login_errors') || ($errors->any() && request()->routeIs('landing.login')))
            document.addEventListener('DOMContentLoaded', function() {
                openModal('loginModal');
            });
        @endif

        @if($errors->any() && request()->routeIs('landing.register'))
            document.addEventListener('DOMContentLoaded', function() {
                openModal('registerModal');
            });
        @endif
    </script>
</body>
</html>




