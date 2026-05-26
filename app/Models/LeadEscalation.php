<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadEscalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id', 'escalated_from', 'escalated_to', 'reason', 'escalated_at',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function escalatedFrom()
    {
        return $this->belongsTo(User::class, 'escalated_from');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
