<?php

namespace App\Services;

class SalaryCalculationService
{
    public function calculate(float $monthlySalary, array $attendance): array
    {
        $dailyRate = $monthlySalary / 30;
        $absentDeduction = round((float) ($attendance['absent'] ?? 0) * $dailyRate, 2);
        $unpaidLeaveDeduction = round((float) ($attendance['unpaid_leave'] ?? 0) * $dailyRate, 2);
        $halfDayDeduction = round((float) ($attendance['half_day'] ?? 0) * 0.5 * $dailyRate, 2);
        $overtimeDays = (float) ($attendance['overtime_working_day'] ?? 0)
            + (float) ($attendance['overtime_week_off'] ?? 0)
            + (float) ($attendance['overtime_holiday'] ?? 0);
        $overtimePayment = round($overtimeDays * $dailyRate, 2);
        $totalDeductions = round($absentDeduction + $unpaidLeaveDeduction + $halfDayDeduction, 2);
        $netSalary = round($monthlySalary - $totalDeductions + $overtimePayment, 2);

        return [
            'monthly_salary' => round($monthlySalary, 2),
            'per_day_salary' => round($dailyRate, 2),
            'absent_days' => (float) ($attendance['absent'] ?? 0),
            'absent_deduction' => $absentDeduction,
            'unpaid_leave_days' => (float) ($attendance['unpaid_leave'] ?? 0),
            'unpaid_leave_deduction' => $unpaidLeaveDeduction,
            'half_days' => (float) ($attendance['half_day'] ?? 0),
            'half_day_deduction' => $halfDayDeduction,
            'paid_leave_days' => (float) ($attendance['paid_leave'] ?? 0),
            'overtime_days' => $overtimeDays,
            'overtime_payment' => $overtimePayment,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ];
    }
}
