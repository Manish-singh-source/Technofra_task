@extends('layout.master')

@section('content')
<style>
    .payroll-page{margin:auto}.payroll-heading{font-size:22px;font-weight:600}.payroll-card{border:1px solid #dee2e6;border-radius:10px;box-shadow:none;overflow:hidden}.payroll-card .card-header{padding:14px 18px;font-weight:700;background:#fff;border-bottom:1px solid #edf0f5;color:#243653}.payroll-table thead th{background:#f8f9fa;border:0;border-bottom:1px solid #dee2e6;color:#5c6879;font-size:11px;font-weight:600;letter-spacing:.3px;padding:10px 12px;text-transform:uppercase;white-space:nowrap}.payroll-table tbody td{border:0;border-bottom:1px solid #edf0f5;padding:11px 12px;vertical-align:middle}
</style>
<div class="page-wrapper"><div class="page-content payroll-page">
    @include('layout.errors')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3"><div class="ps-0"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item">Salary Management</li><li class="breadcrumb-item active">Import SalaryBox Attendance</li></ol></div></div>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4"><div><div class="small text-muted mb-1">PAYROLL WORKSPACE</div><h1 class="payroll-heading mb-1">Import SalaryBox Attendance</h1><div class="small text-muted">Upload attendance, review payroll, and manage saved payroll records.</div></div></div>
    @if($canManage)
    <div class="card payroll-card mb-4"><div class="card-header d-flex align-items-center justify-content-between"><span><i class="bx bx-upload text-primary me-1"></i> Import SalaryBox Attendance</span><a href="{{ route('salary-management.template') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-download"></i> HR Adjustment Template</a></div><div class="card-body"><form action="{{ route('salary-management.upload') }}" method="POST" enctype="multipart/form-data" class="row align-items-end g-3">@csrf<div class="col-md-3"><label class="form-label">Report month (fallback)</label><input type="month" name="month" class="form-control" value="{{ old('month', $salaryMonth) }}"></div><div class="col-md-6"><label class="form-label">Attendance Muster Report</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required></div><div class="col-md-3"><button class="btn btn-primary w-100"><i class="bx bx-spreadsheet"></i> Upload & Preview</button></div></form></div></div>
    @endif
    @if(is_array($previewRows)) @php($hasErrors=collect($previewRows)->contains(fn($row)=>$row['errors']!==[]))
    <div class="card payroll-card mb-4">
        <div class="card-header d-flex justify-content-between"><span>Payroll Preview — {{ count($previewRows) }} employee(s)</span><span class="badge {{ $hasErrors?'bg-danger':'bg-success' }}">{{ $hasErrors?'Fix errors before saving':'Ready to save' }}</span></div>
        <div class="table-responsive"><table class="table payroll-table mb-0">
            <thead><tr><th>Employee</th><th>Present / Absent</th><th>Salary</th><th>Per day</th><th>Deductions</th><th>Net payable</th><th>Adjustment</th></tr></thead>
            <tbody>
                @foreach($previewRows as $rowIndex=>$row)
                    @php($currentAdjustmentDays = (int) data_get($row, 'calculation.salary_adjustment_days', 0))
                    @php($currentAdjustmentDeduction = (float) data_get($row, 'calculation.salary_adjustment_deduction', 0))
                    <tr class="{{ $row['errors']!==[]?'table-danger':'' }}">
                        <td><strong>{{ $row['employee']['name'] }}</strong><br><small>{{ $row['employee']['employee_id'] ?: $row['employee']['phone'] }}</small></td>
                        <td>{{ $row['data']['present'] }} / {{ $row['data']['absent'] }}</td>
                        <td>₹{{ number_format((float)$row['data']['salary'],2) }}</td>
                        <td>₹{{ number_format((float)data_get($row,'calculation.per_day_salary',0),2) }}</td>
                        <td>₹{{ number_format((float)data_get($row,'calculation.total_deductions',0),2) }}</td>
                        <td class="fw-bold text-primary">₹{{ number_format((float)data_get($row,'calculation.net_salary',0),2) }}</td>
                        <td>
                            @if($row['errors']!==[])
                                <span class="small text-danger">{{ implode(' ', $row['errors']) }}</span>
                            @endif
                            @if($row['errors']===[])
                                <button type="button" class="btn btn-outline-primary btn-sm salary-adjust-button"
                                    data-bs-toggle="modal" data-bs-target="#salaryAdjustmentModal"
                                    data-update-url="{{ route('salary-management.preview.adjustment', [$previewToken,$rowIndex]) }}"
                                    data-employee="{{ $row['employee']['name'] }}"
                                    data-month="{{ \Carbon\Carbon::createFromFormat('Y-m', $salaryMonth)->format('F Y') }}"
                                    data-total-days="{{ data_get($row,'calculation.days_in_month',0) }}"
                                    data-days="{{ $currentAdjustmentDays }}"
                                    data-daily-rate="{{ data_get($row,'calculation.per_day_salary',0) }}"
                                    data-current-net-payable="{{ number_format((float)data_get($row,'calculation.net_salary',0), 2, '.', '') }}"
                                    data-base-salary="{{ number_format((float)data_get($row,'calculation.net_salary',0) + $currentAdjustmentDeduction, 2, '.', '') }}">Adjust Salary</button>
                                @if($currentAdjustmentDays>0)
                                    <div class="small text-muted mt-1">{{ $currentAdjustmentDays }} day(s): −₹{{ number_format($currentAdjustmentDeduction,2) }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="card-body text-end"><form action="{{ route('salary-management.save') }}" method="POST">@csrf<input type="hidden" name="preview_token" value="{{ $previewToken }}"><button class="btn btn-success" {{ $hasErrors?'disabled':'' }}>Save as Draft Payroll</button></form></div>
    </div>
    <div class="modal fade" id="salaryAdjustmentModal" tabindex="-1" aria-labelledby="salaryAdjustmentModalLabel" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <form id="salaryAdjustmentForm" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title" id="salaryAdjustmentModalLabel">Adjust Salary</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label for="salaryAdjustmentEmployeeMonth" class="form-label">Employee &amp; month</label><input id="salaryAdjustmentEmployeeMonth" type="text" class="form-control" readonly></div>
                    <div class="mb-3"><label for="salaryAdjustmentTotalDays" class="form-label">Total days in month</label><input id="salaryAdjustmentTotalDays" type="number" class="form-control" readonly></div>
                    <div class="salary-adjustment-panel mb-3">
                        <label for="salaryAdjustmentDays" class="form-label fw-semibold">Days to deduct</label>
                        <input id="salaryAdjustmentDays" class="form-control" type="number" name="days" min="1" max="31" step="1" placeholder="Enter days to deduct" required>
                    </div>
                    <div class="mb-3"><label for="salaryAdjustmentCurrentNet" class="form-label fw-semibold">Current net payable amount</label><div class="input-group"><span class="input-group-text">₹</span><input id="salaryAdjustmentCurrentNet" type="text" class="form-control" readonly></div></div>
                    <div><label for="salaryAdjustmentUpdatedNet" class="form-label fw-semibold">Updated net payable amount</label><div class="input-group"><span class="input-group-text">₹</span><input id="salaryAdjustmentUpdatedNet" type="text" class="form-control fw-bold" readonly></div></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
            </form>
        </div></div>
    </div>
    @endif
    <div class="card payroll-card"><div class="card-header"><i class="bx bx-list-ul text-primary me-1"></i> Saved Payroll</div><div class="table-responsive"><table id="payroll-register-table" class="table payroll-table mb-0"><thead><tr><th>Employee</th><th>Month</th><th>Net Salary</th><th>Payroll status</th><th class="text-end">Action</th></tr></thead><tbody>@forelse($records as $record)<tr><td><strong>{{ data_get($record->data,'employee.name',$record->staffUser?->name) }}</strong></td><td>{{ $record->salary_month?->format('F Y') }}</td><td class="fw-bold text-primary">₹{{ number_format((float)data_get($record->data,'calculation.net_salary',0),2) }}</td><td><span class="badge {{ data_get($record->data,'payroll_status')==='approved'?'bg-success':'bg-warning text-dark' }}">{{ ucfirst(data_get($record->data,'payroll_status','draft')) }}</span></td><td class="text-end">@if(data_get($record->data,'payroll_status')==='approved')<a target="_blank" href="{{ route('salary-management.payslip',$record) }}" class="btn btn-outline-primary btn-sm">Salary Slip</a>@elseif($canManage)<form class="d-inline" method="POST" action="{{ route('salary-management.approve',$record) }}">@csrf<button class="btn btn-success btn-sm">Approve</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No saved payroll yet.</td></tr>@endforelse</tbody></table></div></div>
</div></div>
@endsection
@push('styles')<style>.salary-adjustment-panel{border:1px solid #e4eaf2;border-radius:12px;padding:14px 16px;background:#f8faff}.salary-adjustment-panel .form-range{height:1.2rem}.salary-adjustment-summary{background:#f5f7fb;border:1px solid #e8edf5}.salary-adjustment-summary .input-group{max-width:170px}.salary-adjustment-summary .form-control{background:#fff}</style>@endpush
@push('scripts')<script>$(function(){if($('#payroll-register-table').length&&$.fn.DataTable&&!$.fn.DataTable.isDataTable('#payroll-register-table')){$('#payroll-register-table').DataTable({pageLength:10,order:[],language:{search:'',searchPlaceholder:'Search...'}});}});const salaryAdjustmentForm=document.getElementById('salaryAdjustmentForm');const salaryAdjustmentDays=document.getElementById('salaryAdjustmentDays');function updateNetPayable(){const days=Number(salaryAdjustmentDays.value);const currentNet=Number(salaryAdjustmentForm.dataset.currentNetPayable||0);const baseSalary=Number(salaryAdjustmentForm.dataset.baseSalary||currentNet);const dailyRate=Number(salaryAdjustmentForm.dataset.dailyRate||0);document.getElementById('salaryAdjustmentUpdatedNet').value=days>0?(baseSalary-days*dailyRate).toFixed(2):currentNet.toFixed(2);}document.addEventListener('click',function(event){const button=event.target.closest('.salary-adjust-button');if(!button)return;salaryAdjustmentForm.action=button.dataset.updateUrl;salaryAdjustmentForm.dataset.dailyRate=button.dataset.dailyRate||0;salaryAdjustmentForm.dataset.baseSalary=button.dataset.baseSalary||0;salaryAdjustmentForm.dataset.currentNetPayable=button.dataset.currentNetPayable||0;document.getElementById('salaryAdjustmentEmployeeMonth').value=button.dataset.employee+' — '+button.dataset.month;document.getElementById('salaryAdjustmentTotalDays').value=button.dataset.totalDays||'';document.getElementById('salaryAdjustmentCurrentNet').value=Number(button.dataset.currentNetPayable||0).toFixed(2);salaryAdjustmentDays.max=button.dataset.totalDays||31;salaryAdjustmentDays.value=button.dataset.days==='0'?'':button.dataset.days;updateNetPayable();});salaryAdjustmentDays.addEventListener('input',updateNetPayable);</script>@endpush
