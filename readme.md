#IntelliTask

A task management application built with Vue.js and Laravel.

This project is a task management application that allows users to create, view, edit, and delete tasks. It is built using Vue.js for the frontend and Laravel for the backend. The application provides a user-friendly interface for managing tasks and offers features such as task filtering, sorting, and pagination.

## initial setup

```bash
    composer install
    npm install
    cp .env.example .env
    php artisan key:generate  
    php artisan migrate
    npm run build
    php artisan serve
```
## Features 

- Task creation
- Task viewing and search
- Task editing
- Task deletion
- Task pagination
    
## ANSWERS.md
### 4.1 Performance & Security
1. **Performance**:
   The GET /api/tasks
endpoint is optimized for performance by implementing pagination, indexing and filtering. 
This reduces the amount of data sent to the client and improves response times, especially when dealing with a large number of tasks. Additionally, the use of Eloquent ORM in Laravel allows for efficient database queries and minimizes the number of queries executed.
2. **Security**:
- use Laravel's built-in authentication and authorization features to secure the API endpoints.
- use Sanctum for API token authentication, ensuring that only authenticated users can access the task management features.
- Implement input validation and sanitization to prevent SQL injection and other common security vulnerabilities.
- Use HTTPS for secure communication between the client and server.
- Implement rate limiting to prevent abuse of the API endpoints.
- Use Laravel's built-in CSRF protection to prevent cross-site request forgery attacks.
- Implement proper error handling and logging to monitor and respond to security incidents.
- Use environment variables to store sensitive information such as database credentials and API keys, and avoid hardcoding them in the codebase.
- Regularly update dependencies and libraries to patch known security vulnerabilities.
- Cookies should be set with the HttpOnly and Secure flags to prevent access from client-side scripts and ensure they are only sent over HTTPS.
- block executed scripts from untrusted sources to prevent cross-site scripting (XSS) attacks.
- token use should be short-lived and refreshed regularly to minimize the risk of token theft and misuse.
- use Laravel's built-in session management features to prevent session hijacking and fixation attacks.
### 4.2 TypeScript / Vue
1. What are the main benefits of using TypeScript in a Vue 3 SPA?
   - TypeScript provides static typing, which helps catch errors at compile time rather than runtime, leading to more robust and maintainable code.
   - It enhances code readability and developer experience by providing better autocompletion, navigation, and refactoring capabilities in IDEs.
   - TypeScript allows for better integration with modern JavaScript features and libraries, enabling developers to leverage the latest advancements in the ecosystem while maintaining type safety.
   - It enables the use of interfaces and types, which can improve the clarity of component props and state management in Vue applications.
   - TypeScript can help enforce coding standards and best practices across the development team, leading to more consistent code quality.
2. Show a small code sample of a Vue 3 component using `<script setup lang="ts">` with strongly typed props.
```vue
<script setup lang="ts">
    import { TASK_STATUSES } from '@/types'

    type TaskStatus = (typeof TASK_STATUSES)[number]

    type StatusOption = Pick<TaskStatus, 'title' | 'value'>

    const props = defineProps<{
        statusOptions: StatusOption[]
    }>();
</script>

```

