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
        'workflow_status',
        'priority',
        'start_date',
        'deadline',
        'task_code',
        'milestone_id',
        'parent_task_id',
        'task_type',
        'estimated_hours',
        'actual_hours',
        'completed_at',
        'reviewed_by',
        'reviewed_at',
        'qa_status',
        'blocked_reason',
        'started_at',
        'deployed_at',
        'sequence_order',
        'sprint_id',
        'severity',
        'story_points',
    ];

    protected $casts = [
        'followers' => 'array',
        'assignees' => 'array',
        'tags' => 'array',
        'start_date' => 'date',
        'deadline' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'started_at' => 'datetime',
        'deployed_at' => 'datetime',
        'sequence_order' => 'integer',
        'story_points' => 'integer',
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
        return $this->hasMany(TaskComment::class)
            ->whereNull('parent_id')
            ->with('user', 'replies.user', 'replies.attachments', 'attachments')
            ->orderBy('created_at', 'desc');
    }

    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function childTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function blockedBy()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('sort_order')->orderBy('id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class)->orderByDesc('started_at');
    }
}
