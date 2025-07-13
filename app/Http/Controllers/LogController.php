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
        $user = Auth::user();

        if ($user->role === 'admin') {
            $logs = Log::with(['task', 'user'])->latest()->get();
        } else {
            $logs = $user->logs()->with('task')->latest()->get();
        }

        $logs->transform(function ($log) {
            $log->photo_url = $log->photo ? asset('storage/' . $log->photo) : null;
            return $log;
        });

        return response()->json(['data' => $logs]);
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

        $existing = Log::where('user_id', Auth::id())
            ->where('task_id', $request->task_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Log already submitted for this task.'], 409);
        }

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

        $log->photo_url = asset('storage/' . $log->photo);

        return response()->json([
            'message' => 'Log created',
            'data' => $log,
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        $logQuery = Log::with(['task', 'user'])->where('id', $id);

        if ($user->role !== 'admin') {
            $logQuery->where('user_id', $user->id);
        }

        $log = $logQuery->firstOrFail();
        $log->photo_url = $log->photo ? asset('storage/' . $log->photo) : null;

        return response()->json(['data' => $log]);
    }


    public function update(Request $request, $id)
    {
        $log = Log::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'description' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:pending,done,rejected',
        ]);

        if ($request->hasFile('photo')) {
            if ($log->photo && Storage::disk('public')->exists($log->photo)) {
                Storage::disk('public')->delete($log->photo);
            }

            $path = $request->file('photo')->store('logs', 'public');
            $log->photo = $path;
        }

        $log->description = $request->description ?? $log->description;
        $log->latitude = $request->latitude ?? $log->latitude;
        $log->longitude = $request->longitude ?? $log->longitude;
        $log->status = $request->status ?? $log->status;

        $log->save();
        $log->photo_url = $log->photo ? asset('storage/' . $log->photo) : null;

        return response()->json(['message' => 'Log updated', 'data' => $log]);
    }

    public function logByTask($taskId)
    {
        $log = Log::where('user_id', Auth::id())
            ->where('task_id', $taskId)
            ->with('task')
            ->first();

        if ($log) {
            $log->photo_url = $log->photo ? asset('storage/' . $log->photo) : null;
        }

        return response()->json([
            'exists' => $log !== null,
            'data' => $log,
        ]);
    }
    public function logsForAdmin()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = Log::with(['task', 'user'])->latest()->get();

        $logs->transform(function ($log) {
            $log->photo_url = $log->photo ? asset('storage/' . $log->photo) : null;
            return $log;
        });

        return response()->json(['data' => $logs]);
    }
    
    public function updateComment(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['comment' => 'required|string']);

        $log = Log::findOrFail($id);
        $log->admin_comment = $request->comment;
        $log->save();

        return response()->json(['message' => 'Comment updated', 'data' => $log]);
    }
}