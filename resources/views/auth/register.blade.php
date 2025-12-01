<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
    
    <title>ثبت‌نام کاربر</title>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>

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

<body class="g-sidenav-show rtl bg-gray-100 auth-page register-page">
<div class="container position-sticky z-index-sticky top-0">
    <!-- Navbar ... (همانند قبل) -->
</div>

<main class="main-content mt-0">
    <section>
        <div class="page-header min-vh-100 d-flex align-items-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-6 col-lg-7 col-md-9 col-sm-11">
                        <div class="card card-plain">
                            <div class="card-header pb-0 text-left bg-transparent">
                                <h3 class="font-weight-bolder text-info text-gradient">خوش آمدید</h3>
                                <p class="mb-0">لطفا اطلاعات خود را وارد کنید.</p>
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="card-body">
                                <form id="registerForm" method="POST" action="{{ route('register') }}">
                                    @csrf
                                    <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <label>نام</label>
                                        <input type="text" class="form-control" name="name" placeholder="نام" required>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <label>نام خانوادگی</label>
                                        <input type="text" class="form-control" name="family" placeholder="نام خانوادگی" required>
                                    </div>
                                        <div class="row">

                                    </div>
                                        <div class="col-md-6 mb-1">
                                        <label>ایمیل</label>
                                        <input type="email" class="form-control" name="email" placeholder="ایمیل" required>
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <label>شماره تلفن</label>
                                        <input type="text" class="form-control" name="phone" placeholder="شماره تلفن (...09)" required>
                                    </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-1">
                                            <label>رمز عبور</label>
                                            <input type="password" class="form-control" name="password" placeholder="رمز عبور" required>
                                        </div>
                                        <div class="col-md-6 mb-1">
                                            <label>تکرار رمز عبور</label>
                                            <input type="password" class="form-control" name="password_confirmation" placeholder="تکرار رمز عبور" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                    <div class="col-md-6 mb-1">
                                        <label>کد ملی</label>
                                        <input type="text" class="form-control" name="national_id" placeholder="کد ملی">
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <label>کد پستی</label>
                                        <input type="text" class="form-control" name="postal_code" placeholder="کد پستی">
                                    </div>
                                    </div>
                                    <div class="col-md-6 mb-1" dir="rtl">
                                        <label for="birth_date" class="form-label">تاریخ تولد (شمسی)</label>
                                        <input
                                            id="birth_date"
                                            name="birth_date"
                                            type="text"
                                            class="form-control"
                                            placeholder="مثلاً 1404/07/18"
                                            autocomplete="off"
                                            dir="ltr"
                                            data-jdp
                                            data-jdp-only-date="true"
                                            data-jdp-config='{"selector":"#birth_date","dateFormat":"YYYY/MM/DD","autoShow":true}'
                                        >
                                    </div>



                                    <div class="mb-1">
                                        <label>آدرس</label>
                                        <textarea class="form-control" name="address" placeholder="آدرس"></textarea>
                                    </div>
                                    <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">
                                        ثبت‌نام
                                    </button>
                                </form>
                                <div id="error" class="text-danger mt-2"></div>
                            </div>

                            <div class="card-footer text-center">
                                <p class="mb-0 text-sm">
                                    قبلاً حساب کاربری دارید؟ <a href="{{ route('login') }}" class="text-info text-gradient font-weight-bold">ورود</a>
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
    // ارسال شماره
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        console.log(1111111111111);
        e.preventDefault();
        const phoneInput = document.querySelector('#loginForm input[name="phone"]');
        const phone = phoneInput.value.trim();

        try {
            const response = await axios.post('/api/v1/auth/login', { phone });

            // نمایش فرم OTP
            document.getElementById('loginForm').style.display = 'none';
            const otpForm = document.getElementById('otpForm');
            otpForm.style.display = 'block';
            otpForm.querySelector('input[name="phone"]').value = phone;

            // نمایش OTP تستی (فقط در local)
            if (response.data.otp) {
                alert('Test OTP: ' + response.data.otp);
            }

        } catch (error) {
            console.log(error)
            document.getElementById('error').innerText = error.response?.data?.error?.message || 'خطا در ارسال شماره';
        }
    });

    // ارسال OTP
    document.getElementById('otpForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const otpForm = document.getElementById('otpForm');
        const phone = otpForm.querySelector('input[name="phone"]').value;
        const otp = otpForm.querySelector('input[name="otp"]').value;

        try {
            const response = await axios.post('/api/v1/auth/verify-login-otp', { phone, otp });

            localStorage.setItem('token', response.data.access_token);
            const primaryRole = response.data.primary_role;
            window.location.href = primaryRole === 'admin' ? '/admin' : '/chat';

        } catch (error) {
            document.getElementById('error').innerText = error.response?.data?.error?.message || 'خطا در تایید OTP';
        }
    });
</script>

</body>
</html>
