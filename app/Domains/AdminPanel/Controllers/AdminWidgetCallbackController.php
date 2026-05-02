<?php

namespace App\Domains\AdminPanel\Controllers;

use App\Domains\Shared\Models\WidgetCallbackRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminWidgetCallbackController extends Controller
{
    public function index(Request $request)
    {
        $query = WidgetCallbackRequest::query()
            ->with([
                'user:id,name,phone',
                'conversation:id,title',
                'triggerMessage:id,conversation_id,sender_type,content',
                'handler:id,name',
            ])
            ->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            $allowed = array_keys(WidgetCallbackRequest::statusLabels());
            if (in_array($status, $allowed, true)) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->string('search')->toString()) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                })->orWhereHas('conversation', function ($cq) use ($term) {
                    $cq->where('title', 'like', $term);
                });
            });
        }

        $requests = $query->paginate(25)->withQueryString();

        return view('admin.callbacks', [
            'requests' => $requests,
            'statusLabels' => WidgetCallbackRequest::statusLabels(),
        ]);
    }

    public function update(Request $request, WidgetCallbackRequest $widgetCallbackRequest)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,contacted,declined',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $updates = [
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
        ];

        if ($validated['status'] === WidgetCallbackRequest::STATUS_PENDING) {
            $updates['handled_by'] = null;
            $updates['contacted_at'] = null;
        } else {
            $updates['handled_by'] = $widgetCallbackRequest->handled_by ?: $user->id;
            if ($validated['status'] === WidgetCallbackRequest::STATUS_CONTACTED) {
                $updates['contacted_at'] = $widgetCallbackRequest->contacted_at ?? now();
            }
        }

        $widgetCallbackRequest->update($updates);

        return redirect()->back()->with('success', 'وضعیت به‌روزرسانی شد.');
    }
}
