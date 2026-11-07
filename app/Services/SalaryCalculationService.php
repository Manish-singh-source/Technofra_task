<?php

namespace App\Services;

class SalaryCalculationService
{
    public function calculate(float $monthlySalary, array $attendance, array $adjustments = [], ?int $daysInMonth = null): array
    {
        $daysInMonth = $daysInMonth ?: 30;
        $dailyRate = $monthlySalary / $daysInMonth;
        $absentDays = (float) ($attendance['absent'] ?? 0);
        $paidLeaveDays = (float) ($attendance['paid_leave'] ?? 0);
        $deductibleAbsentDays = max(0.0, $absentDays - $paidLeaveDays);
        $absentDeduction = round($deductibleAbsentDays * $dailyRate, 2);
        $unpaidLeaveDeduction = round((float) ($attendance['unpaid_leave'] ?? 0) * $dailyRate, 2);
        $halfDayDeduction = round((float) ($attendance['half_day'] ?? 0) * 0.5 * $dailyRate, 2);
        $overtimeDays = (float) ($attendance['overtime_working_day'] ?? 0)
            + (float) ($attendance['overtime_week_off'] ?? 0)
            + (float) ($attendance['overtime_holiday'] ?? 0);
        $overtimePayment = round($overtimeDays * $dailyRate, 2);
        $salaryAdjustmentDays = (int) ($adjustments['salary_adjustment_days'] ?? 0);
        $salaryAdjustmentDeduction = round($salaryAdjustmentDays * $dailyRate, 2);
        $totalDeductions = round($absentDeduction + $unpaidLeaveDeduction + $halfDayDeduction + $salaryAdjustmentDeduction, 2);
        $advance = (float) ($adjustments['advance'] ?? 0);
        $otherDeduction = (float) ($adjustments['other_deduction'] ?? 0);
        $bonus = (float) ($adjustments['bonus'] ?? 0);
        $extraPayment = (float) ($adjustments['extra_payment'] ?? 0);
        $paidAmount = (float) ($adjustments['paid_amount'] ?? 0);
        $totalDeductions = round($totalDeductions + $advance + $otherDeduction, 2);
        $netSalary = round($monthlySalary - $totalDeductions + $overtimePayment + $bonus + $extraPayment, 2);

        return [
            'monthly_salary' => round($monthlySalary, 2),
            'per_day_salary' => round($dailyRate, 2),
            'days_in_month' => $daysInMonth,
            'absent_days' => $absentDays,
            'deductible_absent_days' => $deductibleAbsentDays,
            'absent_deduction' => $absentDeduction,
            'unpaid_leave_days' => (float) ($attendance['unpaid_leave'] ?? 0),
            'unpaid_leave_deduction' => $unpaidLeaveDeduction,
            'half_days' => (float) ($attendance['half_day'] ?? 0),
            'half_day_deduction' => $halfDayDeduction,
            'paid_leave_days' => $paidLeaveDays,
            'salary_adjustment_days' => $salaryAdjustmentDays,
            'salary_adjustment_deduction' => $salaryAdjustmentDeduction,
            'overtime_days' => $overtimeDays,
            'overtime_payment' => $overtimePayment,
            'bonus' => round($bonus, 2),
            'extra_payment' => round($extraPayment, 2),
            'advance' => round($advance, 2),
            'other_deduction' => round($otherDeduction, 2),
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'paid_amount' => round($paidAmount, 2),
            'pending_amount' => round(max(0, $netSalary - $paidAmount), 2),
        ];
    }
}
