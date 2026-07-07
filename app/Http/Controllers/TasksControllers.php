<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Service\TaskService;
use App\Http\Requests\TasksGetRequest;
use Illuminate\Support\Facades\Log;

class TasksControllers extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }
    public function index(TasksGetRequest $request): JsonResponse
    {
        return response()->json(
            $this->taskService->view([
                'status' => $request->query('status'),
                'assigned_to' => $request->query('assigned_to'),
            ])
        );
    }
    /**
     * Store a newly created task in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            return response()->json(
                $this->taskService->create($request->all())
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred during task creation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified task in the database.
     * admin can update any task, while a user can only update their own tasks.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request,int $id): JsonResponse
    {
        try {
            return response()->json(
                $this->taskService->update($request->all(), $id)
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred during task update', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the task from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            return response()->json(
                $this->taskService->delete($id)
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred during task deletion', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            return response()->json(
                $this->taskService->get($id)
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred during task retrieval', 'error' => $e->getMessage()], 500);
        }
    }
}
