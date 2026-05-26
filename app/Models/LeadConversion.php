<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id', 'client_id', 'converted_by', 'conversion_value', 'converted_at',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function convertedBy()
    {
        return $this->belongsTo(User::class, 'converted_by');
    }
}
