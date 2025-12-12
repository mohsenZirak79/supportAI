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
    
    <title>ورود کاربر</title>
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
</head>

<body class="g-sidenav-show rtl bg-gray-100 auth-page">
<div class="container position-sticky z-index-sticky top-0">
    <!-- Navbar ... (همانند قبل) -->
</div>


@if(session('success'))
    <div class="alert alert-success" style="text-align: center">
        {{ session('success') }}
    </div>
    <script>
        window.__toast.success({{ session('success') }})
    </script>
@endif
<main class="main-content mt-0">
    <section>
        <div class="page-header min-vh-100 d-flex align-items-center">
            <div class="container">


                <div class="row justify-content-center">
                    <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">
                        <div class="card card-plain">
                            <div class="card-header pb-0 text-left bg-transparent">
                                <h3 class="font-weight-bolder text-info text-gradient">خوش آمدید</h3>
                                <p class="mb-0">شماره تلفن همراه خود را وارد کنید.</p>
                            </div>

                            <div class="card-body">
                                <!-- فرم شماره تلفن -->
                                <form id="loginForm" method="POST" style="transition: opacity 0.3s ease-out, transform 0.3s ease-out;">
                                    @csrf
                                    <label>شماره تلفن</label>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="phone"
                                               placeholder="شماره تلفن (مانند 09123456789)" required>
                                    </div>
                                    <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">
                                        ارسال کد تایید
                                    </button>
                                </form>

                                <!-- فرم OTP -->
                                <form id="otpForm" method="POST" style="display:none; margin-top:20px; opacity: 0; transform: translateY(10px); transition: opacity 0.4s ease-out, transform 0.4s ease-out;">
                                    @csrf
                                    <input type="hidden" name="phone">
                                    <input type="text" name="otp" placeholder="کد تایید" class="form-control" required>
                                    <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">
                                        تایید کد
                                    </button>
                                </form>

                                <div id="error" class="text-danger mt-2"></div>
                            </div>

                            <div class="card-footer text-center">
                                <p class="mb-0 text-sm">
                                    حساب کاربری ندارید؟ <a href="{{ route('register') }}" class="text-info text-gradient font-weight-bold">ثبت‌نام</a>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer py-5">
    <!-- Footer ... (همانند قبل) -->
</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {
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

        // ارسال شماره (OTP request)
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('error');
            errorDiv.textContent = ''; // Clear previous errors
            const phone = normalizeDigits(phoneInput.value.trim());
            phoneInput.value = phone;
            if (!phone) return;

            try {
                const res = await axios.post('/api/v1/auth/login', { phone });
                const loginForm = document.getElementById('loginForm');
                const otpForm = document.getElementById('otpForm');
                loginForm.style.opacity = '0';
                loginForm.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    loginForm.style.display = 'none';
                    otpForm.style.display = 'block';
                    setTimeout(() => {
                        otpForm.style.opacity = '1';
                        otpForm.style.transform = 'translateY(0)';
                    }, 10);
                }, 200);
                otpForm.querySelector('input[name="phone"]').value = phone;

                if (res.data.otp) alert('Test OTP: ' + res.data.otp);
            } catch (err) {
                document.getElementById('error').innerText =
                    err?.response?.data?.message || 'خطا در ارسال شماره';
            }
        });

        // تأیید OTP
        document.getElementById('otpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('error');
            errorDiv.textContent = ''; // Clear previous errors
            const phone = document.querySelector('#otpForm input[name="phone"]').value;
            const otp = normalizeDigits(otpInput.value.trim());
            otpInput.value = otp;

            try {
                const res = await axios.post('/login', { phone, otp });
                window.location.href = res.data.redirect_url;
            } catch (err) {
                document.getElementById('error').innerText =
                    err?.response?.data?.error?.message || 'خطا در تأیید OTP';
            }
        });
    });
</script>
</body>
</html>
