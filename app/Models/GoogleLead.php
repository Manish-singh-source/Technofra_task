<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleLead extends Model
{
    protected $fillable = [
        'lead_id',
        'full_name',
        'email',
        'phone',
        'company',
        'form_id',
        'campaign_id',
        'gcl_id',
        'is_test',
        'lead_stage',
        'lead_submit_time',
        'raw_payload',
        'status',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'is_test' => 'boolean',
        'lead_submit_time' => 'datetime',
    ];
}
