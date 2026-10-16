<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SalaryRecord;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

        $response = $this->actingAs($uploader)->post(route('salary-management.upload'), [
            'file' => $this->attendanceWorkbook([
                ['SBK123', 'Khushi Yadav', 'TFR012', '9321807085', 'Main Branch', 'Admin', 'HR Executive', '', 20, 6, 0, 4, 0, 0, 0, 1, 0, 1, 20, 9, 3000],
                ['SBM456', 'Manish Singh', 'TFR037', '7039553407', 'Main Branch', 'Web designer', 'Website Designer', 'Permanent', 17, 6, 0, 4, 0, 3, 0, 1, 0, 0, 15, 6, 4000],
                ['SBR456', 'Rahul Nadar', '', '8591028709', 'Main Branch', 'Web', 'Developer', '', 0, 29, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 8000],
            ]),
        ]);

        $response->assertOk()
            ->assertViewIs('salary-management.attendance')
            ->assertSee('September 2026')
            ->assertSee('TFR012')
            ->assertSee('Khushi Yadav')
            ->assertSee('khushi@example.com')
            ->assertSee('Administration')
            ->assertSee('HR Executive')
            ->assertSee('Manish Singh')
            ->assertSee('Deleted')
            ->assertSee('3,000.00')
            ->assertSee('4,000.00')
            ->assertSee('8,000.00')
            ->assertSee('800.00')
            ->assertSee('133.33')
            ->assertSee('3,333.33');

        $this->assertDatabaseCount('salary_records', 0);

        $this->post(route('salary-management.save'), [
            'preview_token' => $response->viewData('previewToken'),
        ])->assertRedirect(route('salary-management.index'));

        $this->assertDatabaseCount('salary_records', 3);
        $savedRecord = SalaryRecord::query()->where('staff_user_id', $khushi->id)->firstOrFail();
        $this->assertSame('2026-09-01', $savedRecord->salary_month->toDateString());
        $this->assertSame(3000.0, (float) $savedRecord->data['salary']);
        $this->assertSame(20.0, (float) $savedRecord->data['present']);
        $this->assertSame(6.0, (float) $savedRecord->data['absent']);

        $manishRecord = SalaryRecord::query()->where('staff_user_id', $manish->id)->firstOrFail();
        $this->assertSame(133.33, (float) $manishRecord->data['calculation']['per_day_salary']);
        $this->assertSame(800.0, (float) $manishRecord->data['calculation']['absent_deduction']);
        $this->assertSame(133.33, (float) $manishRecord->data['calculation']['overtime_payment']);
        $this->assertSame(3333.33, (float) $manishRecord->data['calculation']['net_salary']);
    }

    public function test_paid_leave_is_paid_but_unpaid_leave_and_half_days_are_deducted(): void
    {
        $calculation = (new SalaryCalculationService())->calculate(3000, [
            'absent' => 0,
            'paid_leave' => 5,
            'unpaid_leave' => 1,
            'half_day' => 2,
        ]);

        $this->assertSame(100.0, $calculation['per_day_salary']);
        $this->assertSame(100.0, $calculation['unpaid_leave_deduction']);
        $this->assertSame(100.0, $calculation['half_day_deduction']);
        $this->assertSame(0.0, $calculation['absent_deduction']);
        $this->assertSame(2800.0, $calculation['net_salary']);
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
