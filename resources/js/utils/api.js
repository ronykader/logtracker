// Central API helpers — all URLs come from window.LogtrackerConfig
const cfg = () => window.LogtrackerConfig || {};

export const routes = () => cfg().routes || {};
export const i18n   = () => cfg().i18n   || {};
export const locale = () => cfg().locale  || 'en';

export async function get(url) {
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

export function buildUrl(base, params = {}) {
    const url = new URL(base, window.location.origin);
    Object.entries(params).forEach(([k, v]) => {
        if (v !== '' && v !== null && v !== undefined) url.searchParams.set(k, v);
    });
    return url.toString();
}

export function parseUsers(users) {
    try {
        const parsed = typeof users === 'string' ? JSON.parse(users) : users;
        return parsed?.name || 'Unknown';
    } catch {
        return 'Unknown';
    }
}

export function parseJson(value) {
    try {
        return typeof value === 'string' ? JSON.parse(value) : (value || {});
    } catch {
        return {};
    }
}
