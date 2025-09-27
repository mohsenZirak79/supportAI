<?php

namespace App\Domains\SmsPanel\Application\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SmsFailedPermanently
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $to;
    public $message;
    public $error;

    public function __construct(string $to, string $message, string $error)
    {
        $this->to = $to;
        $this->message = $message;
        $this->error = $error;
    }
}
