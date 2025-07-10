<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'assigned_to', 'latitude', 'longitude'];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}