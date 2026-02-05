<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'customer_id',
        'issue_description',
        'priority',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the issue.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the customer that owns the issue.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the tasks for this client issue.
     */
    public function tasks()
    {
        return $this->hasMany(ClientIssueTask::class);
    }
}
