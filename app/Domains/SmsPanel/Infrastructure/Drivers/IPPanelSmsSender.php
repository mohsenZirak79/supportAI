<?php

namespace App\Domains\SmsPanel\Infrastructure\Drivers;

use App\Domains\SmsPanel\Application\Jobs\SendSmsJob;
use App\Domains\SmsPanel\Domain\Contracts\SmsSenderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class IPPanelSmsSender implements SmsSenderInterface
{
    protected $client;
    protected $apiKey;
    protected $senderNumber;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('sms.drivers.ippanel.api_key');
        $this->senderNumber = config('sms.drivers.ippanel.sender_number');
        $this->baseUrl = config('sms.drivers.ippanel.base_url');
    }

    public function send(string $to, string $message, array $options = []): array
    {
        try {
            $response = $this->client->post($this->baseUrl . '/send', [
                'json' => [
                    'apikey' => $this->apiKey,
                    'pid' => $this->senderNumber,
                    'fnum' => $to,
                    'message' => $message,
                    ...$options,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::channel('sms')->error("IPPanel SMS error: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        try {
            $response = $this->client->post($this->baseUrl . '/bulk', [
                'json' => [
                    'apikey' => $this->apiKey,
                    'pid' => $this->senderNumber,
                    'fnums' => implode(',', $recipients),
                    'message' => $message,
                    ...$options,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::channel('sms')->error("IPPanel Bulk SMS error: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getStatus(string $messageId): array
    {
        try {
            $response = $this->client->get($this->baseUrl . "/status/{$messageId}", [
                'query' => ['apikey' => $this->apiKey],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::channel('sms')->error("IPPanel Status error: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function schedule(string $to, string $message, string $sendAt, array $options = []): array
    {
        SendSmsJob::dispatch($to, $message, $options)
            ->delay(now()->parse($sendAt));

        return ['success' => true, 'message_id' => 'scheduled_' . uniqid()];
    }
}
