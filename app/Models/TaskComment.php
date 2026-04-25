<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $fillable = ['task_log_id', 'user_id', 'comment'];

    public function task()
    {
        return $this->belongsTo(TaskLog::class, 'task_log_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
