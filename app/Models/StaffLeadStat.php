<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLeadStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'total_leads', 'converted_leads', 'lost_leads', 'pending_followups',
        'total_calls', 'total_meetings', 'conversion_rate',
    ];

    protected $casts = [
        'conversion_rate' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
