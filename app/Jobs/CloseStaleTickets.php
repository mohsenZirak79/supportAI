<?php

namespace App\Jobs;

use App\Domains\Shared\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CloseStaleTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // پیدا کردن تیکت‌های اصلی که:
        // - وضعیت pending دارند
        // - 24 ساعت از ایجادشان گذشته
        // - هیچ پاسخی از پشتیبان نداشته‌اند
        $staleTickets = Ticket::whereNull('parent_id')
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(24))
            ->whereDoesntHave('replies', function ($query) {
                $query->where('sender_type', 'agent');
            })
            ->pluck('id');

        if ($staleTickets->isNotEmpty()) {
            DB::table('tickets')
                ->whereIn('root_id', $staleTickets)
                ->update(['status' => 'closed']);
        }
    }
}
