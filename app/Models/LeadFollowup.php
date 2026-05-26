<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id', 'staff_id', 'followup_date', 'followup_type', 'outcome', 'discussion_notes',
        'next_followup_date', 'lead_status_after_followup', 'reminder_sent',
    ];

    protected $casts = [
        'followup_date' => 'datetime',
        'next_followup_date' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
