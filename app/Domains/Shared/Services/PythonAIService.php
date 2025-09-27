<?php

namespace App\Domains\Shared\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonAIService
{
    /**
     * ارسال پیام به سرویس Python AI
     *
     * @param array $data [
     *     'message' => string,
     *     'conversation_id' => string,
     *     'user_id' => string,
     *     'first_message' => bool
     * ]
     * @return array ['reply' => string, 'suggested_title' => ?string]
     */
    public function chat(array $data): array
    {
        // در محیط local، پاسخ تستی بده (بدون ارتباط با سرور)
        if (app()->environment('local') && config('app.use_mock_ai', true)) {
            return $this->mockAiResponse($data);
        }

        // در production، به سرور Python وصل شو
        try {
            $response = Http::timeout(15)
                ->post(config('services.python_ai.url') . '/ai/chat', $data);

            if ($response->successful()) {
                $json = $response->json();
                return [
                    'reply' => $json['reply'] ?? 'پاسخی دریافت نشد.',
                    'suggested_title' => $json['suggested_title'] ?? null,
                ];
            }

            Log::error('AI Service HTTP Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('AI Service Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return [
            'reply' => 'خطا در ارتباط با سرویس هوش مصنوعی.',
            'suggested_title' => null,
        ];
    }

    /**
     * پاسخ تستی برای محیط توسعه
     */
    private function mockAiResponse(array $data): array
    {
        // شبیه‌سازی تأخیر 2-5 ثانیه (برای تست loading)
        sleep(rand(2, 5));

        $replies = [
            'در حال پردازش پیام شما...',
            'این یک پاسخ تستی از سامانه هوش مصنوعی است.',
            'سوال شما جالب بود! آیا می‌خوای اطلاعات بیشتری داشته باشی؟',
            'متاسفانه هنوز قابلیت کامل AI فعال نیست، ولی به زودی!',
            'پاسخ موفقیت‌آمیز دریافت شد.',
        ];

        $reply = $replies[array_rand($replies)];

        // اگر اولین پیام بود، عنوان پیشنهادی بده
        $suggestedTitle = null;
        if ($data['first_message'] ?? false) {
            $suggestedTitle = 'چت درباره: ' . substr($data['message'], 0, 30) . '...';
        }

        return [
            'reply' => $reply,
            'suggested_title' => $suggestedTitle,
        ];
    }
}
