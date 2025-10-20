// resources/js/lib/http.js
const API_BASE = '/api/v1';

let isRefreshing = false;
let refreshPromise = null;
let pendingQueue = []; // [{resolve, reject, path, opts}]

async function refreshToken() {
    if (isRefreshing) return refreshPromise;
    isRefreshing = true;
    refreshPromise = fetch(`${API_BASE}/auth/refresh`, {
        method: 'POST',
        credentials: 'include', // کوکی jwt ارسال شود
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(async (res) => {
            isRefreshing = false;
            if (!res.ok) throw new Error('refresh failed');
            // بک‌اند باید کوکی جدید ست کند؛ لازم نیست چیزی برگردانی
            return true;
        })
        .catch((err) => {
            isRefreshing = false;
            throw err;
        });
    return refreshPromise;
}

// هدرهای پیش‌فرض
function defaultHeaders(extra = {}) {
    return {
        'Accept': 'application/json',
        ...extra,
    };
}

// ریدایرکت به لاگین با حفظ مقصد فعلی
function goLogin() {
    const url = new URL(window.location.href);
    const ret = encodeURIComponent(url.pathname + url.search);
    window.location.href = `/login?ret=${ret}`;
}
function enqueueRetry(fn) {
    return new Promise((resolve, reject) => {
        pendingQueue.push({ resolve, reject, fn });
    });
}
function flushQueue(err) {
    const queue = pendingQueue;
    pendingQueue = [];
    queue.forEach(({ resolve, reject, fn }) => {
        if (err) reject(err);
        else resolve(fn());
    });
}
async function refreshTokenOnce() {
    if (isRefreshing) return refreshPromise;
    isRefreshing = true;
    refreshPromise = fetch(`${API_BASE}/auth/refresh`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Accept': 'application/json' },
    })
        .then(res => {
            if (!res.ok) throw new Error('refresh failed');
            return true;
        })
        .finally(() => {
            isRefreshing = false;
        });
    return refreshPromise;
}
/**
 * apiFetch: جانشین fetch برای API
 * - credentials: 'include'
 * - 401 => refresh => retry => redirect
 */
export async function apiFetch(path, options = {}) {
    const opts = {
        method: options.method || 'GET',
        headers: {
            'Accept': 'application/json',
            ...(options.headers || {}),
        },
        credentials: 'include',
        ...(options.body ? { body: options.body } : {}),
    };

    const doFetch = () => fetch(`${API_BASE}${path}`, opts);

    let res = await doFetch();

    if (res.status !== 401) return res;

    // اگر یکی دارد refresh می‌کند، این درخواست را صف کن تا بعد از refresh دوباره ارسال شود
    if (isRefreshing) {
        return enqueueRetry(async () => {
            const retry = await doFetch();
            return retry;
        });
    }

    try {
        await refreshTokenOnce();
        // refresh موفق → همه‌ی منتظرها را اجرا کن
        flushQueue(null);
        // و خود این یکی را هم retry کن
        res = await doFetch();
        return res;
    } catch (err) {
        // refresh شکست → کل صف را با خطا رد کن
        flushQueue(err);
        return res; // همان 401 اولیه
    }
}
