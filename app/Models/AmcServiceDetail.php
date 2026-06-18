<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmcServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'amc_service_id',
        'visit_number',
        'visit_date',
        'status',
        'details',
        'completed_at',
        'before_visit_reminder_sent_at',
        'same_day_reminder_sent_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'completed_at' => 'datetime',
        'before_visit_reminder_sent_at' => 'datetime',
        'same_day_reminder_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function amcService()
    {
        return $this->belongsTo(AmcService::class);
    }
}
