<?php

namespace App\Http\Controllers;

use App\Support\GuestChatQuestionAugmenter;
use App\Support\PageContextForAi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * چت ویجت برای کاربران بدون لاگین — پروکسی به سرویس Python با guest=true (FAQ عمومی).
 */
class GuestFloatingChatController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|min:1|max:3500',
            'lang' => 'nullable|string|max:8',
            'page_context' => 'nullable|array',
        ]);

        $pageContext = PageContextForAi::sanitize($validated['page_context'] ?? null);
        $questionForAi = GuestChatQuestionAugmenter::embed($validated['question'], $pageContext);

        $lang = strtolower((string) ($validated['lang'] ?? 'fa'));
        if (! in_array($lang, ['fa', 'en', 'ar'], true)) {
            $lang = 'fa';
        }

        $aiBaseUrl = rtrim(config('services.python_ai.url', 'http://127.0.0.1:5000'), '/');
        $askUrl = $aiBaseUrl . '/api/ask';

        try {
            $resp = Http::withoutVerifying()
                ->withOptions(['connect_timeout' => 10])
                ->timeout((int) config('services.python_ai.timeout', 60))
                ->post($askUrl, array_merge([
                    'question' => $questionForAi,
                    'guest' => true,
                    'user_type' => 'new',
                    'first_message' => false,
                    'lang' => $lang,
                ], $pageContext !== null ? ['page_context' => $pageContext] : []));

            if (! $resp->successful()) {
                $body = $resp->json();
                $msg = is_array($body) ? ($body['error'] ?? $resp->body()) : $resp->body();

                return response()->json([
                    'success' => false,
                    'message' => is_string($msg) ? mb_substr($msg, 0, 500) : 'خطا در سرویس هوش مصنوعی',
                ], 502);
            }

            $json = $resp->json();
            $answer = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';

            return response()->json([
                'success' => true,
                'answer' => $answer,
                'lang' => $json['lang'] ?? $lang,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'ارتباط با سرویس پاسخ‌گو برقرار نشد.',
            ], 503);
        }
    }
}   
