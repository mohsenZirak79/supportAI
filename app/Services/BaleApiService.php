<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس API بازوی بله (مستندات: https://docs.bale.ai/)
 * درخواست‌ها: https://tapi.bale.ai/bot<token>/METHOD_NAME
 * پاسخ: همیشه JSON با فیلد ok (boolean)، در خطا: error_code و description و گاهی parameters.retry_after
 */
class BaleApiService
{
    /** @var string */
    protected $baseUrl;

    /** @var bool */
    protected $configured;

    public function __construct()
    {
        $token = config('services.bale.bot_token');
        $apiBase = rtrim(config('services.bale.api_base', 'https://tapi.bale.ai'), '/');
        $this->baseUrl = $apiBase . '/bot' . (string) $token;
        $this->configured = $token !== null && $token !== '';
    }

    /**
     * آیا توکن بازو تنظیم شده است.
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * ارسال پیام متنی به یک چت.
     * طبق مستندات: متن بین ۱ تا ۴۰۹۶ کاراکتر (UTF-8).
     *
     * @param int|string $chatId شناسه گفتگو یا @channelusername
     * @param string $text متن پیام (۱ تا ۴۰۹۶ کاراکتر؛ خالی ارسال نشود)
     * @return array {ok: bool, result?: object, description?: string, error_code?: int, parameters?: {retry_after?: int}}
     */
    public function sendMessage($chatId, string $text): array
    {
        if (!$this->configured) {
            Log::warning('Bale sendMessage skipped: BALE_BOT_TOKEN not set');
            return ['ok' => false, 'description' => 'BALE_BOT_TOKEN not set'];
        }

        $text = trim($text);
        $text = mb_substr($text, 0, 4096);
        if ($text === '') {
            $text = '—';
        }

        $response = Http::timeout(15)
            ->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => $text,
            ]);

        $body = $response->json();
        if ($body === null) {
            $body = ['ok' => false, 'description' => $response->body() ?: 'Invalid JSON response'];
        }

        if (!($response->successful() && ($body['ok'] ?? false))) {
            $context = [
                'chat_id'      => $chatId,
                'status'       => $response->status(),
                'body'         => $body,
                'error_code'   => $body['error_code'] ?? null,
                'description'  => $body['description'] ?? null,
            ];
            if (isset($body['parameters']['retry_after'])) {
                $context['retry_after'] = $body['parameters']['retry_after'];
            }
            Log::warning('Bale sendMessage failed', $context);
        }

        return $body;
    }

    /**
     * ویرایش متن یک پیام (editMessageText).
     * برای تبدیل پیام «در حال تحلیل» به پاسخ نهایی استفاده می‌شود.
     *
     * @param int|string $chatId
     * @param int $messageId شناسه پیامی که باید ویرایش شود
     * @param string $text متن جدید (۱ تا ۴۰۹۶ کاراکتر)
     */
    public function editMessageText($chatId, int $messageId, string $text): array
    {
        if (!$this->configured) {
            return ['ok' => false];
        }
        $text = trim($text);
        $text = mb_substr($text, 0, 4096);
        if ($text === '') {
            $text = '—';
        }
        $response = Http::timeout(15)
            ->post("{$this->baseUrl}/editMessageText", [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
                'text'       => $text,
            ]);
        $body = $response->json();
        if ($body === null) {
            $body = ['ok' => false];
        }
        if (!($response->successful() && ($body['ok'] ?? false))) {
            Log::warning('Bale editMessageText failed', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'status' => $response->status(),
                'body' => $body,
            ]);
        }
        return $body;
    }

    /**
     * اعلام وضعیت «در حال تایپ» به کاربر (حداکثر ۶ ثانیه در کلاینت نمایش داده می‌شود).
     * action: typing | upload_photo | record_video | upload_video | record_voice | upload_voice | choose_sticker
     */
    public function sendChatAction($chatId, string $action = 'typing'): array
    {
        if (!$this->configured) {
            return ['ok' => false];
        }
        $response = Http::timeout(5)
            ->post("{$this->baseUrl}/sendChatAction", [
                'chat_id' => $chatId,
                'action'  => $action,
            ]);
        return $response->json() ?? ['ok' => false];
    }

    /**
     * تنظیم وب‌هوک برای دریافت آپدیت‌ها.
     * فقط پورت‌های ۴۴۳ و ۸۸ پشتیبانی می‌شوند.
     * @param string $url آدرس HTTPS (برای حذف وب‌هوک رشته خالی بفرست)
     */
    public function setWebhook(string $url): array
    {
        $response = Http::timeout(10)
            ->post("{$this->baseUrl}/setWebhook", ['url' => $url]);
        return $response->json() ?? ['ok' => false];
    }

    /**
     * دریافت اطلاعات بازو (getMe).
     */
    public function getMe(): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/getMe");
        return $response->json() ?? ['ok' => false];
    }

    /**
     * غیرفعال کردن وب‌هوک (برای استفاده مجدد از getUpdates).
     */
    public function deleteWebhook(): array
    {
        $response = Http::timeout(10)->post("{$this->baseUrl}/deleteWebhook");
        return $response->json() ?? ['ok' => false];
    }

    /**
     * دریافت وضعیت فعلی وب‌هوک (getWebhookInfo).
     * اگر وب‌هوک تنظیم نشده باشد فیلد url خالی است.
     */
    public function getWebhookInfo(): array
    {
        if (!$this->configured) {
            return ['ok' => false];
        }
        $response = Http::timeout(10)->get("{$this->baseUrl}/getWebhookInfo");
        return $response->json() ?? ['ok' => false];
    }
}
