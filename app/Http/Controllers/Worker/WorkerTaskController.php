<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class WorkerTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::where('assigned_to', $user->id)->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }
}