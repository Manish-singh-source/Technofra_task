<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCommentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_comment_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class, 'task_comment_id');
    }
}
