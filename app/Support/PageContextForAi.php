<?php

namespace App\Support;

/**
 * Sanitize client-provided page context before sending to the Python AI service.
 */
final class PageContextForAi
{
    public const MAX_JSON_BYTES = 12000;

    /**
     * @param  array<string, mixed>|null  $pageContext
     * @return array<string, mixed>|null
     */
    public static function sanitize(?array $pageContext): ?array
    {
        if ($pageContext === null || $pageContext === []) {
            return null;
        }

        $clean = self::walk($pageContext, 0);
        if ($clean === null || $clean === []) {
            return null;
        }

        $enc = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if ($enc === false || strlen($enc) > self::MAX_JSON_BYTES) {
            return null;
        }

        return $clean;
    }

    /**
     * @param  mixed  $v
     * @return mixed
     */
    private static function walk($v, int $depth)
    {
        if ($depth > 8) {
            return null;
        }
        if ($v === null || is_bool($v)) {
            return $v;
        }
        if (is_int($v) || is_float($v)) {
            return $v;
        }
        if (is_string($v)) {
            $t = trim($v);

            return $t === '' ? null : mb_substr($t, 0, 800);
        }
        if (! is_array($v)) {
            return null;
        }

        $out = [];
        $i = 0;
        foreach ($v as $k => $item) {
            if ($i++ >= 60) {
                break;
            }
            $key = is_string($k) ? preg_replace('/[^\w\.\-]/', '', $k) : (string) $k;
            if ($key === '') {
                $key = 'k'.$i;
            }
            $key = mb_substr($key, 0, 64);
            $w = self::walk($item, $depth + 1);
            if ($w !== null) {
                $out[$key] = $w;
            }
        }

        return $out === [] ? null : $out;
    }
}
