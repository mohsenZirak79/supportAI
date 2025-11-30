<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>پنل پشتیبانی کیش</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
    <style>
        .landing-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafb 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .landing-container {
            max-width: 1200px;
            width: 100%;
        }

        .landing-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.6s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .landing-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .landing-header p {
            font-size: 1.125rem;
            color: #6b7280;
            margin: 0;
        }

        .auth-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .auth-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .auth-card h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 0.5rem;
        }

        .auth-card p {
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 0.95rem;
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

        .btn-primary:active {
            transform: translateY(0);
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

        @media (max-width: 768px) {
            .auth-cards {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .landing-header h1 {
                font-size: 2rem;
            }

            .auth-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body class="landing-page">
    <div class="landing-container">
        <div class="landing-header">
            <h1>پنل پشتیبانی کیش</h1>
            <p>سیستم مدیریت تیکت‌ها و گفت‌وگوهای پشتیبانی</p>
        </div>

        <div class="auth-cards">
            <!-- Login Card -->
            <div class="auth-card">
                <h2>ورود</h2>
                <p>وارد حساب کاربری خود شوید</p>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
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

            <!-- Register Card -->
            <div class="auth-card">
                <h2>ثبت‌نام</h2>
                <p>حساب کاربری جدید ایجاد کنید</p>

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
    </div>
</body>
</html>

