<?php

namespace App\Domains\AdminPanel\Controllers;

use App\Domains\Role\Models\Role;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class AdminTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // آیا کاربر internal است یا نقش ادمین دارد؟
        $hasInternal = $user->roles()
            ->where('is_internal', 1)
            ->exists();
        $isAdmin = $user->hasRole('ادمین') || $hasInternal;

        $query = Ticket::query()
            ->whereNull('parent_id')
            ->with([
                'sender:id,name',
            ])
            ->withCount(['media as attachments_count' => function ($q) {
                $q->where('collection_name', 'ticket-attachments');
            }])
            ->latest('created_at');

        if (!$isAdmin) {
            // نقش‌های کاربر (idهای int)
            $roleIds = $user->roles()->pluck('id')->all();
            $query->whereIn('department_role_id', $roleIds);
        }

        $tickets = $query->paginate(20);

        return view('admin.tickets', compact('tickets'));
    }

    /**
     * جزئیات یک تیکت والد: پیام ریشه + همهٔ زیر پیام‌ها + فایل‌ها
     * فقط اگر مجاز باشد.
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();

        $root = Ticket::where('id', $id)
            ->whereNull('parent_id')
            ->with(['sender:id,name'])
            ->firstOrFail();

        // مجوز دیدن
        $hasInternal = $user->roles()->where('is_internal', 1)->exists();
        $isAdmin = $user->hasRole('ادمین') || $hasInternal;

        if (!$isAdmin) {
            $roleIds = $user->roles()->pluck('id')->all();
            abort_unless(in_array((int)$root->department_role_id, $roleIds, true), 403);
        }

        // همه پیام‌های این رشته (خود ریشه + زیرمجموعه‌ها)
        $messages = Ticket::where(function ($q) use ($root) {
            $q->where('root_id', $root->id)->orWhere('id', $root->id);
        })
            ->with('media')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (Ticket $m) {
                return [
                    'id'          => $m->id,
                    'message'     => $m->message,
                    'sender_type' => $m->sender_type,     // user | admin | ...
                    'status'      => $m->status,
                    'created_at'  => optional($m->created_at)?->toDateTimeString(),
                    'attachments' => $m->media
                        ->where('collection_name', 'ticket-attachments')
                        ->map(fn($med) => [
                            'id'   => $med->id,
                            'name' => $med->file_name,
                            'mime' => $med->mime_type,
                            'size' => (int) $med->size,
                            'url'  => $med->getFullUrl(),
                        ])->values(),
                ];
            });

        // طبق قانون تو: فقط وقتی وضعیت والد pending باشد، ادمین اجازهٔ پاسخ دارد
        $canReply = ($root->status === 'pending');

        return response()->json([
            'ticket' => [
                'id'                 => $root->id,
                'title'              => $root->title,
                'message'            => $root->message,
                'status'             => $root->status,
                'priority'           => $root->priority,
                'department_role_id' => $root->department_role_id,
                'sender'             => [
                    'id'   => $root->sender?->id,
                    'name' => $root->sender?->name ?? '-',
                ],
                'created_at'         => optional($root->created_at)?->toDateTimeString(),
                'attachments_count'  => $root->attachments_count ?? 0,
            ],
            'messages'   => $messages,
            'can_reply'  => $canReply,
        ]);
    }

    /**
     * پاسخ ادمین/پشتیبان به تیکت والد (فقط وقتی وضعیت والد pending باشد)
     * POST /admin/tickets/{id}/messages
     * fields: message (required), files[] (optional)
     */
    public function reply(Request $request, string $id)
    {
        $user = Auth::user();

        $root = Ticket::where('id', $id)
            ->whereNull('parent_id')
            ->firstOrFail();

        // مجوز پاسخ
        $hasInternal = $user->roles()->where('is_internal', 1)->exists();
        $isAdmin = $user->hasRole('ادمین') || $hasInternal;
        if (!$isAdmin) {
            $roleIds = $user->roles()->pluck('id')->all();
            abort_unless(in_array((int)$root->department_role_id, $roleIds, true), 403);
        }

        // فقط وقتی pending باشد
        abort_unless($root->status === 'pending', 422, 'این تیکت قابل پاسخ نیست.');

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'files.*' => 'file|max:5120', // 5MB
        ]);

        // ساخت پاسخ به‌صورت زیرتیکت
        $reply = new Ticket([
            'id'                 => (string) Str::uuid(),
            'parent_id'          => $root->id,
            'root_id'            => $root->id,
            'title'              => $root->title,
            'message'            => $validated['message'],
            'sender_type'        => 'admin',          // یا 'support' اگر خواستی تمایز بدی
            'sender_id'          => $user->id,
            'status'             => 'answered',       // برای خود پاسخ
            'priority'           => $root->priority,
            'department_role_id' => $root->department_role_id,
        ]);
        $reply->save();

        // فایل‌ها
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $reply->addMedia($file)->toMediaCollection('ticket-attachments', 'public');
            }
        }

        // به‌روزرسانی وضعیت والد (طبق قانونت)
        $root->update(['status' => 'answered']);

        return response()->json([
            'message' => 'پاسخ ثبت شد.',
            'reply'   => [
                'id'          => $reply->id,
                'message'     => $reply->message,
                'sender_type' => $reply->sender_type,
                'created_at'  => optional($reply->created_at)?->toDateTimeString(),
            ],
        ]);
    }
}
