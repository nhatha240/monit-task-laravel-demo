<template>
    <v-main class="auth-bg">
        <v-container class="fill-height" fluid>
            <v-row justify="center" align="center">
                <v-col cols="12" sm="8" md="5" lg="4">
                    <v-card rounded="xl" elevation="8" class="pa-2">
                        <v-card-item>
                            <div class="d-flex align-center mb-1">
                                <v-avatar color="primary" rounded="lg" size="40" class="me-3">
                                    <v-icon>mdi-checkbox-marked-circle-outline</v-icon>
                                </v-avatar>
                                <div>
                                    <div class="text-h6 font-weight-bold">Team Task Manager</div>
                                    <div class="text-caption text-medium-emphasis">
                                        {{ mode === 'login' ? 'Sign in to continue' : 'Create your account' }}
                                    </div>
                                </div>
                            </div>
                        </v-card-item>

                        <v-card-text>
                            <v-alert
                                v-if="errorMessage"
                                type="error"
                                variant="tonal"
                                density="compact"
                                class="mb-4"
                                :text="errorMessage"
                            />

                            <v-form @submit.prevent="submit">
                                <v-text-field
                                    v-if="mode === 'register'"
                                    v-model="form.name"
                                    label="Name"
                                    prepend-inner-icon="mdi-account-outline"
                                    :error-messages="fieldError('name')"
                                    autocomplete="name"
                                />

                                <v-text-field
                                    v-model="form.email"
                                    label="Email"
                                    type="email"
                                    prepend-inner-icon="mdi-email-outline"
                                    :error-messages="fieldError('email')"
                                    autocomplete="email"
                                />

                                <v-text-field
                                    v-model="form.password"
                                    label="Password"
                                    :type="showPassword ? 'text' : 'password'"
                                    prepend-inner-icon="mdi-lock-outline"
                                    :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                    :error-messages="fieldError('password')"
                                    autocomplete="current-password"
                                    @click:append-inner="showPassword = !showPassword"
                                />

                                <template v-if="mode === 'register'">
                                    <v-text-field
                                        v-model="form.password_confirmation"
                                        label="Confirm password"
                                        :type="showPassword ? 'text' : 'password'"
                                        prepend-inner-icon="mdi-lock-check-outline"
                                        autocomplete="new-password"
                                    />

                                    <v-select
                                        v-model="form.role"
                                        label="Role"
                                        :items="roleOptions"
                                        prepend-inner-icon="mdi-shield-account-outline"
                                        :error-messages="fieldError('role')"
                                    />
                                </template>

                                <v-btn
                                    type="submit"
                                    color="primary"
                                    size="large"
                                    block
                                    class="mt-2"
                                    :loading="loading"
                                >
                                    {{ mode === 'login' ? 'Sign in' : 'Create account' }}
                                </v-btn>
                            </v-form>
                        </v-card-text>

                        <v-divider />

                        <v-card-actions class="justify-center">
                            <span class="text-body-2 text-medium-emphasis me-1">
                                {{ mode === 'login' ? "Don't have an account?" : 'Already registered?' }}
                            </span>
                            <v-btn variant="text" color="primary" @click="toggleMode">
                                {{ mode === 'login' ? 'Sign up' : 'Sign in' }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ApiError } from '@/lib/api'
import { login, register } from '@/stores/auth'
import type { UserRole } from '@/types'

type Mode = 'login' | 'register'

const router = useRouter()
const route = useRoute()

const mode = ref<Mode>('login')
const loading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const roleOptions: { title: string; value: UserRole }[] = [
    { title: 'User', value: 'user' },
    { title: 'Admin', value: 'admin' },
]

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user' as UserRole,
})

function fieldError(field: string): string[] {
    return fieldErrors.value[field] ?? []
}

function toggleMode(): void {
    mode.value = mode.value === 'login' ? 'register' : 'login'
    errorMessage.value = ''
    fieldErrors.value = {}
}

async function submit(): Promise<void> {
    loading.value = true
    errorMessage.value = ''
    fieldErrors.value = {}

    try {
        if (mode.value === 'login') {
            await login(form.email, form.password)
            redirectAfterAuth()
        } else {
            await register({
                name: form.name,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation,
                role: form.role,
            })
            // Registration succeeds without a token, so sign in immediately.
            await login(form.email, form.password)
            redirectAfterAuth()
        }
    } catch (error: unknown) {
        handleError(error)
    } finally {
        loading.value = false
    }
}

function redirectAfterAuth(): void {
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/tasks'
    router.replace(redirect)
}

function handleError(error: unknown): void {
    if (error instanceof ApiError) {
        errorMessage.value = error.message
        fieldErrors.value = error.errors

        return
    }

    errorMessage.value = 'Something went wrong. Please try again.'
}
</script>

<style scoped>
.auth-bg {
    background: linear-gradient(135deg, #eef0ff 0%, #f4f5fb 45%, #e8ecff 100%);
}
</style>
