export type TaskStatus = 'todo' | 'in_progress' | 'done';

export type UserRole = 'admin' | 'user';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
}

export interface Task {
    id: number;
    title: string;
    description: string | null;
    status: TaskStatus;
    assigned_to: number | null;
    created_by: number;
    due_date: string | null;
    created_at: string | null;
    updated_at: string | null;
}

/** Shape returned by Laravel's `paginate()`. */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export const TASK_STATUSES: {
    value: TaskStatus;
    title: string;
    color: string;
}[] = [
    { value: 'todo', title: 'To do', color: 'grey' },
    { value: 'in_progress', title: 'In progress', color: 'info' },
    { value: 'done', title: 'Done', color: 'success' },
];
