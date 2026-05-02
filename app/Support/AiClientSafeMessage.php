<?php

namespace App\Support;

/**
 * متن ثابت و امن برای نمایش به کاربر وقتی سرویس هوش مصنوعی خطا می‌دهد.
 * جزئیات فقط در لاگ سرور ثبت می‌شود — در پاسخ API/پیام چت تکرار نشود.
 */
final class AiClientSafeMessage
{
    public const FA = 'مشکلی در سرویس پیش آمده است. لطفاً بعداً دوباره تلاش کنید.';

    public static function fa(): string
    {
        return self::FA;
    }
}
