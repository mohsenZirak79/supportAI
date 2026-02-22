<?php

use App\Services\BaleApiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bale:set-webhook {url : آدرس HTTPS وب‌هوک (برای حذف خالی بگذار "")}', function (string $url): void {
    $token = config('services.bale.bot_token');
    if (empty($token)) {
        $this->error('BALE_BOT_TOKEN در .env تنظیم نشده است.');
        return;
    }
    $bale = app(BaleApiService::class);
    $res = $bale->setWebhook($url);
    if ($res['ok'] ?? false) {
        $this->info($url === '' ? 'وب‌هوک حذف شد.' : 'وب‌هوک با موفقیت تنظیم شد: ' . $url);
    } else {
        $this->error('خطا: ' . ($res['description'] ?? json_encode($res)));
    }
})->purpose('تنظیم یا حذف وب‌هوک بازوی بله');

Artisan::command('bale:webhook-info', function (): void {
    $bale = app(BaleApiService::class);
    if (!$bale->isConfigured()) {
        $this->error('BALE_BOT_TOKEN در .env تنظیم نشده است.');
        return;
    }
    $res = $bale->getWebhookInfo();
    if ($res['ok'] ?? false) {
        $url = $res['result']['url'] ?? '';
        $this->info($url === '' ? 'وب‌هوک تنظیم نشده (در حال استفاده از getUpdates).' : 'وب‌هوک فعلی: ' . $url);
    } else {
        $this->error('خطا: ' . ($res['description'] ?? json_encode($res)));
    }
})->purpose('نمایش وضعیت فعلی وب‌هوک بازوی بله');
