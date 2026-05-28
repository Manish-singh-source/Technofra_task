<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'activity_type',
        'title',
        'description',
        'meta',
        'activity_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'activity_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
