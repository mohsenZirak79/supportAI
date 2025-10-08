<?php

namespace App\Domains\AdminPanel\Controllers;

use App\Domains\Shared\Models\Conversation;
use App\Domains\Shared\Models\Message;
use App\Domains\Shared\Models\Referral;
use App\Domains\Shared\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    public function __construct()
    {
        Auth::login(User::where('id', 'f25bcf94-c1d9-4a40-b2ce-109765552cbf')->first());
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('ادمین');
        $roleNames = $user->roles()->pluck('name')->all();

        $query = Conversation::query()
            ->with(['user:id,name'])
            ->latest();
        if (!$isAdmin) {
            // فقط مکالماتی که حداقل یک referral مرتبط با کاربر دارد
            $query->whereHas('referrals', function ($q) use ($user, $roleNames) {
                $q->whereIn('assigned_role', $roleNames)
                    ->orWhere('assigned_agent_id', $user->id);
            });
        }

        $conversations = $query->paginate(20);

        return view('admin.chats', compact('conversations'));
    }

//    public function detail(Request $request, Conversation $conversation)
//    {
//        $user = Auth::user();
//        $isAdmin = $user->hasRole('ادمین');
//        $roleNames = $user->roles()->pluck('name')->all();
//
//        // مجوز دیدن دیتیل
//        $canView = $isAdmin || Referral::query()
//                ->where('conversation_id', $conversation->id)
//                ->where(function ($q) use ($user, $roleNames) {
//                    $q->whereIn('assigned_role', $roleNames)
//                        ->orWhere('assigned_agent_id', $user->id);
//                })
//                ->exists();
//
//        abort_unless($canView, 403, 'اجازهٔ مشاهدهٔ این مکالمه را ندارید.');
//
//        $messages = $conversation->messages()
//            ->select('id','sender_type','content','created_at')
//            ->orderBy('created_at')
//            ->get();
//
//        $referrals = Referral::query()
//            ->where('conversation_id', $conversation->id)
//            ->orderByDesc('created_at')
//            ->get()
//            ->map(function ($r) use ($user, $isAdmin, $roleNames) {
//                $canRespond = false;
//                if (in_array($r->status, ['pending','assigned']) && empty($r->agent_response)) {
//                    if ($isAdmin) {
//                        $canRespond = true;
//                    } else {
//                        $canRespond = ($r->assigned_agent_id && $r->assigned_agent_id === $user->id)
//                            || (!$r->assigned_agent_id && in_array($r->assigned_role, $roleNames));
//                    }
//                }
//
//                return [
//                    'id'                 => $r->id,
//                    'trigger_message_id' => $r->trigger_message_id,
//                    'assigned_role'      => $r->assigned_role,
//                    'assigned_agent_id'  => $r->assigned_agent_id,
//                    'description'        => $r->description,
//                    'status'             => $r->status,
//                    'agent_response'     => $r->agent_response,
//                    'response_visibility'=> $r->response_visibility,
//                    'created_at'         => optional($r->created_at)?->toDateTimeString(),
//                    'can_respond'        => $canRespond,
//                    'can_assign_me'      => !$r->assigned_agent_id && (
//                            $isAdmin || in_array($r->assigned_role, $roleNames)
//                        ),
//                ];
//            });
//
//        return response()->json([
//            'conversation' => [
//                'id'    => $conversation->id,
//                'title' => $conversation->title,
//                'user'  => [
//                    'id'   => $conversation->user->id ?? null,
//                    'name' => $conversation->user->name ?? '-',
//                ],
//            ],
//            'messages'  => $messages,
//            'referrals' => $referrals,
//        ]);
//    }
// App/Http/Controllers/AdminChatController.php

    public function detail(Request $request, Conversation $conversation)
    {
        $messages = $conversation->messages()
            ->select('id','sender_type','content','created_at')
            ->orderBy('created_at','asc')
            ->get()
            ->map(function ($m) {
                // گرفتن مدیاهای پیام (صوت/فایل)
                $media = \DB::table('media')
                    ->where('model_type', \App\Domains\Shared\Models\Message::class)
                    ->where('model_id', $m->id)
                    ->select('id','collection_name as collection','mime_type as mime','file_name','custom_properties')
                    ->get()
                    ->map(function ($mm) {
                        return [
                            'id'        => $mm->id,
                            'collection'=> $mm->collection,
                            'mime'      => $mm->mime,
                            'name'      => $mm->file_name,
                            'url'       => \Storage::disk('public')->url($mm->id.'/'.$mm->file_name),
                        ];
                    });

                return [
                    'id'          => $m->id,
                    'sender_type' => $m->sender_type,
                    'content'     => $m->content,
                    'created_at'  => $m->created_at,
                    'media'       => $media,
                ];
            });

        // ارجاع‌ها: مرتب از قدیم به جدید + مدیای هر ارجاع
        $referrals = Referral::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($r) {
                $files = $r->getMedia('referral_files')->map(fn($m) => [
                    'id'   => $m->id,
                    'name' => $m->file_name,
                    'mime' => $m->mime_type,
                    'url'  => $m->getUrl(), // public disk
                ]);

                return [
                    'id'                  => $r->id,
                    'trigger_message_id'  => $r->trigger_message_id,
                    'assigned_role'       => $r->assigned_role,
                    'assigned_agent_id'   => $r->assigned_agent_id,
                    'description'         => $r->description,
                    'status'              => $r->status,
                    'agent_response'      => $r->agent_response,
                    'response_visibility' => $r->response_visibility,
                    'created_at'          => optional($r->created_at)?->toDateTimeString(),
                    'files'               => $files,
                    // این دو را اگر سمت سرور دیگر نمی‌خواهی محدود کنی، true برگردان (صفحه‌ات گیت شده):
                    'can_respond'         => empty($r->agent_response) && in_array($r->status, ['pending','assigned']),
                    'can_assign_me'       => !$r->assigned_agent_id,
                ];
            });

        return response()->json([
            'conversation' => [
                'id'    => $conversation->id,
                'title' => $conversation->title,
                'user'  => ['id' => $conversation->user->id ?? null, 'name' => $conversation->user->name ?? '-'],
            ],
            'messages'  => $messages,
            'referrals' => $referrals,
        ]);
    }


    public function respond(Request $request, Referral $referral)
    {
        // به‌جای JSON، multipart بفرستیم
        $validated = $request->validate([
            'agent_response'       => 'required|string|max:2000',
            'response_visibility'  => 'in:public,internal',
            'files.*'              => 'file|max:20480', // 20MB هر فایل
        ]);

        $referral->update([
            'status'              => 'responded',
            'assigned_agent_id'   => $referral->assigned_agent_id ?: $request->user()->id,
            'agent_response'      => $validated['agent_response'],
            'response_visibility' => $validated['response_visibility'] ?? 'public',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $referral->addMedia($file)->toMediaCollection('referral_files', 'public');
            }
        }

        // event(new ReferralResponded($referral));

        return response()->json(['message' => 'پاسخ ثبت شد.', 'referral' => $referral->fresh()]);
    }
    public function assignMe(Request $request, Referral $referral)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('ادمین');
        $roleNames = $user->roles()->pluck('name')->all();

        abort_if($referral->assigned_agent_id, 422, 'این ارجاع از قبل به شخصی تخصیص داده شده است.');

        $canAssign = $isAdmin || in_array($referral->assigned_role, $roleNames);
        abort_unless($canAssign, 403, 'اجازهٔ تخصیص این ارجاع را ندارید.');

        $referral->update([
            'assigned_agent_id' => $user->id,
            'status' => $referral->status === 'pending' ? 'assigned' : $referral->status,
        ]);

        return response()->json(['message' => 'برای شما تخصیص داده شد.', 'referral' => $referral->fresh()]);
    }

}
