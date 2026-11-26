@php($isUnifiedTab = $isUnifiedTab ?? false)

<form method="GET" action="{{ $reportFilterAction }}" class="report-filter-card">
    @if($isUnifiedTab)<input type="hidden" name="tab" value="reports">@endif
    <div class="row g-3 align-items-end">
        <div class="col-6 col-lg-2">
            <label for="salaryReportMonth" class="filter-label">Payroll month</label>
            <input id="salaryReportMonth" type="month" name="month" value="{{ $reportMonth }}" class="form-control">
        </div>
        <div class="col-6 col-lg-3">
            <label for="salaryReportDepartment" class="filter-label">Department</label>
            <select id="salaryReportDepartment" name="department" class="form-select">
                <option value="">All departments</option>
                @foreach($reportDepartmentOptions as $department)
                    <option value="{{ $department }}" @selected($reportFilters['department'] === $department)>{{ $department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label for="salaryReportStatus" class="filter-label">Payroll status</label>
            <select id="salaryReportStatus" name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="approved" @selected($reportFilters['status'] === 'approved')>Approved</option>
                <option value="draft" @selected($reportFilters['status'] === 'draft')>Draft / Pending</option>
            </select>
        </div>
        <div class="col-12 col-lg-3">
            <label for="salaryReportSearch" class="filter-label">Search staff</label>
            <input id="salaryReportSearch" type="search" name="search" value="{{ $reportFilters['search'] }}" class="form-control" placeholder="Name, email, employee ID">
        </div>
        <div class="col-6 col-lg-1 d-grid">
            <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Filter</button>
        </div>
        <div class="col-6 col-lg-1 d-grid">
            <a href="{{ $reportResetUrl }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="report-stat">
            <div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-group"></i></span><span class="report-count">Filtered view</span></div>
            <small>Staff listed</small>
            <strong>{{ number_format($reportSummary['staff_count']) }}</strong>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="report-stat">
            <div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-receipt"></i></span><span class="report-count">Selected month</span></div>
            <small>Payroll records</small>
            <strong>{{ number_format($reportSummary['payroll_count']) }}</strong>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="report-stat">
            <div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-wallet"></i></span><span class="report-count">Attendance payroll</span></div>
            <small>Monthly salary total</small>
            <strong>₹{{ number_format($reportSummary['monthly_salary'], 2) }}</strong>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="report-stat">
            <div class="report-stat-top"><span class="report-stat-icon"><i class="bx bx-money"></i></span><span class="report-count">{{ $reportSummary['pending_count'] }} pending</span></div>
            <small>Net payable · {{ \Carbon\Carbon::createFromFormat('Y-m', $reportMonth)->format('M Y') }}</small>
            <strong>₹{{ number_format($reportSummary['net_payable'], 2) }}</strong>
        </div>
    </div>
</div>

<div class="card salary-report-card">
    <div class="card-header">
        <span><i class="bx bx-spreadsheet text-primary me-1"></i> Staff Payroll Details <span class="report-count ms-1">{{ $reportRows->count() }} result(s)</span></span>
        <form method="GET" action="{{ route('salary-management.reports.export') }}">
            <input type="hidden" name="month" value="{{ $reportMonth }}">
            <input type="hidden" name="department" value="{{ $reportFilters['department'] }}">
            <input type="hidden" name="status" value="{{ $reportFilters['status'] }}">
            <input type="hidden" name="search" value="{{ $reportFilters['search'] }}">
            <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-download me-1"></i>Export to Excel</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table salary-report-table mb-0">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Department / Designation</th>
                    <th>Employee ID</th>
                    <th>Monthly Salary</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Half day</th>
                    <th>Paid leave</th>
                    <th>Unpaid leave</th>
                    <th>Adjustment days</th>
                    <th>Total deductions</th>
                    <th>Net payable</th>
                    <th>Payroll status</th>
                    <th>Salary Slip</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportRows as $row)
                    <tr>
                        <td>
                            <div class="staff-report-name">{{ $row['name'] ?: '—' }}</div>
                            <div class="staff-report-meta">{{ $row['staff']?->email ?? '—' }} · {{ $row['staff_status'] }}</div>
                        </td>
                        <td>
                            {{ $row['department'] }}
                            <div class="staff-report-meta">{{ $row['designation'] }}</div>
                        </td>
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
