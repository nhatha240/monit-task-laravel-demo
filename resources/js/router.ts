import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import { isAuthenticated } from './stores/auth';

const routes: RouteRecordRaw[] = [
    { path: '/', redirect: '/tasks' },
    {
        path: '/login',
        name: 'login',
        component: () => import('./pages/LoginView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/tasks',
        name: 'tasks',
        component: () => import('./pages/TasksView.vue'),
        meta: { requiresAuth: true },
    },
    { path: '/:pathMatch(.*)*', redirect: '/tasks' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const authed = isAuthenticated();

    if (to.meta.requiresAuth && !authed) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && authed) {
        return { name: 'tasks' };
    }

    return true;
});

export default router;
