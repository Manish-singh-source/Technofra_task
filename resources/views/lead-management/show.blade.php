@extends('layout.master')

@push('styles')
<style>
.lead-profile { --journey-border:#e7ecf3; }
.lead-profile .profile-card { border:1px solid var(--journey-border);border-radius:17px;box-shadow:0 6px 24px #23365308;overflow:hidden; }
.lead-profile .profile-card:first-of-type { background:linear-gradient(135deg,#fff 0%,#f5f8ff 100%); }
.lead-profile .profile-avatar { width:54px;height:54px;display:grid;place-items:center;border-radius:16px;background:linear-gradient(145deg,#e5ecff,#cdd9ff);color:#3559b9;font-size:22px;font-weight:700;flex-shrink:0;box-shadow:0 4px 12px #4161bc20; }
.lead-profile h1 { font-size:25px;font-weight:700;color:#223450;letter-spacing:-.5px;overflow-wrap:anywhere; }
.lead-profile .profile-label { font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#7d899c;margin-bottom:7px; }
.lead-profile .profile-value { font-size:13px;color:#3f506a;overflow-wrap:anywhere; }
.lead-profile .stage-track { display:flex;gap:6px;overflow:auto;padding-bottom:5px; }
.lead-profile .stage-step { min-width:105px;flex:1;padding:10px;border-top:3px solid #dfe5ef;background:#f8f9fc;border-radius:7px;font-size:11px;color:#8792a4;transition:.15s ease; }
.lead-profile .stage-step:nth-child(4n+1) { border-top-color:#b9c9ff; }
.lead-profile .stage-step:nth-child(4n+2) { border-top-color:#c7e8d6; }
.lead-profile .stage-step:nth-child(4n+3) { border-top-color:#f1d19e; }
.lead-profile .stage-step:nth-child(4n+4) { border-top-color:#ddc9f5; }
.lead-profile .stage-step.current { border-color:#4f6ef7;background:#edf2ff;color:#3558b9;font-weight:700;box-shadow:0 3px 9px #4f6ef71c; }
.lead-profile .journey-entry { position:relative;padding:0 0 25px 29px;border-left:2px solid #e9edf5;margin-left:8px; }
.lead-profile .journey-entry:last-child { padding-bottom:5px;border-color:transparent; }
.lead-profile .journey-dot { position:absolute;left:-10px;top:0;width:18px;height:18px;border:4px solid #fff;border-radius:50%;background:#6482dc;box-shadow:0 0 0 1px #e3e9f3; }
.lead-profile .journey-entry.followup_added .journey-dot { background:#28a571; }
.lead-profile .journey-entry.status_changed .journey-dot { background:#bd8a30; }
.lead-profile .journey-entry p { white-space:pre-line;overflow-wrap:anywhere;font-size:13px;color:#56667e; }
.lead-profile .next-action { border-left:4px solid #4f6ef7; }
.lead-profile .next-action.overdue { border-left-color:#cf3849;background:#fffafb; }
.lead-profile .next-action.closed { border-left-color:#27a071;background:#f8fdfb; }
.lead-profile .conversation { border:1px solid var(--journey-border);border-radius:12px;padding:16px;margin-bottom:12px; }
.lead-profile .conversation p { white-space:pre-line;overflow-wrap:anywhere; }
.lead-profile .profile-tabs { padding:5px;background:#f5f7fb;border:1px solid #e8edf5;border-radius:11px; }
.lead-profile .profile-tabs .nav-link { font-size:13px;border-radius:8px;color:#64748c;padding:9px 14px;transition:.15s ease; }
.lead-profile .profile-tabs .nav-link:hover { color:#3559cf;background:#edf2ff; }
.lead-profile .profile-tabs .nav-link.active { color:#fff;background:linear-gradient(135deg,#4161bc,#304a86);box-shadow:0 3px 8px #334f8630; }
.lead-profile .sticky-plan { position:sticky;top:90px; }
.lead-profile .close-reason { border-radius:9px;padding:12px;background:#fff0f1;color:#a33244;font-size:13px;white-space:pre-line; }
.lead-profile .modal-dialog { width:calc(100% - 2rem); max-width:800px; margin:1rem auto; }
.lead-profile .modal-content { max-width:100%; overflow:hidden; }
.lead-profile .modal-header,.lead-profile .modal-body,.lead-profile .modal-footer { padding:1rem 1.25rem; }
.lead-profile .modal-body { overflow-wrap:anywhere; }
.lead-profile .modal-footer { gap:.5rem; }
@media(max-width:767px) {
    .lead-profile .modal-dialog { width:calc(100% - 1rem); margin:.5rem auto; }
    .lead-profile .modal-dialog-scrollable { height:calc(100% - 1rem); }
    .lead-profile .modal-dialog-scrollable .modal-content { max-height:100%; }
    .lead-profile .modal-header { align-items:flex-start; }
    .lead-profile .modal-title { font-size:1.1rem; line-height:1.3; }
    .lead-profile .modal-header small { display:block; margin-top:.25rem; line-height:1.4; }
    .lead-profile .modal-body { padding:.9rem 1rem; }
    .lead-profile .modal-body .row { --bs-gutter-y:.85rem; }
    .lead-profile .modal-footer { padding:.85rem 1rem; flex-wrap:wrap; }
}
@media(max-width:480px) {
    .lead-profile .modal-dialog { width:calc(100% - .5rem); margin:.25rem auto; }
    .lead-profile .modal-dialog-scrollable { height:calc(100% - .5rem); }
    .lead-profile .modal-header,.lead-profile .modal-body,.lead-profile .modal-footer { padding-left:.85rem; padding-right:.85rem; }
    .lead-profile .modal-footer { flex-direction:column-reverse; align-items:stretch; }
    .lead-profile .modal-footer .btn { width:100%; }
    .lead-profile .modal-body textarea { min-height:100px; }
}
</style>
@endpush

@section('content')
@php
    $closed = in_array((string) $leadModel->status, \App\Services\LeadManagement\LeadWorklistService::CLOSED, true);
    $canEdit = auth()->user()->can('edit_leads');
    $statusLabels = collect($statusOptions)->pluck('name', 'slug');
    $latestConversation = $followups->first();
    $staffNames = collect($leadModel->assignedStaffNames)->map(fn ($member) => trim($member->first_name . ' ' . $member->last_name))->filter()->implode(', ');
    $stateLabels = ['overdue'=>'Follow-up overdue', 'today'=>'Follow-up due today', 'upcoming'=>'Next follow-up', 'unscheduled'=>'Plan the next action', 'closed'=>'Lead closed'];
    $nextAt = $leadModel->next_followup_at;
@endphp
<div class="page-wrapper"><div class="page-content lead-profile">
    @include('lead-management.partials.feedback')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <a href="{{ route('lead-management.index') }}" class="text-decoration-none small"><i class="bx bx-arrow-back me-1"></i>All leads</a>
        <div class="d-flex gap-2">
            <a href="{{ route('lead-management.followups', ['source'=>$lead['source_type'],'id'=>$lead['source_id']]) }}" class="btn btn-sm btn-outline-secondary">Conversation history</a>
            @if($canEdit && !$closed)<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#logFollowupModal"><i class="bx bx-phone-call me-1"></i>Log follow-up</button>@endif
        </div>
    </div>
    <div class="card profile-card mb-3"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div class="d-flex gap-3 align-items-center"><div class="profile-avatar">{{ mb_strtoupper(mb_substr($lead['name'] === '-' ? 'L' : $lead['name'],0,1)) }}</div><div>
                <div class="profile-label mb-1">{{ $lead['source'] }} · #{{ $lead['source_type'] }}-{{ $lead['source_id'] }}</div>
                <h1 class="mb-1">{{ $lead['name'] === '-' ? 'Unnamed lead' : $lead['name'] }}</h1>
                <span class="text-muted small">{{ $lead['company'] === '-' ? 'Individual lead' : $lead['company'] }}</span>
            </div></div>
            <span class="badge rounded-pill {{ $leadModel->status === 'lost' ? 'bg-danger' : ($closed ? 'bg-success' : 'bg-primary') }} px-3 py-2">{{ $statusLabels[$leadModel->status] ?? ucwords(str_replace('_',' ',$leadModel->status)) }}</span>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3"><div class="profile-label">Contact</div><div class="profile-value">
                @if($lead['number'] !== '-')<a href="tel:{{ preg_replace('/[^+0-9]/','',$lead['number']) }}" class="d-block">{{ $lead['number'] }}</a>@endif
                @if($lead['email'] !== '-')<a href="mailto:{{ $lead['email'] }}">{{ $lead['email'] }}</a>@endif
                @if($lead['number'] === '-' && $lead['email'] === '-')Contact details needed @endif
            </div></div>
            <div class="col-sm-6 col-xl-3"><div class="profile-label">Lead team</div><div class="profile-value">{{ $staffNames ?: $lead['assigned_to'] }}</div></div>
            <div class="col-sm-6 col-xl-3"><div class="profile-label">Created</div><div class="profile-value">{{ $lead['created_at'] }}</div></div>
            <div class="col-sm-6 col-xl-3"><div class="profile-label">Last conversation</div><div class="profile-value">{{ $latestConversation?->followup_date?->format('d M Y, h:i A') ?: 'No conversation logged' }}</div></div>
        </div>
        <div class="stage-track" aria-label="Lead pipeline">
            @foreach($statusOptions as $stage)
            <div class="stage-step {{ $stage['slug'] === $leadModel->status ? 'current' : '' }}" @if($stage['slug'] === $leadModel->status) aria-current="step" @endif>{{ $stage['name'] }}</div>
            @endforeach
        </div>
    </div>
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card profile-card"><div class="card-body p-3 p-lg-4">
                <ul class="nav nav-pills profile-tabs gap-1 mb-4" role="tablist">
                    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#journey" role="tab" aria-controls="journey" aria-selected="true">Lead journey</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#conversations" role="tab" aria-controls="conversations" aria-selected="false">Conversations ({{ $followups->count() }})</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#notes" role="tab" aria-controls="notes" aria-selected="false">Notes</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#details" role="tab" aria-controls="details" aria-selected="false">Details</button></li>
                </ul>
                <div class="tab-content">
                    <div id="journey" class="tab-pane fade show active" role="tabpanel">
                        <div class="d-flex justify-content-between mb-4"><div><h6 class="fw-bold mb-1">What happened to this lead?</h6><span class="small text-muted">Conversations, stage changes, assignments and next actions.</span></div></div>
                        @foreach($timeline as $item)
                        <div class="journey-entry {{ $item->activity_type }}"><span class="journey-dot"></span>
                            <div class="d-flex justify-content-between gap-2 flex-wrap mb-1">
                                <strong class="small">{{ $item->activity_type === 'source_linked' ? 'Journey started' : ucwords(str_replace('_',' ',$item->activity_type)) }}</strong>
                                <small class="text-muted">{{ $item->created_at?->format('d M Y, h:i A') }}</small>
                            </div>
                            <p class="mb-1">{{ $item->description }}</p>
                            @if($item->activity_type === 'lead_assigned' && !empty($item->metadata['assigned_to']))
                                @php($assignee = $staff->firstWhere('id', $item->metadata['assigned_to']))
                                @if($assignee)<div class="small text-muted mb-1">Assigned to {{ trim($assignee->first_name . ' ' . $assignee->last_name) }}</div>@endif
                            @endif
                            @if(!empty($item->metadata['next_followup_at']))<div class="small text-primary mb-1">Next action: {{ \Illuminate\Support\Carbon::parse($item->metadata['next_followup_at'])->format('d M Y, h:i A') }}</div>@endif
                            @if($item->user)<small class="text-muted">By {{ trim($item->user->first_name . ' ' . $item->user->last_name) }}</small>@endif
                        </div>
                        @endforeach
                        <div class="journey-entry"><span class="journey-dot"></span><strong class="small d-block mb-1">Lead received</strong><p class="mb-1">Received through {{ $lead['source'] }}.</p><small class="text-muted">{{ $lead['created_at'] }}</small></div>
                        @if($timeline->count() >= 100)<a class="small d-inline-block mt-3" href="{{ route('lead-management.timeline',['source'=>$lead['source_type'],'id'=>$lead['source_id']]) }}">View older activity</a>@endif
                    </div>
                    <div id="conversations" class="tab-pane fade" role="tabpanel">
                        @include('lead-management.partials.conversations', ['conversations'=>$followups])
                    </div>
                    <div id="notes" class="tab-pane fade" role="tabpanel">
                        @can('edit_leads')
                        <form action="{{ route('lead-management.note',['source'=>$lead['source_type'],'id'=>$lead['source_id']]) }}" method="POST" class="mb-4">@csrf
                            <label for="leadNote" class="form-label small">Add a useful note for the lead team</label>
                            <textarea id="leadNote" name="note" class="form-control mb-2" rows="3" maxlength="5000" required>{{ old('note') }}</textarea>
                            <div class="d-flex justify-content-between align-items-center"><label class="small"><input type="checkbox" name="is_private" value="1" @checked(old('is_private'))> Private to you and admins</label><button class="btn btn-primary btn-sm">Save note</button></div>
                        </form>
                        @endcan
                        @forelse($notes as $note)<div class="conversation"><small class="text-muted">{{ $note->created_at?->format('d M Y, h:i A') }}{{ $note->is_private ? ' · Private' : '' }}</small><p class="small mb-0 mt-2">{{ $note->note }}</p></div>
                        @empty<p class="text-muted small">No notes added yet.</p>@endforelse
                    </div>
                    <div id="details" class="tab-pane fade" role="tabpanel">
                        <div class="profile-label">Requirements / description</div><p class="profile-value" style="white-space:pre-line">{{ $leadModel->description ?: $leadModel->requirements ?: 'No requirements added yet.' }}</p>
                        <div class="profile-label mt-4">Lead value</div><p class="profile-value">{{ number_format((float) ($leadModel->lead_value ?? 0), 2) }}</p>
                        <div class="profile-label mt-4">Tags</div><div class="d-flex flex-wrap gap-1">@forelse($leadModel->tags ?? [] as $tag)<span class="badge bg-light text-dark border">{{ $tag }}</span>@empty<span class="small text-muted">No tags added</span>@endforelse</div>
                        @if($lead['source_type'] === 'enquiryform' && $sourceRecord)
                        <div class="profile-label mt-4">Enquiry message</div><p class="profile-value">{{ $sourceRecord->message ?: '-' }}</p>
                        <div class="profile-label mt-4">Nature of project / delivery time</div><p class="profile-value">{{ $sourceRecord->nature_of_project ?: '-' }} · {{ $sourceRecord->delivery_time ?: '-' }}</p>
                        @endif
                        @if($canEdit && !$closed && $lead['source_type'] === 'lead')<a class="btn btn-sm btn-outline-primary mt-4" href="{{ route('lead.edit',$lead['source_id']) }}">Edit lead details</a>@endif
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-4"><div class="sticky-plan">
            <div class="card profile-card next-action {{ $followupState }} mb-3"><div class="card-body p-4">
                <div class="profile-label">{{ $closed ? 'Final outcome' : 'Next action' }}</div>
                <h5 class="fw-bold {{ $followupState === 'overdue' ? 'text-danger' : '' }}">{{ $stateLabels[$followupState] }}</h5>
                @if($closed)
                    <p class="small text-muted mb-2">{{ $statusLabels[$leadModel->status] ?? $leadModel->status }}{{ $leadModel->converted_at && $leadModel->isWon() ? ' on ' . $leadModel->converted_at->format('d M Y') : '' }}</p>
                    @if($leadModel->status === 'lost' && $leadModel->lost_reason)<div class="close-reason">{{ $leadModel->lost_reason }}</div>@endif
                    @if($leadModel->isWon() && $leadModel->won_value !== null)<div class="profile-value">Won value: {{ number_format((float)$leadModel->won_value,2) }}</div>@endif
                    <p class="small text-muted mt-2 mb-0">Conversation history and the lead journey are preserved.</p>
                @else
                    @if($nextAt)<p class="fw-semibold mb-1">{{ $nextAt->format('l, d M Y') }}</p><p class="text-muted">{{ $nextAt->format('h:i A') }} · {{ $nextAt->diffForHumans() }}</p>
                    @else<p class="small text-muted">No next follow-up is planned. Choose a date so this lead stays on your worklist.</p>@endif
                    @if($canEdit)<div class="d-flex gap-2 flex-wrap"><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#logFollowupModal">Log conversation</button><button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleFollowupModal">{{ $nextAt ? 'Reschedule' : 'Schedule' }}</button></div>@endif
                @endif
            </div></div>
            @if($latestConversation)
            <div class="card profile-card mb-3"><div class="card-body p-4"><div class="profile-label">Latest outcome</div><h6 class="fw-bold">{{ ucwords(str_replace('_',' ',$latestConversation->outcome ?: 'Activity logged')) }}</h6><p class="small text-muted mb-0" style="white-space:pre-line;overflow-wrap:anywhere">{{ $latestConversation->discussion_notes ?: 'No remarks recorded.' }}</p></div></div>
            @endif
            @if($canEdit && !$closed)
            <div class="card profile-card"><div class="card-body p-4">
                <h6 class="fw-bold mb-3">Update lead stage</h6>
                <form method="POST" action="{{ route('lead-management.status',['source'=>$lead['source_type'],'id'=>$lead['source_id']]) }}" data-stage-form>@csrf @method('PATCH')
                    <label class="form-label small" for="profileStage">Stage</label><select id="profileStage" name="status" class="form-select form-select-sm mb-3" data-stage-select required>
                        @foreach($allowedStatuses as $option)<option value="{{ $option['slug'] }}" @selected(old('status',$leadModel->status) === $option['slug'])>{{ $option['name'] }}</option>@endforeach
                    </select>
                    <div data-lost-field class="mb-3"><label class="form-label small">Why was this lead lost?</label><textarea name="lost_reason" class="form-control form-control-sm" rows="2" maxlength="1000">{{ old('lost_reason') }}</textarea></div>
                    <div data-won-field class="mb-3"><label class="form-label small">Won value</label><input type="number" name="won_value" value="{{ old('won_value') }}" min="0" step="0.01" class="form-control form-control-sm"></div>
                    <label class="form-label small">Stage change remarks</label><textarea name="remarks" class="form-control form-control-sm mb-3" rows="2" maxlength="1000" placeholder="What changed?">{{ old('remarks') }}</textarea>
                    <button class="btn btn-outline-primary btn-sm w-100">Save stage</button>
                </form>
            </div></div>
            @endif
        </div></div>
    </div>
</div></div>
@if($canEdit && !$closed)
<div class="modal fade" id="logFollowupModal" tabindex="-1" aria-labelledby="logFollowupTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form method="POST" action="{{ route('lead-management.followup',['source'=>$lead['source_type'],'id'=>$lead['source_id']]) }}" data-stage-form>
        @csrf<input type="hidden" name="_intent" value="followup">
        <div class="modal-header"><div><h5 class="modal-title" id="logFollowupTitle">Log a conversation</h5><small class="text-muted">Record what happened and decide the next step.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Activity type</label><select name="followup_type" class="form-select" required>@foreach(['call','whatsapp','email','meeting','demo','video_call','site_visit','proposal_sent','quotation_sent'] as $type)<option value="{{ $type }}" @selected(old('followup_type','call') === $type)>{{ ucwords(str_replace('_',' ',$type)) }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label small">When did it happen?</label><input type="datetime-local" name="followup_date" class="form-control" value="{{ old('followup_date',now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required></div>
            <div class="col-md-6"><label class="form-label small">Conversation outcome</label><select name="outcome" class="form-select" id="conversationOutcome" required><option value="">Select outcome</option>@foreach(['interested','callback_later','no_response','meeting_scheduled','proposal_requested','negotiation','not_interested','converted','lost'] as $outcome)<option value="{{ $outcome }}" @selected(old('outcome') === $outcome)>{{ $outcome === 'converted' ? 'Closed won' : ucwords(str_replace('_',' ',$outcome)) }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label small">Lead stage after conversation</label><select name="lead_status_after_followup" class="form-select" data-stage-select id="conversationStage"><option value="">Keep current stage</option>@foreach($allowedStatuses as $option)<option value="{{ $option['slug'] }}" @selected(old('lead_status_after_followup') === $option['slug'])>{{ $option['name'] }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label small">What was discussed?</label><textarea name="discussion_notes" rows="3" class="form-control" maxlength="5000" required placeholder="Customer response, requirements, objections or agreed next steps…">{{ old('discussion_notes') }}</textarea></div>
            <div class="col-md-6" data-next-field><label class="form-label small">Next follow-up</label><input type="datetime-local" name="next_followup_date" id="conversationNextDate" class="form-control" value="{{ old('next_followup_date') }}"><small class="text-muted">Required for a callback or scheduled meeting.</small></div>
            <div class="col-md-6" data-lost-field><label class="form-label small">Lost reason</label><textarea name="lost_reason" rows="2" class="form-control" maxlength="1000">{{ old('lost_reason') }}</textarea></div>
            <div class="col-md-6" data-won-field><label class="form-label small">Won value</label><input name="won_value" type="number" min="0" step="0.01" value="{{ old('won_value') }}" class="form-control"></div>
            <div class="col-12"><label class="small"><input type="checkbox" name="complete_scheduled_followup" value="1" @checked(old('_intent') !== 'followup' || old('complete_scheduled_followup'))> This conversation completes the pending follow-up.</label><p class="small text-muted mb-0 mt-1">A new date replaces the previous plan. Without a new date, completed follow-ups move to “No next action”.</p></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save conversation &amp; next step</button></div>
    </form>
</div></div></div>
<div class="modal fade" id="scheduleFollowupModal" tabindex="-1" aria-labelledby="scheduleFollowupTitle" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('lead-management.next-followup',['source'=>$lead['source_type'],'id'=>$lead['source_id']]) }}">@csrf<input type="hidden" name="_intent" value="schedule">
        <div class="modal-header"><h5 id="scheduleFollowupTitle" class="modal-title">{{ $nextAt ? 'Reschedule follow-up' : 'Schedule next follow-up' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><label class="form-label small">Next follow-up date &amp; time</label><input type="datetime-local" name="next_followup_at" class="form-control mb-3" value="{{ old('next_followup_at',$nextAt?->format('Y-m-d\TH:i')) }}" required><label class="form-label small">What is the next action?</label><textarea name="reason" class="form-control" rows="3" maxlength="1000" required placeholder="Call to discuss the proposal, arrange a meeting…">{{ old('reason') }}</textarea><p class="small text-muted mt-2 mb-0">This updates the plan without recording a completed conversation.</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save next action</button></div>
    </form>
</div></div></div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-stage-form]').forEach(form => {
        const select = form.querySelector('[data-stage-select]');
        function syncFields() {
            const stage = select.value;
            form.querySelectorAll('[data-lost-field]').forEach(field => { field.hidden = stage !== 'lost'; field.querySelectorAll('input,textarea').forEach(input => { input.disabled = stage !== 'lost'; input.required = stage === 'lost'; }); });
            form.querySelectorAll('[data-won-field]').forEach(field => { field.hidden = stage !== 'converted'; field.querySelectorAll('input').forEach(input => input.disabled = stage !== 'converted'); });
            form.querySelectorAll('[data-next-field]').forEach(field => { const closed = ['converted','lost','junk'].includes(stage); field.hidden = closed; field.querySelectorAll('input').forEach(input => input.disabled = closed); });
            const outcome = form.querySelector('[name="outcome"]');
            const next = form.querySelector('[name="next_followup_date"]');
            if (next && outcome) next.required = !next.disabled && ['callback_later','meeting_scheduled'].includes(outcome.value);
        }
        select.addEventListener('change', syncFields);
        const outcome = form.querySelector('[name="outcome"]');
        if (outcome) outcome.addEventListener('change', function () { if (['converted','lost'].includes(this.value)) select.value = this.value; syncFields(); });
        syncFields();
    });
    const followupModal = document.getElementById('logFollowupModal');
    const scheduleModal = document.getElementById('scheduleFollowupModal');
    const intent = @json(old('_intent'));
    const hasErrors = @json($errors->any());
    if (scheduleModal && hasErrors && intent === 'schedule') new bootstrap.Modal(scheduleModal).show();
    else if (followupModal && ((hasErrors && intent === 'followup') || new URLSearchParams(window.location.search).get('action') === 'followup')) new bootstrap.Modal(followupModal).show();
});
</script>
@endpush
