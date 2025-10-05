<?php

namespace App\Domains\AgentPanel\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Models\Ticket;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TicketController extends Controller
{
    // GET /api/v1/agent/tickets
    public function index(Request $request)
    {
        // فقط تیکت‌های اصلی (parent_id = NULL)
        $tickets = Ticket::whereNull('parent_id')
            ->when(auth()->user()->hasRole('support_finance'), fn($q) => $q->where('department', 'support_finance'))
            ->when(auth()->user()->hasRole('support_website'), fn($q) => $q->where('department', 'support_website'))
            // ... سایر نقش‌ها
            ->with(['replies' => fn($q) => $q->latest()])
            ->latest()
            ->cursorPaginate(20);

        return TicketResource::collection($tickets);
    }

    // GET /api/v1/agent/tickets/{rootId}
    public function show(string $rootId)
    {
        $ticket = Ticket::where('id', $rootId)->firstOrFail();
        $replies = Ticket::where('root_id', $rootId)->latest()->get();
        return response()->json([
            'ticket' => new TicketResource($ticket),
            'replies' => TicketMessageResource::collection($replies)
        ]);
    }

    // POST /api/v1/agent/tickets/{rootId}/messages
    public function sendMessage(string $rootId, Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'array|max:10',
            'attachments.*' => 'exists:media,id'
        ]);

        $reply = Ticket::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'parent_id' => $rootId,
            'root_id' => $rootId,
            'title' => Ticket::where('id', $rootId)->value('title'),
            'message' => $validated['message'],
            'sender_type' => 'agent',
            'sender_id' => auth()->id(),
            'status' => 'answered'
        ]);

        if ($request->filled('attachments')) {
            $media = Media::whereIn('id', $request->attachments)->get();
            foreach ($media as $file) {
                $reply->addMedia($file->getPath())->toMediaCollection('ticket-attachments');
            }
        }

        // به‌روزرسانی وضعیت همه پیام‌های thread
        Ticket::where('root_id', $rootId)->update(['status' => 'answered']);

        return new TicketMessageResource($reply->load('media'));
    }
}
