<?php

namespace App\Domains\SmsPanel\Facades;

use Illuminate\Support\Facades\Facade;

class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sms'; // دقیقا همون alias
    }
}
