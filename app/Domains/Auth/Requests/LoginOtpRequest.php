<?php

namespace App\Domains\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
//            'phone' => 'required|regex:/^(\+98|0)?9\d{9}$/|exists:users,phone',
            'otp' => 'required|digits:6',
        ];
    }

    public function messages()
    {
        return [
            'otp.digits' => 'OTP must be 6 digits',
            'phone.exists' => 'Phone not registered',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => preg_replace('/\s+/', '', $this->phone), // Sanitize
        ]);
    }
}
