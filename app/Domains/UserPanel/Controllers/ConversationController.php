<?php

namespace App\Domains\UserPanel\Controllers;

use App\Domains\Shared\Models\Referral;
use App\Domains\Shared\Models\User;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Conversation;
use App\Domains\Shared\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\Process\Process;

class ConversationController extends Controller
{
//    public function sendMessage(Request $request, Conversation $conversation)
//    {
//        $user = $request->user();
//        abort_unless($user && $conversation->user_id === $user->id, 403);
//
//        $validated = $request->validate([
//            'content'     => 'nullable|string|max:2000',
//            'media_ids'   => 'nullable|array',
//            'media_ids.*' => 'integer',
//            'media_kind'  => 'nullable|in:file,voice', // ← فرانت برای ویس 'voice' بفرستد
//        ]);
//
//        $isFirstMessage = $conversation->messages()->count() === 0;
//
//        $type = 'text';
//        if (!empty($validated['media_ids'])) {
//            $type = ($validated['media_kind'] ?? null) === 'voice' ? 'voice' : 'file';
//        }
//
//        $userMessage = Message::create([
//            'conversation_id' => $conversation->id,
//            'sender_type'     => 'user',
//            'sender_id'       => $user->id,
//            'type'            => $type,
//            'content'         => $validated['content'] ?? '',
//            'metadata'        => null,
//        ]);
//        $targetCollection = $type === 'voice' ? 'message_voices' : 'message_files';
//        // الصاق مدیاها (انتقال از TempUpload به Message)
//        if (!empty($validated['media_ids'])) {
//            Media::query()
//                ->whereIn('id', $validated['media_ids'] ?? [])
//                ->get()
//                ->each(function (Media $m) use ($userMessage, $targetCollection) {
//                    $m->model_type      = \App\Domains\Shared\Models\Message::class;
//                    $m->model_id        = $userMessage->id;
//                    $m->collection_name = $targetCollection;
//                    $m->save(); // ← این save ایونت را تریگر می‌کند و با moves_media_on_update=true فایل را جابجا می‌کند
//                });
//        }
//
//        // تماس با AI
//        $aiService = new PythonAIService();
//        $aiResponse = $aiService->chat([
//            'message'        => $request->content,
//            'conversation_id'=> $conversation->id,
//            'user_id'        => $user->id,
//            'first_message'  => $isFirstMessage,
//        ]);
//
//        // پیام AI (متن)
//        $aiMessage = Message::create([
//            'conversation_id' => $conversation->id,
//            'sender_type'     => 'ai',
//            'sender_id'       => null,
//            'type'            => 'text', // بعداً اگر ویس هم داشت تغییر می‌دهیم
//            'content'         => $aiResponse['reply'] ?? '',
//        ]);
//
//        // اگر AI ویس برگرداند (اختیاری: voice_media_id یا voice_media_ids)
//        if (!empty($aiResponse['voice_media_id'])) {
//            DB::table('media')
//                ->where('id', $aiResponse['voice_media_id'])
//                ->update([
//                    'model_type'     => \App\Domains\Shared\Models\Message::class,
//                    'model_id'       => $aiMessage->id,
//                    'collection_name'=> 'message_voices',
//                    'updated_at'     => now(),
//                ]);
//            $aiMessage->update(['type' => 'voice']); // هم متن دارد هم ویس → اگر می‌خواهی 'text' بماند و صرفاً media داشته باشد، این خط را بردار.
//        }
//
//        if (!empty($aiResponse['suggested_title'])
//            && (!$conversation->title || $conversation->title === 'چت جدید')) {
//            $conversation->update(['title' => $aiResponse['suggested_title']]);
//        }
//
//        event(new \App\Domains\Shared\Events\MessageSent($aiMessage));
//
//        return response()->json([
//            'user_message' => $userMessage->fresh(),   // ← به‌جای متن ساده، خود مدل
//            'ai_message'   => $aiMessage,
//            'conversation' => $conversation->fresh(),
//        ]);
//    }


    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'integer',        // id جدول media در Spatie عددی است
            'media_kind' => 'nullable|in:file,voice',
            'lang' => 'nullable|string|in:fa,en,ar', // Language code
        ]);

        $isFirstMessage = $conversation->messages()->count() === 0;

        // نوع پیام کاربر
        $type = 'text';
        if (!empty($validated['media_ids'])) {
            $type = ($validated['media_kind'] ?? null) === 'voice' ? 'voice' : 'file';
        }

        // 1) پیام کاربر را ثبت کن
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'type' => $type,
            'content' => $validated['content'] ?? '',
            'metadata' => null,
        ]);

        // 2) مدیاها را از TempUpload به Message منتقل کن (همان روال فعلی)
        if (!empty($validated['media_ids'])) {
            DB::table('media')
                ->whereIn('id', $validated['media_ids'])
                ->update([
                    'model_type' => \App\Domains\Shared\Models\Message::class,
                    'model_id' => $userMessage->id,
                    'collection_name' => $type === 'voice' ? 'message_voices' : 'message_files',
                    'updated_at' => now(),
                ]);
        }

        // 3) تماس با API جدید (متن یا ویس)
        $aiReplyText = '';
        $aiVoiceDataUrl = null;   // اگر API صوتِ base64 برگرداند (data URL یا base64 ساده)

        try {
            if ($type === 'voice') {
                /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media|null $voiceMedia */
                $voiceMedia = $userMessage->getMedia('message_voices')->first();

                if (!$voiceMedia) {
                    $aiReplyText = 'فایل صوتی کاربر پیدا نشد.';
                } else {
                    $srcPath = $voiceMedia->getPath();               // .../recording.webm
                    $srcMime = $voiceMedia->mime_type ?? 'audio/webm';

                    // 1) اگر webm/ogg بود، به wav تبدیل کن (16kHz mono)
                    $tmpWav = null;
                    $needsConv = in_array($srcMime, ['audio/webm','audio/ogg','audio/opus'], true)
                        || str_ends_with(strtolower($srcPath), '.webm')
                        || str_ends_with(strtolower($srcPath), '.ogg');
                    if ($needsConv) {
                        $tmpWav = sys_get_temp_dir().'/'.uniqid('ai_', true).'.wav';
                        // ffmpeg -y -i input -ac 1 -ar 16000 -f wav output
                        $process = new Process([
                            '/usr/bin/ffmpeg','-y','-i',$srcPath,'-ac','1','-ar','16000','-f','wav',$tmpWav
                        ]);
                        $process->setTimeout(30);
                        $process->run();

                        if (!$process->isSuccessful() || !file_exists($tmpWav) || filesize($tmpWav) === 0) {
                            Log::warning('Voice convert failed', [
                                'exit_code' => $process->getExitCode(),
                                'error'     => $process->getErrorOutput(),
                                'src_mime'  => $srcMime,
                                'src_path'  => $srcPath,
                            ]);
                            // اگر تبدیل شکست خورد، همون فایل اصلی رو می‌فرستیم (شاید سرویس قبول کنه)
                            $tmpWav = null;
                        }
                    }

                    // تابع کوچک برای لاگِ پاسخ ناموفق
                    $logHttpError = function ($label, \Illuminate\Http\Client\Response $resp) use ($srcMime, $srcPath, $tmpWav) {
                        Log::warning("AI API {$label} failed", [
                            'status'   => $resp->status(),
                            'body'     => $resp->body(),
                            'src_mime' => $srcMime,
                            'src_path' => $srcPath,
                            'sent_as'  => $tmpWav ? 'wav' : 'original',
                        ]);
                    };

                    // 2) تلاش اول: multipart (اگر wav داریم، همون را بفرست)
                    try {
                        $filePath = $tmpWav ?: $srcPath;
                        $fileName = $tmpWav ? 'audio.wav' : basename($srcPath);

                        $lang = $validated['lang'] ?? 'fa'; // Default to Persian
                        
                        // Get user's name for personalized response
                        $userName = null;
                        if ($user) {
                            $userName = trim(($user->name ?? '') . ' ' . ($user->family ?? ''));
                            if (empty($userName)) {
                                $userName = $user->phone ?? null;
                            }
                        }
                        
                        $resp1 = Http::asMultipart()
                            ->timeout(60)
                            ->attach('file', fopen($filePath, 'r'), $fileName)
                            ->post('https://ai.mokhtal.xyz/api/voice-to-answer', [
                                'user_type'     => 'new',
                                'first_message' => $isFirstMessage ? 'true' : 'false',
                                'lang'          => $lang,
                                'user_name'     => $userName,
                            ]);

                        if ($resp1->successful()) {
                            $json = $resp1->json();
                            $aiReplyText   = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                            $aiVoiceDataUrl= $json['audio_data'] ?? $json['voice_base64'] ?? null;
                            
                            // Handle suggested title for voice messages too
                            $suggestedTitle = $json['title'] ?? $json['suggested_title'] ?? null;
                            if (!empty($suggestedTitle)
                                && (!$conversation->title || $conversation->title === 'چت جدید')) {
                                $conversation->update(['title' => $suggestedTitle]);
                            }
                        } else {
                            $logHttpError('multipart', $resp1);

                            // 3) تلاش دوم: JSON/base64 (اگر هنوز جواب نگرفتیم)
                            $sendPath = $tmpWav ?: $srcPath;
                            $mimeForJson = $tmpWav ? 'audio/wav' : ($srcMime ?: 'application/octet-stream');
                            $b64 = base64_encode(file_get_contents($sendPath));
                            $dataUrl = "data:{$mimeForJson};base64,{$b64}";

                            $lang = $validated['lang'] ?? 'fa'; // Default to Persian
                            $resp2 = Http::timeout(60)->post('https://ai.mokhtal.xyz/api/voice-to-answer', [
                                'audio_data'    => $dataUrl,
                                'user_type'     => 'new',
                                'first_message' => $isFirstMessage,
                                'lang'          => $lang,
                                'user_name'     => $userName,
                            ]);

                            if ($resp2->successful()) {
                                $json = $resp2->json();
                                $aiReplyText   = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                                $aiVoiceDataUrl= $json['audio_data'] ?? $json['voice_base64'] ?? null;
                                
                                // Handle suggested title for voice messages too
                                $suggestedTitle = $json['title'] ?? $json['suggested_title'] ?? null;
                                if (!empty($suggestedTitle)
                                    && (!$conversation->title || $conversation->title === 'چت جدید')) {
                                    $conversation->update(['title' => $suggestedTitle]);
                                }
                            } else {
                                $logHttpError('json_base64', $resp2);
                                $aiReplyText = 'خطا در سرویس voice-to-answer ('
                                    .$resp1->status().'/'.$resp2->status().')';
                            }
                        }
                    } catch (ConnectionException|RequestException $e) {
                        Log::error('AI voice call exception', [
                            'msg'      => $e->getMessage(),
                            'src_path' => $srcPath,
                            'converted'=> (bool)$tmpWav,
                        ]);
                        $aiReplyText = 'خطا در ارتباط با سرویس هوش مصنوعی.';
                    } finally {
                        if ($tmpWav && file_exists($tmpWav)) @unlink($tmpWav);
                    }
                }
            } else {
                // متن
                $lang = $validated['lang'] ?? 'fa'; // Default to Persian
                
                // Get user's name for personalized response
                $userName = null;
                if ($user) {
                    $userName = trim(($user->name ?? '') . ' ' . ($user->family ?? ''));
                    if (empty($userName)) {
                        $userName = $user->phone ?? null;
                    }
                }
                
                // Log the request being sent
                $messagesCountNow = $conversation->messages()->count();
                \Log::info('Sending to AI API', [
                    'question' => substr($validated['content'] ?? '', 0, 50),
                    'first_message' => $isFirstMessage,
                    'first_message_type' => gettype($isFirstMessage),
                    'messages_count_now' => $messagesCountNow,
                    'user_name' => $userName,
                    'lang' => $lang,
                ]);
                
                $resp = Http::timeout(45)->post('https://ai.mokhtal.xyz/api/ask', [
                    'question' => $validated['content'] ?? '',
                    'user_type' => 'new',
                    'first_message' => $isFirstMessage,
                    'lang' => $lang,
                    'user_name' => $userName, // Send user's name for personalization
                ]);

                if ($resp->successful()) {
                    $json = $resp->json();
                    $aiReplyText = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                    $aiVoiceDataUrl = $json['audio_data'] ?? $json['voice_base64'] ?? null;

                    // Log for debugging
                    \Log::info('AI Response received', [
                        'first_message_sent' => $isFirstMessage,
                        'first_message_received_by_python' => $json['_debug_first_message'] ?? 'N/A',
                        'has_title' => isset($json['title']),
                        'title' => $json['title'] ?? null,
                        'current_conversation_title' => $conversation->title,
                        'full_response_keys' => array_keys($json),
                    ]);

                    // اگر عنوان پیشنهاد داد (هم title و هم suggested_title را چک کن)
                    $suggestedTitle = $json['title'] ?? $json['suggested_title'] ?? null;
                    if (!empty($suggestedTitle)
                        && (!$conversation->title || $conversation->title === 'چت جدید')) {
                        $conversation->update(['title' => $suggestedTitle]);
                        \Log::info('Conversation title updated', ['new_title' => $suggestedTitle]);
                    }
                } else {
                    $errorBody = $resp->body();
                    \Log::warning('AI API failed', ['status' => $resp->status(), 'body' => $errorBody]);
                    // Show actual error for debugging
                    $errorJson = json_decode($errorBody, true);
                    $actualError = $errorJson['error'] ?? 'Unknown error';
                    $aiReplyText = "خطا در سرویس ask ({$resp->status()}): {$actualError}";
                }
            }
        } catch (\Throwable $e) {
            \Log::error('AI API error: ' . $e->getMessage());
            $aiReplyText = 'خطا در ارتباط با سرویس هوش مصنوعی.';
        }

        // 4) ثبت پیام AI (متن)
        $aiMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
            'sender_id' => null,
            'type' => 'text',       // متن همیشه هست
            'content' => $aiReplyText,
        ]);

        // 5) اگر پاسخ API صوت base64 هم داشت → به message_voices بچسبان
        if ($aiVoiceDataUrl) {
            try {
                // هم data URL را پشتیبانی می‌کنیم هم base64 خالص
                [$mime, $raw] = $this->splitDataUrlOrBase64($aiVoiceDataUrl);

                $tmp = tmpfile();
                $meta = stream_get_meta_data($tmp);
                $tmpPath = $meta['uri'];
                file_put_contents($tmpPath, base64_decode($raw));

                $ext = $this->guessAudioExtension($mime);
                $fileName = 'ai_reply.' . $ext;

                $aiMessage
                    ->addMedia($tmpPath)
                    ->usingFileName($fileName)
                    ->withCustomProperties(['source' => 'ai'])
                    ->toMediaCollection('message_voices', 'public');
            } catch (\Throwable $e) {
                \Log::warning('Attach AI voice failed: ' . $e->getMessage());
            }
        }

        try {
            //event(new \App\Domains\Shared\Events\MessageSent($aiMessage));
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
            // ادامه بده؛ پاسخ HTTP را عادی برگردان
        }

        return response()->json([
            'user_message' => $userMessage->fresh(),
            'ai_message' => $aiMessage->fresh(),
            'conversation' => $conversation->fresh(),
        ]);
    }

    private function splitDataUrlOrBase64(string $input): array
    {
        if (str_starts_with($input, 'data:')) {
            // data:[mime];base64,[data]
            if (preg_match('#^data:(.*?);base64,(.*)$#', $input, $m)) {
                return [$m[1] ?: 'audio/webm', $m[2]];
            }
        }
        // base64 خالص
        return ['audio/webm', $input];
    }

    /** یک حدس ساده برای پسوند از روی mime */
    private function guessAudioExtension(string $mime): string
    {
        switch ($mime) {
            case 'audio/wav':
            case 'audio/x-wav':
                return 'wav';
            case 'audio/mpeg':
                return 'mp3';
            case 'audio/ogg':
                return 'ogg';
            case 'audio/webm':
                return 'webm';
            default:
                return 'webm';
        }
    }

    // لیست چت‌ها (فقط active)
    public function index(Request $request)
    {
        $user = $request->user();
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
        $user = $request->user();
        abort_unless($user, 403);

        $request->validate(['title' => 'nullable|string|max:100']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => trim($request->title) ?: 'چت جدید',
            'status' => 'ai',
        ]);

        return response()->json($conversation, 201);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $messages = $conversation->messages()
            ->select('id', 'conversation_id', 'sender_type', 'content', 'created_at')->addSelect([
                // آیا هر نوع مدیایی دارد؟
                'has_media' => DB::raw("EXISTS (
                SELECT 1 FROM media
                WHERE media.model_type = '".addslashes(Message::class)."'
                  AND media.model_id = messages.id
            )"),

                'has_voice' => DB::raw("EXISTS (
                SELECT 1 FROM media
                WHERE media.model_type = '".addslashes(Message::class)."'
                  AND media.model_id = messages.id
                  AND (
                        media.collection_name = 'message_voices'
                     OR media.mime_type LIKE 'audio/%'
                  )
            )"),
            ]) // ← conversation_id رو هم اضافه کردم
            ->orderBy('created_at')
            ->get();
        return response()->json(['data' => $messages]);
    }

    public function referrals(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $referrals = $conversation->referrals()
            ->with(['triggerMessage' => function ($query) {
                $query->select('id', 'conversation_id', 'sender_type', 'content', 'created_at');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Referral $referral) {
                $responseIsPublic = $referral->response_visibility === 'public' && $referral->agent_response;

                $files = $responseIsPublic
                    ? $referral->getMedia('referral_files')->map(fn($media) => [
                        'id'   => $media->id,
                        'name' => $media->file_name,
                        'mime' => $media->mime_type,
                        'size' => $media->size,
                        'url'  => $media->getUrl(),
                    ])->values()->all()
                    : [];

                return [
                    'id'                 => $referral->id,
                    'assigned_role'      => $referral->assigned_role,
                    'status'             => $referral->status,
                    'description'        => $referral->description,
                    'created_at'         => $referral->created_at ? $referral->created_at->toIso8601String() : null,
                    'trigger_message_id' => $referral->trigger_message_id,
                    'trigger_message'    => $referral->triggerMessage ? [
                        'id'          => $referral->triggerMessage->id,
                        'sender_type' => $referral->triggerMessage->sender_type,
                        'content'     => $referral->triggerMessage->content,
                        'created_at'  => $referral->triggerMessage->created_at ? $referral->triggerMessage->created_at->toIso8601String() : null,
                    ] : null,
                    'response'           => $responseIsPublic ? [
                        'text'        => $referral->agent_response,
                        'visibility'  => $referral->response_visibility,
                        'created_at'  => ($referral->updated_at ?? $referral->created_at) ? ($referral->updated_at ?? $referral->created_at)->toIso8601String() : null,
                        'files'       => $files,
                    ] : null,
                ];
            })->values();

        return response()->json(['data' => $referrals]);
    }

    // تغییر عنوان چت
    public function updateTitle(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $request->validate(['title' => 'required|string|max:100']);
        $conversation->update(['title' => $request->title]);

        return response()->json($conversation);
    }

    // حذف چت (soft delete)
    public function destroy(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($user && $conversation->user_id === $user->id, 403);

        $conversation->delete(); // soft delete
        return response()->json([
            'message' => 'چت با موفقیت حذف شد.',
            'conversation_id' => $conversation->id,
        ]);
    }

    public function userReferrals(Request $request)
    {
        $user = $request->user();
        $query = Referral::where('user_id', $user->id)
            ->with(['conversation', 'assignedAgent']) // No triggerMessage to avoid heavy load
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(10); // Specs: cursor
        return response()->json(['data' => $query->items(), 'meta' => ['next_cursor' => $query->nextCursor()]]);
    }

    public function respond(Request $request, Referral $referral)
    {
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
        //event(new \App\Domains\Shared\Events\ReferralResponded($referral)); // Broadcast to user
        return response()->json($referral->fresh()->load(['user', 'conversation']));
    }

    public function handoff(Request $request, Message $message)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',     // ← اختیاری
            'target_role' => 'required|string',         // ← نقش مقصد
        ]);
//        $validated = $request->validate([
//            'reason' => 'nullable|string|max:1000',
//            'target_role' => ['required', 'string', \Illuminate\Validation\Rule::in($allowedRoles)],
//        ]);
        $conversation = \App\Domains\Shared\Models\Conversation::findOrFail($message->conversation_id);
        abort_unless($conversation->user_id === $user->id, 403);

        // جلوگیری از ارجاع تکراری روی همان پیام وقتی هنوز باز است
        $exists = \App\Domains\Shared\Models\Referral::query()
            ->where('trigger_message_id', $message->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => 'برای این پیام قبلاً ارجاع باز وجود دارد.'
            ], 422);
        }
        $referral = Referral::create([
            'conversation_id' => $message->conversation_id,
            'trigger_message_id' => $message->id,
            'user_id' => $user->id,
            'assigned_role' => $validated['target_role'],
            'assigned_agent_id' => null,
            'description' => $validated['reason'] ?? '',
            'status' => 'pending',
        ]);
        try {
            $conversation->update(['status' => 'handoff_pending']);
        } catch (\Throwable $e) {
            \Log::warning('Failed to update conversation status after handoff', ['id' => $conversation->id]);
        }
        //event(new \App\Domains\Shared\Events\ReferralCreated($referral));

        return response()->json(
            $referral->load(['conversation']),
            201
        );
    }

    /**
     * Convert text to speech using Microsoft Edge TTS (FREE) with Persian support
     * Matches the implementation from the reference app.py
     */
    public function textToSpeech(Request $request)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string',
                'chunk_index' => 'nullable|integer|min:0',
                'lang' => 'nullable|string|in:fa,en,ar', // Language code
            ]);

            $text = $validated['text'];
            $chunkIndex = $validated['chunk_index'] ?? 0;

            if (empty($text)) {
                return response()->json([
                    'success' => false,
                    'error' => 'متن خالی است'
                ], 400);
            }

            // Get the path to the Python script
            $scriptPath = base_path('scripts/tts.py');
            
            if (!file_exists($scriptPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسکریپت TTS یافت نشد'
                ], 500);
            }

            // Get language parameter (default to Persian)
            $lang = $validated['lang'] ?? 'fa';
            
            // Prepare input data
            $inputData = json_encode([
                'text' => $text,
                'chunk_index' => $chunkIndex,
                'lang' => $lang,
            ], JSON_UNESCAPED_UNICODE);

            // Execute Python script - try python3 first, fallback to python
            $pythonCommand = $this->findPythonCommand();
            if (!$pythonCommand) {
                return response()->json([
                    'success' => false,
                    'error' => 'Python یافت نشد. لطفا Python را نصب کنید.'
                ], 500);
            }

            $process = new Process([
                $pythonCommand,
                $scriptPath,
            ]);

            $process->setInput($inputData);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                $standardOutput = $process->getOutput();
                
                Log::error('TTS Python script failed', [
                    'exit_code' => $process->getExitCode(),
                    'error_output' => $errorOutput,
                    'standard_output' => $standardOutput,
                    'command' => $pythonCommand . ' ' . $scriptPath,
                ]);

                // Try to parse error output as JSON (Python script writes errors to stderr)
                $errorJson = json_decode($errorOutput, true);
                if ($errorJson && isset($errorJson['error'])) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorJson['error']
                    ], 500);
                }

                // Try to parse standard output as JSON (in case error was written to stdout)
                $outputJson = json_decode($standardOutput, true);
                if ($outputJson && isset($outputJson['error'])) {
                    return response()->json([
                        'success' => false,
                        'error' => $outputJson['error']
                    ], 500);
                }

                // Return more detailed error
                $errorMessage = 'خطا در تولید صوت';
                if ($errorOutput) {
                    $errorMessage .= ' (' . mb_substr(strip_tags($errorOutput), 0, 100) . ')';
                }
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'debug' => [
                        'exit_code' => $process->getExitCode(),
                        'error_preview' => mb_substr($errorOutput, 0, 200),
                    ]
                ], 500);
            }

            $output = $process->getOutput();
            
            // If output is empty, check error output
            if (empty($output)) {
                $errorOutput = $process->getErrorOutput();
                Log::warning('TTS Python script returned empty output', [
                    'error_output' => $errorOutput,
                ]);
                
                $errorJson = json_decode($errorOutput, true);
                if ($errorJson && isset($errorJson['error'])) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorJson['error']
                    ], 500);
                }
                
                return response()->json([
                    'success' => false,
                    'error' => 'اسکریپت TTS خروجی نداشت'
                ], 500);
            }
            
            $result = json_decode($output, true);

            if (!$result) {
                Log::error('TTS Python script output is not valid JSON', [
                    'output' => mb_substr($output, 0, 500),
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'خطا در پردازش پاسخ از اسکریپت TTS',
                    'debug' => [
                        'output_preview' => mb_substr($output, 0, 200),
                    ]
                ], 500);
            }

            // If result contains audio data URL, extract base64 and create a temporary file
            // This avoids browser security issues with data URLs
            if (isset($result['audio']) && str_starts_with($result['audio'], 'data:')) {
                try {
                    // Extract base64 data
                    $matches = [];
                    if (preg_match('/^data:([^;]+);base64,(.+)$/', $result['audio'], $matches)) {
                        $mimeType = $matches[1];
                        $base64Data = $matches[2];
                        $audioBytes = base64_decode($base64Data);
                        
                        // Determine file extension from MIME type
                        $extension = 'webm'; // default
                        if (str_contains($mimeType, 'webm')) {
                            $extension = 'webm';
                        } elseif (str_contains($mimeType, 'mp3') || str_contains($mimeType, 'mpeg')) {
                            $extension = 'mp3';
                        } elseif (str_contains($mimeType, 'ogg')) {
                            $extension = 'ogg';
                        }
                        
                        // Create temporary file
                        $tempFile = tempnam(sys_get_temp_dir(), 'tts_') . '.' . $extension;
                        file_put_contents($tempFile, $audioBytes);
                        
                        // Store temp file path in session or return it
                        // For now, let's return the data URL as-is but ensure it's correct
                        // Actually, let's keep the data URL approach but ensure MIME type is correct
                        $result['audio'] = 'data:' . $mimeType . ';base64,' . $base64Data;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to process audio data URL: ' . $e->getMessage());
                    // Continue with original result
                }
            }

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'داده‌های ورودی نامعتبر است',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('TTS error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'خطا در پردازش درخواست'
            ], 500);
        }
    }

    /**
     * Split text into chunks for streaming TTS
     * Matches the implementation from the reference app.py exactly
     */
    public function textToSpeechChunks(Request $request)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string',
            ]);

            $text = $validated['text'];

            if (empty($text)) {
                return response()->json([
                    'success' => false,
                    'error' => 'متن خالی است'
                ], 400);
            }

            // Get the path to the Python script
            $scriptPath = base_path('scripts/tts_chunks.py');
            
            if (!file_exists($scriptPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسکریپت TTS chunks یافت نشد'
                ], 500);
            }

            // Prepare input data
            $inputData = json_encode([
                'text' => $text,
            ], JSON_UNESCAPED_UNICODE);

            // Execute Python script - try python3 first, fallback to python
            $pythonCommand = $this->findPythonCommand();
            if (!$pythonCommand) {
                return response()->json([
                    'success' => false,
                    'error' => 'Python یافت نشد. لطفا Python را نصب کنید.'
                ], 500);
            }

            $process = new Process([
                $pythonCommand,
                $scriptPath,
            ]);

            $process->setInput($inputData);
            $process->setTimeout(30);
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                Log::error('TTS chunks Python script failed', [
                    'exit_code' => $process->getExitCode(),
                    'error' => $errorOutput,
                ]);

                // Try to parse error output as JSON
                $errorJson = json_decode($errorOutput, true);
                if ($errorJson && isset($errorJson['error'])) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorJson['error']
                    ], 500);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'خطا در پردازش متن'
                ], 500);
            }

            $output = $process->getOutput();
            $result = json_decode($output, true);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'error' => 'خطا در پردازش پاسخ از اسکریپت TTS chunks'
                ], 500);
            }

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'داده‌های ورودی نامعتبر است',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('TTS chunks error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'خطا در پردازش متن'
            ], 500);
        }
    }

    /**
     * Find Python command (python3 or python)
     */
    private function findPythonCommand(): ?string
    {
        $commands = ['python3', 'python'];
        foreach ($commands as $cmd) {
            $process = new Process([$cmd, '--version']);
            $process->run();
            if ($process->isSuccessful()) {
                return $cmd;
            }
        }
        return null;
    }

    /**
     * Clean text for TTS - PHP implementation matching Python version
     */
    private function cleanTextForTTS(string $text): string
    {
        // If text contains HTML, clean it first
        if (strpos($text, '<') !== false && strpos($text, '>') !== false) {
            $text = $this->cleanHtmlForTTS($text);
        }

        // Remove HTML/XML tags
        $text = preg_replace('/<[^>]+>/', '', $text);

        // Remove markdown formatting
        $text = preg_replace('/#{1,6}\s*/', '', $text); // headers
        $text = preg_replace('/\*{1,3}([^*]+)\*{1,3}/', '$1', $text); // bold/italic
        $text = preg_replace('/_{1,3}([^_]+)_{1,3}/', '$1', $text); // underline
        $text = preg_replace('/`{1,3}[^`]*`{1,3}/', '', $text); // code blocks
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text); // links

        // Remove bullet points and numbers
        $text = preg_replace('/^[\s]*[-\*\+•▪▫◦‣⁃]\s+/m', '', $text);
        $text = preg_replace('/^[\s]*\d+[\.\)]\s+/m', '', $text);
        $text = preg_replace('/^[\s]*[a-zA-Z][\.\)]\s+/m', '', $text);

        // Remove emoji and special characters
        $text = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text); // emoji
        $text = preg_replace('/[\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $text); // symbols

        // Remove control characters
        $text = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $text);
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text); // zero-width
        $text = preg_replace('/[\x{2000}-\x{200F}]/u', ' ', $text); // various spaces

        // Remove ZWNJ
        $text = str_replace("\xE2\x80\x8C", '', $text); // ZWNJ in UTF-8
        $text = preg_replace('/\x{200C}/u', '', $text);

        // Normalize Arabic to Persian
        $arabicToPersian = [
            'ك' => 'ک',
            'ي' => 'ی',
            'ى' => 'ی',
            'ة' => 'ه',
            'ؤ' => 'و',
            'إ' => 'ا',
            'أ' => 'ا',
            'ء' => '',
            'ئ' => 'ی'
        ];
        foreach ($arabicToPersian as $arabic => $persian) {
            $text = str_replace($arabic, $persian, $text);
        }

        // Replace English punctuation with Persian equivalents
        $text = str_replace(',', '،', $text);
        $text = str_replace('?', '؟', $text);
        $text = str_replace('!', '.', $text);
        $text = str_replace(';', '،', $text);
        $text = str_replace(':', '،', $text);

        // Remove all other symbols except Persian letters, numbers, spaces, and basic punctuation
        $text = preg_replace('/[^\x{0600}-\x{06FF}\x{06F0}-\x{06F9}\s\.،؟]/u', '', $text);

        // Normalize multiple spaces to single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Clean up punctuation spacing
        $text = preg_replace('/([\.،؟])\s*/', '$1 ', $text);
        $text = preg_replace('/\s+([\.،؟])/', '$1', $text);

        // Remove multiple consecutive punctuation marks
        $text = preg_replace('/([\.،؟]){2,}/', '$1', $text);

        // Normalize line breaks to spaces
        $text = preg_replace('/\n+/', ' ', $text);

        // Final cleanup
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * Clean HTML for TTS
     */
    private function cleanHtmlForTTS(string $htmlText): string
    {
        $text = preg_replace('/<[^>]+>/', ' ', $htmlText);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Split text into chunks for TTS - PHP implementation matching Python version
     */
    private function splitTextForTTS(string $text, int $maxLength = 350): array
    {
        // Clean text first
        $text = $this->cleanTextForTTS($text);

        if (mb_strlen($text) <= $maxLength) {
            return [$text];
        }

        $chunks = [];
        $currentChunk = '';

        // Split by paragraphs first
        $paragraphs = preg_split('/\n\n+|\n+/', $text);

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) {
                continue;
            }

            if (mb_strlen($paragraph) <= $maxLength) {
                // Can add to current chunk
                if (!empty($currentChunk) && mb_strlen($currentChunk) + mb_strlen($paragraph) + 2 <= $maxLength) {
                    $currentChunk .= ' . ' . $paragraph;
                } else {
                    if (!empty($currentChunk)) {
                        $chunks[] = trim($currentChunk);
                    }
                    $currentChunk = $paragraph;
                }
            } else {
                // Paragraph too long, save current chunk
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }

                // Split by sentences
                $sentences = preg_split('/(?<=[.!?؟])\s+/u', $paragraph);

                foreach ($sentences as $sentence) {
                    $sentence = trim($sentence);
                    if (empty($sentence)) {
                        continue;
                    }

                    if (mb_strlen($sentence) <= $maxLength) {
                        if (!empty($currentChunk) && mb_strlen($currentChunk) + mb_strlen($sentence) + 1 <= $maxLength) {
                            $currentChunk .= ' ' . $sentence;
                        } else {
                            if (!empty($currentChunk)) {
                                $chunks[] = trim($currentChunk);
                            }
                            $currentChunk = $sentence;
                        }
                    } else {
                        // Sentence too long, split by commas
                        if (!empty($currentChunk)) {
                            $chunks[] = trim($currentChunk);
                            $currentChunk = '';
                        }

                        $parts = preg_split('/[،,؛;]\s*/u', $sentence);
                        foreach ($parts as $i => $part) {
                            $part = trim($part);
                            if (empty($part)) {
                                continue;
                            }

                            if ($i > 0) {
                                $part = '، ' . $part;
                            }

                            if (mb_strlen($currentChunk) + mb_strlen($part) <= $maxLength) {
                                $currentChunk .= $part;
                            } else {
                                if (!empty($currentChunk)) {
                                    $chunks[] = trim($currentChunk);
                                }
                                $currentChunk = $part;
                            }
                        }
                    }
                }
            }
        }

        // Don't forget the last chunk
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        // Filter out empty chunks
        return array_filter($chunks, fn($chunk) => !empty(trim($chunk)));
    }
}
