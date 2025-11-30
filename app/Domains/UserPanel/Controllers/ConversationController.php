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

                        $resp1 = Http::asMultipart()
                            ->timeout(60)
                            ->attach('file', fopen($filePath, 'r'), $fileName)
                            ->post('https://ai.mokhtal.xyz/api/voice-to-answer', [
                                'user_type'     => 'new',
                                'first_message' => $isFirstMessage ? 'true' : 'false',
                            ]);

                        if ($resp1->successful()) {
                            $json = $resp1->json();
                            $aiReplyText   = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                            $aiVoiceDataUrl= $json['audio_data'] ?? $json['voice_base64'] ?? null;
                        } else {
                            $logHttpError('multipart', $resp1);

                            // 3) تلاش دوم: JSON/base64 (اگر هنوز جواب نگرفتیم)
                            $sendPath = $tmpWav ?: $srcPath;
                            $mimeForJson = $tmpWav ? 'audio/wav' : ($srcMime ?: 'application/octet-stream');
                            $b64 = base64_encode(file_get_contents($sendPath));
                            $dataUrl = "data:{$mimeForJson};base64,{$b64}";

                            $resp2 = Http::timeout(60)->post('https://ai.mokhtal.xyz/api/voice-to-answer', [
                                'audio_data'    => $dataUrl,
                                'user_type'     => 'new',
                                'first_message' => $isFirstMessage,
                            ]);

                            if ($resp2->successful()) {
                                $json = $resp2->json();
                                $aiReplyText   = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                                $aiVoiceDataUrl= $json['audio_data'] ?? $json['voice_base64'] ?? null;
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
                $resp = Http::timeout(45)->post('https://ai.mokhtal.xyz/api/ask', [
                    'question' => $validated['content'] ?? '',
                    'user_type' => 'new',
                    'first_message' => $isFirstMessage,
                ]);

                if ($resp->successful()) {
                    $json = $resp->json();
                    $aiReplyText = $json['answer'] ?? $json['reply'] ?? $json['text'] ?? '';
                    $aiVoiceDataUrl = $json['audio_data'] ?? $json['voice_base64'] ?? null;

                    // اگر عنوان پیشنهاد داد
                    if (!empty($json['suggested_title'])
                        && (!$conversation->title || $conversation->title === 'چت جدید')) {
                        $conversation->update(['title' => $json['suggested_title']]);
                    }
                } else {
                    $aiReplyText = 'خطا در سرویس ask (' . $resp->status() . ')';
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
        return match ($mime) {
            'audio/wav', 'audio/x-wav' => 'wav',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/webm' => 'webm',
            default => 'webm',
        };
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
                    'created_at'         => optional($referral->created_at)?->toIso8601String(),
                    'trigger_message_id' => $referral->trigger_message_id,
                    'trigger_message'    => $referral->triggerMessage ? [
                        'id'          => $referral->triggerMessage->id,
                        'sender_type' => $referral->triggerMessage->sender_type,
                        'content'     => $referral->triggerMessage->content,
                        'created_at'  => optional($referral->triggerMessage->created_at)?->toIso8601String(),
                    ] : null,
                    'response'           => $responseIsPublic ? [
                        'text'        => $referral->agent_response,
                        'visibility'  => $referral->response_visibility,
                        'created_at'  => optional($referral->updated_at ?? $referral->created_at)?->toIso8601String(),
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
}
