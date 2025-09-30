<?php

namespace App\Domains\UserPanel\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Ticket;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TicketController extends Controller
{
    // GET /api/v1/tickets
    public function index()
    {
        $tickets = Ticket::where('sender_id', auth()->id())
            ->whereNull('parent_id')
            ->with(['replies' => fn($q) => $q->orderBy('created_at')])
            ->latest()
            ->cursorPaginate(20);

        return TicketResource::collection($tickets);
    }

    // POST /api/v1/tickets
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'department' => 'required|in:support_finance,support_website,support_sales,support_admin',
            'priority' => 'nullable|in:low,normal,high,urgent'
        ]);

        $ticket = Ticket::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'root_id' => \Illuminate\Support\Str::uuid(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'department' => $validated['department'],
            'priority' => $validated['priority'] ?? 'normal'
        ]);

        if ($request->filled('attachments')) {
            $media = Media::whereIn('id', $request->attachments)->get();
            foreach ($media as $file) {
                $ticket->addMedia($file->getPath())->toMediaCollection('ticket-attachments');
            }
        }

        return new TicketResource($ticket->load('media'));
    }

    // POST /api/v1/tickets/{rootId}/messages
    public function sendMessage(string $rootId, Request $request)
    {
        $root = Ticket::where('id', $rootId)->firstOrFail();
        abort_if($root->sender_id !== auth()->id(), 403);

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'array|max:10',
            'attachments.*' => 'exists:media,id'
        ]);

        $reply = Ticket::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'parent_id' => $rootId,
            'root_id' => $rootId,
            'title' => $root->title,
            'message' => $validated['message'],
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'status' => 'pending' // وضعیت thread به pending تغییر می‌کند
        ]);

        if ($request->filled('attachments')) {
            $media = Media::whereIn('id', $request->attachments)->get();
            foreach ($media as $file) {
                $reply->addMedia($file->getPath())->toMediaCollection('ticket-attachments');
            }
        }

        // به‌روزرسانی وضعیت همه پیام‌های thread
        Ticket::where('root_id', $rootId)->update(['status' => 'pending']);

        return new TicketMessageResource($reply->load('media'));
    }

    public function show(string $rootId)
    {
        // بررسی اینکه تیکت متعلق به کاربر است
        $rootTicket = Ticket::where('id', $rootId)
            ->where('sender_type', 'user')
            ->where('sender_id', auth()->id())
            ->firstOrFail();

        // دریافت تمام پیام‌های thread
        $messages = Ticket::where('root_id', $rootId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'ticket' => $rootTicket,
            'messages' => $messages
        ]);
    }
}
