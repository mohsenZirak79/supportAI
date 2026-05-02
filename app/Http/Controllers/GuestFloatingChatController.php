<?php

namespace App\Http\Controllers;

use App\Support\AiClientSafeMessage;
use App\Support\GuestChatQuestionAugmenter;
use App\Support\PageContextForAi;
use App\Support\PythonAiAskExtras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                ->timeout((int) config('services.python_ai.timeout', 120))
                ->post($askUrl, array_merge([
                    'question' => $questionForAi,
                    'guest' => true,
                    'user_type' => 'new',
                    'first_message' => false,
                    'lang' => $lang,
                ], PythonAiAskExtras::forAskRequest(), $pageContext !== null ? ['page_context' => $pageContext] : []));

            if (! $resp->successful()) {
                Log::warning('guest-floating-chat: upstream AI failed', [
                    'status' => $resp->status(),
                    'body' => mb_substr($resp->body(), 0, 4000),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => AiClientSafeMessage::fa(),
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
            Log::error('guest-floating-chat: exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'success' => false,
                'message' => AiClientSafeMessage::fa(),
            ], 503);
        }
    }
}   
