const BASE_URL = '/api';
const TOKEN_KEY = 'auth_token';

export function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
    localStorage.removeItem(TOKEN_KEY);
}

/** Error thrown for any non-2xx API response, carrying Laravel validation errors. */
export class ApiError extends Error {
    constructor(
        message: string,
        readonly status: number,
        readonly errors: Record<string, string[]> = {},
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

type QueryValue = string | number | null | undefined;

interface RequestOptions {
    method?: string;
    body?: unknown;
    query?: Record<string, QueryValue>;
}

const SAFE_METHODS = new Set(['GET', 'HEAD', 'OPTIONS']);

function readCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

/**
 * Sanctum's stateful middleware protects same-origin requests with CSRF, so we
 * prime the `XSRF-TOKEN` cookie before the first state-changing request.
 */
async function ensureCsrfCookie(): Promise<void> {
    if (readCookie('XSRF-TOKEN')) {
        return;
    }

    await fetch('/sanctum/csrf-cookie', {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });
}

export async function apiRequest<T>(
    path: string,
    options: RequestOptions = {},
): Promise<T> {
    const { method = 'GET', body, query } = options;

    const url = new URL(`${BASE_URL}${path}`, window.location.origin);

    if (query) {
        for (const [key, value] of Object.entries(query)) {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, String(value));
            }
        }
    }

    const headers: Record<string, string> = { Accept: 'application/json' };
    const token = getToken();

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    if (!SAFE_METHODS.has(method.toUpperCase())) {
        await ensureCsrfCookie();
        const xsrf = readCookie('XSRF-TOKEN');

        if (xsrf) {
            headers['X-XSRF-TOKEN'] = xsrf;
        }
    }

    const response = await fetch(url.toString(), {
        method,
        headers,
        credentials: 'same-origin',
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    if (response.status === 204) {
        return undefined as T;
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        const message =
            (data && typeof data.message === 'string' && data.message) ||
            `Request failed with status ${response.status}`;

        throw new ApiError(message, response.status, data?.errors ?? {});
    }

    return data as T;
}
