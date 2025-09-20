<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<form method="POST" action="{{ route('register') }}" class="bg-white p-6 rounded-lg shadow-lg w-96">
    @csrf
    <h2 class="text-2xl font-bold text-blue-600 mb-4">Register</h2>
    <div class="mb-4">
        <input type="text" name="phone" placeholder="Enter your phone (e.g., +989123456789)" class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
        @error('phone')
        <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </div>
    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-green-500 transition">Send OTP</button>
    @if (session('success'))
        <p class="text-green-500 mt-2">{{ session('success') }}</p>
    @endif
</form>
<form method="POST" action="{{ route('activate') }}" class="bg-white p-6 rounded-lg shadow-lg w-96 mt-4">
    @csrf
    <h2 class="text-2xl font-bold text-blue-600 mb-4">Verify OTP</h2>
    <input type="hidden" name="phone" value="{{ old('phone') }}">
    <div class="mb-4">
        <input type="text" name="otp" placeholder="Enter OTP" class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
        @error('otp')
        <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </div>
    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-green-500 transition">Verify</button>
    @if ($errors->any())
        <p class="text-red-500 mt-2">{{ $errors->first() }}</p>
    @endif
</form>
</body>
</html>
