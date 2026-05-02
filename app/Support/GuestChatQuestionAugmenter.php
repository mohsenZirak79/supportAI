<?php

namespace App\Support;

/**
 * برای چت مهمان ویجت: متن سؤال را با خطاهای واقعی صفحه و قوانین ثبت‌نام این سامانه غنی می‌کند
 * تا مدل به OTP یا سناریوهای نامرتبط حدس نزند.
 */
final class GuestChatQuestionAugmenter
{
    /**
     * @param  array<string, mixed>|null  $pageContext
     */
    public static function embed(string $question, ?array $pageContext, int $maxTotalChars = 4000): string
    {
        $question = trim($question);
        if ($pageContext === null || $pageContext === []) {
            return $question;
        }

        $prefix = self::buildPrefix($pageContext);
        if ($prefix === '') {
            return $question;
        }

        $combined = $prefix."\n\n---\n".$question;
        if (mb_strlen($combined) > $maxTotalChars) {
            return mb_substr($combined, 0, $maxTotalChars);
        }

        return $combined;
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private static function buildPrefix(array $ctx): string
    {
        $lines = [];
        $pageKind = (string) ($ctx['pageKind'] ?? '');
        $pathname = mb_strtolower((string) ($ctx['pathname'] ?? ''));

        if (! empty($ctx['proactiveTrigger'])) {
            $lines[] = '[زمینهٔ رویداد] کاربر پس از بروز خطا یا هشدار در برنامه، روی «باز کردن چت» در پیشنهاد کمک کلیک کرده و هنوز متنی از خودش ننوشته است؛ فقط زمینهٔ صفحه و در صورت وجود جزئیات رویداد کمک را دارد.';
            $src = isset($ctx['offerHelpSource']) && is_string($ctx['offerHelpSource']) ? trim($ctx['offerHelpSource']) : '';
            if ($src !== '') {
                $lines[] = 'منبع تشخیص خطا در اپ: '.$src;
            }
            $mh = isset($ctx['offerHelpMessage']) && is_string($ctx['offerHelpMessage']) ? trim($ctx['offerHelpMessage']) : '';
            if ($mh !== '') {
                $lines[] = 'پیام/کد همراه رویداد کمک: '.mb_substr($mh, 0, 400);
            }
            $tm = isset($ctx['offerHelpToastMethod']) && is_string($ctx['offerHelpToastMethod']) ? trim($ctx['offerHelpToastMethod']) : '';
            if ($tm !== '') {
                $lines[] = 'نوع toast: '.$tm;
            }
        }

        $kid = $ctx['kishDemoScenarioId'] ?? null;
        if ($kid !== null && $kid !== '' && $kid !== 0) {
            $lines[] = '[پرزنت — فرم نمونهٔ سفر کیش — سناریوی شمارهٔ '.(string) $kid.']';
            $lines[] = 'این دادهٔ نمایشی است؛ کاربر برای دمو خطا را عمداً فعال کرده. پاسخ باید چندمرحله‌ای، با فرم/کد فرضی مشخص، و قابل ارائه در جلسهٔ رسمی باشد — نه یک جملهٔ کلی.';
            $n = isset($ctx['kishDemoNarrative']) && is_string($ctx['kishDemoNarrative']) ? trim($ctx['kishDemoNarrative']) : '';
            if ($n !== '') {
                $lines[] = 'شرح سناریو: '.mb_substr($n, 0, 800);
            }
            $h = isset($ctx['kishDemoResolutionHints']) && is_string($ctx['kishDemoResolutionHints']) ? trim($ctx['kishDemoResolutionHints']) : '';
            if ($h !== '') {
                $lines[] = 'ساختار پیشنهادی پاسخ: '.mb_substr($h, 0, 500);
            }
        }

        $errs = $ctx['serverValidationErrors'] ?? null;
        if (is_array($errs) && $errs !== []) {
            $lines[] = '[زمینهٔ صفحه — فقط بر اساس موارد زیر جواب بده؛ به سناریوهای دیگر حدس نزن]';
            $lines[] = 'پیام‌های خطا یا اعتبارسنجی که همین الان روی صفحه به کاربر نشان داده شده:';
            foreach (array_slice($errs, 0, 14) as $e) {
                if (is_string($e) && trim($e) !== '') {
                    $lines[] = '• '.trim($e);
                }
            }
        } else {
            $ve = $ctx['visibleErrors'] ?? null;
            if (is_array($ve) && $ve !== []) {
                $texts = [];
                foreach (array_slice($ve, 0, 10) as $row) {
                    if (is_array($row) && isset($row['text']) && is_string($row['text']) && trim($row['text']) !== '') {
                        $t = trim($row['text']);
                        if (mb_strlen($t) > 2) {
                            $texts[] = $t;
                        }
                    }
                }
                $texts = array_values(array_unique($texts));
                if ($texts !== []) {
                    $lines[] = '[زمینهٔ صفحه — فقط بر اساس موارد زیر جواب بده]';
                    $lines[] = 'متن خطا/هشدار دیده‌شده روی صفحه:';
                    foreach ($texts as $t) {
                        $lines[] = '• '.$t;
                    }
                }
            }
        }

        $fi = $ctx['fieldIssues'] ?? null;
        if (is_array($fi) && $fi !== [] && count($lines) === 0) {
            $lines[] = '[زمینهٔ صفحه — فیلدهای نامعتبر]';
            foreach (array_slice($fi, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $name = isset($row['name']) && is_string($row['name']) ? $row['name'] : '';
                $vm = isset($row['validationMessage']) && is_string($row['validationMessage']) ? trim($row['validationMessage']) : '';
                if ($name !== '' || $vm !== '') {
                    $lines[] = '• '.($name !== '' ? "فیلد {$name}: " : '').($vm !== '' ? $vm : 'نامعتبر');
                }
            }
        }

        if ($pageKind === 'register' || str_contains($pathname, 'register')) {
            $lines[] = 'قانون این سامانه (الزام رعایت در پاسخ): فرم ثبت‌نام این نسخه پس از زدن دکمهٔ ثبت‌نام، مرحلهٔ OTP یا کد تأیید پیامکی ندارد. دربارهٔ «منتظر بمانید تا OTP بیاید»، «درخواست مجدد کد»، یا تأیید شماره با SMS صحبت نکن مگر کاربر خودش بگوید کد نمی‌گیرد یا سؤالش صریحاً دربارهٔ OTP است.';
        }

        if ($lines === []) {
            return '';
        }

        return implode("\n", $lines);
    }
}
