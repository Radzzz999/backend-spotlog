<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;


class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Only admins can access this.'
            ], 403);
        }

        $tasks = Task::with('assignedUser')->latest()->get();

        return response()->json([
        'data' => $tasks
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Only admins can create tasks.'
            ], 403);
        }

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
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Only admins can update tasks.'
            ], 403);
        }

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

        if (!in_array($user->role, ['admin', 'worker'])) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($user->role === 'admin') {
            $tasks = Task::whereHas('logs')->get();
        } else {
            $tasks = Task::where('assigned_to', $user->id)->get();
        }

        return response()->json([
            'message' => 'Tasks retrieved successfully.',
            'tasks' => $tasks
        ]);
    }

}
