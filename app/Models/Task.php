<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'followers',
        'assignees',
        'tags',
        'status',
        'priority',
        'start_date',
        'deadline',
    ];

    protected $casts = [
        'followers' => 'array',
        'assignees' => 'array',
        'tags' => 'array',
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->with('user')->orderBy('created_at', 'desc');
    }
}
