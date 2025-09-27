<?php

namespace App\Domains\SmsPanel\Providers;

use App\Domains\SmsPanel\Domain\Contracts\SmsSenderInterface;
use App\Domains\SmsPanel\Infrastructure\Drivers\FakeSmsSender;
use App\Domains\SmsPanel\Infrastructure\Drivers\IPPanelSmsSender;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // bind به Interface
        $this->app->singleton(SmsSenderInterface::class, function ($app) {
            $driver = config('sms.driver', 'fake');

            return match ($driver) {
                'ippanel' => new IPPanelSmsSender(),
                'fake'    => new FakeSmsSender(),
                default   => throw new \InvalidArgumentException("SMS driver {$driver} not supported"),
            };
        });

        // alias هم به اسم ساده‌ی "sms"
        $this->app->alias(SmsSenderInterface::class, 'sms');
    }

    public function boot()
    {
        $this->app['events']->listen(
            \App\Domains\SmsPanel\Application\Events\SmsFailedPermanently::class,
            \App\Domains\SmsPanel\Application\Listeners\LogFailedSms::class
        );
    }
}
