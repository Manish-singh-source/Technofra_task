@extends('layout.master')

@section('content')
<div class="page-wrapper">
    <div class="page-content crm-lead-view">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="page-breadcrumb d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="ps-1">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lead-management.index') }}">Lead Management</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $lead['name'] ?: 'Lead Profile' }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-semibold">Lead Profile</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('lead-management.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                    <i class='bx bx-calendar-plus me-1'></i>Add Followup
                </button>
            </div>
        </div>


        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Lead Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><small class="text-muted d-block">Email</small><div class="fw-semibold">{{ $lead['email'] ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Phone</small><div class="fw-semibold">{{ $lead['number'] ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Company</small><div class="fw-semibold">{{ $lead['company'] ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Source</small><div class="fw-semibold">{{ $lead['source'] ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Created Date</small><div class="fw-semibold">{{ $lead['created_at'] ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Previous Status</small><div class="fw-semibold">{{ $leadModel->previous_status ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Converted At</small><div class="fw-semibold">{{ optional($leadModel->converted_at)->format('d M Y h:i A') ?: '-' }}</div></div>
                            <div class="col-md-6"><small class="text-muted d-block">Lost Reason</small><div class="fw-semibold">{{ $leadModel->lost_reason ?: '-' }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Update Status</h6>
                        <form method="POST" action="{{ route('lead-management.status', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="row g-2">
                            @csrf
                            @method('PATCH')
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    @foreach(($statusOptions ?? []) as $statusOption)
                                        <option value="{{ $statusOption['slug'] }}" {{ ($leadModel->status ?? '') === $statusOption['slug'] ? 'selected' : '' }}>
                                            {{ $statusOption['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Won Value</label>
                                <input type="number" step="0.01" name="won_value" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lost Reason</label>
                                <input type="text" name="lost_reason" class="form-control" placeholder="Required for lost">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                            </div>
                            <div class="col-12 d-grid mt-2">
                                <button class="btn btn-success">Save Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <ul class="nav nav-pills crm-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#timeline" type="button">Timeline</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#followups" type="button">Followups</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#notes" type="button">Notes</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reminders" type="button">Reminders</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#assignments" type="button">Assignments</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#statuses" type="button">Status History</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="timeline">
                        @forelse($timeline as $item)
                            <div class="crm-list-item">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ ucwords(str_replace('_', ' ', $item->activity_type)) }}</div>
                                        <div class="text-muted small">{{ $item->description }}</div>
                                    </div>
                                    <small class="text-muted">{{ $item->created_at?->format('d M Y h:i A') }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No timeline data found.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="followups">
                        @forelse($followups as $followup)
                            <div class="crm-list-item">
                                <div class="fw-semibold">{{ ucwords(str_replace('_', ' ', (string) $followup->followup_type)) }}</div>
                                <div class="small text-muted">{{ $followup->followup_date?->format('d M Y h:i A') }} | Outcome: {{ $followup->outcome ?: '-' }}</div>
                                <div>{{ $followup->discussion_notes ?: '-' }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No followups yet.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="notes">
                        <form method="POST" action="{{ route('lead-management.note', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="mb-3">
                            @csrf
                            <textarea name="note" class="form-control mb-2" rows="2" placeholder="Add note" required></textarea>
                            <div class="d-flex justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_private" value="1" id="privateNote">
                                    <label class="form-check-label" for="privateNote">Private</label>
                                </div>
                                <button class="btn btn-sm btn-primary">Add Note</button>
                            </div>
                        </form>
                        @forelse($notes as $note)
                            <div class="crm-list-item">
                                <div class="small text-muted">{{ $note->created_at?->format('d M Y h:i A') }}</div>
                                <div>{{ $note->note }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No notes yet.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="reminders">
                        <form method="POST" action="{{ route('lead-management.reminder', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-5"><input type="datetime-local" name="remind_at" class="form-control" required></div>
                            <div class="col-md-4">
                                <select name="reminder_type" class="form-select">
                                    <option value="dashboard">Dashboard</option>
                                    <option value="email">Email</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                            <div class="col-md-3"><button class="btn btn-primary w-100">Add Reminder</button></div>
                        </form>
                        @forelse($reminders as $reminder)
                            <div class="crm-list-item d-flex justify-content-between">
                                <span>{{ $reminder->remind_at?->format('d M Y h:i A') }} ({{ strtoupper((string) $reminder->reminder_type) }})</span>
                                <span class="badge bg-secondary">{{ strtoupper((string) $reminder->status) }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No reminders yet.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="assignments">
                        @forelse($assignments as $assignment)
                            <div class="crm-list-item">
                                <div class="fw-semibold">{{ optional($assignment->assignee)->name ?? 'Unassigned' }}</div>
                                <div class="small text-muted">Assigned at: {{ optional($assignment->assigned_at)->format('d M Y h:i A') ?: '-' }}</div>
                                <div class="small">{{ $assignment->assignment_note ?: '-' }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No assignment history yet.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="statuses">
                        @forelse($statusHistory as $history)
                            <div class="crm-list-item">
                                <div><strong>{{ $history->old_status ?: 'N/A' }}</strong> -> <strong>{{ $history->new_status }}</strong></div>
                                <div class="small text-muted">{{ ($history->changed_at ?? $history->created_at)?->format('d M Y h:i A') }}</div>
                                <div class="small">{{ $history->remarks ?: '-' }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No status history yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .crm-lead-view .pipeline-wrap {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 10px;
    }
    .crm-lead-view .pipeline-stage {
        border: 1px solid #dfe3ea;
        border-radius: 10px;
        padding: 10px;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .crm-lead-view .pipeline-stage.active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.12);
    }
    .crm-lead-view .pipeline-stage .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .crm-lead-view .pipeline-stage .label {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
    }
    .crm-lead-view .crm-tabs .nav-link {
        border-radius: 999px;
        padding: 6px 14px;
        font-weight: 600;
    }
    .crm-lead-view .crm-list-item {
        border: 1px solid #e6e8ee;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        background: #fff;
    }
</style>
@endpush

<!-- Add Followup Modal -->
<div class="modal fade" id="addFollowupModal" tabindex="-1" aria-labelledby="addFollowupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('lead-management.followup', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addFollowupModalLabel">Add Followup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Followup Type</label>
                            <select name="followup_type" class="form-select" required>
                                @foreach (['call','whatsapp','email','meeting','demo','video_call','site_visit','proposal_sent','quotation_sent'] as $type)
                                    <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outcome</label>
                            <select name="outcome" class="form-select">
                                <option value="">Select</option>
                                @foreach (['interested','not_interested','callback_later','converted','no_response','meeting_scheduled','proposal_requested','negotiation','lost'] as $outcome)
                                    <option value="{{ $outcome }}">{{ ucwords(str_replace('_', ' ', $outcome)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Followup Date</label>
                            <input type="datetime-local" name="followup_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Followup</label>
                            <input type="datetime-local" name="next_followup_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lead Status</label>
                            <select name="lead_status_after_followup" class="form-select">
                                <option value="">No Change</option>
                                @foreach (['new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk'] as $status)
                                    <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reminder Type</label>
                            <select name="reminder_type" class="form-select">
                                <option value="dashboard">Dashboard</option>
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="create_reminder" value="1" id="createReminder">
                                <label class="form-check-label" for="createReminder">Create Reminder</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Discussion Notes</label>
                            <textarea name="discussion_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Followup</button>
                </div>
            </form>
        </div>
    </div>
</div>
