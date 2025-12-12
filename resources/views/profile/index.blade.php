<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('profile.title') }} - {{ config('app.name', 'Support AI') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/user.css', 'resources/js/profile.js'])
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Vazirmatn', 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            background: #0f172a;
        }
        
        #app {
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div id="app"></div>
</body>
</html>

