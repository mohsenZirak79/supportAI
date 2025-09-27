<?php

namespace App\Domains\UserPanel\Controllers;
use App\Domains\Shared\Models\User;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Conversation;
use App\Domains\Shared\Models\Message;
use App\Domains\Shared\Services\PythonAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $request->validate(['content' => 'required|string|max:2000']);

        // بررسی اینکه آیا اولین پیام چت هست
        $isFirstMessage = $conversation->messages()->count() === 0;

        // ذخیره پیام کاربر
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id, // فقط برای user/agent
            'type' => 'text',
            'content' => $request->content,
        ]);

        // ارسال به سرویس AI
        $aiService = new PythonAIService();
        $aiResponse = $aiService->chat([
            'message' => $request->content,
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'first_message' => $isFirstMessage,
        ]);

        // پیدا کردن کاربر AI
//        $aiUser = User::firstOrCreate(
//            ['email' => 'ai@system.local'],
//            ['name' => 'ربات هوشمند', 'email' => 'ai@system.local']
//        );

        // ذخیره پاسخ AI
        $aiMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',    // ← مهم!
            'sender_id' => null,      // ← null کاملاً مجازه
            'type' => 'text',
            'content' => $aiResponse['reply'],
        ]);

        // اگر عنوان پیشنهادی داده شده و چت هنوز عنوان نداره، آپدیت کن
        if ($aiResponse['suggested_title'] && (!$conversation->title || $conversation->title === 'چت جدید')) {
            $conversation->update(['title' => $aiResponse['suggested_title']]);
        }

        event(new \App\Domains\Shared\Events\MessageSent($aiMessage));

        return response()->json([
            'user_message' => $request->content,
            'ai_message' => $aiMessage,
            'conversation' => $conversation->fresh(), // شامل عنوان جدید
        ]);
    }
    // لیست چت‌ها (فقط active)
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $conversations = Conversation::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->latest()
            ->get(['id', 'title', 'status', 'created_at']);

        return response()->json(['data' => $conversations]);
    }

    // ایجاد چت جدید
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate(['title' => 'nullable|string|max:100']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => $request->title ?: 'چت جدید',
            'status' => 'ai',
        ]);

        return response()->json($conversation, 201);
    }

    public function messages(Conversation $conversation)
    {
        $user = Auth::user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $messages = $conversation->messages()
            ->select('id', 'sender_type', 'content', 'created_at') // ← created_at حتماً باشه
            ->orderBy('created_at')
            ->get();
        return response()->json(['data' => $messages]);
    }

    // تغییر عنوان چت
    public function updateTitle(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $request->validate(['title' => 'required|string|max:100']);
        $conversation->update(['title' => $request->title]);

        return response()->json($conversation);
    }

    // حذف چت (soft delete)
    public function destroy(Conversation $conversation)
    {
        $user = Auth::user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $conversation->delete(); // soft delete
        return response()->json(['message' => 'چت با موفقیت حذف شد.']);
    }
    // ... سایر متدها (index, store, messages, updateTitle, destroy) بدون تغییر
}
