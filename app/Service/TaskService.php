<?php

namespace App\Service;
use App\Role;
use Illuminate\Http\Request;

class TaskService
{
    public function view(array $filter = []): array
    {
        $query = \App\Models\Task::query();

        $role = auth()->user()->role;
        if ($role === Role::User) {
            $query->where('assigned_to', auth()->user()->id)->orWhere('created_by', auth()->user()->id);
        }
        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (isset($filter['assigned_to']) && $role === Role::Admin) {
            $query->where('assigned_to', $filter['assigned_to']);
        }

        return $query->paginate(10  )->toArray();
    }

    public function create(array $data): array
    {
        $data['created_by'] = auth()->user()->id;
        return \App\Models\Task::create($data)->toArray();
    }

    public function update(array $data, int $id): array
    {
        $task = \App\Models\Task::find($id);
        if(!$task) {
            throw new \RuntimeException('Task not found');
        }
        if ($task->created_by !== auth()->user()->id && auth()->user()->role !== Role::Admin) {
            throw new \RuntimeException('You are not authorized to update this task');
        }
        $task->update($data);
        return $task->toArray();
    }

    public function delete(int $id): bool
    {
        $task = \App\Models\Task::find($id);
        if(!$task) {
            throw new \RuntimeException('Task not found');
        }
        if ($task->created_by !== auth()->user()->id && auth()->user()->role !== Role::Admin) {
            throw new \RuntimeException('You are not authorized to delete this task');
        }
        return $task->delete();
    }

    public function get(int $id): array{

        $task = \App\Models\Task::find($id);
        if(!$task) {
            throw new \RuntimeException('Task not found');
        }
        if ($task->created_by !== auth()->user()->id && auth()->user()->role !== Role::Admin && $task->assigned_to !== auth()->user()->id) {
            throw new \RuntimeException('You are not authorized to view this task');
        }
        return $task->toArray();
    }
}
