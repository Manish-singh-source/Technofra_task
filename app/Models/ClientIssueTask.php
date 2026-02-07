<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientIssueTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_issue_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'start_date',
        'due_date',
        'due_time',
        'reminder_date',
        'reminder_time',
        'checklist_data',
        'labels_data',
        'attachment',
        'attachments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'reminder_date' => 'date',
        'checklist_data' => 'array',
        'labels_data' => 'array',
        'attachments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client issue that owns the task.
     */
    public function clientIssue()
    {
        return $this->belongsTo(ClientIssue::class);
    }
}
