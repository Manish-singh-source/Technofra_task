<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRecord extends Model
{
    protected $fillable = [
        'staff_user_id',
        'uploaded_by',
        'salary_month',
        'data',
    ];

    protected $casts = [
        'salary_month' => 'date',
        'data' => 'array',
    ];

    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id')->withTrashed();
    }
}
