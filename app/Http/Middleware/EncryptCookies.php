<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;

class EncryptCookies extends BaseEncryptCookies
{
    protected $except = [
        'jwt', // ⬅️ حتماً اینجا باشه
    ];
}
