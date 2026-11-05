@extends('layout.master')

@section('content')
<style>
    .salary-reports-page{--report-ink:#1d3150;--report-muted:#75839a;--report-line:#e7edf5;margin:auto;color:var(--report-ink)}.salary-reports-heading{font-size:24px;font-weight:700;letter-spacing:-.3px}.salary-reports-subtitle{color:var(--report-muted);font-size:13px}.salary-report-card,.report-filter-card{border:1px solid var(--report-line);border-radius:14px;box-shadow:0 5px 20px rgba(27,52,89,.045);overflow:hidden;background:#fff}.report-filter-card{padding:18px;margin-bottom:20px}.filter-label{font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#697991;margin-bottom:6px}.salary-reports-page .form-control,.salary-reports-page .form-select{min-height:40px;border-color:#dfe6f0;border-radius:8px;font-size:13px}.salary-reports-page .btn{min-height:40px;border-radius:8px;font-size:13px;font-weight:600}.salary-report-card .card-header{padding:15px 18px;font-weight:700;background:#fff;border-bottom:1px solid #edf0f5;color:var(--report-ink);display:flex;align-items:center;justify-content:space-between}.salary-report-table thead th{background:#f7f9fc;border:0;border-bottom:1px solid var(--report-line);color:#65738a;font-size:10px;font-weight:700;letter-spacing:.35px;padding:12px;text-transform:uppercase;white-space:nowrap}.salary-report-table tbody td{border:0;border-bottom:1px solid #edf0f5;padding:12px;vertical-align:middle;white-space:nowrap;font-size:13px}.salary-report-table tbody tr:hover{background:#fafcff}.report-stat{height:100%;border:1px solid var(--report-line);border-radius:13px;padding:17px;background:#fff;box-shadow:0 4px 16px rgba(27,52,89,.035)}.report-stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}.report-stat-icon{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:#edf4ff;color:#2671d8;font-size:17px}.report-stat small{display:block;color:var(--report-muted);font-size:12px;margin-bottom:5px}.report-stat strong{font-size:20px;color:var(--report-ink);font-weight:700}.staff-report-name{font-weight:700;color:var(--report-ink)}.staff-report-meta{font-size:11px;color:var(--report-muted);margin-top:3px}.report-count{font-size:12px;color:var(--report-muted);font-weight:500}.report-status{font-size:11px;padding:6px 9px;border-radius:20px}
</style>

<div class="page-wrapper"><div class="page-content salary-reports-page">
    @include('layout.errors')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3"><div class="ps-0"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item">Salary Management</li><li class="breadcrumb-item active">Salary Reports</li></ol></div></div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div><div class="small text-primary fw-bold mb-1">PAYROLL WORKSPACE</div><h1 class="salary-reports-heading mb-1">Salary Reports</h1><div class="salary-reports-subtitle">Review staff compensation, attendance, and payroll status.</div></div>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-2"><i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::createFromFormat('Y-m', $salaryMonth)->format('F Y') }}</span>
    </div>

    <form method="GET" action="{{ route('salary-management.reports') }}" class="report-filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-6 col-lg-2"><label for="salaryReportMonth" class="filter-label">Payroll month</label><input id="salaryReportMonth" type="month" name="month" value="{{ $salaryMonth }}" class="form-control"></div>
            <div class="col-6 col-lg-3"><label for="salaryReportDepartment" class="filter-label">Department</label><select id="salaryReportDepartment" name="department" class="form-select"><option value="">All departments</option>@foreach($departmentOptions as $department)<option value="{{ $department }}" @selected($filters['department'] === $department)>{{ $department }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label for="salaryReportStatus" class="filter-label">Payroll status</label><select id="salaryReportStatus" name="status" class="form-select"><option value="">All statuses</option><option value="approved" @selected($filters['status'] === 'approved')>Approved</option><option value="draft" @selected($filters['status'] === 'draft')>Draft / Pending</option></select></div>
            <div class="col-12 col-lg-3"><label for="salaryReportSearch" class="filter-label">Search staff</label><input id="salaryReportSearch" type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Name, email, employee ID"></div>
            <div class="col-6 col-lg-1 d-grid"><button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Filter</button></div>
            <div class="col-6 col-lg-1 d-grid"><a href="{{ route('salary-management.reports') }}" class="btn btn-outline-secondary">Reset</a></div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="report-stat"><div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-group"></i></span><span class="report-count">Filtered view</span></div><small>Staff listed</small><strong>{{ number_format($summary['staff_count']) }}</strong></div></div>
        <div class="col-6 col-xl-3"><div class="report-stat"><div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-receipt"></i></span><span class="report-count">Selected month</span></div><small>Payroll records</small><strong>{{ number_format($summary['payroll_count']) }}</strong></div></div>
        <div class="col-6 col-xl-3"><div class="report-stat"><div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-wallet"></i></span><span class="report-count">Attendance payroll</span></div><small>Monthly salary total</small><strong>₹{{ number_format($summary['monthly_salary'], 2) }}</strong></div></div>
        <div class="col-6 col-xl-3"><div class="report-stat"><div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-money"></i></span><span class="report-count">{{ $summary['pending_count'] }} pending</span></div><small>Net payable · {{ \Carbon\Carbon::createFromFormat('Y-m', $salaryMonth)->format('M Y') }}</small><strong>₹{{ number_format($summary['net_payable'], 2) }}</strong></div></div>
    </div>

    <div class="card salary-report-card">
        <div class="card-header">
            <span><i class="bx bx-spreadsheet text-primary me-1"></i> Staff Payroll Details <span class="report-count ms-1">{{ $reportRows->count() }} result(s)</span></span>
            <form method="GET" action="{{ route('salary-management.reports.export') }}">
                <input type="hidden" name="month" value="{{ $salaryMonth }}">
                <input type="hidden" name="department" value="{{ $filters['department'] }}">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
                <input type="hidden" name="search" value="{{ $filters['search'] }}">
                <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-download me-1"></i>Export to Excel</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table salary-report-table mb-0">
                <thead><tr>
                    <th>Staff</th><th>Department / Designation</th><th>Employee ID</th><th>Monthly Salary</th>
                    <th>Present</th><th>Absent</th><th>Half day</th><th>Paid leave</th><th>Unpaid leave</th>
                    <th>Adjustment days</th><th>Total deductions</th><th>Net payable</th><th>Payroll status</th><th>Salary Slip</th>
                </tr></thead>
                <tbody>
                    @forelse($reportRows as $row)
                        <tr>
                            <td><div class="staff-report-name">{{ $row['name'] ?: '—' }}</div><div class="staff-report-meta">{{ $row['staff']?->email ?? '—' }} · {{ $row['staff_status'] }}</div></td>
                            <td>{{ $row['department'] }}<div class="staff-report-meta">{{ $row['designation'] }}</div></td>
                            <td>{{ $row['employee_code'] }}</td>
                            <td>₹{{ number_format($row['monthly_salary'], 2) }}</td>
                            <td>{{ data_get($row, 'attendance.present', 0) }}</td>
                            <td>{{ data_get($row, 'attendance.absent', 0) }}</td>
                            <td>{{ data_get($row, 'attendance.half_day', 0) }}</td>
                            <td>{{ data_get($row, 'attendance.paid_leave', 0) }}</td>
                            <td>{{ data_get($row, 'attendance.unpaid_leave', 0) }}</td>
                            <td>{{ data_get($row, 'calculation.salary_adjustment_days', 0) }}</td>
                            <td>₹{{ number_format((float) data_get($row, 'calculation.total_deductions', 0), 2) }}</td>
                            <td class="fw-semibold">₹{{ number_format((float) data_get($row, 'calculation.net_salary', 0), 2) }}</td>
                            <td>
                                <span class="badge report-status {{ $row['payroll_status'] === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($row['payroll_status'] ?? 'draft') }}</span>
                            </td>
                            <td>
                                @if($row['payroll_status'] === 'approved')
                                    <a href="{{ route('salary-management.payslip', $row['record']) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bx bx-file me-1"></i>View Salary Slip</a>
                                @else
                                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Approve payroll before viewing the salary slip."><i class="bx bx-file me-1"></i>View Salary Slip</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="14" class="text-center text-muted py-4">No attendance payroll records found for this month and filter selection.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div></div>
@endsection
