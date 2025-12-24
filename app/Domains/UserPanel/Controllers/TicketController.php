<?php

namespace App\Domains\UserPanel\Controllers;

use App\Domains\Role\Models\Role;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Ticket;
use App\Http\Resources\TicketResource;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class TicketController extends Controller
{
    // GET /api/v1/tickets
//    public function index()
//    {
//        $tickets = Ticket::where('sender_id', auth()->id())
//            ->whereNull('parent_id')
//            ->withCount([
//                'media as attachments_count' => fn($q) =>
//                $q->where('collection_name', 'ticket-attachments')
//            ])
//            ->latest()
//            ->cursorPaginate(20);
//
//        return TicketResource::collection($tickets);
//    }
//    public function index()
//    {
//        $tickets = Ticket::query()
//            ->where('sender_id', auth()->id())
//            ->whereNull('parent_id')
//            // تعداد پیوست‌ها
//            ->withCount([
//                'media as attachments_count' => fn($q) =>
//                $q->where('collection_name', 'ticket-attachments')
//            ])
//            // ⬇️ وضعیت مؤثر = وضعیت آخرین پیام کل رشته (خود ریشه یا زیرمجموعه‌ها)
//            ->select('tickets.*')
//            ->selectSub(function ($q) {
//                $q->from('tickets as t2')
//                    ->select('t2.status')
//                    ->whereColumn('t2.root_id', 'tickets.id')
//                    ->orWhereColumn('t2.id', 'tickets.id')
//                    ->orderByDesc('t2.created_at')
//                    ->limit(1);
//            }, 'effective_status')
//            // برای UI مفید است بدانیم آخرین فرستنده چه نقشی بوده:
//            ->selectSub(function ($q) {
//                $q->from('tickets as t3')
//                    ->select('t3.sender_type')
//                    ->whereColumn('t3.root_id', 'tickets.id')
//                    ->orWhereColumn('t3.id', 'tickets.id')
//                    ->orderByDesc('t3.created_at')
//                    ->limit(1);
//            }, 'last_sender_type')
//            ->latest()
//            ->cursorPaginate(20);
//
//        return TicketResource::collection($tickets);
//    }
    public function index()
    {
        $tickets = Ticket::query()
            ->where('sender_id', auth()->id())
            ->whereNull('parent_id')
            // تعداد پیوست‌ها
            ->withCount([
                'media as attachments_count' => fn($q) =>
                $q->where('collection_name', 'ticket-attachments')
            ])
            // ⬇️ وضعیت مؤثر = وضعیت آخرین پیام کل رشته (خود ریشه یا زیرمجموعه‌ها)
            ->select('tickets.*')
            ->selectSub(function ($q) {
                $q->from('tickets as t2')
                    ->select('t2.status')
                    ->whereColumn('t2.root_id', 'tickets.id')
                    ->orWhereColumn('t2.id', 'tickets.id')
                    ->orderByDesc('t2.created_at')
                    ->limit(1);
            }, 'effective_status')
            // برای UI مفید است بدانیم آخرین فرستنده چه نقشی بوده:
            ->selectSub(function ($q) {
                $q->from('tickets as t3')
                    ->select('t3.sender_type')
                    ->whereColumn('t3.root_id', 'tickets.id')
                    ->orWhereColumn('t3.id', 'tickets.id')
                    ->orderByDesc('t3.created_at')
                    ->limit(1);
            }, 'last_sender_type')
            ->latest()
            ->cursorPaginate(20);

        return TicketResource::collection($tickets);
    }


    // POST /api/v1/tickets
    public function store(Request $request)
    {
        $ipKey = 'ticket_store_ip:' . $request->ip();
        $userKey = 'ticket_store_user:' . auth()->id();

        if (RateLimiter::tooManyAttempts($ipKey, 6)) {
            return response()->json([
                'message' => 'تعداد ثبت تیکت از این آی‌پی بیش از حد مجاز است. لطفاً کمی بعد دوباره تلاش کنید.'
            ], 429);
        }
        if ($userKey && RateLimiter::tooManyAttempts($userKey, 10)) {
            return response()->json([
                'message' => 'در حال حاضر امکان ایجاد تیکت جدید برای شما محدود شده است.'
            ], 429);
        }

        RateLimiter::hit($ipKey, 600);
        if ($userKey) {
            RateLimiter::hit($userKey, 600);
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
            'department' => 'required|exists:roles,id',
            'priority'   => 'nullable|in:low,normal,high,urgent'
        ]);

        $id = (string) Str::uuid(); // ✅ یکبار بساز
        $department = Role::where('id', $validated['department'])->firstOrFail()->name;
        $ticket = Ticket::create([
            'id'          => $id,
            'root_id'     => $id,                 // ✅ مهم: ریشه = خودش
            'title'       => $validated['title'],
            'message'     => $validated['message'],
            'sender_type' => 'user',
            'sender_id'   => auth()->id(),
            'department'  => $department,
            'department_role_id'  => (int)$validated['department'],
            'priority'    => $validated['priority'] ?? 'normal'
        ]);

        // فایل‌ها
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $ticket->addMedia($file)->toMediaCollection('ticket-attachments');
            }
        }

        // ✅ تعداد پیوست هم همراه پاسخ بیاید
        $ticket->loadCount([
            'media as attachments_count' => fn($q) =>
            $q->where('collection_name', 'ticket-attachments')
        ])->load('media');

        return new TicketResource($ticket);
    }


    // POST /api/v1/tickets/{rootId}/messages
    public function sendMessage(string $rootId, Request $request)
    {
        $root = Ticket::where('id', $rootId)->firstOrFail();
        abort_if($root->sender_id !== auth()->id(), 403);

        $validated = $request->validate([
            'message'        => 'required|string',
            'attachments'    => 'array',
            'attachments.*'  => 'exists:media,id',
            'files'          => 'array',
            'files.*'        => 'file|max:5120', // 5MB
        ]);

        $reply = Ticket::create([
            'id'          => (string) Str::uuid(),
            'parent_id'   => $rootId,
            'root_id'     => $rootId,
            'title'       => $root->title,
            'message'     => $validated['message'],
            'sender_type' => 'user',
            'sender_id'   => auth()->id(),
            'status'      => 'pending'
        ]);

        // فایل‌های آپلودی جدید
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $reply->addMedia($file)->toMediaCollection('ticket-attachments');
            }
        }

        // الصاق از Media ID
        if ($request->filled('attachments')) {
            $media = Media::whereIn('id', $request->attachments)->get();
            foreach ($media as $file) {
                $reply->addMedia($file->getPath())->toMediaCollection('ticket-attachments');
            }
        }

        // همه پیام‌های thread به pending
        Ticket::where('root_id', $rootId)->update(['status' => 'pending']);

        // ✅ تعداد پیوست پاسخ
        $reply->loadCount([
            'media as attachments_count' => fn($q) =>
            $q->where('collection_name', 'ticket-attachments')
        ])->load('media');

        return $reply;
        return new TicketMessageResource($reply);
    }


//    public function show(string $rootId)
//    {
//        $rootTicket = Ticket::where('id', $rootId)
//            ->where('sender_type', 'user')
//            ->where('sender_id', auth()->id())
//            ->firstOrFail();
//
//    $messages = Ticket::where('root_id', $rootId)
//        ->with('media')
//            ->orderBy('created_at')
//        ->get()
//        ->map(function ($m) {
//            $attachments = $m->media
//                ->where('collection_name', 'ticket-attachments')
//                ->map(function ($med) {
//                    return [
//                        'id'   => $med->id,
//                        'name' => $med->file_name,
//                        'mime' => $med->mime_type,
//                        'size' => (int) $med->size,
//                        'url'  => $med->getFullUrl(), // ✅ لینک مستقیم
//                    ];
//                })->values();
//
//            return [
//                'id'          => $m->id,
//                'message'     => $m->message,
//                'sender_type' => $m->sender_type, // user|admin|support|...
//                'status'      => $m->status,
//                'created_at'  => $m->created_at,
//                'attachments' => $attachments,
//            ];
//        });
//
//    $last = $messages->last();
//        $supportSenders = ['admin', 'support', 'agent', 'staff', 'operator'];
//    $canUserReply = $last && in_array(strtolower($last['sender_type']), $supportSenders, true);
//
//        $rootTicket->loadCount([
//            'media as attachments_count' => fn($q) =>
//            $q->where('collection_name', 'ticket-attachments')
//        ]);
//
//        return response()->json([
//            'ticket'          => $rootTicket,
//            'messages'        => $messages,
//            'can_user_reply'  => $canUserReply,
//        ]);
//    }
//    public function show(string $rootId)
//    {
//        // فقط تیکتی که متعلق به همین کاربر است (همان شرط فعلی شما)
//        $rootTicket = Ticket::where('id', $rootId)
//            ->where('sender_type', 'user')
//            ->where('sender_id', auth()->id())
//            ->firstOrFail();
//
//        // کل پیام‌های ترد (ریشه + زیرمجموعه‌ها) به صورت مدل (برای دسترسی به last مدل)
//        $messagesModels = Ticket::where(function ($q) use ($rootId) {
//            $q->where('root_id', $rootId)->orWhere('id', $rootId);
//        })
//            ->with('media')           // برای پیوست‌ها
//            ->orderBy('created_at')
//            ->get();
//
//        // آخرین پیام (مدل)
//        $lastModel = $messagesModels->last();
//
//        // به صورت پیش‌فرض اجازه‌ی پاسخ false
//        $canUserReply = false;
//
//        if ($lastModel) {
//            // اگر آخرین پیام توسط پشتیبان ثبت شده باشد (sender_type = admin)
//            if (strtolower($lastModel->sender_type) === 'admin') {
//                // بررسی نقش‌های فرستنده‌ی این پیام:
//                // دارد حداقل یک نقش با allow_ticket = '1' و is_internal = 0؟
//                // (Spatie: model_has_roles => role_id / model_id / model_type)
//                $hasAllowedRole = Role::query()
//                    ->where('allow_ticket', '1')
//                    ->where('is_internal', 0) // یا is_system_role = 0 اگر آن نام را انتخاب کرده‌ای
//                    ->whereHas('users', function ($q) use ($lastModel) {
//                        $q->where('users.id', $lastModel->sender_id);
//                    })
//                    ->exists();
//
//                $canUserReply = $hasAllowedRole;
//            }
//        }
//
//        // Map خروجی پیام‌ها (مثل قبل)
//        $messages = $messagesModels->map(function ($m) {
//            return [
//                'id'          => $m->id,
//                'message'     => $m->message,
//                'sender_type' => $m->sender_type, // user | admin
//                'status'      => $m->status,
//                'created_at'  => $m->created_at,
//                'attachments' => $m->media
//                    ->where('collection_name','ticket-attachments')
//                    ->map(fn($med) => [
//                        'id'   => $med->id,
//                        'name' => $med->file_name,
//                        'mime' => $med->mime_type,
//                        'size' => (int) $med->size,
//                        'url'  => $med->getFullUrl(),
//                    ])->values(),
//            ];
//        });
//
//        // شمارش فایل‌های پیوست برای خود ریشه (مثل قبل)
//        $rootTicket->loadCount([
//            'media as attachments_count' => fn($q) => $q->where('collection_name','ticket-attachments')
//        ]);
//
//        return response()->json([
//            'ticket'         => $rootTicket,
//            'messages'       => $messages,       // شامل پیام ریشه
//            'can_user_reply' => $canUserReply,   // حالا داینامیک از roles
//        ]);
//    }
    public function show(string $rootId)
    {
        // فقط تیکتی که متعلق به همین کاربر است
        $rootTicket = Ticket::where('id', $rootId)
            ->where('sender_type', 'user')
            ->where('sender_id', auth()->id())
            ->firstOrFail();

        // کل پیام‌های ترد (ریشه + زیرمجموعه‌ها)
        $messagesModels = Ticket::where(function ($q) use ($rootId) {
            $q->where('root_id', $rootId)->orWhere('id', $rootId);
        })
            ->with('media')
            ->orderBy('created_at')
            ->get();

        // آخرین پیام
        $lastModel = $messagesModels->last();

        // قانون: اگر آخرین پیام از "کاربر" نباشد و تیکت بسته نباشد، کاربر می‌تواند پاسخ دهد
        $canUserReply = false;
        if ($lastModel) {
            $lastIsUser = strtolower((string)$lastModel->sender_type) === 'user';
            $isClosed   = strtolower((string)$rootTicket->status) === 'closed';
            $canUserReply = !$lastIsUser && !$isClosed;
        }

        // خروجی پیام‌ها
        $messages = $messagesModels->map(function ($m) {
            return [
                'id'          => $m->id,
                'message'     => $m->message,
                'sender_type' => $m->sender_type, // user | admin | ...
                'status'      => $m->status,
                'created_at'  => $m->created_at,
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

        // شمارش پیوست‌های ریشه
        $rootTicket->loadCount([
            'media as attachments_count' => fn($q) => $q->where('collection_name', 'ticket-attachments')
        ]);

        return response()->json([
            'ticket'         => $rootTicket,
            'messages'       => $messages,
            'can_user_reply' => $canUserReply,
        ]);
    }
    public function getDepartments()
    {
        // دریافت نقش‌های پشتیبانی از Spatie
//        $roles = User::role(['support_website', 'support_sales', 'support_admin', 'support_finance'])
//            ->distinct()
//            ->pluck('roles.name');
//
//        $departments = $roles->map(function ($role) {
//            return [
//                'id' => $role,
//                'name' => match($role) {
//                    'support_website' => 'پشتیبانی وب سایت',
//                    'support_sales' => 'پشتیبانی فروش',
//                    'support_admin' => 'پشتیبانی اداری',
//                    'support_finance' => 'پشتیبانی مالی',
//                    default => ucfirst(str_replace('_', ' ', $role))
//                }
//            ];
//        });
        $departments = [
            'support_website' => 'پشتیبانی وب سایت',
            'support_sales' => 'پشتیبانی فروش',
            'support_admin' => 'پشتیبانی اداری',
            'support_finance' => 'پشتیبانی مالی'
        ];

// تبدیل آرایه به فرمت مورد نیاز
        $formattedDepartments = array_map(function ($key, $value) {
            return [
                'id' => $key,
                'name' => $value
            ];
        }, array_keys($departments), $departments);

        return response()->json($formattedDepartments);
    }
}
