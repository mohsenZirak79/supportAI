/**
 * با خطا یا هشدار در اپ، رویداد supportai:offer-help را می‌فرستد تا ویجت چت شناور
 * پیشنهاد کمک را با انیمیشن و (در صورت امکان) صدا نشان دهد.
 */

const EVENT_NAME = 'supportai:offer-help';

const COOLDOWN_MS = 90_000;

let lastFiredAt = 0;
let axiosHooked = false;
let hooksInstalled = false;

function shouldThrottle(detail) {
    if (detail?.force) return false;
    const now = Date.now();
    return now - lastFiredAt < COOLDOWN_MS;
}

/**
 * @param {{ source?: string, message?: string, silentSound?: boolean, force?: boolean }} [detail]
 */
export function notifySupportAiOfferHelp(detail = {}) {
    if (typeof document === 'undefined') return;
    if (shouldThrottle(detail)) return;
    lastFiredAt = Date.now();
    try {
        document.dispatchEvent(
            new CustomEvent(EVENT_NAME, {
                bubbles: true,
                cancelable: true,
                detail: { ...detail, ts: Date.now() },
            })
        );
    } catch {
        /* ignore */
    }
}

if (typeof window !== 'undefined') {
    window.supportAiOfferHelp = notifySupportAiOfferHelp;
}

function shouldSkipAxiosUrl(url) {
    if (!url || typeof url !== 'string') return false;
    const u = url.toLowerCase();
    if (u.includes('guest-floating-chat')) return true;
    if (u.includes('/auth/refresh')) return true;
    if (u.includes('widget-callback-lock')) return true;
    return false;
}

export function installAxiosErrorInterceptor() {
    if (axiosHooked || typeof window === 'undefined') return;
    const ax = window.axios;
    if (!ax || typeof ax.interceptors?.response?.use !== 'function') return;
    axiosHooked = true;
    ax.interceptors.response.use(
        (r) => r,
        (err) => {
            try {
                const url = err?.config?.url || '';
                const status = err?.response?.status;
                if (!shouldSkipAxiosUrl(url) && (status == null || status >= 400)) {
                    notifySupportAiOfferHelp({ source: 'axios', message: String(status || 'network'), silentSound: false });
                }
            } catch {
                /* ignore */
            }
            return Promise.reject(err);
        }
    );
}

export function patchToastLikeObject(obj, label) {
    if (!obj || obj.__supportAiToastPatched) return;
    const methods = ['error', 'warning'];
    let patched = false;
    methods.forEach((m) => {
        const orig = obj[m];
        if (typeof orig !== 'function') return;
        patched = true;
        obj[m] = function (...args) {
            try {
                const msg = typeof args[0] === 'string' ? args[0] : '';
                notifySupportAiOfferHelp({ source: label || 'toast', toastMethod: m, message: msg });
            } catch {
                /* ignore */
            }
            return orig.apply(this, args);
        };
    });
    if (patched) {
        obj.__supportAiToastPatched = true;
    }
}

export function tryPatchLateToasts() {
    patchToastLikeObject(window.toast, 'window.toast');
    patchToastLikeObject(window.__toast, 'window.__toast');
}

/**
 * یک‌بار بعد از mount ویجت؛ toastهای سراسری و axios را وصل می‌کند.
 */
export function installSupportAiErrorHooks() {
    if (typeof window === 'undefined' || hooksInstalled) return;
    hooksInstalled = true;

    tryPatchLateToasts();

    let ticks = 0;
    const id = window.setInterval(() => {
        tryPatchLateToasts();
        if (++ticks > 30) {
            window.clearInterval(id);
        }
    }, 200);
}
