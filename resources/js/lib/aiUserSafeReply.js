/**
 * متن پاسخ دستیار را برای نمایش در UI ایمن می‌کند (جزئیات فنی/سرویس بیرونی در DOM نرود).
 * جزئیات واقعی فقط در لاگ سرور بماند.
 *
 * @param {unknown} raw
 * @param {(key: string) => string} t مثلاً از useLanguage
 */
export function aiUserSafeReply(raw, t) {
    const s = raw != null ? String(raw) : '';
    const trimmed = s.trim();
    if (trimmed === '') {
        return t('chat.noAiReply');
    }
    if (
        /خطا در سرویس|خطا در ارتباط|generation failed|quota exceeded|rate limit|rate-limit|googleapis|generativelanguage|exceeded your current quota|billing details|429|503|502|401|You exceeded/i.test(
            s
        )
    ) {
        return t('chat.aiServiceUnavailable');
    }
    return s;
}
