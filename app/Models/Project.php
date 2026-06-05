<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_name',
        'customer_id',
        'status',
        'lifecycle_stage',
        'start_date',
        'deadline',
        'billing_type',
        'total_rate',
        'estimated_hours',
        'tags',
        'members',
        'description',
        'priority',
        'technologies',
        'project_code',
        'project_type',
        'actual_hours',
        'progress_percentage',
        'project_manager_id',
        'approved_by',
        'approved_at',
        'deployment_date',
        'maintenance_expiry',
        'health_status',
        'last_activity_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'members' => 'array',
        'start_date' => 'date',
        'deadline' => 'date',
        'total_rate' => 'decimal:2',
        'technologies' => 'array',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'integer',
        'approved_at' => 'datetime',
        'deployment_date' => 'date',
        'maintenance_expiry' => 'date',
        'last_activity_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'customer_id');
    }

    public function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staffMembers()
    {
        $memberIds = $this->members ?? [];

        return User::staffMembers()
            ->whereIn('id', $memberIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectStatusLog::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(ProjectStatusHistory::class);
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function issues()
    {
        return $this->hasMany(ProjectIssue::class);
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function activities()
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function changeRequests()
    {
        return $this->hasMany(ProjectChangeRequest::class);
    }

    // project members list
    public function membersList()
    {
        $memberIds = $this->members ?? [];

        return User::staffMembers()
            ->whereIn('id', $memberIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
            // ->map(function ($staff) {
            //     $staff->profile_image = asset($staff->profile_image);

            //     return $staff;
            // })
            // ->values();
    }
}
