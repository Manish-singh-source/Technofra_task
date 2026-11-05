<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRecordVersion extends Model
{
    protected $fillable = [
        'salary_record_id',
        'changed_by',
        'action',
        'reason',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function salaryRecord()
    {
        return $this->belongsTo(SalaryRecord::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by')->withTrashed();
    }
}
