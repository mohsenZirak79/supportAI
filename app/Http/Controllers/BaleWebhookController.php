<?php

namespace App\Http\Controllers;

use App\Services\BaleApiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * وب‌هوک بازوی بله (مستندات: https://docs.bale.ai/)
 * آپدیت‌ها به صورت POST با بدنه JSON ارسال می‌شوند.
 * همیشه 200 برمی‌گردانیم تا بله آپدیت را تایید شده فرض کند و دوباره ارسال نکند.
 */
class BaleWebhookController extends Controller
{
    /** @var BaleApiService */
    protected $bale;

    /** حداقل طول متن برای پاسخ (مستندات: sendMessage بین ۱ تا ۴۰۹۶ کاراکتر) */
    const FALLBACK_REPLY = 'پاسخی در دسترس نیست. لطفاً بعداً تلاش کنید.';

    public function __construct(BaleApiService $bale)
    {
        $this->bale = $bale;
    }

    /**
     * دریافت آپدیت از سرور بله و پاسخ با پیام AI.
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->processUpdate($request);
        } catch (\Throwable $e) {
            Log::error('Bale webhook: unhandled exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response('', 200);
        }
    }

    /**
     * پردازش یک آپدیت (message یا edited_message با متن).
     */
    protected function processUpdate(Request $request): Response
    {
        $payload = $request->all();
        if (!is_array($payload)) {
            return response('', 200);
        }

        $updateId = $payload['update_id'] ?? null;

        // طبق مستندات: آپدیت حداکثر یکی از message یا edited_message یا callback_query یا pre_checkout_query را دارد
        $message = $payload['message'] ?? $payload['edited_message'] ?? null;
        if (!$message || !isset($message['text'])) {
            return response('', 200);
        }

        $chatId = isset($message['chat']['id']) ? $message['chat']['id'] : null;
        $text = trim((string) $message['text']);
        if ($chatId === null || $text === '') {
            return response('', 200);
        }

        if (!$this->bale->isConfigured()) {
            Log::warning('Bale webhook: BALE_BOT_TOKEN not set', ['update_id' => $updateId]);
            return response('', 200);
        }

        $from = $message['from'] ?? [];
        $userName = $from['first_name'] ?? null;
        if (!empty($from['last_name'])) {
            $userName = trim(($userName ?? '') . ' ' . $from['last_name']);
        }

        $cacheKey = 'bale_first_message:' . $chatId;
        $isFirstMessage = !Cache::has($cacheKey);
        if ($isFirstMessage) {
            Cache::put($cacheKey, true, now()->addYears(1));
        }

        $this->bale->sendChatAction($chatId, 'typing');

        $replyText = $this->getAiReply($text, $isFirstMessage, $userName);
        if (trim($replyText) === '') {
            $replyText = self::FALLBACK_REPLY;
        }

        $res = $this->bale->sendMessage($chatId, $replyText);
        if (!($res['ok'] ?? false)) {
            Log::error('Bale sendMessage failed after AI reply', [
                'update_id' => $updateId,
                'chat_id'   => $chatId,
                'response'  => $res,
            ]);
        }

        return response('', 200);
    }

    /**
     * فراخوانی سرویس AI و برگرداندن متن پاسخ.
     */
    protected function getAiReply(string $question, bool $isFirstMessage, ?string $userName): string
    {
        $aiBaseUrl = rtrim(config('services.python_ai.url', 'http://127.0.0.1:5000'), '/');
        $askUrl = $aiBaseUrl . '/api/ask';

        try {
            $resp = Http::withoutVerifying()
                ->withOptions(['connect_timeout' => 10])
                ->timeout(60)
                ->post($askUrl, [
                    'question'      => $question,
                    'user_type'      => 'new',
                    'first_message'  => $isFirstMessage,
                    'lang'           => 'fa',
                    'user_name'      => $userName ?? '',
                ]);

            if ($resp->successful()) {
                $json = $resp->json();
                $out = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                return trim((string) $out) !== '' ? $out : 'پاسخی دریافت نشد.';
            }

            $body = $resp->json();
            $err = is_array($body) ? ($body['error'] ?? $body['message'] ?? null) : null;
            if ($err === null) {
                $err = $resp->body();
            }
            Log::warning('Bale webhook: AI ask failed', [
                'status' => $resp->status(),
                'error'  => $err,
            ]);
            return 'در ارتباط با سرویس پشتیبانی خطایی رخ داد. لطفاً بعداً تلاش کنید.';
        } catch (\Throwable $e) {
            Log::error('Bale webhook: AI exception', [
                'message' => $e->getMessage(),
                'url'     => $askUrl,
                'trace'   => $e->getTraceAsString(),
            ]);
            return 'در ارتباط با سرویس پشتیبانی خطایی رخ داد. لطفاً بعداً تلاش کنید.';
        }
    }
}
