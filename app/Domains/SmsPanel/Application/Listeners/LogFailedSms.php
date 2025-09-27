<?php

namespace App\Domains\SmsPanel\Application\Listeners;

use App\Domains\SmsPanel\Application\Events\SmsFailedPermanently;
use Illuminate\Support\Facades\Log;

class LogFailedSms
{
    public function handle(SmsFailedPermanently $event)
    {
        Log::channel('sms')->error("Permanent SMS failure to {$event->to}", [
            'message' => $event->message,
            'error' => $event->error,
        ]);

        // می‌تونی اینجا ذخیره در دیتابیس یا ارسال ایمیل به ادمین اضافه کنی
    }
}
