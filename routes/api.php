<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Worker\WorkerTaskController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsWorker;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::put('/logs/{id}/comment', [LogController::class, 'updateComment']);

// Protected routes (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Logged-in user info
    Route::get('/me', function () {
        return auth()->user();
    });

    // Logs (Worker only, or shared if needed)
    Route::get('/logs', [LogController::class, 'index']);
    Route::post('/logs', [LogController::class, 'store']);
    Route::get('/logs/{id}', [LogController::class, 'show']);
    Route::put('/logs/{id}', [LogController::class, 'update']);
    Route::get('/logs/task/{taskId}', [LogController::class, 'logByTask']);
    Route::get('/admin/worker-tasks', [TaskController::class, 'getWorkerTasks']);
    Route::get('/admin/logs', [LogController::class, 'logsForAdmin']);
    


    // Admin-only routes
    Route::middleware(IsAdmin::class)
        ->prefix('admin')
        ->group(function () {
            Route::get('/tasks', [TaskController::class, 'index']);
            Route::post('/tasks', [TaskController::class, 'store']);
            Route::put('/tasks/{id}', [TaskController::class, 'update']);
            Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
        });

    // Worker-only routes
    Route::middleware(IsWorker::class)
        ->prefix('worker')
        ->group(function () {
            Route::get('/tasks', [WorkerTaskController::class, 'index']);
        });
});
