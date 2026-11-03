@extends('layout.master')

@section('content')
<style>
    .payroll-page{margin:auto}.payroll-heading{font-size:22px;font-weight:600}.payroll-card{border:1px solid #dee2e6;border-radius:10px;box-shadow:none;overflow:hidden}.payroll-card .card-header{padding:14px 18px;font-weight:700;background:#fff;border-bottom:1px solid #edf0f5;color:#243653}.payroll-table thead th{background:#f8f9fa;border:0;border-bottom:1px solid #dee2e6;color:#5c6879;font-size:11px;font-weight:600;letter-spacing:.3px;padding:10px 12px;text-transform:uppercase;white-space:nowrap}.payroll-table tbody td{border:0;border-bottom:1px solid #edf0f5;padding:11px 12px;vertical-align:middle}.avatar-letter{align-items:center;background:#edf2ff;border-radius:50%;color:#0d6efd;display:inline-flex;font-weight:800;height:32px;justify-content:center;margin-right:8px;width:32px}#salary-master-table th:nth-child(2),#salary-master-table td:nth-child(2),#salary-master-table th:nth-child(3),#salary-master-table td:nth-child(3),#salary-master-table th:nth-child(5),#salary-master-table td:nth-child(5){display:none}form[action*="salary-management/profiles"] .col-lg-2:has(input[name="employee_code"]),form[action*="salary-management/profiles"] .col-lg-2:has(input[name="salarybox_id"]),form[action*="salary-management/profiles"] .col-lg-2:has(input[name="standard_deduction"]){display:none}
</style>
<div class="page-wrapper"><div class="page-content payroll-page">
    @include('layout.errors')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3"><div class="ps-0"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item">Salary Management</li><li class="breadcrumb-item active">Salary Master</li></ol></div></div>
    <div class="mb-4"><div class="small text-muted mb-1">PAYROLL WORKSPACE</div><h1 class="payroll-heading mb-1">Salary Master</h1><div class="small text-muted">Set and view monthly salary for staff members.</div></div>
    @if($canManage)
    <div class="card payroll-card"><div class="card-header">Salary Master <small class="fw-normal text-muted">— staff details are managed from <a href="{{ route('staff') }}">Staff</a></small></div><div class="card-body">
        <form method="POST" action="{{ route('salary-management.profiles.save') }}" class="row g-2 mb-4">@csrf
            <div class="col-lg-3"><select name="staff_user_id" class="form-select" required><option value="">Select staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->id }}">{{ $staff->full_name }} — {{ $staff->email }}</option>@endforeach</select></div>
            <div class="col-lg-2"><input name="employee_code" class="form-control" placeholder="Employee ID"></div><div class="col-lg-2"><input name="salarybox_id" class="form-control" placeholder="SalaryBox ID"></div><div class="col-lg-2"><input name="monthly_salary" type="number" min="0" step="0.01" class="form-control" placeholder="Monthly salary" required></div><div class="col-lg-2"><input name="standard_deduction" type="number" min="0" step="0.01" class="form-control" placeholder="TDS / PF"></div><div class="col-lg-1"><button class="btn btn-success w-100">Save</button></div>
        </form>
        <div class="table-responsive"><table id="salary-master-table" class="table payroll-table mb-0"><thead><tr><th>Staff</th><th>Employee ID</th><th>SalaryBox ID</th><th>Monthly Salary</th><th>Standard deduction</th></tr></thead><tbody>@forelse($salaryProfiles as $profile)<tr><td><span class="avatar-letter">{{ strtoupper(substr($profile->staffUser?->name ?? 'S',0,1)) }}</span><strong>{{ $profile->staffUser?->name }}</strong></td><td>{{ $profile->employee_code ?: '—' }}</td><td>{{ $profile->salarybox_id ?: '—' }}</td><td class="fw-semibold">₹{{ number_format((float)$profile->monthly_salary,2) }}</td><td>₹{{ number_format((float)$profile->standard_deduction,2) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No salary profile yet. Add salary before importing attendance.</td></tr>@endforelse</tbody></table></div>
    </div></div>
    @endif
</div></div>
@endsection
@push('scripts')<script>$(function(){if($('#salary-master-table').length&&$.fn.DataTable&&!$.fn.DataTable.isDataTable('#salary-master-table')){$('#salary-master-table').DataTable({pageLength:10,order:[],language:{search:'',searchPlaceholder:'Search...'}});}});</script>@endpush
