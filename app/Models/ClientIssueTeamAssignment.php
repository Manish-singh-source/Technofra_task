<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientIssueTeamAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_issue_id',
        'team_name',
        'assigned_to',
        'note',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_by' => 'integer',
    ];

    /**
     * Get the client issue that owns the assignment.
     */
    public function clientIssue()
    {
        return $this->belongsTo(ClientIssue::class, 'client_issue_id');
    }

    /**
     * Get the user who assigned the issue.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the staff member assigned to this issue.
     */
    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }
}
