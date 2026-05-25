<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignedLead extends Model
{
    protected $fillable = [
        'lead_model',
        'lead_id',
        'staff_ids',
    ];

    protected $casts = [
        'staff_ids' => 'array',
    ];
}

