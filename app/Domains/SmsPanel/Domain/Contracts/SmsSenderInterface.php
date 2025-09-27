<?php

namespace App\Domains\SmsPanel\Domain\Contracts;

interface SmsSenderInterface
{
    /**
     * ارسال یک SMS تکی
     */
    public function send(string $to, string $message, array $options = []): array;

    /**
     * ارسال گروهی SMS
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array;

    /**
     * بررسی وضعیت یک SMS
     */
    public function getStatus(string $messageId): array;

    /**
     * زمان‌بندی ارسال SMS
     */
    public function schedule(string $to, string $message, string $sendAt, array $options = []): array;
}
