<?php

namespace App\Domains\UserPanel\Controllers;

use App\Domains\Shared\Models\Conversation;
use App\Domains\Shared\Models\Message;
use App\Domains\Shared\Models\WidgetCallbackRequest;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class WidgetCallbackRequestController extends Controller
{
    /**
     * اگر برای این گفتگو درخواست تماس فعال (غیر از لغوشده) وجود داشته باشد، ویجت نباید پیام جدید بفرستد.
     */
    public function lockStatus(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $locked = WidgetCallbackRequest::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', WidgetCallbackRequest::STATUS_CANCELLED)
            ->exists();

        return response()->json(['locked' => $locked]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,id',
            'trigger_message_id' => 'required|uuid|exists:messages,id',
            'consent' => 'required|accepted',
        ]);

        $conversation = Conversation::query()->findOrFail($validated['conversation_id']);
        abort_unless($conversation->user_id === $user->id, 403);

        $message = Message::query()->findOrFail($validated['trigger_message_id']);
        abort_unless($message->conversation_id === $conversation->id, 403);
        abort_unless($message->sender_type === 'ai', 422, 'فقط برای پیام‌های دستیار امکان ثبت تماس وجود دارد.');

        $bucketKey = 'widget_callback_create:' . $user->id;
        if (RateLimiter::tooManyAttempts($bucketKey, 25)) {
            return response()->json([
                'message' => 'تعداد درخواست تماس در این بازه زیاد است. کمی بعد دوباره تلاش کنید.',
            ], 429);
        }

        try {
            $row = WidgetCallbackRequest::create([
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'trigger_message_id' => $message->id,
                'status' => WidgetCallbackRequest::STATUS_PENDING,
                'source' => 'floating_widget',
            ]);
        } catch (QueryException $e) {
            if (str_contains(strtolower($e->getMessage()), 'unique')) {
                return response()->json([
                    'message' => 'برای این پیام قبلاً درخواست تماس ثبت شده است.',
                ], 422);
            }
            throw $e;
        }

        RateLimiter::hit($bucketKey, 3600);

        return response()->json([
            'message' => 'درخواست تماس با موفقیت ثبت شد. همکاران ما در اولین فرصت با شما تماس می‌گیرند.',
            'callback' => $row,
        ], 201);
    }

    /**
     * لغو درخواست توسط کاربر (فقط وضعیت pending).
     */
    public function destroy(Request $request, WidgetCallbackRequest $widgetCallbackRequest)
    {
        $user = $request->user();
        abort_unless($user && $widgetCallbackRequest->user_id === $user->id, 403);
        abort_unless($widgetCallbackRequest->status === WidgetCallbackRequest::STATUS_PENDING, 422);

        $widgetCallbackRequest->update(['status' => WidgetCallbackRequest::STATUS_CANCELLED]);

        return response()->json(['message' => 'درخواست تماس لغو شد.']);
    }
}
