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
        return $this->hasOne(Customer::class, 'user_id', 'customer_id');
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

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    // project members list
    public function membersList()
    {
        $memberIds = $this->members ?? [];

        return User::staffMembers()
            ->whereIn('id', $memberIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($staff) {
                $staff->profile_image = $staff->profile_image_url;

                return $staff;
            })
            ->values();
    }
}
