import type { Paginated, Task, TaskStatus } from '@/types';
import { apiRequest } from './api';

export interface TaskFilters {
    status?: TaskStatus | null;
    assigned_to?: number | null;
    page?: number;
}

export interface TaskPayload {
    title: string;
    description: string | null;
    status: TaskStatus;
    assigned_to: number | null;
    due_date: string | null;
}

export function listTasks(filters: TaskFilters = {}): Promise<Paginated<Task>> {
    return apiRequest<Paginated<Task>>('/tasks', {
        query: {
            status: filters.status ?? undefined,
            assigned_to: filters.assigned_to ?? undefined,
            page: filters.page,
        },
    });
}

export function createTask(payload: TaskPayload): Promise<Task> {
    return apiRequest<Task>('/tasks', { method: 'POST', body: payload });
}

export function updateTask(id: number, payload: TaskPayload): Promise<Task> {
    return apiRequest<Task>(`/tasks/${id}`, { method: 'PUT', body: payload });
}

export function deleteTask(id: number): Promise<unknown> {
    return apiRequest(`/tasks/${id}`, { method: 'DELETE' });
}
