<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Log;
use App\Models\Task;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'preferensi_ui',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'preferensi_ui' => 'array',
    ];

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
}
