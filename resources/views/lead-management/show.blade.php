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
                <a href="{{ route('lead-management.followups', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="btn btn-outline-primary btn-sm">Followup History</a>
                <a href="{{ route('lead-management.timeline', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="btn btn-outline-info btn-sm">Full Timeline</a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                    <i class='bx bx-calendar-plus me-1'></i>Add Followup
                </button>
            </div>
        </div>


        @php
            $leadName = trim((string) ($lead['name'] ?: 'Lead'));
            $leadInitials = collect(preg_split('/\s+/', $leadName))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_substr($part, 0, 1))
                ->implode('');
            $leadInitials = $leadInitials !== '' ? mb_strtoupper($leadInitials) : 'L';
            $leadStatus = strtolower((string) ($leadModel->status ?? ''));
            $statusBadgeClass = match ($leadStatus) {
                'converted', 'won' => 'bg-success-subtle text-success border border-success-subtle',
                'lost', 'junk' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'qualified' => 'bg-primary-subtle text-primary border border-primary-subtle',
                'contacted' => 'bg-info-subtle text-info border border-info-subtle',
                default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            };
            $assignedStaffNames = collect($leadModel->assignedStaffNames ?? []);
            $leadTags = collect(is_array($leadModel->tags ?? null) ? $leadModel->tags : []);
            $statusUpdatedByName = $leadModel->statusUpdatedBy
                ? trim(($leadModel->statusUpdatedBy->first_name ?? '') . ' ' . ($leadModel->statusUpdatedBy->last_name ?? ''))
                : '';
        @endphp

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card lead-info-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="lead-avatar">
                                    {{ $leadInitials }}
                                </div>
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h5 class="mb-0 fw-semibold">{{ $leadName }}</h5>
                                        <span class="badge rounded-pill {{ $statusBadgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $leadStatus ?: 'new')) }}
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $lead['company'] ?: 'Independent lead' }}
                                        <span class="mx-1">•</span>
                                        {{ $lead['source'] ?: 'Lead' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Created Date</div>
                                <div class="fw-semibold">{{ $lead['created_at'] ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="lead-info-tile h-100">
                                    <div class="lead-info-label">Contact</div>
                                    <div class="lead-info-value">{{ $lead['email'] ?: '-' }}</div>
                                    <div class="lead-info-subvalue">{{ $lead['number'] ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="lead-info-tile h-100">
                                    <div class="lead-info-label">Company</div>
                                    <div class="lead-info-value">{{ $lead['company'] ?: '-' }}</div>
                                    <div class="lead-info-subvalue">{{ $leadModel->website ?: 'No website added' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="lead-info-tile h-100">
                                    <div class="lead-info-label">Assigned Staff</div>
                                    <div class="lead-info-value">
                                        {{ $assignedStaffNames->isNotEmpty() ? $assignedStaffNames->map(fn ($member) => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')))->filter()->implode(', ') : '-' }}
                                    </div>
                                    <div class="lead-info-subvalue">{{ $assignedStaffNames->count() }} team member(s)</div>
                                </div>
                            </div>
                        </div>

                        <div class="lead-section mb-3">
                            <div class="lead-section-title">Description</div>
                            <div class="lead-section-body">
                                {{ $leadModel->description ?: 'No description added yet.' }}
                            </div>
                        </div>
                        
                        <div class="lead-section mb-3">
                            <div class="lead-section-title">Lead Value</div>
                            <div class="lead-section-body">
                                {{ $leadModel->lead_value ?: '0' }}
                            </div>
                        </div>

                        <div class="lead-section">
                            <div class="lead-section-title">Tags</div>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($leadTags as $tag)
                                    <span class="badge rounded-pill bg-light text-dark border">{{ $tag }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                {{-- 
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Lead Actions</h6>
                        <form method="POST" action="{{ route('lead-management.convert', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">Conversion Value</label>
                                <input type="number" step="0.01" min="0" name="conversion_value" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-outline-success btn-sm">Mark As Converted</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('lead-management.escalate', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">Escalate To</label>
                                <select name="escalated_to" class="form-select" required>
                                    <option value="">Select Staff</option>
                                    @foreach ($staff as $member)
                                        <option value="{{ $member->id }}">
                                            {{ $member->name ?: trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" class="form-control" rows="2" placeholder="Optional"></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Escalate Lead</button>
                            </div>
                        </form>
                    </div>
                </div> 
                --}}
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
                                <label class="form-label">Conversion Value</label>
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
    .crm-lead-view .lead-info-card {
        background:
            radial-gradient(circle at top right, rgba(13, 110, 253, 0.08), transparent 34%),
            linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border: 1px solid #e7ebf3;
        border-radius: 20px;
    }
    .crm-lead-view .lead-avatar {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
        color: #0d6efd;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.12), rgba(13, 110, 253, 0.04));
        border: 1px solid rgba(13, 110, 253, 0.12);
        flex-shrink: 0;
    }
    .crm-lead-view .lead-info-tile {
        border: 1px solid #e6e8ee;
        border-radius: 16px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.04);
    }
    .crm-lead-view .lead-info-label {
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 8px;
        font-weight: 700;
    }
    .crm-lead-view .lead-info-value {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.35;
        word-break: break-word;
    }
    .crm-lead-view .lead-info-subvalue {
        margin-top: 6px;
        font-size: 13px;
        color: #6c757d;
        word-break: break-word;
    }
    .crm-lead-view .lead-section {
        border: 1px solid #e6e8ee;
        border-radius: 16px;
        background: #fff;
        padding: 16px;
    }
    .crm-lead-view .lead-section-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 10px;
    }
    .crm-lead-view .lead-section-body {
        color: #1f2937;
        line-height: 1.7;
        white-space: pre-line;
    }
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
                                @foreach (['new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','converted','lost','junk'] as $status)
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
