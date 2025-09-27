<?php

namespace App\Domains\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => 'required|size:11|numeric',
            'password' => 'required'
        ];
    }
}
