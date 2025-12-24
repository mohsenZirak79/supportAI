<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
{{--    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">--}}
{{--    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>--}}
{{--    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>--}}
    <script src="https://cdn.tailwindcss.com"></script>

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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        window.AppConfig = Object.assign({}, window.AppConfig, {
            csrfToken: '{{ csrf_token() }}',
            logoutUrl: '{{ route('logout') }}'
        });
    </script>

    <title>تیکت‌ها</title>
    @vite(['resources/css/app.css', 'resources/js/tickets.js'])
</head>
<body>
<div id="app"></div>
</body>
</html>
