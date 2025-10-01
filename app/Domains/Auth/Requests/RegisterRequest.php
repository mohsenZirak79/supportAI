<?php

namespace App\Domains\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; // یا شرط خاص برای نقش‌ها
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'postal_code' => 'required|string|max:11',
            'national_id' => 'required|string|size:10|unique:users,national_id',
            'birth_date' => 'required|string|date_format:Y-m-d',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
//            'phone' => 'required|regex:/^(\+98|0)?9\d{9}$/|unique:users,phone|max:11',
//            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => 'Phone must be a valid Iranian number (e.g., +989123456789 or 09123456789)',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => preg_replace('/\s+/', '', $this->phone), // Sanitize phone
        ]);
    }
}
