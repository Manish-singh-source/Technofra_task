<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'customer_id',
        'status',
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
    ];

    protected $casts = [
        'tags' => 'array',
        'members' => 'array',
        'start_date' => 'date',
        'deadline' => 'date',
        'total_rate' => 'decimal:2',
        'technologies' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staffMembers()
    {
        return $this->belongsToMany(Staff::class, 'project_staff', 'project_id', 'staff_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectStatusLog::class);
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }
}
