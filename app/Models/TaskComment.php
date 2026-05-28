<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'parent_id',
        'user_id',
        'comment',
        'mentions',
        'edited_at',
        'edit_history',
    ];

    protected $casts = [
        'mentions' => 'array',
        'edit_history' => 'array',
        'edited_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user', 'attachments')->orderBy('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(TaskCommentAttachment::class, 'task_comment_id');
    }
}
