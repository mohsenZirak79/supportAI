<?php

namespace App\Domains\UserPanel\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Ticket;
use App\Http\Resources\TicketResource;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
            'department' => 'required|in:support_finance,support_website,support_sales,support_admin',
            'priority'   => 'nullable|in:low,normal,high,urgent'
        ]);

        $id = (string) Str::uuid(); // ✅ یکبار بساز

        $ticket = Ticket::create([
            'id'          => $id,
            'root_id'     => $id,                 // ✅ مهم: ریشه = خودش
            'title'       => $validated['title'],
            'message'     => $validated['message'],
            'sender_type' => 'user',
            'sender_id'   => auth()->id(),
            'department'  => $validated['department'],
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
    public function show(string $rootId)
    {
        $rootTicket = Ticket::where('id', $rootId)
            ->where('sender_type', 'user')
            ->where('sender_id', auth()->id())
            ->firstOrFail();

        // ⬇️ هم خود ریشه و هم زیرمجموعه‌ها
        $messages = Ticket::where(function($q) use ($rootId) {
            $q->where('root_id', $rootId)->orWhere('id', $rootId);
        })
            ->with('media')
            ->orderBy('created_at')
            ->get()
            ->map(function ($m) {
                return [
                    'id'          => $m->id,
                    'message'     => $m->message,
                    'sender_type' => $m->sender_type, // user | admin | support | ...
                    'status'      => $m->status,
                    'created_at'  => $m->created_at,
                    'attachments' => $m->media
                        ->where('collection_name','ticket-attachments')
                        ->map(fn($med)=>[
                            'id'=>$med->id,'name'=>$med->file_name,'mime'=>$med->mime_type,
                            'size'=>(int)$med->size,'url'=>$med->getFullUrl()
                        ])->values(),
                ];
            });

        $last = $messages->last();
        $canUserReply = $last && in_array(strtolower($last['sender_type']), ['admin','support','agent','staff','operator'], true);

        $rootTicket->loadCount(['media as attachments_count'=>fn($q)=>$q->where('collection_name','ticket-attachments')]);

        return response()->json([
            'ticket'         => $rootTicket,
            'messages'       => $messages,       // شامل پیام ریشه است
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
