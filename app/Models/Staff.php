<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image',
        'first_name',
        'last_name',
        'email',
        'phone',
        'role',
        'password',
        'status',
        'departments',
        'team',
    ];

    protected $casts = [
        'departments' => 'array',
    ];

    /**
     * Get the projects where this staff is a member.
     */
    public function projects()
    {
        return Project::whereJsonContains('members', (string) $this->id);
    }

    /**
     * Get the tasks assigned to this staff member.
     */
    public function tasks()
    {
        return Task::whereJsonContains('assignees', (string) $this->id)->orWhereJsonContains('followers', (string) $this->id);
    }

    /**
     * Get the user associated with the staff member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full name of the staff member.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
