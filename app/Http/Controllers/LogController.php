<?php


namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogController extends Controller
{
    public function index()
    {
        $logs = Auth::user()->logs()->with('task')->latest()->get();
        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'required|image|max:2048',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $path = $request->file('photo')->store('logs', 'public');

        $log = Log::create([
            'user_id' => Auth::id(),
            'task_id' => $request->task_id,
            'photo' => $path,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Log created', 'data' => $log], 201);
    }

    public function show($id)
    {
        $log = Log::where('id', $id)
                  ->where('user_id', Auth::id())
                  ->with('task')
                  ->firstOrFail();

        return response()->json($log);
    }
}
