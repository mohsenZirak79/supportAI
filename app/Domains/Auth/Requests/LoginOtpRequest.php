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
            'otp.required' => 'کد تأیید را وارد کنید.',
            'otp.digits' => 'کد تأیید باید دقیقاً ۶ رقم باشد.',
            'phone.exists' => 'این شماره تلفن ثبت نشده است.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => preg_replace('/\s+/', '', $this->phone), // Sanitize
        ]);
    }
}
