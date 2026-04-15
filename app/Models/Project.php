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
        $memberIds = $this->members ?? [];

        return Staff::whereIn('id', $memberIds)->get();
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

    // project members list
    public function membersList()
    {
        $memberIds = $this->members ?? [];

        return Staff::whereIn('id', $memberIds)
            ->get()
            ->map(function ($staff) {
                $staff->profile_image = $staff->profile_image
                                    ? asset('uploads/staff/'.$staff->profile_image)
                                    : null;

                return $staff;
            })
            ->values();
    }
}
