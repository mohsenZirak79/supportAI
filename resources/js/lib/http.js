// resources/js/lib/http.js
const API_BASE = '/api/v1';

let isRefreshing = false;
let refreshPromise = null;

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
    credentials: 'include', // ⬅️ حیاتی: کوکی jwt بره/برگرده
    ...(options.body ? { body: options.body } : {}),
    };

  let res = await fetch(`${API_BASE}${path}`, opts);

  // هندل 401 + تلاش refresh
    if (res.status === 401) {
    const r = await fetch(`${API_BASE}/auth/refresh`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Accept': 'application/json' },
    });

    // if (r.ok) {
    //   res = await fetch(`${API_BASE}${path}`, opts);
    //   if (res.status !== 401) return res;
    //         }
    //
    // const ret = encodeURIComponent(location.pathname + location.search);
    // location.href = `/login?ret=${ret}`;
    // return res;
    }

    return res;
}
