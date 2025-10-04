<?php

namespace App\Domains\UserPanel\Controllers;
use App\Domains\Shared\Models\Referral;
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

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'exists:files,id' // ✅ اعتبارسنجی file_id
        ]);

        // بررسی اینکه آیا اولین پیام چت هست
        $isFirstMessage = $conversation->messages()->count() === 0;
        $type = empty($validated['attachments']) ? 'text' : 'voice'; // یا 'file'
        // ذخیره پیام کاربر
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id, // فقط برای user/agent
            'type' => $type,
            'content' => $validated['content'] ?? '',
            'attachments' => $validated['attachments'] ?? [], // ✅ ذخیره به‌صورت آرایه
        ]);
        if (!empty($validated['attachments'])) {
            $userMessage->attachments()->attach($validated['attachments']);
        }
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
            ->select('id', 'conversation_id', 'sender_type', 'content', 'created_at') // ← conversation_id رو هم اضافه کردم
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
    public function userReferrals(Request $request) {
        $user = Auth::user();
        $query = Referral::forOrg($user->org_id)
            ->where('user_id', $user->id)
            ->with(['conversation', 'assignedAgent']) // No triggerMessage to avoid heavy load
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(10); // Specs: cursor
        return response()->json(['data' => $query->items(), 'meta' => ['next_cursor' => $query->nextCursor()]]);
    }
    public function respond(Request $request, Referral $referral) {
        abort_unless(auth()->user()->hasRole('support_agent'), 403); // RBAC
        $validated = $request->validate([
            'agent_response' => 'required|string|max:2000',
            'response_visibility' => 'in:public,internal',
        ]);
        $referral->update([
            'status' => 'responded',
            'assigned_agent_id' => auth()->id(), // Auto-assign if null
            'agent_response' => $validated['agent_response'],
            'response_visibility' => $validated['response_visibility'],
        ]);
        event(new \App\Domains\Shared\Events\ReferralResponded($referral)); // Broadcast to user
        return response()->json($referral->fresh()->load(['user', 'conversation']));
    }

    public function handoff(Request $request, Message $message) {

        $user = Auth::user(); // Assume Sanctum/JWT – add middleware later
//        abort_unless($user && $message->sender_id === $user->id, 403);
        $validated = $request->validate([
            'reason' => 'required|string|max:1000', // Reason
//            'target_role' => 'required|string|in:support_agent,senior_agent,supervisor', // From RBAC
        ], [
//            'target_role.in' => 'نقش معتبر انتخاب کنید.',
        ]);
        $referral = Referral::create([
            'conversation_id' => $message->id,
            'trigger_message_id' => $request->trigger_message_id, // From Vue: selectedMessage.id
            'user_id' => $user->id,
            'assigned_role' => $validated['target_role'],
            'assigned_agent_id' => null, // Assign later via queue/admin
            'description' => $validated['reason'],
            'status' => 'pending',
//            'org_id' => $orgId,
        ]);
        event(new \App\Domains\Shared\Events\ReferralCreated($referral));
        return response()->json($referral->load(['conversation']), 201); // Include conversation for UI refresh
    }
}
