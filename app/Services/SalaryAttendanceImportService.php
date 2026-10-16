<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SalaryAttendanceImportService
{
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $title = trim((string) $sheet->getCell('A1')->getFormattedValue());
        $rows = [];

        for ($rowNumber = 4; $rowNumber <= $sheet->getHighestRow(); $rowNumber++) {
            $values = [];
            foreach (range('A', 'U') as $column) {
                $values[] = trim((string) $sheet->getCell($column . $rowNumber)->getFormattedValue());
            }

            if ($values[0] === '' && $values[1] === '' && $values[2] === '' && $values[3] === '') {
                continue;
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'salarybox_id' => $values[0],
                'employee_name' => $values[1],
                'employee_id' => $values[2],
                'phone' => $values[3],
                'branch' => $values[4],
                'department' => $values[5],
                'designation' => $values[6],
                'employee_type' => $values[7],
                'present' => $values[8],
                'absent' => $values[9],
                'half_day' => $values[10],
                'week_off' => $values[11],
                'holiday' => $values[12],
                'paid_leave' => $values[13],
                'unpaid_leave' => $values[14],
                'overtime_working_day' => $values[15],
                'overtime_week_off' => $values[16],
                'overtime_holiday' => $values[17],
                'late_arrival' => $values[18],
                'early_leaving' => $values[19],
                'salary' => $values[20],
            ];
        }

        return [
            'title' => $title,
            'salary_month' => $this->extractMonth($title),
            'rows' => $rows,
        ];
    }

    private function extractMonth(string $title): ?string
    {
        preg_match_all('/\b\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4}\b/', $title, $matches);

        if (count($matches[0] ?? []) < 2) {
            return null;
        }

        $start = Carbon::parse($matches[0][0]);
        $end = Carbon::parse($matches[0][1]);

        return $start->isSameMonth($end) ? $start->format('Y-m') : null;
    }
}
