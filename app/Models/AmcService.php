<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmcService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'total_visits',
        'amc_start_date',
        'amc_end_date',
    ];

    protected $casts = [
        'amc_start_date' => 'date',
        'amc_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function amcServiceDetails()
    {
        return $this->hasMany(AmcServiceDetail::class)->orderBy('visit_number');
    }
}
