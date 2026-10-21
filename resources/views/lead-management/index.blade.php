@extends('layout.master')

@push('styles')
<style>
.lead-workspace { --lead-border:#dee2e6; --lead-muted:#6c757d; font-family:inherit; }
.lead-workspace .lead-heading { font-family:inherit; font-size:24px; font-weight:500; letter-spacing:0; color:#212529; }
.lead-workspace .work-cards { display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px; }
.lead-workspace .work-card { position:relative;overflow:hidden;background:#fff;border:1px solid var(--lead-border);border-left:4px solid #0dcaf0;border-radius:10px;padding:18px 18px 16px;text-decoration:none;color:#6c757d;transition:box-shadow .15s ease,border-color .15s ease; }
.lead-workspace .work-card::after { display:none; }
.lead-workspace .work-card:hover { border-color:#adb5bd;box-shadow:0 .125rem .25rem rgba(0,0,0,.075); }
.lead-workspace .work-card.active { border-color:#0d6efd;border-left-color:#0d6efd;background:#fff;box-shadow:0 .125rem .25rem rgba(0,0,0,.075); }
.lead-workspace .work-card:nth-child(2) { border-left-color:#dc3545; }
.lead-workspace .work-card:nth-child(3) { border-left-color:#ffc107; }
.lead-workspace .work-card:nth-child(4) { border-left-color:#6f42c1; }
.lead-workspace .work-card:nth-child(5) { border-left-color:#198754; }
.lead-workspace .work-card strong { display:block;font-size:28px;line-height:1.2;color:#0d6efd;margin-top:8px;letter-spacing:0; }
.lead-workspace .work-card.danger strong { color:#dc3545; }
.lead-workspace .work-card small { font-size:11px; }
.lead-workspace .leads-panel { border:1px solid var(--lead-border);border-radius:10px;box-shadow:none;overflow:hidden; }
.lead-workspace .source-tabs { display:flex;gap:9px;overflow-x:auto;padding:5px 2px 13px;border-bottom:1px solid #eef1f6;scrollbar-width:thin; }
.lead-workspace .source-pill { flex-shrink:0;border:1px solid #e1e7f0;border-radius:10px;padding:9px 14px;color:#637189;text-decoration:none;font-size:12px;font-weight:600;background:#fff;transition:all .15s ease; }
.lead-workspace .source-pill:hover { border-color:#aebdf3;color:#3559cf;background:#f8faff;transform:translateY(-1px); }
.lead-workspace .source-pill.active { background:#0d6efd;color:#fff;border-color:#0d6efd;box-shadow:0 .125rem .25rem rgba(0,0,0,.075); }
.lead-workspace .source-pill span { display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:20px;padding:0 6px;margin-left:6px;border-radius:10px;background:#f0f3f8;color:#637189;font-size:10px; }
.lead-workspace .source-pill.active span { background:#ffffff26;color:#fff; }
.lead-workspace .source-pill:nth-child(n) { border-color:#dee2e6;color:#495057;background:#fff;border-radius:6px; }
.lead-workspace .source-pill:hover { border-color:#86b7fe;color:#0d6efd;background:#f8f9fa; }
.lead-workspace .source-pill.active { background:#0d6efd;color:#fff;border-color:#0d6efd; }
.lead-workspace .work-toolbar { display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap; }
.lead-workspace .table-responsive { width:100%;max-width:100%;overflow-x:auto;overflow-y:hidden;border:1px solid var(--lead-border);border-radius:6px;background:#fff; }
#leadWorklist { width:100%!important;min-width:1130px;table-layout:fixed;border-collapse:collapse!important;font-size:13px; }
#leadWorklist_wrapper { width:100%;max-width:100%; }
#leadWorklist th:nth-child(1),#leadWorklist td:nth-child(1) { width:40px; }
#leadWorklist th:nth-child(2),#leadWorklist td:nth-child(2) { width:270px; }
#leadWorklist th:nth-child(3),#leadWorklist td:nth-child(3) { width:135px; }
#leadWorklist th:nth-child(4),#leadWorklist td:nth-child(4) { width:105px; }
#leadWorklist th:nth-child(5),#leadWorklist td:nth-child(5) { width:165px; }
#leadWorklist th:nth-child(6),#leadWorklist td:nth-child(6) { width:160px; }
#leadWorklist th:nth-child(7),#leadWorklist td:nth-child(7) { width:190px; }
#leadWorklist th:nth-child(8),#leadWorklist td:nth-child(8) { width:125px; }
#leadWorklist thead th { background:#f8f9fa;color:#6c757d;font-size:11px;letter-spacing:.3px;text-transform:none;border:0;border-bottom:1px solid var(--lead-border);padding:14px 12px;white-space:nowrap; }
#leadWorklist td { padding:15px 12px;border:0;border-bottom:1px solid #edf0f5;vertical-align:middle;overflow:hidden; }
#leadWorklist tr:last-child td { border-bottom:0; }
#leadWorklist tbody tr:hover { background:#f9fbff; }
#leadWorklist tbody tr.priority-high { background:#fff1f2; }
#leadWorklist tbody tr.priority-high > td { background:#fff1f2!important; }
#leadWorklist tbody tr.priority-high:hover { background:#ffe4e6; }
#leadWorklist tbody tr.priority-high:hover > td { background:#ffe4e6!important; }
#leadWorklist tbody tr.priority-high td:first-child { border-left:3px solid #d84b5b; }
#leadWorklist tbody tr.priority-medium td:first-child { border-left:3px solid #d49a2c; }
#leadWorklist tbody tr.priority-low td:first-child { border-left:3px solid #70a3d8; }
#leadWorklist .lead-identity { display:flex;gap:10px;align-items:center;min-width:0;width:100%; }
#leadWorklist .lead-identity > div { min-width:0;flex:1; }
#leadWorklist .lead-avatar { width:36px;height:36px;flex-shrink:0;display:grid;place-items:center;border-radius:11px;background:#edf2ff;color:#4161bc;font-weight:700;font-size:13px; }
#leadWorklist .lead-title { color:#243653;font-weight:600;text-decoration:none;display:-webkit-box;overflow:hidden;overflow-wrap:anywhere;-webkit-box-orient:vertical;-webkit-line-clamp:2;line-clamp:2; }
#leadWorklist .lead-sub { display:block;color:#7c8798;font-size:11px;margin-top:3px;overflow-wrap:anywhere; }
#leadWorklist .priority-control { display:flex;align-items:center;gap:5px;margin-top:7px; }
#leadWorklist .priority-control i { font-size:13px; }
#leadWorklist .priority-select { border:0;background:transparent;padding:0;color:#697991;font-size:10px;font-weight:700;text-transform:uppercase;cursor:pointer; }
#leadWorklist .priority-select.high { color:#c43f50; }
#leadWorklist .priority-select.medium { color:#a36b14; }
#leadWorklist .priority-select.low { color:#3976a8; }
#leadWorklist .lead-contact a { color:#5d6d83;text-decoration:none;display:block;font-size:12px;max-width:210px;overflow-wrap:anywhere; }
#leadWorklist .lead-contact a:hover { color:#3559cf; }
#leadWorklist .lead-assignees { width:200px;min-width:160px;white-space:normal; }
#leadWorklist .lead-assignee-names { display:block;max-width:180px;white-space:normal;overflow-wrap:anywhere;line-height:1.6; }
#leadWorklist .state-pill { display:inline-flex;padding:5px 9px;border-radius:7px;background:#eef2f8;color:#51627e;font-size:11px;font-weight:600;white-space:nowrap; }
#leadWorklist .state-pill.overdue,#leadWorklist .state-pill.lost { color:#bd3245;background:#fff0f1; }
#leadWorklist .state-pill.today,#leadWorklist .state-pill.demo_scheduled { color:#936108;background:#fff7e4; }
#leadWorklist .state-pill.converted,#leadWorklist .state-pill.qualified { color:#22734b;background:#eaf8f0; }
#leadWorklist .state-pill.new,#leadWorklist .state-pill.upcoming { color:#375dc2;background:#edf2ff; }
#leadWorklist .last-outcome { max-width:190px; }
#leadWorklist .last-note { max-width:190px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
#leadWorklist .row-actions { display:flex;gap:6px;align-items:center;white-space:nowrap; }
#leadWorklist .row-action { display:inline-flex;align-items:center;justify-content:center;border:1px solid #e4e9f1;background:#fff;border-radius:8px;min-width:30px;height:30px;color:#546988;text-decoration:none;padding:0 7px;font-size:12px;transition:all .15s ease; }
#leadWorklist .row-action:nth-child(1) { color:#4161bc;border-color:#cbd8ff;background:#f5f7ff; }
#leadWorklist .row-action:nth-child(2) { color:#238258;border-color:#c9ead8;background:#f3fbf6; }
#leadWorklist .row-action:nth-child(3) { color:#7950b5;border-color:#e3d3f5;background:#faf6ff; }
#leadWorklist .row-action:nth-child(4) { color:#b87518;border-color:#f1d7ad;background:#fffaf1; }
#leadWorklist .row-action:hover { transform:translateY(-1px);filter:brightness(.94);box-shadow:0 3px 8px #23365318; }
.lead-workspace .dataTables_filter { display:none; }
.lead-workspace .dataTables_info,.lead-workspace .dataTables_length,.lead-workspace .dataTables_paginate { font-size:12px;margin-top:12px; }
@media(max-width:991px) { .lead-workspace .work-cards {grid-template-columns:repeat(3,minmax(0,1fr));} }
@media(max-width:575px) { .lead-workspace .work-cards {grid-template-columns:repeat(2,minmax(0,1fr));}.lead-workspace .lead-heading{font-size:22px;}.lead-workspace .work-card{padding:12px;} }
</style>
@endpush

@section('content')
@php
    $query = array_filter($filters, fn ($value) => $value !== '');
    $statusLabels = collect($statusOptions)->pluck('name', 'slug');
    $canSelect = $canManageLeadAssignments || auth()->user()->can('delete_leads');
    $cards = [
        '' => ['All leads', 'all', 'Your selected pipeline'],
        'overdue' => ['Overdue', 'overdue', 'Needs attention now'],
        'today' => ['Due today', 'today', 'Today’s follow-up plan'],
        'unscheduled' => ['No next action', 'unscheduled', 'Plan the next conversation'],
        'closed' => ['Closed', 'closed', 'Won, lost and disqualified'],
    ];
@endphp
<div class="page-wrapper"><div class="page-content lead-workspace">
    @include('lead-management.partials.feedback')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div><div class="small text-muted mb-1">SALES WORKSPACE</div><h1 class="lead-heading mb-1">Lead Management</h1></div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('lead-management.performance') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-line-chart me-1"></i>Performance</a>
            @can('create_leads')<a href="{{ route('add-lead') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i>Add lead</a>@endcan
        </div>
    </div>
    <div class="work-cards mb-4">
        @foreach($cards as $key => [$label, $countKey, $hint])
        <a href="{{ route('lead-management.index', array_merge($query, ['work' => $key])) }}" class="work-card {{ $key === 'overdue' ? 'danger' : '' }} {{ ($filters['work'] ?? '') === (string) $key ? 'active' : '' }}" @if(($filters['work'] ?? '') === (string) $key) aria-current="page" @endif>
            <span>{{ $label }}</span><strong>{{ $workSummary[$countKey] ?? 0 }}</strong><small>{{ $hint }}</small>
        </a>
        @endforeach
    </div>
    <div class="card leads-panel"><div class="card-body p-3 p-lg-4">
        <div class="source-tabs mb-3" aria-label="Lead sources">
            <a class="source-pill {{ empty($filters['source']) ? 'active' : '' }}" href="{{ route('lead-management.index', array_merge($query, ['source' => ''])) }}">All sources <span>{{ $tabCounts['all'] ?? 0 }}</span></a>
            @foreach($sources as $key => $label)
                <a class="source-pill {{ ($filters['source'] ?? '') === $key ? 'active' : '' }}" href="{{ route('lead-management.index', array_merge($query, ['source' => $key])) }}">{{ $label }} <span>{{ $tabCounts[$key] ?? 0 }}</span></a>
            @endforeach
        </div>
        <div class="work-toolbar mb-3">
            <form method="GET" class="d-flex gap-2 flex-wrap" action="{{ route('lead-management.index') }}">
                <input type="hidden" name="source" value="{{ $filters['source'] ?? '' }}">
                <input type="hidden" name="work" value="{{ $filters['work'] ?? '' }}">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" style="width:220px" placeholder="Search name, phone, email…" aria-label="Search leads">
                <select name="status" class="form-select form-select-sm" style="width:175px" aria-label="Lead status">
                    <option value="">All statuses</option>
                    @foreach($statusOptions as $option)<option value="{{ $option['slug'] }}" @selected(($filters['status'] ?? '') === $option['slug'])>{{ $option['name'] }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-primary">Apply</button>
                @if($query)<a href="{{ route('lead-management.index') }}" class="btn btn-sm btn-light">Reset</a>@endif
            </form>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted" id="selectionCount" aria-live="polite">0 selected</small>
                @if($canManageLeadAssignments)<button class="btn btn-sm btn-outline-primary" id="bulkAssignButton" type="button" disabled>Assign selected</button>@endif
                @can('delete_leads')<button class="btn btn-sm btn-outline-danger" id="bulkDeleteButton" type="button" disabled>Delete</button>@endcan
                @if(($filters['source'] ?? '') === 'meta')
                <form method="POST" action="{{ route('leads.sync') }}" onsubmit="return confirm('Sync latest Meta leads?')">@csrf<input type="hidden" name="return_to" value="{{ route('lead-management.index', $query) }}"><button class="btn btn-sm btn-light" type="submit">Sync Meta</button></form>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table id="leadWorklist" data-no-default-datatable="true" class="table mb-0">
                <thead><tr>
                    <th style="width:28px">@if($canSelect)<input type="checkbox" id="selectAllLeads" aria-label="Select leads on this page">@endif</th>
                    <th>Lead</th><th>Source</th><th>Stage</th><th>Assigned to</th><th>Next follow-up</th><th>Latest outcome</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @foreach($leads as $lead)
                    @php
                        $profileUrl = route('lead-management.show', ['source' => $lead['source_type'], 'id' => $lead['source_id']]);
                        $nextAt = $lead['next_followup_at'];
                        $last = $lead['last_followup'];
                        $state = $lead['followup_state'];
                        $stateLabel = ['overdue'=>'Overdue','today'=>'Due today','upcoming'=>'Upcoming','unscheduled'=>'Not scheduled','closed'=>'Closed'][$state];
                    @endphp
                    <tr class="priority-{{ $lead['priority'] ?? 'medium' }}">
                        <td>@if($canSelect)<input type="checkbox" class="lead-selection" data-source="{{ $lead['source_type'] }}" data-id="{{ $lead['source_id'] }}" aria-label="Select {{ $lead['name'] }}">@endif</td>
                        <td>
                            <div class="lead-identity"><span class="lead-avatar">{{ mb_strtoupper(mb_substr($lead['name'] === '-' ? 'L' : $lead['name'], 0, 1)) }}</span><div>
                                <a href="{{ $profileUrl }}" class="lead-title">{{ $lead['name'] === '-' ? 'Unnamed lead' : $lead['name'] }}</a>
                                <span class="lead-sub">{{ $lead['company'] === '-' ? 'Individual lead' : $lead['company'] }}</span>
                                @can('edit_leads')
                                <form method="POST" action="{{ route('lead-management.priority', ['source'=>$lead['source_type'], 'id'=>$lead['source_id']]) }}" class="priority-control">
                                    @csrf @method('PATCH')
                                    <i class="bx bxs-flag-alt"></i>
                                    <select name="priority" class="priority-select {{ $lead['priority'] ?? 'medium' }}" onchange="this.form.submit()" aria-label="Set lead priority">
                                        @foreach(['high' => 'High priority', 'medium' => 'Medium priority', 'low' => 'Low priority'] as $priority => $label)
                                            <option value="{{ $priority }}" @selected(($lead['priority'] ?? 'medium') === $priority)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @else
                                    <span class="priority-control {{ $lead['priority'] ?? 'medium' }}"><i class="bx bxs-flag-alt"></i><span>{{ ucfirst($lead['priority'] ?? 'medium') }}</span></span>
                                @endcan
                            </div></div>
                        </td>
                        <td><span class="small">{{ $lead['source'] }}</span><span class="lead-sub">{{ $lead['created_at'] }}</span></td>
                        <td><span class="state-pill {{ $lead['status'] }}">{{ $statusLabels[$lead['status']] ?? ucwords(str_replace('_', ' ', $lead['status'])) }}</span></td>
                        <td class="lead-assignees"><span class="lead-assignee-names">{{ $lead['assigned_to'] === '-' ? 'Unassigned' : $lead['assigned_to'] }}</span></td>
                        <td data-order="{{ $state === 'closed' ? PHP_INT_MAX : ($nextAt?->timestamp ?? PHP_INT_MAX - 1) }}">
                            <span class="state-pill {{ $state }}">{{ $stateLabel }}</span>
                            @if($nextAt && $state !== 'closed')<span class="lead-sub">{{ $nextAt->format('d M Y, h:i A') }}</span>
                            @elseif($state === 'unscheduled')<span class="lead-sub">Set the next action</span>
                            @elseif($state === 'closed')<span class="lead-sub">No pending follow-up</span>@endif
                        </td>
                        <td class="last-outcome">
                            @if($last)
                                <span class="fw-semibold small">{{ ucwords(str_replace('_', ' ', $last->outcome ?: $last->followup_type)) }}</span>
                                <span class="lead-sub">{{ $last->followup_date?->format('d M, h:i A') }} · {{ ucwords(str_replace('_', ' ', $last->followup_type)) }}</span>
                                <div class="last-note lead-sub" title="{{ $last->discussion_notes }}">{{ $last->discussion_notes }}</div>
                            @elseif($lead['status'] === 'lost' && $lead['lost_reason'])
                                <span class="lead-sub">{{ $lead['lost_reason'] }}</span>
                            @else<span class="lead-sub">No conversation logged</span>@endif
                        </td>
                        <td><div class="row-actions">
                            <a href="{{ $profileUrl }}" class="row-action" aria-label="View journey for {{ $lead['name'] }}" title="View lead journey"><i class="bx bx-show"></i></a>
                            @can('edit_leads')
                                @if($state !== 'closed')<a href="{{ $profileUrl }}?action=followup" class="row-action" title="Log follow-up" aria-label="Log follow-up for {{ $lead['name'] }}"><i class="bx bx-phone-call"></i></a>@endif
                            @endcan
                            @if($canManageLeadAssignments)
                            <button type="button" class="row-action assign-one" data-url="{{ route('lead-management.assign', ['source'=>$lead['source_type'], 'id'=>$lead['source_id']]) }}" data-name="{{ $lead['name'] }}" aria-label="Assign {{ $lead['name'] }}" title="Add assignees"><i class="bx bx-user-plus"></i></button>
                            @endif
                            @can('edit_leads')
                                @if($lead['source_type'] === 'lead' && $state !== 'closed')<a href="{{ route('lead.edit', $lead['source_id']) }}" class="row-action" title="Edit lead details" aria-label="Edit {{ $lead['name'] }}"><i class="bx bx-edit"></i></a>@endif
                            @endcan
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="small text-muted mt-3 mb-0"><i class="bx bx-info-circle me-1"></i>Open a lead to see its full journey or log the next conversation. Overdue leads appear first.</p>
    </div></div>
</div></div>
@if($canManageLeadAssignments)
<div class="modal fade" id="assignLeadsModal" tabindex="-1" aria-labelledby="assignLeadsTitle" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" id="assignLeadsForm" action="{{ route('lead-management.bulk-assign') }}">
        @csrf
        <div class="modal-header"><h5 id="assignLeadsTitle" class="modal-title">Assign leads</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><p id="assignLeadContext" class="text-muted small"></p><p class="small">Selected staff will be added to the existing lead team.</p><div id="selectedLeadInputs"></div>
            <div class="border rounded p-3" style="max-height:280px;overflow:auto">
                @forelse($staff as $member)
                <label class="d-flex gap-2 align-items-center py-2"><input class="form-check-input m-0" type="checkbox" name="assigned_user_ids[]" value="{{ $member->id }}"><span>{{ trim($member->first_name . ' ' . $member->last_name) }}</span></label>
                @empty<p class="text-muted mb-0">No staff members available.</p>@endforelse
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" @disabled($staff->isEmpty())>Save assignment</button></div>
    </form>
</div></div></div>
@endif
@can('delete_leads')
<form id="deleteLeadsForm" action="{{ route('lead-management.bulk-delete') }}" method="POST">@csrf @method('DELETE')<div id="deleteLeadInputs"></div></form>
@endcan
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#leadWorklist').DataTable({
        order: [], pageLength: 10, autoWidth: false,
        columnDefs: [{targets: [0, 7], orderable: false, searchable: false}],
        language: {emptyTable: 'No leads match these filters. Try another view or reset the filters.', zeroRecords: 'No matching leads found.'}
    });
    const allBoxes = () => Array.from(table.rows().nodes()).flatMap(row => Array.from(row.querySelectorAll('.lead-selection')));
    const selected = () => allBoxes().filter(box => box.checked);
    function refreshSelection() {
        const count = selected().length;
        document.getElementById('selectionCount').textContent = count + ' selected';
        ['bulkAssignButton', 'bulkDeleteButton'].forEach(id => { const button = document.getElementById(id); if (button) button.disabled = count === 0; });
        const page = Array.from(table.rows({page:'current', search:'applied'}).nodes()).flatMap(row => Array.from(row.querySelectorAll('.lead-selection')));
        const all = document.getElementById('selectAllLeads');
        if (all) { all.checked = page.length > 0 && page.every(box => box.checked); all.indeterminate = page.some(box => box.checked) && !all.checked; }
    }
    $('#leadWorklist').on('change', '.lead-selection', refreshSelection);
    $('#selectAllLeads').on('change', function () {
        table.rows({page:'current', search:'applied'}).nodes().toArray().forEach(row => row.querySelectorAll('.lead-selection').forEach(box => box.checked = this.checked));
        refreshSelection();
    });
    table.on('draw', refreshSelection);
    function fillSelection(container) {
        container.replaceChildren();
        selected().forEach((box, index) => {
            [['source', box.dataset.source], ['id', box.dataset.id]].forEach(([key, value]) => {
                const input = document.createElement('input'); input.type = 'hidden'; input.name = 'selected_leads[' + index + '][' + key + ']'; input.value = value; container.appendChild(input);
            });
        });
    }
    const assignForm = document.getElementById('assignLeadsForm');
    const assignElement = document.getElementById('assignLeadsModal');
    const assignModal = assignElement ? new bootstrap.Modal(assignElement) : null;
    const bulkUrl = assignForm ? assignForm.action : '';
    $('#leadWorklist').on('click', '.assign-one', function () {
        assignForm.reset(); assignForm.action = this.dataset.url;
        document.getElementById('selectedLeadInputs').replaceChildren();
        document.getElementById('assignLeadContext').textContent = this.dataset.name;
        assignModal.show();
    });
    $('#bulkAssignButton').on('click', function () {
        if (!selected().length) return;
        assignForm.reset(); assignForm.action = bulkUrl;
        fillSelection(document.getElementById('selectedLeadInputs'));
        document.getElementById('assignLeadContext').textContent = selected().length + ' selected lead(s)';
        assignModal.show();
    });
    if (assignForm) assignForm.addEventListener('submit', function (event) {
        if (!this.querySelector('input[name="assigned_user_ids[]"]:checked')) { event.preventDefault(); alert('Select at least one staff member.'); }
    });
    $('#bulkDeleteButton').on('click', function () {
        if (!selected().length || !confirm('Delete ' + selected().length + ' selected lead(s)?')) return;
        fillSelection(document.getElementById('deleteLeadInputs')); document.getElementById('deleteLeadsForm').requestSubmit();
    });
});
</script>
@endpush
