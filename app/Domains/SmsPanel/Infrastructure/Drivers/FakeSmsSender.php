<?php

namespace App\Domains\SmsPanel\Infrastructure\Drivers;

use App\Domains\SmsPanel\Domain\Contracts\SmsSenderInterface;
use Illuminate\Support\Facades\Log;

class FakeSmsSender implements SmsSenderInterface
{
    public function send(string $to, string $message, array $options = []): array
    {
        Log::channel('sms')->info("Fake SMS sent to {$to}: {$message}", $options);
        return [
            'success' => true,
            'message_id' => 'fake_' . uniqid(),
            'to' => $to,
            'message' => $message,
        ];
    }

    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        Log::channel('sms')->info("Fake Bulk SMS sent to " . implode(', ', $recipients) . ": {$message}", $options);
        return [
            'success' => true,
            'message_ids' => array_map(fn($to) => 'fake_' . uniqid(), $recipients),
        ];
    }

    public function getStatus(string $messageId): array
    {
        Log::channel('sms')->info("Fake SMS status checked for {$messageId}");
        return ['success' => true, 'status' => 'delivered'];
    }

    public function schedule(string $to, string $message, string $sendAt, array $options = []): array
    {
        Log::channel('sms')->info("Fake SMS scheduled to {$to} at {$sendAt}: {$message}", $options);
        return ['success' => true, 'message_id' => 'fake_' . uniqid()];
    }
}
