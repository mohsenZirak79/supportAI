<?php

return [
    // Driver پیش‌فرض (ippanel، kavenegar، fake و غیره)
    'driver' => env('SMS_DRIVER', 'fake'), // در لوکال fake می‌شه
    'drivers' => [
        'ippanel' => [
            'api_key' => env('SMS_IPPANEL_API_KEY'),
            'sender_number' => env('SMS_IPPANEL_SENDER_NUMBER'),
            'base_url' => env('SMS_IPPANEL_BASE_URL', 'https://api.ippanel.com/api/select'),
        ],
        'kavenegar' => [
            'api_key' => env('SMS_KAVENEGAR_API_KEY'),
            'sender_number' => env('SMS_KAVENEGAR_SENDER_NUMBER'),
            'base_url' => env('SMS_KAVENEGAR_BASE_URL', 'http://api.kavenegar.com/v1'),
        ],
        'fake' => [
            // برای لوکال، فقط لاگ می‌کنه
            'log_channel' => env('SMS_FAKE_LOG_CHANNEL', 'sms'),
        ],
    ],
    // تنظیمات عمومی
    'default_sender' => env('SMS_DEFAULT_SENDER', ''),
    'queue' => env('SMS_QUEUE', false), // آیا از Queue استفاده بشه؟
    'queue_name' => env('SMS_QUEUE_NAME', 'sms'),
    'priority_levels' => [
        'high' => 1,
        'medium' => 2,
        'low' => 3,
    ],
];
