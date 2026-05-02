<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}">

    <!-- Manifest -->
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#0e7490">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="پارس فناوران مبتکر">

    <title>تیکت‌ها</title>
    @vite(['resources/css/app.css', 'resources/css/user.css', 'resources/js/tickets.js'])
    <style>
        body {
            background: #f8fafc;
        }
        /* RTL font family */
        html[dir="rtl"] body {
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif;
        }
        /* LTR font family */
        html[dir="ltr"] body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
    </style>
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
</head>
<body>
<div id="app"></div>
</body>
</html>
