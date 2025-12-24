<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(40)
            ->get();

        return response()->json([
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->data['title'] ?? null,
                    'body' => $notification->data['body'] ?? null,
                    'title_key' => $notification->data['title_key'] ?? null,
                    'body_key' => $notification->data['body_key'] ?? null,
                    'params' => $notification->data['params'] ?? [],
                    'category' => $notification->data['category'] ?? 'general',
                    'conversation_id' => $notification->data['conversation_id'] ?? null,
                    'ticket_id' => $notification->data['ticket_id'] ?? null,
                    'referral_id' => $notification->data['referral_id'] ?? null,
                    'related_id' => $notification->data['related_id'] ?? null,
                    'meta' => $notification->data['meta'] ?? [],
                    'created_at' => optional($notification->created_at)?->toIso8601String(),
                    'read_at' => optional($notification->read_at)?->toIso8601String(),
                ];
            }),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->each->markAsRead();
        return response()->json(['ok' => true]);
    }
}
