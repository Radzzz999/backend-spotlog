<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('assignedUser')->latest()->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $task = Task::create($validated);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    public function getWorkerTasks(Request $request)
{
    $user = $request->user();

    // Cek apakah user adalah worker
    if ($user->role !== 'worker') {
        return response()->json([
            'message' => 'Unauthorized. Only workers can access this.'
        ], 403);
    }

    // Ambil semua task yang ditugaskan ke user ini
    $tasks = Task::where('assigned_to', $user->id)->get();

        return response()->json([
            'message' => 'Tasks retrieved successfully.',
            'tasks' => $tasks
        ]);
    }
}
