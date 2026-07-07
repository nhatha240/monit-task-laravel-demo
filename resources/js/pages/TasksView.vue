<template>
    <v-app-bar flat color="surface" border>
        <v-app-bar-title>
            <div class="d-flex align-center">
                <v-icon color="primary" class="me-2">mdi-checkbox-marked-circle-outline</v-icon>
                <span class="font-weight-bold">Team Task Manager</span>
            </div>
        </v-app-bar-title>

        <template #append>
            <v-chip v-if="auth.user" size="small" variant="tonal" class="me-3" prepend-icon="mdi-account">
                {{ auth.user.name }}
                <span class="text-medium-emphasis ms-1">· {{ auth.user.role }}</span>
            </v-chip>
            <v-btn variant="text" prepend-icon="mdi-logout" @click="onLogout">Logout</v-btn>
        </template>
    </v-app-bar>

    <v-main class="page-bg">
        <v-container>
            <div class="d-flex flex-wrap align-center justify-space-between mb-4 ga-2">
                <div>
                    <h1 class="text-h5 font-weight-bold">Tasks</h1>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                        {{ total }} task{{ total === 1 ? '' : 's' }}
                    </p>
                </div>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">New task</v-btn>
            </div>

            <v-card rounded="lg" border flat class="mb-4">
                <v-card-text>
                    <v-row dense align="center">
                        <v-col cols="12" sm="4">
                            <v-select
                                v-model="filters.status"
                                label="Status"
                                :items="statusFilterItems"
                                clearable
                                hide-details
                                @update:model-value="reload"
                            />
                        </v-col>
                        <v-col v-if="isAdmin()" cols="12" sm="4">
                            <v-text-field
                                v-model.number="filters.assigned_to"
                                label="Assigned to (user id)"
                                type="number"
                                clearable
                                hide-details
                                @update:model-value="reloadDebounced"
                            />
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

            <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                density="compact"
                class="mb-4"
                closable
                :text="errorMessage"
                @click:close="errorMessage = ''"
            />

            <v-card rounded="lg" border flat>
                <v-data-table-server
                    :headers="headers"
                    :items="tasks"
                    :items-length="total"
                    :loading="loading"
                    :items-per-page="perPage"
                    :items-per-page-options="[{ value: perPage, title: String(perPage) }]"
                    :page="page"
                    @update:page="onPageChange"
                >
                    <template #[`item.status`]="{ item }">
                        <v-chip :color="statusColor(item.status)" size="small" variant="tonal">
                            {{ statusTitle(item.status) }}
                        </v-chip>
                    </template>

                    <template #[`item.due_date`]="{ item }">
                        {{ formatDate(item.due_date) }}
                    </template>

                    <template #[`item.assigned_to`]="{ item }">
                        {{ item.assigned_to ?? '—' }}
                    </template>

                    <template #[`item.actions`]="{ item }">
                        <v-btn icon="mdi-pencil" size="small" variant="text" @click="openEdit(item)" />
                        <v-btn
                            icon="mdi-delete-outline"
                            size="small"
                            variant="text"
                            color="error"
                            @click="confirmDelete(item)"
                        />
                    </template>

                    <template #no-data>
                        <div class="py-8 text-center text-medium-emphasis">No tasks found.</div>
                    </template>
                </v-data-table-server>
            </v-card>
        </v-container>
    </v-main>

    <!-- Create / edit dialog -->
    <v-dialog v-model="dialog" max-width="560" persistent>
        <v-card rounded="lg">
            <v-card-title class="d-flex align-center">
                <span class="text-h6">{{ editing ? 'Edit task' : 'New task' }}</span>
                <v-spacer />
                <v-btn icon="mdi-close" variant="text" size="small" @click="dialog = false" />
            </v-card-title>

            <v-divider />

            <v-card-text>
                <v-form @submit.prevent="save">
                    <v-text-field
                        v-model="taskForm.title"
                        label="Title"
                        :error-messages="formError('title')"
                    />
                    <v-textarea
                        v-model="taskForm.description"
                        label="Description"
                        rows="3"
                        :error-messages="formError('description')"
                    />
                    <v-select
                        v-model="taskForm.status"
                        label="Status"
                        :items="statusOptions"
                        :error-messages="formError('status')"
                    />
                    <v-text-field
                        v-model.number="taskForm.assigned_to"
                        label="Assigned to (user id)"
                        type="number"
                        :error-messages="formError('assigned_to')"
                    />
                    <v-text-field
                        v-model="taskForm.due_date"
                        label="Due date"
                        type="date"
                        :error-messages="formError('due_date')"
                    />
                </v-form>
            </v-card-text>

            <v-divider />

            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
                <v-btn color="primary" :loading="saving" @click="save">Save</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Delete confirmation -->
    <v-dialog v-model="deleteDialog" max-width="420">
        <v-card rounded="lg">
            <v-card-title class="text-h6">Delete task</v-card-title>
            <v-card-text>
                Are you sure you want to delete
                <strong>{{ taskToDelete?.title }}</strong>? This cannot be undone.
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="error" :loading="deleting" @click="performDelete">Delete</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
        {{ snackbar.text }}
    </v-snackbar>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ApiError } from '@/lib/api'
import { createTask, deleteTask, listTasks, updateTask  } from '@/lib/tasks'
import type {TaskPayload} from '@/lib/tasks';
import { auth, isAdmin, logout } from '@/stores/auth'
import { TASK_STATUSES   } from '@/types'
import type {Task, TaskStatus} from '@/types';

const router = useRouter()

const headers = [
    { title: 'ID', key: 'id', width: 70 },
    { title: 'Title', key: 'title' },
    { title: 'Status', key: 'status' },
    { title: 'Assigned to', key: 'assigned_to' },
    { title: 'Due date', key: 'due_date' },
    { title: 'Actions', key: 'actions', sortable: false, align: 'end' as const, width: 120 },
]

const statusOptions = TASK_STATUSES.map((s) => ({ title: s.title, value: s.value }))
const statusFilterItems = statusOptions

const perPage = 10
const page = ref(1)
const total = ref(0)
const tasks = ref<Task[]>([])
const loading = ref(false)
const errorMessage = ref('')

const filters = reactive<{ status: TaskStatus | null; assigned_to: number | null }>({
    status: null,
    assigned_to: null,
})

const snackbar = reactive({ show: false, text: '', color: 'success' })

function notify(text: string, color = 'success'): void {
    snackbar.text = text
    snackbar.color = color
    snackbar.show = true
}

function statusColor(status: TaskStatus): string {
    return TASK_STATUSES.find((s) => s.value === status)?.color ?? 'grey'
}

function statusTitle(status: TaskStatus): string {
    return TASK_STATUSES.find((s) => s.value === status)?.title ?? status
}

function formatDate(value: string | null): string {
    if (!value) {
return '—'
}

    return new Date(value).toLocaleDateString()
}

async function fetchTasks(): Promise<void> {
    loading.value = true
    errorMessage.value = ''

    try {
        const result = await listTasks({
            status: filters.status,
            assigned_to: filters.assigned_to,
            page: page.value,
        })
        tasks.value = result.data
        total.value = result.total
    } catch (error: unknown) {
        errorMessage.value = error instanceof ApiError ? error.message : 'Failed to load tasks.'
    } finally {
        loading.value = false
    }
}

function reload(): void {
    page.value = 1
    fetchTasks()
}

let debounceTimer: ReturnType<typeof setTimeout> | undefined
function reloadDebounced(): void {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(reload, 400)
}

function onPageChange(next: number): void {
    page.value = next
    fetchTasks()
}

// --- Create / edit ---
const dialog = ref(false)
const editing = ref<Task | null>(null)
const saving = ref(false)
const formErrors = ref<Record<string, string[]>>({})

const taskForm = reactive<{
    title: string
    description: string
    status: TaskStatus
    assigned_to: number | null
    due_date: string
}>({
    title: '',
    description: '',
    status: 'todo',
    assigned_to: null,
    due_date: '',
})

function formError(field: string): string[] {
    return formErrors.value[field] ?? []
}

function resetForm(task: Task | null): void {
    formErrors.value = {}
    taskForm.title = task?.title ?? ''
    taskForm.description = task?.description ?? ''
    taskForm.status = task?.status ?? 'todo'
    taskForm.assigned_to = task?.assigned_to ?? null
    taskForm.due_date = task?.due_date ? task.due_date.slice(0, 10) : ''
}

function openCreate(): void {
    editing.value = null
    resetForm(null)
    dialog.value = true
}

function openEdit(task: Task): void {
    editing.value = task
    resetForm(task)
    dialog.value = true
}

function buildPayload(): TaskPayload {
    return {
        title: taskForm.title,
        description: taskForm.description || null,
        status: taskForm.status,
        assigned_to: taskForm.assigned_to || null,
        due_date: taskForm.due_date || null,
    }
}

async function save(): Promise<void> {
    saving.value = true
    formErrors.value = {}

    try {
        if (editing.value) {
            await updateTask(editing.value.id, buildPayload())
            notify('Task updated')
        } else {
            await createTask(buildPayload())
            notify('Task created')
        }

        dialog.value = false
        await fetchTasks()
    } catch (error: unknown) {
        if (error instanceof ApiError) {
            formErrors.value = error.errors
            notify(error.message, 'error')
        } else {
            notify('Failed to save task.', 'error')
        }
    } finally {
        saving.value = false
    }
}

// --- Delete ---
const deleteDialog = ref(false)
const deleting = ref(false)
const taskToDelete = ref<Task | null>(null)

function confirmDelete(task: Task): void {
    taskToDelete.value = task
    deleteDialog.value = true
}

async function performDelete(): Promise<void> {
    if (!taskToDelete.value) {
return
}

    deleting.value = true

    try {
        await deleteTask(taskToDelete.value.id)
        notify('Task deleted')
        deleteDialog.value = false

        // Step back a page if we just removed the last row on it.
        if (tasks.value.length === 1 && page.value > 1) {
            page.value -= 1
        }

        await fetchTasks()
    } catch (error: unknown) {
        notify(error instanceof ApiError ? error.message : 'Failed to delete task.', 'error')
    } finally {
        deleting.value = false
    }
}

async function onLogout(): Promise<void> {
    await logout()
    router.replace({ name: 'login' })
}

onMounted(fetchTasks)
</script>

<style scoped>
.page-bg {
    background: #f4f5fb;
}
</style>
