<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryProfile extends Model
{
    protected $fillable = ['staff_user_id', 'employee_code', 'salarybox_id', 'monthly_salary', 'standard_deduction', 'bank_name', 'bank_account_last4'];

    protected $casts = ['monthly_salary' => 'decimal:2', 'standard_deduction' => 'decimal:2'];

    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id')->withTrashed();
    }
}
