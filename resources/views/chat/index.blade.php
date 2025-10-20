<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy"
          content="
default-src 'self';
script-src 'self' 'unsafe-inline' http: https: ws: wss:;
connect-src 'self' http: https: ws: wss:;
img-src 'self' data: https:;
style-src 'self' 'unsafe-inline' http: https:;
font-src 'self' data: http: https:;
">
    <title>ربات چت</title>
    @vite(['resources/css/app.css', 'resources/js/chat.js'])
    {{-- فونت فارسی --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            margin: 0;
            font-family: 'Vazirmatn', sans-serif;
        }
    </style>
</head>
<body>
<div id="app"></div>
</body>
</html>
