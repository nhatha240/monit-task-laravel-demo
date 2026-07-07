<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed tasks for the default admin and test users.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $test = User::query()->where('email', 'test@example.com')->firstOrFail();

        $tasks = [
            [
                'title' => 'Review pending tasks',
                'description' => 'Review the current task list and update priorities.',
                'status' => 'todo',
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'due_date' => now()->addDays(2),
            ],
            [
                'title' => 'Prepare weekly report',
                'description' => 'Summarize completed work and outstanding issues.',
                'status' => 'in_progress',
                'assigned_to' => $admin->id,
                'created_by' => $test->id,
                'due_date' => now()->addDays(4),
            ],
            [
                'title' => 'Test task management flow',
                'description' => 'Verify task creation, editing, and status updates.',
                'status' => 'todo',
                'assigned_to' => $test->id,
                'created_by' => $admin->id,
                'due_date' => now()->addDays(3),
            ],
            [
                'title' => 'Update test results',
                'description' => 'Record the latest test results and resolved issues.',
                'status' => 'done',
                'assigned_to' => $test->id,
                'created_by' => $test->id,
                'due_date' => now()->subDay(),
            ],
        ];

        foreach ($tasks as $attributes) {
            $task = Task::query()
                ->where('title', $attributes['title'])
                ->where('created_by', $attributes['created_by'])
                ->first() ?? new Task;

            $task->forceFill($attributes)->save();
        }
    }
}
