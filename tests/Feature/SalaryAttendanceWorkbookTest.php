<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SalaryRecord;
use App\Models\SalaryProfile;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SalaryAttendanceWorkbookTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_workbook_matches_users_and_saves_salary_values(): void
    {
        $uploader = $this->createStaff('Payroll', 'Manager', 'payroll@example.com', 'admin');
        $uploader->givePermissionTo(
            Permission::findOrCreate('view_salary_management', 'web'),
            Permission::findOrCreate('upload_salary_management', 'web')
        );

        $khushi = $this->createStaff('Khushi', 'Yadav', 'khushi@example.com', 'staff', '8080803375');
        $department = Department::create(['name' => 'Administration', 'is_active' => true]);
        $khushi->departments()->attach($department->id);
        $manish = $this->createStaff('Manish', 'Singh', 'manish@example.com', 'staff', '7039553407');

        $rahul = $this->createStaff('Rahul', 'Nadar', 'rahul@example.com', 'staff', '8591028709');
        $rahul->delete();

        $this->actingAs($uploader)->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([
                ['SBK123', 'Khushi Yadav', 'TFR012', '9321807085', 'Main Branch', 'Admin', 'HR Executive', '', 20, 6, 0, 4, 0, 0, 0, 1, 0, 1, 20, 9, 3000],
                ['SBM456', 'Manish Singh', 'TFR037', '7039553407', 'Main Branch', 'Web designer', 'Website Designer', 'Permanent', 17, 6, 0, 4, 0, 3, 0, 1, 0, 0, 15, 6, 4000],
                ['SBR456', 'Rahul Nadar', '', '8591028709', 'Main Branch', 'Web', 'Developer', '', 0, 29, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 8000],
            ]),
        ])->assertRedirect(route('salary-management.upload-form'));

        $response = $this->get(route('salary-management.upload-form'));
        $response->assertOk()
            ->assertViewIs('salary-management.attendance-import')
            ->assertSee('2026-09')
            ->assertSee('TFR012')
            ->assertSee('Khushi Yadav')
            ->assertSee('Manish Singh')
            ->assertSee('3,000.00')
            ->assertSee('4,000.00')
            ->assertSee('8,000.00')
            ->assertSee('400.00')
            ->assertSee('133.33')
            ->assertSee('3,733.33');

        $this->assertDatabaseCount('salary_records', 0);

        $this->post(route('salary-management.save'), [
            'preview_token' => $response->viewData('previewToken'),
            'mode' => 'save',
        ])->assertRedirect(route('salary-management.attendance'));

        $this->assertDatabaseCount('salary_records', 3);
        $savedRecord = SalaryRecord::query()->where('staff_user_id', $khushi->id)->firstOrFail();
        $this->assertSame('2026-09-01', $savedRecord->salary_month->toDateString());
        $this->assertSame(3000.0, (float) $savedRecord->data['salary']);
        $this->assertSame(20.0, (float) $savedRecord->data['present']);
        $this->assertSame(6.0, (float) $savedRecord->data['absent']);

        $manishRecord = SalaryRecord::query()->where('staff_user_id', $manish->id)->firstOrFail();
        $this->assertSame(133.33, (float) $manishRecord->data['calculation']['per_day_salary']);
        $this->assertSame(400.0, (float) $manishRecord->data['calculation']['absent_deduction']);
        $this->assertSame(3.0, (float) $manishRecord->data['calculation']['deductible_absent_days']);
        $this->assertSame(133.33, (float) $manishRecord->data['calculation']['overtime_payment']);
        $this->assertSame(3733.33, (float) $manishRecord->data['calculation']['net_salary']);
    }

    public function test_paid_leave_is_paid_but_unpaid_leave_and_half_days_are_deducted(): void
    {
        $calculation = (new SalaryCalculationService())->calculate(3000, [
            'absent' => 6,
            'paid_leave' => 5,
            'unpaid_leave' => 1,
            'half_day' => 2,
        ]);

        $this->assertSame(100.0, $calculation['per_day_salary']);
        $this->assertSame(100.0, $calculation['unpaid_leave_deduction']);
        $this->assertSame(100.0, $calculation['half_day_deduction']);
        $this->assertSame(1.0, $calculation['deductible_absent_days']);
        $this->assertSame(100.0, $calculation['absent_deduction']);
        $this->assertSame(2700.0, $calculation['net_salary']);

        $paidLeaveExceedsAbsence = (new SalaryCalculationService())->calculate(3000, [
            'absent' => 2,
            'paid_leave' => 5,
        ]);
        $this->assertSame(0.0, $paidLeaveExceedsAbsence['deductible_absent_days']);
        $this->assertSame(0.0, $paidLeaveExceedsAbsence['absent_deduction']);
    }

    public function test_salary_adjustment_is_applied_to_the_current_payroll_only(): void
    {
        $uploader = $this->createStaff('Payroll', 'Manager', 'adjustment@example.com', 'admin');
        $uploader->givePermissionTo(
            Permission::findOrCreate('view_salary_management', 'web'),
            Permission::findOrCreate('upload_salary_management', 'web')
        );

        $staff = $this->createStaff('Khushi', 'Yadav', 'khushi-adjustment@example.com', 'staff', '8080803375');
        $profile = SalaryProfile::create([
            'staff_user_id' => $staff->id,
            'employee_code' => 'TFR012',
            'salarybox_id' => 'SBK123',
            'monthly_salary' => 3000,
            'standard_deduction' => 0,
        ]);

        $this->actingAs($uploader);
        $this->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([
                ['SBK123', 'Khushi Yadav', 'TFR012', '8080803375', 'Main Branch', 'Admin', 'HR Executive', '', 20, 10, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3000],
            ]),
        ])->assertRedirect(route('salary-management.upload-form'));

        $uploadPreview = $this->get(route('salary-management.upload-form'))->assertOk();
        $previewToken = $uploadPreview->viewData('previewToken');
        $this->from(route('salary-management.upload-form'))
            ->post(route('salary-management.preview.adjustment', [$previewToken, 0]), [
                'days' => 31,
            ])
            ->assertRedirect(route('salary-management.upload-form'))
            ->assertSessionHasErrors('days');

        $this->post(route('salary-management.preview.adjustment', [$previewToken, 0]), [
            'days' => 2,
        ])->assertRedirect(route('salary-management.upload-form'));

        $previewResponse = $this->get(route('salary-management.upload-form'))->assertOk();
        $previewRow = $previewResponse->viewData('previewRows')[0];
        $this->assertSame(2, $previewRow['calculation']['salary_adjustment_days']);
        $this->assertSame(200.0, $previewRow['calculation']['salary_adjustment_deduction']);
        $this->assertSame(1800.0, $previewRow['calculation']['net_salary']);

        $this->get(route('salary-management.upload-form'))
            ->assertOk()
            ->assertViewHas('previewRows', null);

        $this->post(route('salary-management.save'), [
            'preview_token' => $previewToken,
            'mode' => 'save',
        ])->assertRedirect(route('salary-management.attendance'));

        $record = SalaryRecord::query()->where('staff_user_id', $staff->id)->firstOrFail();
        $this->assertSame(2, $record->data['calculation']['salary_adjustment_days']);
        $this->assertSame(200.0, (float) $record->data['calculation']['salary_adjustment_deduction']);
        $this->assertSame(1800.0, (float) $record->data['calculation']['net_salary']);
        $this->assertSame(3000.0, (float) $profile->fresh()->monthly_salary);
    }

    public function test_identical_reupload_does_not_change_saved_or_approved_payroll(): void
    {
        $uploader = $this->createStaff('Payroll', 'Manager', 'duplicate@example.com', 'admin');
        $uploader->givePermissionTo(
            Permission::findOrCreate('view_salary_management', 'web'),
            Permission::findOrCreate('upload_salary_management', 'web')
        );
        $staff = $this->createStaff('Asha', 'Patel', 'asha-duplicate@example.com', 'staff', '8080803375');
        SalaryProfile::create([
            'staff_user_id' => $staff->id,
            'employee_code' => 'TF123',
            'salarybox_id' => 'SB123',
            'monthly_salary' => 3000,
            'standard_deduction' => 0,
        ]);
        $row = ['SB123', 'Asha Patel', 'TF123', '8080803375', 'Main Branch', 'Admin', 'HR Executive', '', 20, 6, 0, 4, 0, 0, 0, 1, 0, 1, 20, 9, 3000];

        $this->actingAs($uploader)->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([$row]),
        ])->assertRedirect(route('salary-management.upload-form'));
        $preview = $this->get(route('salary-management.upload-form'))->assertOk();
        $this->post(route('salary-management.save'), [
            'preview_token' => $preview->viewData('previewToken'),
            'mode' => 'save',
        ])->assertRedirect(route('salary-management.attendance'));

        $record = SalaryRecord::query()->where('staff_user_id', $staff->id)->firstOrFail();
        $this->post(route('salary-management.approve', $record))->assertRedirect();
        $approvedData = $record->fresh()->data;

        $this->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([$row]),
        ])->assertRedirect(route('salary-management.upload-form'));
        $duplicatePreview = $this->get(route('salary-management.upload-form'))
            ->assertOk()
            ->assertSee('Identical to saved');

        $this->post(route('salary-management.save'), [
            'preview_token' => $duplicatePreview->viewData('previewToken'),
            'mode' => 'apply',
            'reason' => 'Retrying the same import',
        ])->assertRedirect(route('salary-management.attendance'));

        $this->assertSame($approvedData, $record->fresh()->data);
        $this->assertSame(0, $record->versions()->count());
    }

    public function test_changed_reupload_requires_confirmation_and_preserves_old_payroll_versions(): void
    {
        $uploader = $this->createStaff('Payroll', 'Manager', 'changed@example.com', 'admin');
        $uploader->givePermissionTo(
            Permission::findOrCreate('view_salary_management', 'web'),
            Permission::findOrCreate('upload_salary_management', 'web')
        );
        $staff = $this->createStaff('Asha', 'Patel', 'asha-changed@example.com', 'staff', '8080803375');
        SalaryProfile::create([
            'staff_user_id' => $staff->id,
            'employee_code' => 'TF124',
            'salarybox_id' => 'SB124',
            'monthly_salary' => 3000,
            'standard_deduction' => 0,
        ]);
        $originalRow = ['SB124', 'Asha Patel', 'TF124', '8080803375', 'Main Branch', 'Admin', 'HR Executive', '', 20, 6, 0, 4, 0, 0, 0, 1, 0, 1, 20, 9, 3000];
        $changedRow = $originalRow;
        $changedRow[9] = 8;

        $this->actingAs($uploader)->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([$originalRow]),
        ])->assertRedirect(route('salary-management.upload-form'));
        $initialPreview = $this->get(route('salary-management.upload-form'))->assertOk();
        $this->post(route('salary-management.save'), [
            'preview_token' => $initialPreview->viewData('previewToken'),
            'mode' => 'save',
        ])->assertRedirect(route('salary-management.attendance'));

        $record = SalaryRecord::query()->where('staff_user_id', $staff->id)->firstOrFail();
        $this->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([$changedRow]),
        ])->assertRedirect(route('salary-management.upload-form'));
        $changedPreview = $this->get(route('salary-management.upload-form'))
            ->assertOk()
            ->assertSee('Changed · Draft')
            ->assertSee('Absent: 6 → 8');

        $this->post(route('salary-management.save'), [
            'preview_token' => $changedPreview->viewData('previewToken'),
            'mode' => 'save',
        ])->assertRedirect(route('salary-management.upload-form'))
            ->assertSessionHasErrors('file');
        $changedPreview = $this->get(route('salary-management.upload-form'))->assertOk();

        $this->post(route('salary-management.save'), [
            'preview_token' => $changedPreview->viewData('previewToken'),
            'mode' => 'apply',
            'reason' => 'Corrected attendance from the revised workbook',
        ])->assertRedirect(route('salary-management.attendance'));

        $updatedRecord = $record->fresh();
        $this->assertSame(8.0, (float) $updatedRecord->data['absent']);
        $this->assertSame('draft', $updatedRecord->data['payroll_status']);
        $this->assertSame(1, $updatedRecord->versions()->count());
        $this->assertSame(6.0, (float) $updatedRecord->versions()->first()->data['absent']);
        $this->assertSame('Corrected attendance from the revised workbook', $updatedRecord->versions()->first()->reason);

        $this->post(route('salary-management.approve', $updatedRecord))->assertRedirect();
        $this->post(route('salary-management.reopen', $updatedRecord), [
            'reason' => 'Approved with an incorrect attendance figure',
        ])->assertRedirect(route('salary-management.attendance'));
        $this->assertSame('draft', $updatedRecord->fresh()->data['payroll_status']);
        $this->assertSame(2, $updatedRecord->versions()->count());
        $reopenedVersion = $updatedRecord->versions()->where('action', 'reopened')->firstOrFail();
        $this->assertSame('approved', $reopenedVersion->data['payroll_status']);
    }

    public function test_salary_reports_lists_staff_and_selected_month_payroll_details(): void
    {
        $viewer = $this->createStaff('Report', 'Viewer', 'report-viewer@example.com', 'admin');
        $viewer->givePermissionTo(Permission::findOrCreate('view_salary_management', 'web'));

        $staffWithPayroll = $this->createStaff('Asha', 'Patel', 'asha-report@example.com', 'staff');
        SalaryProfile::create([
            'staff_user_id' => $staffWithPayroll->id,
            'employee_code' => 'TF123',
            'salarybox_id' => 'SB123',
            'monthly_salary' => 45000,
            'standard_deduction' => 0,
        ]);
        SalaryRecord::create([
            'staff_user_id' => $staffWithPayroll->id,
            'salary_month' => '2026-10-01',
            'data' => [
                'present' => 16,
                'absent' => 15,
                'paid_leave' => 10,
                'unpaid_leave' => 0,
                'payroll_status' => 'approved',
                'salary' => 30000,
                'employee' => [
                    'name' => 'Asha Patel',
                    'employee_id' => 'TF123',
                ],
                'calculation' => [
                    'monthly_salary' => 30000,
                    'total_deductions' => 5322.58,
                    'net_salary' => 24677.42,
                    'salary_adjustment_days' => 0,
                ],
            ],
        ]);
        $this->createStaff('No', 'Payroll', 'no-payroll@example.com', 'staff');

        $response = $this->actingAs($viewer)
            ->get(route('salary-management.reports', ['month' => '2026-10']))
            ->assertOk()
            ->assertViewIs('salary-management.reports')
            ->assertSee('Asha Patel')
            ->assertSee('TF123')
            ->assertSee('₹24,677.42')
            ->assertSee('Approved')
            ->assertDontSee('No payroll');

        $this->assertCount(1, $response->viewData('reportRows'));
        $this->assertSame(1, $response->viewData('summary')['payroll_count']);
        $this->assertSame(30000.0, $response->viewData('reportRows')[0]['monthly_salary']);

        $this->get(route('salary-management.payslip', SalaryRecord::query()->where('staff_user_id', $staffWithPayroll->id)->firstOrFail()))
            ->assertOk()
            ->assertViewIs('salary-management.payslip')
            ->assertSee('EMPLOYEE SALARY SLIP');

        $filteredResponse = $this->get(route('salary-management.reports', [
            'month' => '2026-10',
            'status' => 'approved',
            'search' => 'asha-report@example.com',
        ]))->assertOk()
            ->assertSee('Asha Patel');

        $this->assertCount(1, $filteredResponse->viewData('reportRows'));
        $this->assertSame(1, $filteredResponse->viewData('summary')['payroll_count']);

        $exportResponse = $this->get(route('salary-management.reports.export', [
            'month' => '2026-10',
            'status' => 'approved',
            'search' => 'asha-report@example.com',
        ]))->assertOk()
            ->assertDownload('salary-reports-2026-10.xlsx');

        $temporaryFile = tempnam(sys_get_temp_dir(), 'salary-report-');
        try {
            file_put_contents($temporaryFile, $exportResponse->streamedContent());
            $exportedSheet = IOFactory::load($temporaryFile)->getActiveSheet();
            $this->assertSame('Asha Patel', $exportedSheet->getCell('A2')->getValue());
            $this->assertSame('asha-report@example.com', $exportedSheet->getCell('B2')->getValue());
            $this->assertSame(1, $exportedSheet->getHighestDataRow() - 1);
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    private function createStaff(string $first, string $last, string $email, string $role, string $phone = ''): User
    {
        return User::create([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'phone' => $phone,
            'password' => bcrypt('password'),
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function attendanceWorkbook(array $employeeRows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Attendance Muster Report (01 Sep 2026 - 30 Sep 2026)');
        $sheet->fromArray([
            ['SalaryBox ID', 'Employee Name', 'Employee ID', 'Phone Number', 'Branch', 'Department', 'Designation', 'Employee Type', 'Present', 'Absent', 'Half Day', 'Week Off', 'Holiday', 'Paid Leave', 'Unpaid Leave', 'Overtime', '', '', 'Late Arrival', 'Early Leaving', 'Salary'],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Working Day', 'Week Off', 'Holiday', '', '', ''],
        ], null, 'A2');
        foreach ($employeeRows as $offset => $employeeRow) {
            $sheet->fromArray([$employeeRow], null, 'A' . ($offset + 4));
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'attendance-');
        (new Xlsx($spreadsheet))->save($temporaryFile);
        $content = file_get_contents($temporaryFile);
        unlink($temporaryFile);

        return UploadedFile::fake()->createWithContent('attendance.xlsx', $content);
    }
}
