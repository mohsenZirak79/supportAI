<?php

namespace App\Domains\SmsPanel\Domain\Models;

use InvalidArgumentException;

class SmsMessage
{
    public string $to;
    public string $message;
    public array $options;

    public function __construct(string $to, string $message, array $options = [])
    {
        $this->validatePhoneNumber($to);
        $this->to = $to;
        $this->message = $message;
        $this->options = $options;
    }

    protected function validatePhoneNumber(string $phone): void
    {
        // اعتبارسنجی شماره (مثال ساده)
        if (!preg_match('/^\+?\d{10,12}$/', $phone)) {
            throw new InvalidArgumentException("Invalid phone number: {$phone}");
        }
    }
}
