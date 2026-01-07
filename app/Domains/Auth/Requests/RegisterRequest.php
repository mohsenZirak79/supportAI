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
            'birth_date' => 'required',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
//            'phone' => 'required|regex:/^(\+98|0)?9\d{9}$/|unique:users,phone|max:11',
            'password' => 'required|string|min:8|confirmed',
            'captcha' => 'required|captcha',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'وارد کردن نام الزامی است.',
            'name.string' => 'نام باید یک رشته باشد.',
            'name.max' => 'نام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',

            'family.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'family.string' => 'نام خانوادگی باید یک رشته باشد.',
            'family.max' => 'نام خانوادگی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',

            'postal_code.required' => 'کد پستی الزامی است.',
            'postal_code.string' => 'کد پستی باید رشته‌ای از اعداد باشد.',
            'postal_code.max' => 'کد پستی نمی‌تواند بیش از ۱۱ رقم باشد.',

            'national_id.required' => 'کد ملی الزامی است.',
            'national_id.string' => 'کد ملی باید از نوع رشته باشد.',
            'national_id.size' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'national_id.unique' => 'کد ملی وارد شده قبلاً ثبت شده است.',

            'birth_date.required' => 'تاریخ تولد الزامی است.',
            'birth_date.string' => 'تاریخ تولد باید رشته‌ای معتبر باشد.',
            'birth_date.date_format' => 'فرمت تاریخ تولد باید به صورت YYYY-MM-DD باشد.',

            'address.required' => 'وارد کردن آدرس الزامی است.',
            'address.string' => 'آدرس باید به صورت رشته‌ای باشد.',
            'address.max' => 'آدرس نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',

            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'email.max' => 'ایمیل نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',

            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'شماره موبایل باید معتبر و ایرانی باشد (مثلاً: +989123456789 یا 09123456789).',
            'phone.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'phone.max' => 'شماره موبایل نمی‌تواند بیش از ۱۱ رقم باشد.',

            'password.required' => 'رمز عبور الزامی است.',
            'password.string' => 'رمز عبور باید از نوع رشته باشد.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تأیید رمز عبور با رمز عبور وارد شده مطابقت ندارد.',

            'captcha.required' => 'کد کپچا الزامی است.',
            'captcha.captcha' => 'کد کپچا نادرست است.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => preg_replace('/\s+/', '', $this->phone), // حذف فاصله‌ها از شماره موبایل
        ]);
    }

}
