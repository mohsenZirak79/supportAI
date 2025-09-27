<?php

namespace App\Domains\SmsPanel\Application\Jobs;

use App\Domains\SmsPanel\Domain\Contracts\SmsSenderInterface;
use App\Domains\SmsPanel\Domain\Models\SmsMessage;
use App\Domains\SmsPanel\Application\Events\SmsFailedPermanently;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry 3 بار
    public $retryAfter = 60; // فاصله 60 ثانیه بین retryها

    protected $smsMessage;

    public function __construct(string $to, string $message, array $options = [])
    {
        $this->smsMessage = new SmsMessage($to, $message, $options);
        $this->onQueue(config('sms.queue_name', 'sms'));
    }

    public function handle(SmsSenderInterface $sms)
    {
        // لاگ Caller
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = $this->getCallerFromBacktrace($backtrace);

        Log::channel('sms')->info("SMS Job dispatched from: {$caller['file']}@{$caller['line']} in {$caller['function']}", [
            'to' => $this->smsMessage->to,
            'message' => $this->smsMessage->message,
            'attempt' => $this->attempts(),
        ]);

        // ارسال SMS
        $response = $sms->send(
            $this->smsMessage->to,
            $this->smsMessage->message,
            $this->smsMessage->options
        );

        if (!$response['success']) {
            throw new \Exception("SMS send failed: " . ($response['error'] ?? 'Unknown error'));
        }

        Log::channel('sms')->info("SMS sent successfully to {$this->smsMessage->to}", ['response' => $response]);
    }

    public function failed(\Throwable $exception)
    {
        event(new SmsFailedPermanently(
            $this->smsMessage->to,
            $this->smsMessage->message,
            $exception->getMessage()
        ));

        Log::channel('sms')->error("SMS permanently failed after {$this->tries} attempts to {$this->smsMessage->to}", [
            'error' => $exception->getMessage(),
        ]);
    }

    protected function getCallerFromBacktrace(array $backtrace): array
    {
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && strpos($trace['file'], 'vendor') === false && strpos($trace['file'], 'Job.php') === false) {
                return [
                    'file' => $trace['file'] ?? 'Unknown',
                    'line' => $trace['line'] ?? 'Unknown',
                    'function' => $trace['function'] ?? 'Unknown',
                ];
            }
        }
        return ['file' => 'Unknown', 'line' => 'Unknown', 'function' => 'Unknown'];
    }
}
