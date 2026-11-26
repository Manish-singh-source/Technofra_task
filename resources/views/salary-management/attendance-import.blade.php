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
    @include('salary-management.partials.attendance-import-content', ['isUnifiedTab' => false])
</div></div>
@endsection
@push('styles')<style>.salary-adjustment-panel{border:1px solid #e4eaf2;border-radius:12px;padding:14px 16px;background:#f8faff}.salary-adjustment-panel .form-range{height:1.2rem}.salary-adjustment-summary{background:#f5f7fb;border:1px solid #e8edf5}.salary-adjustment-summary .input-group{max-width:170px}.salary-adjustment-summary .form-control{background:#fff}.bulk-approve-checkbox,#bulkApproveSelectAll{width:1.15rem;height:1.15rem;vertical-align:middle;cursor:pointer}#bulkApproveButton{margin-bottom:14px}#payroll-register-table_wrapper .dataTables_filter{margin-bottom:12px}</style>@endpush
@push('scripts')
<script>
$(function(){
    if($('#payroll-register-table').length&&$.fn.DataTable&&!$.fn.DataTable.isDataTable('#payroll-register-table')){
        $('#payroll-register-table').DataTable({pageLength:10,order:[],language:{search:'',searchPlaceholder:'Search...'}});
    }
});

const salaryAdjustmentForm=document.getElementById('salaryAdjustmentForm');
if(salaryAdjustmentForm){
    const salaryAdjustmentDays=document.getElementById('salaryAdjustmentDays');
    const salaryAdjustmentBonus=document.getElementById('salaryAdjustmentBonus');
    const salaryAdjustmentExtraPayment=document.getElementById('salaryAdjustmentExtraPayment');
    const salaryAdjustmentNote=document.getElementById('salaryAdjustmentNote');

    function updateNetPayable(){
        const baseSalary=Number(salaryAdjustmentForm.dataset.baseSalary||0);
        const dailyRate=Number(salaryAdjustmentForm.dataset.dailyRate||0);
        const days=Number(salaryAdjustmentDays.value||0);
        const bonus=Number(salaryAdjustmentBonus.value||0);
        const extraPayment=Number(salaryAdjustmentExtraPayment.value||0);
        document.getElementById('salaryAdjustmentUpdatedNet').value=(baseSalary-days*dailyRate+bonus+extraPayment).toFixed(2);
    }

    document.addEventListener('click',function(event){
        const button=event.target.closest('.salary-adjust-button');
        if(!button)return;
        salaryAdjustmentForm.action=button.dataset.updateUrl;
        salaryAdjustmentForm.dataset.dailyRate=button.dataset.dailyRate||0;
        salaryAdjustmentForm.dataset.baseSalary=button.dataset.baseSalary||0;
        salaryAdjustmentForm.dataset.currentNetPayable=button.dataset.currentNetPayable||0;
        document.getElementById('salaryAdjustmentEmployeeMonth').value=button.dataset.employee+' — '+button.dataset.month;
        document.getElementById('salaryAdjustmentTotalDays').value=button.dataset.totalDays||'';
        document.getElementById('salaryAdjustmentCurrentNet').value=Number(button.dataset.currentNetPayable||0).toFixed(2);
        salaryAdjustmentDays.max=button.dataset.totalDays||31;
        salaryAdjustmentDays.value=button.dataset.days==='0'?'':button.dataset.days;
        salaryAdjustmentBonus.value=button.dataset.bonus||0;
        salaryAdjustmentExtraPayment.value=button.dataset.extraPayment||0;
        salaryAdjustmentNote.value=button.dataset.note||'';
        updateNetPayable();
    });

    [salaryAdjustmentDays,salaryAdjustmentBonus,salaryAdjustmentExtraPayment].forEach(function(input){
        input.addEventListener('input',updateNetPayable);
    });
}

const bulkApproveForm=document.getElementById('bulkApproveForm');
if(bulkApproveForm){
    const bulkCheckboxes=Array.from(document.querySelectorAll('.bulk-approve-checkbox'));
    const bulkButton=document.getElementById('bulkApproveButton');
    const selectAll=document.getElementById('bulkApproveSelectAll');
    function updateBulkApproveControls(){
        const checkedCount=bulkCheckboxes.filter(checkbox=>checkbox.checked).length;
        bulkButton.disabled=checkedCount===0;
        bulkButton.textContent=checkedCount?`Approve selected (${checkedCount})`:'Approve selected';
        selectAll.checked=bulkCheckboxes.length>0&&checkedCount===bulkCheckboxes.length;
        selectAll.indeterminate=checkedCount>0&&checkedCount<bulkCheckboxes.length;
    }
    bulkCheckboxes.forEach(checkbox=>checkbox.addEventListener('change',updateBulkApproveControls));
    selectAll.addEventListener('change',function(){
        bulkCheckboxes.forEach(checkbox=>{checkbox.checked=selectAll.checked;});
        updateBulkApproveControls();
    });
    bulkApproveForm.addEventListener('submit',function(event){
        const checkedCount=bulkCheckboxes.filter(checkbox=>checkbox.checked).length;
        if(checkedCount===0||!window.confirm(`Approve ${checkedCount} selected payroll record(s)?`))event.preventDefault();
    });
}
</script>
@endpush
