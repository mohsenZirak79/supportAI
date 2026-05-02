/**
 * Snapshot of the current page for the AI (no secrets: never sends password values).
 * Pages may set window.__supportAIPageContext = { ... } or dispatch:
 *   document.dispatchEvent(new CustomEvent('supportai:page-context', { detail: { ... } }))
 * The latest custom detail is merged (shallow) on each collect.
 */

const EVENT_NAME = 'supportai:page-context';
let lastEventDetail = null;
let pageContextListenerAttached = false;

function attachListenerOnce() {
    if (typeof document === 'undefined' || pageContextListenerAttached) return;
    pageContextListenerAttached = true;
    document.addEventListener(
        EVENT_NAME,
        (e) => {
            if (e && e.detail && typeof e.detail === 'object') {
                lastEventDetail = { ...e.detail };
            }
        },
        { passive: true }
    );
}

function guessPageKindFromPath(pathname) {
    const p = (pathname || '').toLowerCase();
    if (p.includes('/login')) return 'login';
    if (p.includes('/register')) return 'register';
    if (p.includes('/otp') || p.includes('/verify')) return 'verify-otp';
    if (p === '/' || p.endsWith('/landing')) return 'landing';
    if (p.includes('/chat')) return 'chat';
    return '';
}

function safeLabel(el) {
    const aria = el.getAttribute?.('aria-label');
    if (aria) return String(aria).slice(0, 200);
    const lid = el.getAttribute?.('id');
    if (lid) return `#${String(lid).slice(0, 120)}`;
    const name = el.getAttribute?.('name');
    if (name) return String(name).slice(0, 120);
    return '';
}

function collectVisibleErrors(max = 12) {
    const out = [];
    const seen = new Set();
    const selectors = [
        '[role="alert"]',
        '.alert-danger',
        '.alert.alert-danger',
        '.invalid-feedback',
        '.text-red-500.text-sm',
        '.error-message',
        '.field-error',
    ];
    for (const sel of selectors) {
        let nodes;
        try {
            nodes = document.querySelectorAll(sel);
        } catch {
            continue;
        }
        nodes.forEach((node) => {
            if (out.length >= max) return;
            const text = (node.textContent || '').replace(/\s+/g, ' ').trim();
            if (!text || text.length < 2) return;
            const key = text.slice(0, 200);
            if (seen.has(key)) return;
            seen.add(key);
            out.push({
                text: text.slice(0, 600),
                selector: sel,
            });
        });
    }
    return out;
}

function collectFieldSignals(maxFields = 24) {
    const issues = [];
    const fields = document.querySelectorAll('input, select, textarea');
    fields.forEach((el) => {
        if (issues.length >= maxFields) return;
        const tag = el.tagName?.toLowerCase() || '';
        const type = String(el.type || '').toLowerCase();
        const name = el.name || '';
        const id = el.id || '';
        const isPassword = type === 'password';
        const invalid =
            el.getAttribute('aria-invalid') === 'true' ||
            el.classList.contains('is-invalid') ||
            el.classList.contains('border-red-500');

        let vm = '';
        try {
            if (!el.validity?.valid && el.validationMessage) {
                vm = String(el.validationMessage).trim();
            }
        } catch {
            vm = '';
        }

        if (!invalid && !vm) return;

        const entry = {
            name: name ? String(name).slice(0, 120) : null,
            id: id ? String(id).slice(0, 120) : null,
            type: isPassword ? 'password' : type || tag,
            invalid: !!invalid,
            label: safeLabel(el),
        };
        if (vm) {
            entry.validationMessage = vm.slice(0, 500);
        }
        if (isPassword && typeof el.value === 'string') {
            entry.valueLength = el.value.length;
        }
        issues.push(entry);
    });
    return issues;
}

function activeElementSummary() {
    try {
        const a = document.activeElement;
        if (!a || a === document.body) return null;
        const tag = a.tagName?.toLowerCase();
        if (!tag || tag === 'body' || tag === 'html') return null;
        const type = String(a.type || '').toLowerCase();
        return {
            tag,
            type: type || undefined,
            name: a.name ? String(a.name).slice(0, 120) : undefined,
            id: a.id ? String(a.id).slice(0, 120) : undefined,
            invalid: a.getAttribute('aria-invalid') === 'true' || a.classList?.contains?.('is-invalid'),
        };
    } catch {
        return null;
    }
}

/**
 * @param {{ source?: string }} [extra]
 * @returns {Record<string, unknown>}
 */
export function collectPageContextForAi(extra = {}) {
    attachListenerOnce();
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return { v: 1, ...extra };
    }

    const pathname = window.location.pathname || '';
    const pageHint =
        document.documentElement?.dataset?.supportAiPage ||
        document.body?.dataset?.supportAiPage ||
        '';

    const base = {
        v: 1,
        source: extra.source || 'web',
        capturedAt: new Date().toISOString(),
        pathname: pathname.slice(0, 500),
        title: (document.title || '').slice(0, 200),
        lang: (document.documentElement.lang || '').slice(0, 16),
        dir: (document.documentElement.dir || '').slice(0, 8),
        pageKind: (pageHint || guessPageKindFromPath(pathname)).slice(0, 64),
        referrerPath: (() => {
            try {
                const r = document.referrer;
                if (!r) return undefined;
                const u = new URL(r);
                if (u.origin !== window.location.origin) return '(external)';
                return (u.pathname || '').slice(0, 500);
            } catch {
                return undefined;
            }
        })(),
        focus: activeElementSummary(),
        fieldIssues: collectFieldSignals(),
        visibleErrors: collectVisibleErrors(),
    };

    let merged = { ...base };
    try {
        const g = window.__supportAIPageContext;
        if (g && typeof g === 'object' && !Array.isArray(g)) {
            merged = { ...merged, ...g };
        }
    } catch {
        /* ignore */
    }
    if (lastEventDetail && typeof lastEventDetail === 'object') {
        merged = { ...merged, ...lastEventDetail };
    }
    if (extra && typeof extra === 'object') {
        merged = { ...merged, ...extra };
    }
    return merged;
}
