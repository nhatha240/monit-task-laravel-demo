import { reactive, readonly } from 'vue';
import { apiRequest, clearToken, getToken, setToken } from '@/lib/api';
import type { User, UserRole } from '@/types';

interface AuthState {
    user: User | null;
    initialized: boolean;
}

const state = reactive<AuthState>({
    user: null,
    initialized: false,
});

export const auth = readonly(state);

export function isAuthenticated(): boolean {
    return getToken() !== null;
}

export function isAdmin(): boolean {
    return state.user?.role === 'admin';
}

interface LoginResponse {
    access_token: string;
    token_type: string;
}

export async function login(email: string, password: string): Promise<void> {
    const response = await apiRequest<LoginResponse>('/login', {
        method: 'POST',
        body: { email, password },
    });
    setToken(response.access_token);
    await loadUser();
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: UserRole;
}

export function register(payload: RegisterPayload): Promise<unknown> {
    return apiRequest('/register', { method: 'POST', body: payload });
}

/** Hydrate the current user from the token; clears the token if it is invalid. */
export async function loadUser(): Promise<User | null> {
    if (!getToken()) {
        state.user = null;
        state.initialized = true;

        return null;
    }

    try {
        state.user = await apiRequest<User>('/user');
    } catch {
        clearToken();
        state.user = null;
    }

    state.initialized = true;

    return state.user;
}

export async function logout(): Promise<void> {
    try {
        await apiRequest('/logout', { method: 'POST' });
    } catch {
        // Ignore network/API errors on logout; we clear locally regardless.
    }

    clearToken();

    state.user = null;
}
