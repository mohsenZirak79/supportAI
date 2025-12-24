<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="Content-Security-Policy"
          content="
default-src 'self';
script-src 'self' 'unsafe-inline' http: https: ws: wss:;
connect-src 'self' http: https: ws: wss:;
img-src 'self' data: https: blob:;
style-src 'self' 'unsafe-inline' http: https:;
font-src 'self' data: http: https:;
media-src 'self' blob: data: https:;
">
    <title>ربات چت</title>

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

    @vite(['resources/css/app.css', 'resources/css/user.css', 'resources/js/chat.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            height: 100dvh;
            overflow: hidden;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
        }
        /* RTL font family */
        html[dir="rtl"] body {
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif;
        }
        /* LTR font family */
        html[dir="ltr"] body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        #app {
            height: 100%;
            overflow: hidden;
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
