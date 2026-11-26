@php($isUnifiedTab = $isUnifiedTab ?? false)

@if($canManage)
<div class="card payroll-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bx bx-upload text-primary me-1"></i> Import SalaryBox Attendance</span>
        <a href="{{ route('salary-management.template') }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-download"></i> HR Adjustment Template
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('salary-management.upload') }}" method="POST" enctype="multipart/form-data" class="row align-items-end g-3">
            @csrf
            @if($isUnifiedTab)<input type="hidden" name="return_to" value="salary-master">@endif
            <div class="col-md-3">
                <label class="form-label">Report month (fallback)</label>
                <input type="month" name="month" class="form-control" value="{{ old('month', $salaryMonth) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Attendance Muster Report</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="bx bx-spreadsheet"></i> Upload &amp; Preview</button>
            </div>
        </form>
    </div>
</div>
@endif

@if(is_array($previewRows))
@php($previewCollection = collect($previewRows))
@php($hasErrors = $previewCollection->contains(fn($row) => $row['errors'] !== []))
@php($hasActionableRows = $previewCollection->contains(fn($row) => $row['import_state'] !== 'unchanged'))
@php($hasExistingRows = $previewCollection->contains(fn($row) => $row['import_state'] !== 'new') || $missingExistingCount > 0)
@php($hasApprovedChanges = $previewCollection->contains(fn($row) => $row['import_state'] === 'changed' && $row['existing_status'] === 'approved'))
<div class="card payroll-card mb-4">
    <div class="card-header d-flex justify-content-between">
        <span>Payroll Preview — {{ count($previewRows) }} employee(s)</span>
        <span class="badge {{ $hasErrors ? 'bg-danger' : ($hasActionableRows ? 'bg-warning text-dark' : 'bg-success') }}">
            {{ $hasErrors ? 'Fix errors before saving' : ($hasActionableRows ? 'Review changes' : 'No changes detected') }}
        </span>
    </div>
    @if($hasExistingRows)
        <div class="alert alert-info m-3 mb-0">
            This month already has saved payroll. Identical rows will be left untouched. Changed or new rows require explicit confirmation.
            @if($missingExistingCount > 0)<strong>{{ $missingExistingCount }} saved record(s) missing from this workbook will be kept.</strong>@endif
        </div>
    @endif
    @if($hasApprovedChanges)
        <div class="alert alert-warning m-3 mb-0">
            An approved payroll record has changed. Reopen that record with a reason in Saved Payroll, then upload the workbook again. Approved amounts cannot be replaced directly.
        </div>
    @endif
    <div class="table-responsive">
        <table class="table payroll-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Present / Absent</th>
                    <th>Salary</th>
                    <th>Per day</th>
                    <th>Deductions</th>
                    <th>Net payable</th>
                    <th>Adjustment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previewRows as $rowIndex => $row)
                    @php($currentAdjustmentDays = (int) data_get($row, 'calculation.salary_adjustment_days', 0))
                    @php($currentAdjustmentDeduction = (float) data_get($row, 'calculation.salary_adjustment_deduction', 0))
                    <tr class="{{ $row['errors'] !== [] ? 'table-danger' : '' }}">
                        <td>
                            <strong>{{ $row['employee']['name'] }}</strong><br>
                            <small>{{ $row['employee']['employee_id'] ?: $row['employee']['phone'] }}</small>
                            @if($row['import_state'] === 'new')
                                <div><span class="badge bg-primary mt-1">New record</span></div>
                            @elseif($row['import_state'] === 'unchanged')
                                <div><span class="badge bg-success mt-1">Identical to saved</span></div>
                            @else
                                <div><span class="badge bg-warning text-dark mt-1">Changed · {{ ucfirst($row['existing_status'] ?? 'draft') }}</span></div>
                                @if($row['changed_fields'] !== [])
                                    <small class="d-block text-muted mt-1">
                                        @foreach($row['changed_values'] as $field => $change)
                                            {{ $field }}: {{ $change['before'] ?? '—' }} → {{ $change['after'] ?? '—' }}@if(! $loop->last); @endif
                                        @endforeach
                                    </small>
                                @endif
                            @endif
                        </td>
                        <td>{{ $row['data']['present'] }} / {{ $row['data']['absent'] }}</td>
                        <td>₹{{ number_format((float) $row['data']['salary'], 2) }}</td>
                        <td>₹{{ number_format((float) data_get($row, 'calculation.per_day_salary', 0), 2) }}</td>
                        <td>₹{{ number_format((float) data_get($row, 'calculation.total_deductions', 0), 2) }}</td>
                        <td class="fw-bold text-primary">₹{{ number_format((float) data_get($row, 'calculation.net_salary', 0), 2) }}</td>
                        <td>
                            @if($row['errors'] !== [])
                                <span class="small text-danger">{{ implode(' ', $row['errors']) }}</span>
                            @endif
                            @if($row['errors'] === [])
                                <button type="button" class="btn btn-outline-primary btn-sm salary-adjust-button"
                                    data-bs-toggle="modal" data-bs-target="#salaryAdjustmentModal"
                                    data-update-url="{{ route('salary-management.preview.adjustment', [$previewToken, $rowIndex]) }}"
                                    data-employee="{{ $row['employee']['name'] }}"
                                    data-month="{{ \Carbon\Carbon::createFromFormat('Y-m', $salaryMonth)->format('F Y') }}"
                                    data-total-days="{{ data_get($row, 'calculation.days_in_month', 0) }}"
                                    data-days="{{ $currentAdjustmentDays }}"
                                    data-daily-rate="{{ data_get($row, 'calculation.per_day_salary', 0) }}"
                                    data-current-net-payable="{{ number_format((float) data_get($row, 'calculation.net_salary', 0), 2, '.', '') }}"
                                    data-base-salary="{{ number_format((float) data_get($row, 'calculation.net_salary', 0) + $currentAdjustmentDeduction - (float) data_get($row, 'calculation.bonus', 0) - (float) data_get($row, 'calculation.extra_payment', 0), 2, '.', '') }}"
                                    data-bonus="{{ number_format((float) data_get($row, 'calculation.bonus', 0), 2, '.', '') }}"
                                    data-extra-payment="{{ number_format((float) data_get($row, 'calculation.extra_payment', 0), 2, '.', '') }}"
                                    data-note="{{ data_get($row, 'calculation_adjustments.adjustment_note', '') }}">Adjust Salary</button>
                                @if($currentAdjustmentDays > 0)
                                    <div class="small text-muted mt-1">{{ $currentAdjustmentDays }} day(s): −₹{{ number_format($currentAdjustmentDeduction, 2) }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">
        @if($hasActionableRows && ! $hasApprovedChanges)
            <form action="{{ route('salary-management.save') }}" method="POST" class="row g-2 align-items-end justify-content-end">
                @csrf
                @if($isUnifiedTab)<input type="hidden" name="return_to" value="salary-master">@endif
                <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                <input type="hidden" name="mode" value="{{ $hasExistingRows ? 'apply' : 'save' }}">
                @if($hasExistingRows)
                    <div class="col-md-6">
                        <label for="payrollUpdateReason" class="form-label">Reason for this re-import</label>
                        <input id="payrollUpdateReason" name="reason" class="form-control" maxlength="500" required placeholder="Explain why this payroll is being updated">
                    </div>
                @endif
                <div class="col-md-3 text-end">
                    <button class="btn btn-success" {{ $hasErrors ? 'disabled' : '' }}>
                        {{ $hasExistingRows ? 'Confirm changes and save' : 'Save as Draft Payroll' }}
                    </button>
                </div>
            </form>
        @endif
        <div class="text-end {{ $hasActionableRows && ! $hasApprovedChanges ? 'mt-2' : '' }}">
            <form action="{{ route('salary-management.preview.cancel') }}" method="POST" class="d-inline">
                @csrf
                @if($isUnifiedTab)<input type="hidden" name="return_to" value="salary-master">@endif
                <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                <button class="btn btn-outline-secondary">{{ $hasActionableRows ? 'Cancel' : 'Done' }}</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="salaryAdjustmentModal" tabindex="-1" aria-labelledby="salaryAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form id="salaryAdjustmentForm" method="POST">
            @csrf
            @if($isUnifiedTab)<input type="hidden" name="return_to" value="salary-master">@endif
            <div class="modal-header"><h5 class="modal-title" id="salaryAdjustmentModalLabel">Adjust Salary</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label for="salaryAdjustmentEmployeeMonth" class="form-label">Employee &amp; month</label><input id="salaryAdjustmentEmployeeMonth" type="text" class="form-control" readonly></div>
                <div class="mb-3"><label for="salaryAdjustmentTotalDays" class="form-label">Total days in month</label><input id="salaryAdjustmentTotalDays" type="number" class="form-control" readonly></div>
                <div class="salary-adjustment-panel mb-3">
                    <label for="salaryAdjustmentDays" class="form-label fw-semibold">Days to deduct</label>
                    <input id="salaryAdjustmentDays" class="form-control" type="number" name="days" min="0" max="31" step="1" placeholder="0">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label for="salaryAdjustmentBonus" class="form-label fw-semibold">Bonus / incentive</label><div class="input-group"><span class="input-group-text">₹</span><input id="salaryAdjustmentBonus" class="form-control" type="number" name="bonus" min="0" step="0.01" placeholder="0.00"></div></div>
                    <div class="col-md-6"><label for="salaryAdjustmentExtraPayment" class="form-label fw-semibold">Extra payment</label><div class="input-group"><span class="input-group-text">₹</span><input id="salaryAdjustmentExtraPayment" class="form-control" type="number" name="extra_payment" min="0" step="0.01" placeholder="0.00"></div></div>
                </div>
                <div class="mb-3"><label for="salaryAdjustmentNote" class="form-label">Adjustment note <span class="text-muted">(optional)</span></label><textarea id="salaryAdjustmentNote" name="adjustment_note" class="form-control" rows="2" maxlength="500" placeholder="e.g. Festival bonus or travel reimbursement"></textarea></div>
                <div class="mb-3"><label for="salaryAdjustmentCurrentNet" class="form-label fw-semibold">Current net payable amount</label><div class="input-group"><span class="input-group-text">₹</span><input id="salaryAdjustmentCurrentNet" type="text" class="form-control" readonly></div></div>
                <div><label for="salaryAdjustmentUpdatedNet" class="form-label fw-semibold">Updated net payable amount</label><div class="input-group"><span class="input-group-text">₹</span><input id="salaryAdjustmentUpdatedNet" type="text" class="form-control fw-bold" readonly></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
        </form>
    </div></div>
</div>
@endif

<div class="card payroll-card">
    <div class="card-header"><i class="bx bx-list-ul text-primary me-1"></i> Saved Payroll</div>
    @if($canManage)
        <div class="d-flex justify-content-end px-3 pt-3">
            <button type="submit" form="bulkApproveForm" id="bulkApproveButton" class="btn btn-success btn-sm" disabled>Approve selected</button>
            <form id="bulkApproveForm" method="POST" action="{{ route('salary-management.approve-bulk') }}">@csrf</form>
        </div>
    @endif
    <div class="table-responsive">
        <table id="payroll-register-table" class="table payroll-table mb-0">
            <thead>
                <tr>
                    @if($canManage)<th><input type="checkbox" class="form-check-input m-0" id="bulkApproveSelectAll" aria-label="Select all draft payroll records"></th>@endif
                    <th>Employee</th>
                    <th>Month</th>
                    <th>Net Salary</th>
                    <th>Payroll status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    @php($recordStatus = data_get($record->data, 'payroll_status'))
                    <tr>
                        @if($canManage)
                            <td>@if($recordStatus !== 'approved')<input type="checkbox" class="form-check-input m-0 bulk-approve-checkbox" name="salary_record_ids[]" value="{{ $record->id }}" form="bulkApproveForm" aria-label="Select {{ data_get($record->data, 'employee.name', $record->staffUser?->name) }} payroll for approval">@endif</td>
                        @endif
                        <td><strong>{{ data_get($record->data, 'employee.name', $record->staffUser?->name) }}</strong></td>
                        <td>{{ $record->salary_month?->format('F Y') }}</td>
                        <td class="fw-bold text-primary">₹{{ number_format((float) data_get($record->data, 'calculation.net_salary', 0), 2) }}</td>
                        <td><span class="badge {{ $recordStatus === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($recordStatus ?? 'draft') }}</span></td>
                        <td class="text-end">
                            @if($recordStatus === 'approved')
                                <a target="_blank" href="{{ route('salary-management.payslip', $record) }}" class="btn btn-outline-primary btn-sm">Salary Slip</a>
                            @elseif($canManage)
                                <form class="d-inline" method="POST" action="{{ route('salary-management.approve', $record) }}">@csrf<button class="btn btn-success btn-sm">Approve</button></form>
                            @endif
                            @if($canManage)
                                <form class="d-inline" method="POST" action="{{ route('salary-management.destroy', $record) }}" onsubmit="return confirm('Permanently delete payroll for {{ addslashes(data_get($record->data, 'employee.name', $record->staffUser?->name)) }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    @if($isUnifiedTab)<input type="hidden" name="return_to" value="salary-master">@endif
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete payroll" aria-label="Delete payroll"><i class="bx bx-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $canManage ? 6 : 5 }}" class="text-center text-muted py-4">No saved payroll yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
