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

    @include('salary-management.partials.reports-content', [
        'isUnifiedTab' => false,
        'reportMonth' => $salaryMonth,
        'reportRows' => $reportRows,
        'reportSummary' => $summary,
        'reportDepartmentOptions' => $departmentOptions,
        'reportFilters' => $filters,
        'reportFilterAction' => route('salary-management.reports'),
        'reportResetUrl' => route('salary-management.reports'),
    ])
</div></div>
@endsection
