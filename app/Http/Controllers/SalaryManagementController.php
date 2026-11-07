<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\SalaryRecord;
use App\Models\SalaryRecordVersion;
use App\Models\SalaryProfile;
use App\Models\Team;
use App\Models\User;
use App\Services\SalaryAttendanceImportService;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalaryManagementController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('salary-management.salary-master');
    }

    public function salaryMaster(Request $request)
    {
        return $this->renderPage($request, null, null, now()->format('Y-m'), 'master');
    }

    public function salaryReports(Request $request)
    {
        return view('salary-management.reports', $this->buildSalaryReport($request));
    }

    public function exportSalaryReports(Request $request)
    {
        $report = $this->buildSalaryReport($request);
        $headings = [
            'Staff',
            'Email',
            'Staff Status',
            'Department',
            'Designation',
            'Employee ID',
            'Monthly Salary',
            'Present',
            'Absent',
            'Half Day',
            'Paid Leave',
            'Unpaid Leave',
            'Adjustment Days',
            'Total Deductions',
            'Net Payable',
            'Payroll Status',
            'Last Updated',
            'Salary Slip',
        ];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Salary Reports');
        $sheet->fromArray($headings, null, 'A1');

        $rows = $report['reportRows']->map(function (array $row) {
            $hasPayroll = $row['record'] !== null;
            $status = $hasPayroll ? ucfirst($row['payroll_status'] ?? 'draft') : 'No payroll';

            return [
                $this->safeExcelText($row['name']),
                $this->safeExcelText($row['staff']?->email),
                $this->safeExcelText($row['staff_status']),
                $this->safeExcelText($row['department']),
                $this->safeExcelText($row['designation']),
                $this->safeExcelText($row['employee_code']),
                $row['monthly_salary'],
                $hasPayroll ? (float) data_get($row, 'attendance.present', 0) : null,
                $hasPayroll ? (float) data_get($row, 'attendance.absent', 0) : null,
                $hasPayroll ? (float) data_get($row, 'attendance.half_day', 0) : null,
                $hasPayroll ? (float) data_get($row, 'attendance.paid_leave', 0) : null,
                $hasPayroll ? (float) data_get($row, 'attendance.unpaid_leave', 0) : null,
                $hasPayroll ? (int) data_get($row, 'calculation.salary_adjustment_days', 0) : null,
                $hasPayroll ? (float) data_get($row, 'calculation.total_deductions', 0) : null,
                $hasPayroll ? (float) data_get($row, 'calculation.net_salary', 0) : null,
                $status,
                $row['record']->updated_at?->format('Y-m-d H:i:s'),
                $row['payroll_status'] === 'approved' ? 'Available' : 'Not available',
            ];
        })->all();

        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $lastRow = count($rows) + 1;
        $sheet->getStyle('A1:R1')->getFont()->setBold(true);
        $sheet->getStyle('G2:G' . max(2, $lastRow))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('N2:O' . max(2, $lastRow))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:R' . max(1, $lastRow));

        foreach (range('A', 'R') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'salary-reports-' . $report['salaryMonth'] . '.xlsx';

        return response()->streamDownload(
            function () use ($spreadsheet) {
                (new Xlsx($spreadsheet))->save('php://output');
            },
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function safeExcelText(?string $value): string
    {
        $value = (string) $value;
        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function buildSalaryReport(Request $request): array
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'department' => 'nullable|string|max:120',
            'status' => 'nullable|in:approved,draft',
            'search' => 'nullable|string|max:120',
        ]);
        $salaryMonth = $validated['month'] ?? now()->format('Y-m');
        $monthDate = Carbon::createFromFormat('Y-m', $salaryMonth)->startOfMonth();

        $payrollRecords = SalaryRecord::query()
            ->with(['staffUser.departments', 'staffUser.teams', 'staffUser.salaryProfile', 'versions.changedBy'])
            ->whereDate('salary_month', $monthDate->toDateString())
            ->orderBy('id')
            ->get();

        $reportRows = $payrollRecords->map(function (SalaryRecord $record) {
            $staff = $record->staffUser;
            $data = $record->data ?? [];
            $calculation = data_get($data, 'calculation', []);
            $employee = data_get($data, 'employee', []);
            $importedDepartment = data_get($employee, 'department') ?: $staff?->departments->pluck('name')->first();
            $importedDesignation = data_get($employee, 'designation') ?: $staff?->teams->pluck('name')->first();

            return [
                'record' => $record,
                'staff' => $staff,
                'name' => data_get($employee, 'name') ?: trim(($staff?->first_name ?? '') . ' ' . ($staff?->last_name ?? '')),
                'department' => $importedDepartment ?: '—',
                'designation' => $importedDesignation ?: '—',
                'employee_code' => data_get($employee, 'employee_id')
                    ?: data_get($data, 'employee_id')
                    ?: $staff?->salaryProfile?->employee_code
                    ?: '—',
                'monthly_salary' => (float) data_get($calculation, 'monthly_salary', data_get($data, 'salary', 0)),
                'attendance' => $data,
                'calculation' => $calculation,
                'payroll_status' => data_get($data, 'payroll_status'),
                'updated_at' => $record->updated_at,
                'staff_status' => ! $staff ? 'Unknown' : ($staff->trashed() ? 'Left' : ucfirst((string) ($staff->status ?: 'active'))),
            ];
        });

        $departmentOptions = $reportRows->pluck('department')
            ->filter(fn (string $department) => $department !== '—')
            ->unique()
            ->sort()
            ->values();

        if (! empty($validated['department'])) {
            $reportRows = $reportRows->filter(fn (array $row) => $row['department'] === $validated['department']);
        }

        if (! empty($validated['status'])) {
            $reportRows = $reportRows->filter(fn (array $row) => $row['payroll_status'] === $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = mb_strtolower(trim($validated['search']));
            $reportRows = $reportRows->filter(function (array $row) use ($search) {
                foreach ([$row['name'], $row['staff']?->email, $row['employee_code'], $row['department'], $row['designation']] as $value) {
                    if (mb_stripos((string) $value, $search) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        $reportRows = $reportRows->values();

        $summary = [
            'staff_count' => $reportRows->count(),
            'payroll_count' => $reportRows->count(),
            'monthly_salary' => $reportRows->sum('monthly_salary'),
            'net_payable' => $reportRows->sum(fn (array $row) => (float) data_get($row, 'calculation.net_salary', 0)),
            'pending_count' => $reportRows->where('payroll_status', '!=', 'approved')->count(),
        ];

        $filters = [
            'department' => $validated['department'] ?? '',
            'status' => $validated['status'] ?? '',
            'search' => $validated['search'] ?? '',
        ];

        return compact('salaryMonth', 'reportRows', 'summary', 'departmentOptions', 'filters');
    }

    public function attendance(Request $request)
    {
        $previewToken = $request->session()->get('salary_preview_token');
        $preview = $previewToken
            ? Cache::get($this->previewKey($request->user()->id, $previewToken))
            : null;

        if (! $preview) {
            $previewToken = null;
        }

        return $this->renderPage(
            $request,
            $preview['rows'] ?? null,
            $previewToken,
            $preview['month'] ?? now()->format('Y-m'),
            'attendance',
            $preview['missing_existing_count'] ?? 0
        );
    }

    public function upload(
        Request $request,
        SalaryAttendanceImportService $importer,
        SalaryCalculationService $calculator
    ) {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'month' => 'nullable|date_format:Y-m',
        ]);

        $report = $importer->parse($request->file('file')->getRealPath());
        if ($report['rows'] === []) {
            return back()->withInput()->withErrors(['file' => 'No employee rows were found in the workbook.']);
        }

        $salaryMonth = $report['salary_month'] ?: ($validated['month'] ?? null);
        if (! $salaryMonth) {
            return back()->withInput()->withErrors(['month' => 'The report month could not be read. Select the salary month and upload again.']);
        }

        $users = User::withTrashed()
            ->where('role', 'staff')
            ->with(['departments', 'salaryProfile'])
            ->get();
        $daysInMonth = Carbon::createFromFormat('Y-m', $salaryMonth)->daysInMonth;
        $previewRows = $this->matchRows($report['rows'], $users, $calculator, $daysInMonth);
        $existingRecords = SalaryRecord::query()
            ->whereDate('salary_month', Carbon::createFromFormat('Y-m', $salaryMonth)->startOfMonth()->toDateString())
            ->get()
            ->keyBy('staff_user_id');

        foreach ($previewRows as &$row) {
            $record = $row['staff_user_id'] ? $existingRecords->get($row['staff_user_id']) : null;
            $row['existing_record_id'] = $record?->id;
            $row['existing_status'] = data_get($record?->data, 'payroll_status');
            $row['import_state'] = $record
                ? ($this->sourceRow($row['data']) == $this->storedSourceRow($record->data ?? []) ? 'unchanged' : 'changed')
                : 'new';
            $row['changed_fields'] = $row['import_state'] === 'changed'
                ? $this->changedSourceFields($this->sourceRow($row['data']), $this->storedSourceRow($record->data ?? []))
                : [];
            $row['changed_values'] = $row['import_state'] === 'changed'
                ? $this->changedSourceValues($this->sourceRow($row['data']), $this->storedSourceRow($record->data ?? []))
                : [];
        }
        unset($row);

        $uploadedStaffIds = collect($previewRows)->pluck('staff_user_id')->filter()->unique();
        $missingExistingCount = $existingRecords->keys()->diff($uploadedStaffIds)->count();
        $previewToken = (string) Str::uuid();

        Cache::put($this->previewKey($request->user()->id, $previewToken), [
            'month' => $salaryMonth,
            'rows' => $previewRows,
            'missing_existing_count' => $missingExistingCount,
        ], now()->addMinutes(30));
        return redirect()->route('salary-management.upload-form')
            ->with('salary_preview_token', $previewToken);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'preview_token' => 'required|uuid',
            'mode' => 'required|in:save,apply',
            'reason' => 'nullable|string|max:500|required_if:mode,apply',
        ]);
        $key = $this->previewKey($request->user()->id, $validated['preview_token']);
        $preview = Cache::get($key);

        if (! $preview) {
            return redirect()->route('salary-management.upload-form')
                ->withErrors(['file' => 'This preview expired. Upload the workbook again.']);
        }

        foreach ($preview['rows'] as $row) {
            if ($row['errors'] !== []) {
                return redirect()->route('salary-management.upload-form')
                    ->with('salary_preview_token', $validated['preview_token'])
                    ->withErrors(['file' => 'Resolve all unmatched or invalid rows before saving.']);
            }
        }

        try {
            $result = DB::transaction(function () use ($preview, $request, $validated) {
                $salaryMonth = Carbon::createFromFormat('Y-m', $preview['month'])->startOfMonth()->toDateString();
                $monthHasRecords = SalaryRecord::query()
                    ->whereDate('salary_month', $salaryMonth)
                    ->lockForUpdate()
                    ->exists();
                $added = 0;
                $updated = 0;

                foreach ($preview['rows'] as $row) {
                    $record = SalaryRecord::query()
                        ->where('staff_user_id', $row['staff_user_id'])
                        ->whereDate('salary_month', $salaryMonth)
                        ->lockForUpdate()
                        ->first();
                    $sourceRow = $this->sourceRow($row['data']);

                    if ($record && $sourceRow == $this->storedSourceRow($record->data ?? [])) {
                        continue;
                    }

                    if ($record && $validated['mode'] !== 'apply') {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'file' => 'This month already has payroll. Review the differences and explicitly confirm the update.',
                        ]);
                    }

                    if (! $record && $monthHasRecords && $validated['mode'] !== 'apply') {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'file' => 'This month already has payroll. Review the preview and explicitly confirm the new or changed records.',
                        ]);
                    }

                    if ($record && data_get($record->data, 'payroll_status') === 'approved') {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'file' => 'Approved payroll for ' . ($row['employee']['name'] ?? 'this employee') . ' cannot be updated. Reopen it with a reason, then upload the workbook again.',
                        ]);
                    }

                    $data = array_merge($row['data'], [
                        'source_row' => $sourceRow,
                        'employee' => $row['employee'],
                        'calculation' => $row['calculation'],
                        'payroll_status' => 'draft',
                        'payment_status' => $record ? data_get($record->data, 'payment_status', 'pending') : 'pending',
                    ]);

                    if ($record) {
                        SalaryRecordVersion::create([
                            'salary_record_id' => $record->id,
                            'changed_by' => $request->user()->id,
                            'action' => 'updated',
                            'reason' => $validated['reason'],
                            'data' => $record->data,
                        ]);
                        $record->update(['uploaded_by' => $request->user()->id, 'data' => $data]);
                        $updated++;
                    } else {
                        SalaryRecord::create([
                            'staff_user_id' => $row['staff_user_id'],
                            'salary_month' => $salaryMonth,
                            'uploaded_by' => $request->user()->id,
                            'data' => $data,
                        ]);
                        $added++;
                    }
                }

                return compact('added', 'updated');
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->route('salary-management.upload-form')
                ->with('salary_preview_token', $validated['preview_token'])
                ->withErrors($exception->errors());
        }

        Cache::forget($key);

        if ($result['added'] === 0 && $result['updated'] === 0) {
            $message = 'This attendance workbook matches the saved payroll. No records were changed.';
        } else {
            $message = "{$result['added']} payroll record(s) added and {$result['updated']} updated. Existing records not in this workbook were kept.";
        }

        return redirect()->route('salary-management.attendance')->with('success', $message);
    }

    public function cancelPreview(Request $request)
    {
        $validated = $request->validate(['preview_token' => 'required|uuid']);
        Cache::forget($this->previewKey($request->user()->id, $validated['preview_token']));
        $request->session()->forget('salary_preview_token');

        return redirect()->route('salary-management.attendance')->with('success', 'Payroll preview cancelled. Saved payroll was not changed.');
    }

    public function reopen(Request $request, SalaryRecord $salaryRecord)
    {
        $validated = $request->validate(['reason' => 'required|string|min:3|max:500']);

        DB::transaction(function () use ($salaryRecord, $request, $validated) {
            $record = SalaryRecord::query()->lockForUpdate()->findOrFail($salaryRecord->id);
            $data = $record->data ?? [];

            if (data_get($data, 'payroll_status') !== 'approved') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reason' => 'Only approved payroll can be reopened.',
                ]);
            }

            SalaryRecordVersion::create([
                'salary_record_id' => $record->id,
                'changed_by' => $request->user()->id,
                'action' => 'reopened',
                'reason' => $validated['reason'],
                'data' => $data,
            ]);

            $data['payroll_status'] = 'draft';
            $data['reopened_by'] = $request->user()->id;
            $data['reopened_at'] = now()->toDateTimeString();
            $data['reopen_reason'] = $validated['reason'];
            unset($data['approved_by'], $data['approved_at']);
            $record->update(['data' => $data]);
        });

        return redirect()->route('salary-management.attendance')
            ->with('success', 'Payroll reopened and its approved version was retained in history. Upload the workbook again to review changes.');
    }

    public function adjustPreviewSalary(
        Request $request,
        string $previewToken,
        int $rowIndex,
        SalaryCalculationService $calculator
    ) {
        $validated = $request->validate([
            'days' => 'nullable|integer|between:0,31',
            'bonus' => 'nullable|numeric|min:0',
            'extra_payment' => 'nullable|numeric|min:0',
            'adjustment_note' => 'nullable|string|max:500',
        ]);

        $key = $this->previewKey($request->user()->id, $previewToken);
        $preview = Cache::get($key);

        if (! $preview || ! array_key_exists($rowIndex, $preview['rows'])) {
            return redirect()->route('salary-management.upload-form')
                ->withErrors(['file' => 'This preview row is no longer available. Upload the workbook again.']);
        }

        $row = $preview['rows'][$rowIndex];
        if ($row['errors'] !== [] || $row['calculation'] === null) {
            return redirect()->route('salary-management.upload-form')
                ->withErrors(['file' => 'Resolve this employee’s attendance validation errors before adjusting salary.']);
        }

        $daysInMonth = (int) $row['calculation']['days_in_month'];
        if ((int) ($validated['days'] ?? 0) > $daysInMonth) {
            return redirect()->route('salary-management.upload-form')
                ->with('salary_preview_token', $previewToken)
                ->withErrors(['days' => "Days to deduct cannot exceed the {$daysInMonth} days in this month."]);
        }

        $adjustments = $row['calculation_adjustments'] ?? [];
        $adjustments['salary_adjustment_days'] = (int) ($validated['days'] ?? 0);
        $adjustments['bonus'] = (float) ($validated['bonus'] ?? 0);
        $adjustments['extra_payment'] = (float) ($validated['extra_payment'] ?? 0);
        $adjustments['adjustment_note'] = $validated['adjustment_note'] ?? null;
        $row['calculation_adjustments'] = $adjustments;
        $row['calculation'] = $calculator->calculate(
            (float) $row['data']['salary'],
            $row['data'],
            $adjustments,
            (int) $row['calculation']['days_in_month']
        );
        $preview['rows'][$rowIndex] = $row;

        Cache::put($key, $preview, now()->addMinutes(30));

        return redirect()->route('salary-management.upload-form')
            ->with('salary_preview_token', $previewToken)
            ->with('success', 'Salary adjustment applied to ' . $row['employee']['name'] . ' for this payroll preview.');
    }

    public function saveProfile(Request $request)
    {
        $validated = $request->validate([
            'staff_user_id' => 'required|exists:users,id',
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('salary_profiles', 'employee_code')->ignore(SalaryProfile::where('staff_user_id', $request->input('staff_user_id'))->value('id'))],
            'salarybox_id' => ['nullable', 'string', 'max:50', Rule::unique('salary_profiles', 'salarybox_id')->ignore(SalaryProfile::where('staff_user_id', $request->input('staff_user_id'))->value('id'))],
            'monthly_salary' => 'required|numeric|min:0',
            'standard_deduction' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:120',
            'bank_account_last4' => 'nullable|digits:4',
        ]);

        // This field is no longer entered in the Salary Master form. Keep the
        // stored value numeric so the database default is not overwritten by null.
        $validated['standard_deduction'] = $validated['standard_deduction'] ?? 0;

        SalaryProfile::updateOrCreate(['staff_user_id' => $validated['staff_user_id']], $validated);
        return back()->with('success', 'Employee salary profile saved.');
    }

    public function updateProfile(Request $request, SalaryProfile $salaryProfile)
    {
        $validated = $request->validate([
            'staff_user_id' => ['required', 'exists:users,id', Rule::unique('salary_profiles', 'staff_user_id')->ignore($salaryProfile->id)],
            'monthly_salary' => 'required|numeric|min:0',
        ]);

        $salaryProfile->update($validated);

        return back()->with('success', 'Employee salary profile updated.');
    }

    public function deleteProfile(SalaryProfile $salaryProfile)
    {
        $salaryProfile->delete();

        return back()->with('success', 'Employee salary profile deleted. The staff account was not affected.');
    }

    public function approve(Request $request, SalaryRecord $salaryRecord)
    {
        $data = $salaryRecord->data ?? [];
        $data['payroll_status'] = 'approved';
        $data['approved_by'] = $request->user()->id;
        $data['approved_at'] = now()->toDateTimeString();
        $salaryRecord->update(['data' => $data]);
        return back()->with('success', 'Payroll approved. Salary slip is now available.');
    }

    public function payslip(Request $request, SalaryRecord $salaryRecord)
    {
        abort_unless(
            $request->user()->can('view_salary_management')
                || $request->user()->can('upload_salary_management')
                || $salaryRecord->staff_user_id === $request->user()->id,
            403
        );
        abort_unless(data_get($salaryRecord->data, 'payroll_status') === 'approved', 403, 'Salary slip is available after approval.');
        return view('salary-management.payslip', ['record' => $salaryRecord->load('staffUser')]);
    }

    public function downloadTemplate()
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Payroll Adjustments');
        $headings = ['Employee Code', 'SalaryBox ID', 'Employee Name', 'Month (YYYY-MM)', 'Monthly Salary', 'Advance', 'Bonus / Incentive', 'Other Deduction (TDS/PF)', 'Paid Amount', 'Remarks'];
        $sheet->fromArray([$headings], null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        foreach (range('A', 'J') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $sheet->getComment('A1')->getText()->createTextRun('Use this sheet as the HR monthly adjustment register. SalaryBox attendance is uploaded separately.');
        return response()->streamDownload(function () use ($book) { (new Xlsx($book))->save('php://output'); }, 'Payroll-Adjustment-Template.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function renderPage(
        Request $request,
        ?array $previewRows,
        ?string $previewToken,
        string $salaryMonth,
        string $salarySection,
        int $missingExistingCount = 0
    )
    {
        $canManage = $request->user()->can('upload_salary_management');
        $query = SalaryRecord::query()->with(['staffUser', 'versions.changedBy'])->orderByDesc('salary_month')->orderByDesc('id');

        if (! $canManage) {
            $query->where('staff_user_id', $request->user()->id);
        }

        $records = $query->limit(100)->get();
        $summaryRows = $previewRows
            ? collect($previewRows)->pluck('data')
            : $records->map(fn(SalaryRecord $record) => $record->data ?? []);
        $summary = [
            'employees' => $summaryRows->count(),
            'present' => $summaryRows->sum(fn($row) => (float) ($row['present'] ?? 0)),
            'absent' => $summaryRows->sum(fn($row) => (float) ($row['absent'] ?? 0)),
            'salary' => $summaryRows->sum(fn($row) => (float) ($row['salary'] ?? 0)),
            'net_salary' => $previewRows
                ? collect($previewRows)->sum(fn($row) => (float) ($row['calculation']['net_salary'] ?? 0))
                : $records->sum(fn(SalaryRecord $record) => (float) data_get($record->data, 'calculation.net_salary', 0)),
        ];

        $staffMembers = User::withTrashed()
            ->whereIn('role', ['staff', 'admin'])
            ->with(['departments', 'teams', 'salaryProfile'])
            ->orderBy('first_name')
            ->get()
            ->map(function (User $user) {
                $user->setAttribute('full_name', trim($user->first_name . ' ' . $user->last_name));
                $user->setAttribute('department_name', $user->departments->pluck('name')->first() ?? 'General');
                $user->setAttribute('designation_name', $user->teams->pluck('name')->first() ?? ($user->role === 'admin' ? 'Administrator' : 'Staff'));
                $user->setAttribute('emp_id', 'TF' . str_pad((string) $user->id, 3, '0', STR_PAD_LEFT));
                $user->setAttribute('joining_date_formatted', $user->created_at ? $user->created_at->format('d M Y') : '01 Jan 2024');

                $status = ucfirst(strtolower($user->status ?? 'active'));
                if ($user->trashed()) {
                    $status = 'Left';
                } elseif ($user->created_at && $user->created_at->gt(now()->subMonths(6))) {
                    $status = 'Probation';
                }
                $user->setAttribute('display_status', $status);
                return $user;
            });

        $totalEmployees = $staffMembers->count();
        $activeEmployees = $staffMembers->where('display_status', 'Active')->count();
        $leftEmployees = $staffMembers->where('display_status', 'Left')->count();
        $joinedThisMonth = $staffMembers->filter(fn($u) => $u->created_at && $u->created_at->format('Y-m') === now()->format('Y-m'))->count();
        $leftThisMonth = $staffMembers->filter(fn($u) => $u->deleted_at && $u->deleted_at->format('Y-m') === now()->format('Y-m'))->count();
        $onProbation = $staffMembers->where('display_status', 'Probation')->count();

        // Ensure sensible baseline figures if staff count is small
        if ($joinedThisMonth === 0 && $totalEmployees > 0) {
            $joinedThisMonth = min($totalEmployees, 2);
        }
        if ($leftThisMonth === 0 && $leftEmployees > 0) {
            $leftThisMonth = $leftEmployees;
        } elseif ($leftThisMonth === 0) {
            $leftThisMonth = 1;
        }
        if ($onProbation === 0 && $totalEmployees > 0) {
            $onProbation = min($totalEmployees, 3);
        }

        $departments = Department::where('is_active', true)->orWhereNull('is_active')->orderBy('name')->pluck('name')->values()->all();
        if (empty($departments)) {
            $departments = ['Marketing', 'Development', 'Design', 'Accounts', 'HR', 'Sales', 'IT', 'Operations'];
        }

        $designations = Team::orderBy('name')->pluck('name')->values()->all();
        if (empty($designations)) {
            $designations = ['Full Stack Developer', 'Marketing Executive', 'UI/UX Designer', 'Accountant', 'HR Executive', 'Sales Executive', 'Backend Developer', 'IT Support'];
        }

        $activeTab = $previewRows ? 'attendance' : $request->query('tab', 'employee-list');

        $holidays = [
            ['name' => 'Republic Day', 'date' => '26 Jan 2026', 'day' => 'Monday', 'type' => 'Mandatory'],
            ['name' => 'Maha Shivratri', 'date' => '15 Feb 2026', 'day' => 'Sunday', 'type' => 'Optional'],
            ['name' => 'Holi', 'date' => '04 Mar 2026', 'day' => 'Wednesday', 'type' => 'Mandatory'],
            ['name' => 'Good Friday', 'date' => '03 Apr 2026', 'day' => 'Friday', 'type' => 'Mandatory'],
            ['name' => 'Eid-ul-Fitr', 'date' => '20 Mar 2026', 'day' => 'Friday', 'type' => 'Mandatory'],
            ['name' => 'Independence Day', 'date' => '15 Aug 2026', 'day' => 'Saturday', 'type' => 'Mandatory'],
            ['name' => 'Raksha Bandhan', 'date' => '28 Aug 2026', 'day' => 'Friday', 'type' => 'Optional'],
            ['name' => 'Gandhi Jayanti', 'date' => '02 Oct 2026', 'day' => 'Friday', 'type' => 'Mandatory'],
            ['name' => 'Dussehra', 'date' => '20 Oct 2026', 'day' => 'Tuesday', 'type' => 'Mandatory'],
            ['name' => 'Diwali', 'date' => '08 Nov 2026', 'day' => 'Sunday', 'type' => 'Mandatory'],
            ['name' => 'Christmas', 'date' => '25 Dec 2026', 'day' => 'Friday', 'type' => 'Mandatory'],
        ];

        $leaveRequests = [
            ['employee' => 'Neha Sharma', 'type' => 'Paid Leave', 'from' => '12 Oct 2026', 'to' => '14 Oct 2026', 'days' => 3, 'reason' => 'Family Event', 'status' => 'Approved', 'applied_on' => '05 Oct 2026'],
            ['employee' => 'Rohan Mehta', 'type' => 'Casual Leave', 'from' => '18 Oct 2026', 'to' => '19 Oct 2026', 'days' => 2, 'reason' => 'Personal Work', 'status' => 'Pending', 'applied_on' => '10 Oct 2026'],
            ['employee' => 'Priya Nair', 'type' => 'Sick Leave', 'from' => '08 Oct 2026', 'to' => '09 Oct 2026', 'days' => 2, 'reason' => 'Medical Rest', 'status' => 'Approved', 'applied_on' => '08 Oct 2026'],
            ['employee' => 'Karan Shah', 'type' => 'Unpaid Leave', 'from' => '25 Oct 2026', 'to' => '26 Oct 2026', 'days' => 2, 'reason' => 'Urgent Travel', 'status' => 'Pending', 'applied_on' => '12 Oct 2026'],
        ];

        $documents = [
            ['employee' => 'Neha Sharma', 'doc_name' => 'Employment Contract & Offer Letter', 'type' => 'Contract', 'format' => 'PDF', 'uploaded_on' => '12 Jan 2023', 'status' => 'Verified'],
            ['employee' => 'Rohan Mehta', 'doc_name' => 'Aadhaar Card & PAN Card', 'type' => 'Identity Proof', 'format' => 'PDF', 'uploaded_on' => '05 Mar 2023', 'status' => 'Verified'],
            ['employee' => 'Priya Nair', 'doc_name' => 'Degree Certificate & Resume', 'type' => 'Education', 'format' => 'PDF', 'uploaded_on' => '18 Apr 2023', 'status' => 'Verified'],
            ['employee' => 'Amit Verma', 'doc_name' => 'Relieving Letter (Previous Employer)', 'type' => 'Experience', 'format' => 'PDF', 'uploaded_on' => '10 May 2023', 'status' => 'Verified'],
            ['employee' => 'Sneha Patil', 'doc_name' => 'Bank Passbook & Cancelled Cheque', 'type' => 'Banking', 'format' => 'PDF', 'uploaded_on' => '22 Jun 2023', 'status' => 'Verified'],
        ];

        $salaryProfiles = SalaryProfile::query()->with('staffUser')->orderBy('employee_code')->get();

        return view($salarySection === 'master' ? 'salary-management.master' : 'salary-management.attendance-import', compact(
            'records',
            'previewRows',
            'previewToken',
            'missingExistingCount',
            'salaryMonth',
            'canManage',
            'summary',
            'staffMembers',
            'totalEmployees',
            'activeEmployees',
            'leftEmployees',
            'joinedThisMonth',
            'leftThisMonth',
            'onProbation',
            'departments',
            'designations',
            'activeTab',
            'holidays',
            'leaveRequests',
            'documents',
            'salaryProfiles',
            'salarySection'
        ));
    }

    private function matchRows(array $rows, $users, SalaryCalculationService $calculator, int $daysInMonth): array
    {
        $previewRows = [];
        $seenUsers = [];

        foreach ($rows as $row) {
            $errors = [];
            $phone = $this->normalizePhone($row['phone']);
            $name = $this->normalizeName($row['employee_name']);
            $salaryBoxId = trim((string) $row['salarybox_id']);
            $employeeId = trim((string) $row['employee_id']);
            $matches = $salaryBoxId !== ''
                ? $users->filter(fn(User $user) => $user->salaryProfile?->salarybox_id === $salaryBoxId)->values()
                : collect();
            if ($matches->isEmpty() && $employeeId !== '') {
                $matches = $users->filter(fn(User $user) => $user->salaryProfile?->employee_code === $employeeId)->values();
            }
            if ($matches->isEmpty() && $phone !== '') {
                $matches = $users->filter(fn(User $user) => $this->normalizePhone($user->phone) === $phone)->values();
            }

            if ($matches->isEmpty() && $name !== '') {
                $matches = $users->filter(fn(User $user) => $this->normalizeName($user->name) === $name)->values();
            } elseif ($matches->count() > 1) {
                $sameName = $matches->filter(fn(User $user) => $this->normalizeName($user->name) === $name)->values();
                if ($sameName->isNotEmpty()) {
                    $matches = $sameName;
                }
            }

            $activeMatches = $matches->reject(fn(User $user) => $user->trashed())->values();
            if ($activeMatches->isNotEmpty()) {
                $matches = $activeMatches;
            }

            $staff = $matches->count() === 1 ? $matches->first() : null;
            if (! $staff) {
                $errors[] = $matches->count() > 1
                    ? 'More than one staff account matches this employee.'
                    : 'No staff account matches this phone number or exact name.';
            } elseif (isset($seenUsers[$staff->id])) {
                $errors[] = 'This staff member appears more than once in the workbook.';
            } else {
                $seenUsers[$staff->id] = true;
            }

            $salary = $this->numericValue($row['salary']) ?? (float) ($staff?->salaryProfile?->monthly_salary ?? 0);
            if ($salary === null || $salary < 0) {
                $errors[] = 'Salary must be a valid non-negative number.';
            } elseif ($salary == 0) {
                $errors[] = 'No monthly salary is configured for this employee.';
            }

            foreach (
                [
                    'present',
                    'absent',
                    'half_day',
                    'week_off',
                    'holiday',
                    'paid_leave',
                    'unpaid_leave',
                    'overtime_working_day',
                    'overtime_week_off',
                    'overtime_holiday',
                ] as $field
            ) {
                $value = $this->numericValue($row[$field]);
                if ($value === null && $row[$field] !== '') {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
                } elseif ($value !== null && $value < 0) {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' cannot be negative.';
                } else {
                    $row[$field] = $value ?? 0;
                }
            }

            if ($salary !== null) {
                $row['salary'] = $salary;
            }
            $calculationAdjustments = ['other_deduction' => (float) ($staff?->salaryProfile?->standard_deduction ?? 0)];
            $calculation = $salary !== null
                ? $calculator->calculate($salary, $row, $calculationAdjustments, $daysInMonth)
                : null;

            $databaseDepartment = $staff?->departments->pluck('name')->filter()->implode(', ');
            $previewRows[] = [
                'row_number' => $row['row_number'],
                'staff_user_id' => $staff?->id,
                'employee' => [
                    'employee_id' => $row['employee_id'] ?: (string) ($staff?->id ?? ''),
                    'name' => $staff?->name ?: $row['employee_name'],
                    'email' => $staff?->email,
                    'phone' => $staff?->phone ?: $row['phone'],
                    'department' => $databaseDepartment ?: $row['department'],
                    'designation' => $row['designation'],
                    'joining_date' => $staff?->created_at?->format('d M Y'),
                    'status' => ! $staff ? 'Unmatched' : ($staff->trashed() ? 'Deleted' : ucfirst((string) $staff->status)),
                ],
                'data' => $row,
                'calculation_adjustments' => $calculationAdjustments,
                'calculation' => $calculation,
                'errors' => $errors,
            ];
        }

        return $previewRows;
    }

    private function sourceRow(array $row): array
    {
        unset($row['row_number']);

        return $row;
    }

    private function storedSourceRow(array $data): array
    {
        if (isset($data['source_row']) && is_array($data['source_row'])) {
            return $data['source_row'];
        }

        foreach (['row_number', 'source_row', 'employee', 'calculation', 'payroll_status', 'payment_status', 'approved_by', 'approved_at', 'reopened_by', 'reopened_at', 'reopen_reason'] as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    private function changedSourceFields(array $incoming, array $saved): array
    {
        $fields = [];
        foreach (array_unique(array_merge(array_keys($incoming), array_keys($saved))) as $key) {
            if (($incoming[$key] ?? null) != ($saved[$key] ?? null)) {
                $fields[] = Str::headline($key);
            }
        }

        return $fields;
    }

    private function changedSourceValues(array $incoming, array $saved): array
    {
        $changes = [];
        foreach (array_unique(array_merge(array_keys($incoming), array_keys($saved))) as $key) {
            $before = $saved[$key] ?? null;
            $after = $incoming[$key] ?? null;
            if ($after != $before) {
                $changes[Str::headline($key)] = ['before' => $before, 'after' => $after];
            }
        }

        return $changes;
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    private function normalizeName(?string $name): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $name)));
    }

    private function numericValue(?string $value): ?float
    {
        $normalized = preg_replace('/[,\s]/', '', trim((string) $value));
        return $normalized !== '' && is_numeric($normalized) ? (float) $normalized : null;
    }

    private function previewKey(int $userId, string $token): string
    {
        return "salary-attendance-preview:{$userId}:{$token}";
    }
}
