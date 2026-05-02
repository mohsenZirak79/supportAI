<?php

namespace App\Support;

/**
 * پارامترهای اضافه برای POST /api/ask سرویس Python.
 * اگر همان سرویس max_output_tokens را بخواند، سقف خروجی مدل بالا می‌رود و پاسخ وسط جمله قطع نمی‌شود.
 */
final class PythonAiAskExtras
{
    /**
     * @return array<string, int>
     */
    public static function forAskRequest(): array
    {
        $tokens = (int) config('services.python_ai.max_output_tokens', 8192);
        if ($tokens < 256) {
            return [];
        }

        return ['max_output_tokens' => $tokens];
    }
}
