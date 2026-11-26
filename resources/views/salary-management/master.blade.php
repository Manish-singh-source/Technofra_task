@extends('layout.master')

@push('styles')
<style>
    .payroll-page { margin: auto; }
    .payroll-heading { font-size: 22px; font-weight: 600; }
    .payroll-card { border: 1px solid #dee2e6; border-radius: 10px; box-shadow: none; overflow: hidden; }
    .payroll-card .card-header { padding: 14px 18px; font-weight: 700; background: #fff; border-bottom: 1px solid #edf0f5; color: #243653; }
    .payroll-table thead th { background: #f8f9fa; border: 0; border-bottom: 1px solid #dee2e6; color: #5c6879; font-size: 11px; font-weight: 600; letter-spacing: .3px; padding: 10px 12px; text-transform: uppercase; white-space: nowrap; }
    .payroll-table tbody td { border: 0; border-bottom: 1px solid #edf0f5; padding: 11px 12px; vertical-align: middle; }
    .avatar-letter { align-items: center; background: #edf2ff; border-radius: 50%; color: #0d6efd; display: inline-flex; font-weight: 800; height: 32px; justify-content: center; margin-right: 8px; width: 32px; }
    #salary-master-table th:nth-child(2), #salary-master-table td:nth-child(2),
    #salary-master-table th:nth-child(3), #salary-master-table td:nth-child(3),
    #salary-master-table th:nth-child(5), #salary-master-table td:nth-child(5) { display: none; }
    form[action*="salary-management/profiles"] .col-lg-2:has(input[name="employee_code"]),
    form[action*="salary-management/profiles"] .col-lg-2:has(input[name="salarybox_id"]),
    form[action*="salary-management/profiles"] .col-lg-2:has(input[name="standard_deduction"]) { display: none; }
    .salary-adjustment-panel { border: 1px solid #e4eaf2; border-radius: 12px; padding: 14px 16px; background: #f8faff; }
    .bulk-approve-checkbox, #bulkApproveSelectAll { width: 1.15rem; height: 1.15rem; vertical-align: middle; cursor: pointer; }
    #bulkApproveButton { margin-bottom: 14px; }
    #payroll-register-table_wrapper .dataTables_filter { margin-bottom: 12px; }
    .salary-reports-page { --report-ink: #1d3150; --report-muted: #75839a; --report-line: #e7edf5; color: var(--report-ink); }
    .salary-report-card, .report-filter-card { border: 1px solid var(--report-line); border-radius: 14px; box-shadow: 0 5px 20px rgba(27, 52, 89, .045); overflow: hidden; background: #fff; }
    .report-filter-card { padding: 18px; margin-bottom: 20px; }
    .filter-label { font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #697991; margin-bottom: 6px; }
    .salary-reports-page .form-control, .salary-reports-page .form-select { min-height: 40px; border-color: #dfe6f0; border-radius: 8px; font-size: 13px; }
    .salary-reports-page .btn { min-height: 40px; border-radius: 8px; font-size: 13px; font-weight: 600; }
    .salary-report-card .card-header { padding: 15px 18px; font-weight: 700; background: #fff; border-bottom: 1px solid #edf0f5; color: var(--report-ink); display: flex; align-items: center; justify-content: space-between; }
    .salary-report-table thead th { background: #f7f9fc; border: 0; border-bottom: 1px solid var(--report-line); color: #65738a; font-size: 10px; font-weight: 700; letter-spacing: .35px; padding: 12px; text-transform: uppercase; white-space: nowrap; }
    .salary-report-table tbody td { border: 0; border-bottom: 1px solid #edf0f5; padding: 12px; vertical-align: middle; white-space: nowrap; font-size: 13px; }
    .salary-report-table tbody tr:hover { background: #fafcff; }
    .report-stat { height: 100%; border: 1px solid var(--report-line); border-radius: 13px; padding: 17px; background: #fff; box-shadow: 0 4px 16px rgba(27, 52, 89, .035); }
    .report-stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .report-stat-icon { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: #edf4ff; color: #2671d8; font-size: 17px; }
    .report-stat small { display: block; color: var(--report-muted); font-size: 12px; margin-bottom: 5px; }
    .report-stat strong { font-size: 20px; color: var(--report-ink); font-weight: 700; }
    .staff-report-name { font-weight: 700; color: var(--report-ink); }
    .staff-report-meta { font-size: 11px; color: var(--report-muted); margin-top: 3px; }
    .report-count { font-size: 12px; color: var(--report-muted); font-weight: 500; }
    .report-status { font-size: 11px; padding: 6px 9px; border-radius: 20px; }
    .crm-salary-tabs { border-bottom: 1px solid #e2e8f0; gap: 6px; }
    .crm-salary-tabs .nav-link { border: 0; border-radius: 10px 10px 0 0; padding: 10px 18px; font-weight: 600; color: #475569; background: transparent; display: inline-flex; align-items: center; gap: 6px; }
    .crm-salary-tabs .nav-link:hover { color: #0d6efd; background: rgba(13, 110, 253, .05); }
    .crm-salary-tabs .nav-link.active { color: #0d6efd; background: #fff; border: 1px solid #e2e8f0; border-bottom-color: #fff; margin-bottom: -1px; }
</style>
@endpush

@section('content')
@php($currentSalaryTab = $activeSalaryTab ?? 'master')
<div class="page-wrapper"><div class="page-content payroll-page">
    @include('layout.errors')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="ps-0">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item">Employee Master</li>
                <li class="breadcrumb-item active">Salary Management</li>
            </ol>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="small text-muted mb-1">PAYROLL WORKSPACE</div>
            <h1 class="payroll-heading mb-0">Salary Management</h1>
        </div>
    </div>

    <ul class="nav nav-tabs crm-salary-tabs mb-4" id="salaryWorkspaceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentSalaryTab === 'master' ? 'active' : '' }}" id="tab-btn-master"
                data-bs-toggle="tab" data-bs-target="#tab-pane-master" type="button" role="tab"
                aria-controls="tab-pane-master" aria-selected="{{ $currentSalaryTab === 'master' ? 'true' : 'false' }}">
                <i class="bx bx-wallet"></i>Salary Master
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentSalaryTab === 'attendance' ? 'active' : '' }}" id="tab-btn-attendance"
                data-bs-toggle="tab" data-bs-target="#tab-pane-attendance" type="button" role="tab"
                aria-controls="tab-pane-attendance" aria-selected="{{ $currentSalaryTab === 'attendance' ? 'true' : 'false' }}">
                <i class="bx bx-spreadsheet"></i>Import SalaryBox Attendance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentSalaryTab === 'reports' ? 'active' : '' }}" id="tab-btn-reports"
                data-bs-toggle="tab" data-bs-target="#tab-pane-reports" type="button" role="tab"
                aria-controls="tab-pane-reports" aria-selected="{{ $currentSalaryTab === 'reports' ? 'true' : 'false' }}">
                <i class="bx bx-bar-chart-alt-2"></i>Salary Reports
            </button>
        </li>
    </ul>

    <div class="tab-content" id="salaryWorkspaceTabContent">
        {{-- Tab 1: Salary Master --}}
        <div class="tab-pane fade {{ $currentSalaryTab === 'master' ? 'show active' : '' }}" id="tab-pane-master" role="tabpanel" aria-labelledby="tab-btn-master">
            @if($canManage)
            <div class="card payroll-card">
                <div class="card-header">Salary Master <small class="fw-normal text-muted">— staff details are managed from <a href="{{ route('staff') }}">Staff</a></small></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('salary-management.profiles.save') }}" class="row g-2 mb-4">@csrf
                        <div class="col-lg-3"><select name="staff_user_id" class="form-select" required><option value="">Select staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->id }}">{{ $staff->full_name }} — {{ $staff->email }}</option>@endforeach</select></div>
                        <div class="col-lg-2"><input name="employee_code" class="form-control" placeholder="Employee ID"></div><div class="col-lg-2"><input name="salarybox_id" class="form-control" placeholder="SalaryBox ID"></div><div class="col-lg-2"><input name="monthly_salary" type="number" min="0" step="0.01" class="form-control" placeholder="Monthly salary" required></div><div class="col-lg-2"><input name="standard_deduction" type="number" min="0" step="0.01" class="form-control" placeholder="TDS / PF"></div><div class="col-lg-1"><button class="btn btn-success w-100">Save</button></div>
                    </form>
                    <div class="table-responsive"><table id="salary-master-table" class="table payroll-table mb-0"><thead><tr><th>Staff</th><th>Employee ID</th><th>SalaryBox ID</th><th>Monthly Salary</th><th>Standard deduction</th><th>Actions</th></tr></thead><tbody>@forelse($salaryProfiles as $profile)<tr><td><span class="avatar-letter">{{ strtoupper(substr($profile->staffUser?->name ?? 'S',0,1)) }}</span><strong>{{ $profile->staffUser?->name }}</strong></td><td>{{ $profile->employee_code ?: '—' }}</td><td>{{ $profile->salarybox_id ?: '—' }}</td><td class="fw-semibold">₹{{ number_format((float)$profile->monthly_salary,2) }}</td><td>₹{{ number_format((float)$profile->standard_deduction,2) }}</td><td class="text-nowrap"><button type="button" class="btn btn-sm btn-outline-primary edit-profile-button" data-bs-toggle="modal" data-bs-target="#editSalaryProfileModal" data-update-url="{{ route('salary-management.profiles.update', $profile) }}" data-staff-user-id="{{ $profile->staff_user_id }}" data-monthly-salary="{{ $profile->monthly_salary }}">Edit</button><form method="POST" action="{{ route('salary-management.profiles.delete', $profile) }}" class="d-inline" onsubmit="return confirm('Delete this salary profile? The staff account will not be deleted.');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">No salary profile yet. Add salary before importing attendance.</td></tr>@endforelse</tbody></table></div>
                </div>
            </div>
            <div class="modal fade" id="editSalaryProfileModal" tabindex="-1" aria-labelledby="editSalaryProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog"><div class="modal-content">
                    <form id="editSalaryProfileForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header"><h5 class="modal-title" id="editSalaryProfileModalLabel">Edit Staff Salary Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">
                            <div class="mb-3"><label for="editStaffUserId" class="form-label">Staff</label><select id="editStaffUserId" name="staff_user_id" class="form-select" required>@foreach($staffMembers as $staff)<option value="{{ $staff->id }}">{{ $staff->full_name }} — {{ $staff->email }}</option>@endforeach</select></div>
                            <div class="mb-3"><label for="editMonthlySalary" class="form-label">Monthly Salary</label><input id="editMonthlySalary" name="monthly_salary" type="number" min="0" step="0.01" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
                    </form>
                </div></div>
            </div>
            @else
                <div class="alert alert-info mb-0">You do not have permission to manage salary profiles. Check your Saved Payroll or Salary Reports tabs.</div>
            @endif
        </div>

        {{-- Tab 2: Import SalaryBox Attendance --}}
        <div class="tab-pane fade {{ $currentSalaryTab === 'attendance' ? 'show active' : '' }}" id="tab-pane-attendance" role="tabpanel" aria-labelledby="tab-btn-attendance">
            @include('salary-management.partials.attendance-import-content', ['isUnifiedTab' => true])
        </div>

        {{-- Tab 3: Salary Reports --}}
        <div class="tab-pane fade {{ $currentSalaryTab === 'reports' ? 'show active' : '' }} salary-reports-page" id="tab-pane-reports" role="tabpanel" aria-labelledby="tab-btn-reports">
            @include('salary-management.partials.reports-content', [
                'isUnifiedTab' => true,
                'reportFilterAction' => route('salary-management.salary-master'),
                'reportResetUrl' => route('salary-management.salary-master', ['tab' => 'reports']),
            ])
        </div>
    </div>
</div></div>
@endsection

@push('scripts')
<script>
$(function(){
    if($('#salary-master-table').length && $.fn.DataTable && !$.fn.DataTable.isDataTable('#salary-master-table')){
        $('#salary-master-table').DataTable({pageLength:10,order:[],language:{search:'',searchPlaceholder:'Search...'}});
    }
    if($('#payroll-register-table').length && $.fn.DataTable && !$.fn.DataTable.isDataTable('#payroll-register-table')){
        $('#payroll-register-table').DataTable({pageLength:10,order:[],language:{search:'',searchPlaceholder:'Search...'}});
    }
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event){
        if($.fn.dataTable){
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
        const targetId = event.target && event.target.id;
        const tabMap = { 'tab-btn-master': 'master', 'tab-btn-attendance': 'attendance', 'tab-btn-reports': 'reports' };
        if(targetId && tabMap[targetId] && window.history && window.history.replaceState){
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabMap[targetId]);
            window.history.replaceState({}, '', url.toString());
        }
    });
});

document.addEventListener('click', function(event){
    const button = event.target.closest('.edit-profile-button');
    if(!button) return;
    const form = document.getElementById('editSalaryProfileForm');
    if(!form) return;
    form.action = button.dataset.updateUrl;
    form.elements.staff_user_id.value = button.dataset.staffUserId || '';
    form.elements.monthly_salary.value = button.dataset.monthlySalary || '';
});

const salaryAdjustmentForm = document.getElementById('salaryAdjustmentForm');
if(salaryAdjustmentForm){
    const salaryAdjustmentDays = document.getElementById('salaryAdjustmentDays');
    const salaryAdjustmentBonus = document.getElementById('salaryAdjustmentBonus');
    const salaryAdjustmentExtraPayment = document.getElementById('salaryAdjustmentExtraPayment');
    const salaryAdjustmentNote = document.getElementById('salaryAdjustmentNote');

    function updateNetPayable(){
        const baseSalary = Number(salaryAdjustmentForm.dataset.baseSalary || 0);
        const dailyRate = Number(salaryAdjustmentForm.dataset.dailyRate || 0);
        const days = Number(salaryAdjustmentDays.value || 0);
        const bonus = Number(salaryAdjustmentBonus.value || 0);
        const extraPayment = Number(salaryAdjustmentExtraPayment.value || 0);
        document.getElementById('salaryAdjustmentUpdatedNet').value = (baseSalary - days * dailyRate + bonus + extraPayment).toFixed(2);
    }

    document.addEventListener('click', function(event){
        const button = event.target.closest('.salary-adjust-button');
        if(!button) return;
        salaryAdjustmentForm.action = button.dataset.updateUrl;
        salaryAdjustmentForm.dataset.dailyRate = button.dataset.dailyRate || 0;
        salaryAdjustmentForm.dataset.baseSalary = button.dataset.baseSalary || 0;
        salaryAdjustmentForm.dataset.currentNetPayable = button.dataset.currentNetPayable || 0;
        document.getElementById('salaryAdjustmentEmployeeMonth').value = button.dataset.employee + ' — ' + button.dataset.month;
        document.getElementById('salaryAdjustmentTotalDays').value = button.dataset.totalDays || '';
        document.getElementById('salaryAdjustmentCurrentNet').value = Number(button.dataset.currentNetPayable || 0).toFixed(2);
        salaryAdjustmentDays.max = button.dataset.totalDays || 31;
        salaryAdjustmentDays.value = button.dataset.days === '0' ? '' : button.dataset.days;
        salaryAdjustmentBonus.value = button.dataset.bonus || 0;
        salaryAdjustmentExtraPayment.value = button.dataset.extraPayment || 0;
        salaryAdjustmentNote.value = button.dataset.note || '';
        updateNetPayable();
    });

    [salaryAdjustmentDays, salaryAdjustmentBonus, salaryAdjustmentExtraPayment].forEach(function(input){
        input.addEventListener('input', updateNetPayable);
    });
}

const bulkApproveForm = document.getElementById('bulkApproveForm');
if(bulkApproveForm){
    const bulkCheckboxes = Array.from(document.querySelectorAll('.bulk-approve-checkbox'));
    const bulkButton = document.getElementById('bulkApproveButton');
    const selectAll = document.getElementById('bulkApproveSelectAll');
    function updateBulkApproveControls(){
        const checkedCount = bulkCheckboxes.filter(checkbox => checkbox.checked).length;
        bulkButton.disabled = checkedCount === 0;
        bulkButton.textContent = checkedCount ? `Approve selected (${checkedCount})` : 'Approve selected';
        if(selectAll){
            selectAll.checked = bulkCheckboxes.length > 0 && checkedCount === bulkCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < bulkCheckboxes.length;
        }
    }
    bulkCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateBulkApproveControls));
    if(selectAll){
        selectAll.addEventListener('change', function(){
            bulkCheckboxes.forEach(checkbox => { checkbox.checked = selectAll.checked; });
            updateBulkApproveControls();
        });
    }
    bulkApproveForm.addEventListener('submit', function(event){
        const checkedCount = bulkCheckboxes.filter(checkbox => checkbox.checked).length;
        if(checkedCount === 0 || !window.confirm(`Approve ${checkedCount} selected payroll record(s)?`)) event.preventDefault();
    });
}
</script>
@endpush
