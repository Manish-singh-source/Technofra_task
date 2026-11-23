@extends('layout.master')
<?php $isClient = in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client', 'customer'], true); ?>

@push('styles')
<style>
.plan-badge-draft{background:#6c757d;color:#fff}
.plan-badge-pending_client_review{background:#fd7e14;color:#fff}
.plan-badge-client_approved{background:#198754;color:#fff}
.plan-badge-changes_requested{background:#dc3545;color:#fff}

@media (max-width: 1439px) {
    .social-media-plans .plan-notes-column { display: none !important; }
}

.plan-actions-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    justify-content: flex-end;
}
.plan-btn-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.32rem 0.65rem;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.2;
    border-radius: 6px;
    border: 1px solid transparent;
    text-decoration: none;
    transition: all 0.18s ease-in-out;
    cursor: pointer;
}
.plan-btn-action i {
    font-size: 1rem;
}
.plan-btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
.plan-btn-post {
    color: #0d6efd;
    background-color: #edf5ff;
    border-color: #cfe2ff;
}
.plan-btn-post:hover {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
}
.plan-btn-edit {
    color: #b45309;
    background-color: #fffbeb;
    border-color: #fde68a;
}
.plan-btn-edit:hover {
    color: #fff;
    background-color: #f59e0b;
    border-color: #f59e0b;
}
.plan-btn-delete {
    color: #dc3545;
    background-color: #fff5f5;
    border-color: #fecaca;
}
.plan-btn-delete:hover {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}
</style>
@endpush

@section('content')
<div class="page-wrapper"><div class="page-content social-media-plans">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Monthly Content Plans</h4>
            <p class="text-muted mb-0">Create and oversee monthly planning containers for client social media campaigns.</p>
        </div>
        <div>
            <a href="{{ route('social-media.dashboard') }}" class="btn btn-outline-primary">
                <i class="bx bx-left-arrow-alt me-1"></i> Social Media Post Overview
            </a>
        </div>
    </div>

    <div class="nav nav-pills flex-wrap gap-2 mb-4" aria-label="Content planning sections">
        <a @class(['nav-link', 'active' => $activeTab === 'plans']) href="{{ route('social-media.plans.index', ['tab' => 'plans']) }}"><i class="bx bx-calendar me-1"></i>Monthly Plans</a>
        @if(!$isClient)
            <a @class(['nav-link', 'active' => $activeTab === 'work']) href="{{ route('social-media.plans.index', ['tab' => 'work']) }}"><i class="bx bx-task me-1"></i>My Work</a>
            <a @class(['nav-link', 'active' => $activeTab === 'review']) href="{{ route('social-media.plans.index', ['tab' => 'review']) }}"><i class="bx bx-search-alt me-1"></i>Review</a>
            <a @class(['nav-link', 'active' => $activeTab === 'approvals']) href="{{ route('social-media.plans.index', ['tab' => 'approvals']) }}"><i class="bx bx-user-check me-1"></i>Client Approvals</a>
            <a @class(['nav-link', 'active' => $activeTab === 'publishing']) href="{{ route('social-media.plans.index', ['tab' => 'publishing']) }}"><i class="bx bx-send me-1"></i>Publishing</a>
            <a @class(['nav-link', 'active' => $activeTab === 'festivals']) href="{{ route('social-media.plans.index', ['tab' => 'festivals']) }}"><i class="bx bx-party me-1"></i>Festival Master</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($activeTab === 'plans')
    @if($canManageContentPlans)
    <div class="card mb-4">
        <div class="card-header bg-transparent">
            <h6 class="mb-0 fw-semibold"><i class="bx bx-plus-circle me-1"></i> Create New Content Plan</h6>
        </div>
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('social-media.plans.store') }}">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Client *</label>
                    <select required class="form-select" name="client_id">
                        <option value="">Select client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Month *</label>
                    <select name="plan_month" class="form-select">
                        @for($m=1;$m<=12;$m++)
                            <option value="{{ $m }}" @selected($m==now()->month)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Year *</label>
                    <input name="plan_year" type="number" class="form-control" value="{{ now()->year }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Plan Title</label>
                    <input name="title" class="form-control" placeholder="e.g. Diwali Campaign / October Plan">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="pending_client_review">Pending Client Review</option>
                        <option value="client_approved">Client Approved</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Notes / Strategy (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Brief outline, themes, target audience or notes for this monthly plan..."></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bx bx-save me-1"></i> Save Content Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">All Content Plans</h5>
            <small class="text-muted">Total: {{ $plans->count() }}</small>
        </div>
        <div class="table-responsive">
            <table id="monthly-plans-table" data-no-default-datatable="true" class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Plan Title</th>
                        <th>Month & Year</th>
                        <th>Posts Count</th>
                        <th>Status</th>
                        <th class="plan-notes-column">Notes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr>
                        <td><strong>{{ $plan->client->name ?? '-' }}</strong></td>
                        <td>{{ $plan->title ?: 'Content Plan' }}</td>
                        <td data-order="{{ sprintf('%04d-%02d', $plan->plan_year, $plan->plan_month) }}">{{ \Carbon\Carbon::create($plan->plan_year, $plan->plan_month, 1)->format('F Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $plan->posts_count }} posts</span></td>
                        <td>
                            @php
                                $statusBadge = match($plan->status) {
                                    'client_approved' => 'bg-success',
                                    'pending_client_review' => 'bg-warning text-dark',
                                    'changes_requested', 'not_required' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ', $plan->status ?? 'draft')) }}</span>
                        </td>
                        <td class="plan-notes-column">
                            <small class="text-muted d-block">{{ Str::limit($plan->notes ?: '-', 45) }}</small>
                            @if($plan->client_feedback)<small class="d-block mt-1 text-danger"><strong>Client feedback:</strong> {{ Str::limit($plan->client_feedback, 60) }}</small>@endif
                        </td>
                        <td class="text-end">
                            <a class="plan-btn-action plan-btn-post" href="{{ route('social-media.plans.show', $plan) }}" title="View monthly plan and content">
                                <i class="bx bx-show"></i> View
                            </a>
                            @if($canManageContentPlans)
                                <div class="plan-actions-group">
                                    <a class="plan-btn-action plan-btn-post" href="{{ route('social-media.posts.create', ['client_id' => $plan->client_id, 'plan_id' => $plan->id]) }}" title="Create Post for this plan">
                                        <i class="bx bx-plus"></i> Post
                                    </a>
                                    <button type="button" class="plan-btn-action plan-btn-edit edit-plan-btn"
                                        data-id="{{ $plan->id }}"
                                        data-client-id="{{ $plan->client_id }}"
                                        data-month="{{ $plan->plan_month }}"
                                        data-year="{{ $plan->plan_year }}"
                                        data-title="{{ $plan->title }}"
                                        data-status="{{ $plan->status }}"
                                        data-notes="{{ $plan->notes }}"
                                        title="Edit Plan">
                                        <i class="bx bx-edit-alt"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('social-media.plans.destroy', $plan) }}" class="d-inline m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this content plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="plan-btn-action plan-btn-delete" title="Delete Plan">
                                            <i class="bx bx-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            @elseif($isClient && $plan->status === 'pending_client_review')
                                <form method="POST" action="{{ route('social-media.plans.client-decision', $plan) }}" class="text-start" style="min-width:210px">
                                    @csrf
                                    <textarea name="feedback" rows="2" class="form-control form-control-sm mb-2" placeholder="Feedback required if requesting changes"></textarea>
                                    <button name="decision" value="approved" class="btn btn-sm btn-success">Approve</button>
                                    <button name="decision" value="changes_requested" class="btn btn-sm btn-outline-warning">Request changes</button>
                                    <div class="small text-muted mt-2">Monthly planning is not needed?</div>
                                    <button name="decision" value="not_required" class="btn btn-sm btn-outline-danger mt-1" onclick="return confirm('Confirm that you do not need this monthly content plan?');">I don't need monthly planning</button>
                                </form>
                            @else
                                <small class="text-muted">{{ $plan->client_reviewed_at ? 'Reviewed '.$plan->client_reviewed_at->format('d M Y') : 'No action needed' }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No content plans found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
    @else
        @if($activeTab === 'festivals')
            <div class="card mb-4">
                <div class="card-header bg-transparent"><h6 class="mb-0 fw-semibold"><i class="bx bx-plus-circle me-1"></i>Add Festival / Event</h6></div>
                <div class="card-body">
                    <form class="row g-3" method="POST" action="{{ route('social-media.festivals.store') }}">
                        @csrf
                        <div class="col-md-4"><label class="form-label small fw-semibold">Name *</label><input required name="name" class="form-control" placeholder="e.g. Diwali"></div>
                        <div class="col-md-2"><label class="form-label small fw-semibold">Date</label><input type="date" name="event_date" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label small fw-semibold">Category</label><input name="category" class="form-control" placeholder="Festival"></div>
                        <div class="col-md-2"><label class="form-label small fw-semibold">Recurrence *</label><select name="recurrence_type" class="form-select"><option value="yearly">Yearly</option><option value="none">One time</option></select></div>
                        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit"><i class="bx bx-save me-1"></i>Save</button></div>
                    </form>
                </div>
            </div>
            <div class="card"><div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div><h5 class="mb-0">Festival Master</h5><small class="text-muted">{{ $festivals->count() }} events</small></div>
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('social-media.plans.festivals.import') }}" class="d-flex gap-1" onsubmit="return confirm('Add India and international festivals for the selected year? Existing matching events will be updated.');">
                            @csrf
                            <select name="year" class="form-select form-select-sm" required aria-label="Festival year to add">
                                <option value="">Select year</option>
                                @foreach($festivalImportYears as $festivalYear)<option value="{{ $festivalYear }}">{{ $festivalYear }}</option>@endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-primary btn-sm text-nowrap"><i class="bx bx-calendar-plus me-1"></i>Add Festivals</button>
                        </form>
                        <button type="submit" form="bulk-festival-delete-form" class="btn btn-outline-danger btn-sm" id="bulkFestivalDeleteButton" disabled onclick="return confirm('Delete the selected festivals/events?');"><i class="bx bx-trash me-1"></i>Delete Selected</button>
                        <form method="POST" action="{{ route('social-media.plans.festivals.delete-year') }}" class="d-flex gap-1" onsubmit="return confirm('Delete all festivals/events for the selected year? This cannot be undone.');">
                            @csrf
                            <select name="year" class="form-select form-select-sm" required aria-label="Festival year to delete">
                                <option value="">Select year</option>
                                @foreach($festivalYears as $festivalYear)<option value="{{ $festivalYear }}">{{ $festivalYear }}</option>@endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-danger btn-sm text-nowrap"><i class="bx bx-trash me-1"></i>Delete Year</button>
                        </form>
                    </div>
                </div>
                <form method="POST" action="{{ route('social-media.plans.festivals.bulk-delete') }}" id="bulk-festival-delete-form">@csrf</form>
                <div class="table-responsive"><table class="table align-middle table-hover mb-0"><thead class="table-light"><tr><th style="width:40px"><input type="checkbox" class="form-check-input" id="selectAllFestivals" title="Select all"></th><th>Event</th><th>Date</th><th>Category</th><th>Region</th><th>Client</th><th>Repeats</th><th class="text-end">Action</th></tr></thead><tbody>
                    @forelse($festivals as $festival)
                        <tr><td><input type="checkbox" form="bulk-festival-delete-form" class="form-check-input festival-checkbox" name="festival_ids[]" value="{{ $festival->id }}"></td><td><strong>{{ $festival->name }}</strong><small class="d-block text-muted">{{ Str::limit($festival->description, 65) }}</small></td><td>{{ optional($festival->event_date)->format('d M Y') ?: '-' }}</td><td>{{ $festival->category ?: '-' }}</td><td>{{ $festival->country_region ?: '-' }}</td><td>{{ $festival->client->name ?? 'All clients' }}</td><td>{{ $festival->recurrence_type === 'yearly' ? 'Yearly' : 'One time' }}</td><td class="text-end"><form method="POST" action="{{ route('social-media.festivals.destroy', $festival) }}" onsubmit="return confirm('Delete this festival/event?');" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button></form></td></tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No festival events added yet.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div></div>
        @else
            @php
                $tabTitle = ['work' => 'My Work', 'review' => 'Internal Review', 'approvals' => 'Client Approvals', 'publishing' => 'Publishing Tracker'][$activeTab] ?? 'Content Items';
            @endphp
            <div class="card"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ $tabTitle }}</h5><small class="text-muted">{{ $tabPosts->count() }} content items</small></div>
                <div class="table-responsive"><table class="table align-middle table-hover mb-0"><thead class="table-light"><tr><th>Content</th><th>Client</th><th>Planned date</th><th>Platforms</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>
                    @forelse($tabPosts as $post)
                        <tr>
                            <td><strong>{{ $post->title }}</strong><small class="d-block text-muted">{{ $post->content_category ?: ucfirst($post->post_type) }}</small></td>
                            <td>{{ $post->client->name ?? '-' }}</td>
                            <td>{{ optional($post->planned_at)->format('d M Y') ?: optional($post->publishing_scheduled_at)->format('d M Y') ?: '-' }}</td>
                            <td>{{ $post->platforms->pluck('platform')->join(', ') ?: '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $post->status)) }}</span></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('social-media.posts.show', $post) }}"><i class="bx bx-show"></i> View</a>
                                @if(!$isClient)
                                    <form method="POST" action="{{ route('social-media.posts.destroy', $post) }}" class="d-inline" onsubmit="return confirm('Delete this post and all of its related versions, comments and publishing records?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Delete post">
                                            <i class="bx bx-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No content items in this section.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div></div>
        @endif
    @endif
</div></div>

{{-- Edit Plan Modal --}}
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editPlanForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlanModalLabel"><i class="bx bx-edit me-1"></i> Edit Content Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Client *</label>
                            <select required class="form-select" name="client_id" id="edit_client_id">
                                <option value="">Select client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Month *</label>
                            <select name="plan_month" id="edit_plan_month" class="form-select">
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Year *</label>
                            <input name="plan_year" id="edit_plan_year" type="number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Plan Title</label>
                            <input name="title" id="edit_title" class="form-control" placeholder="Plan title">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="pending_client_review">Pending Client Review</option>
                                <option value="client_approved">Client Approved</option>
                                <option value="changes_requested">Changes Requested</option>
                                <option value="not_required">Monthly Planning Not Required</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes / Strategy</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="3" placeholder="Outline, notes, target audience..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Content Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var plansTable = $('#monthly-plans-table');
    if (plansTable.length && $.fn.DataTable && !$.fn.DataTable.isDataTable(plansTable)) {
        plansTable.DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            order: [[2, 'desc']],
            columnDefs: [{ targets: 6, orderable: false, searchable: false }],
            language: {
                search: '',
                searchPlaceholder: 'Search plans...'
            }
        });
    }

    var editModalEl = document.getElementById('editPlanModal');
    var editModal = editModalEl && window.bootstrap ? new bootstrap.Modal(editModalEl) : null;
    var editForm = document.getElementById('editPlanForm');

    document.querySelectorAll('.edit-plan-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.id;
            editForm.action = "{{ url('social-media/plans') }}/" + id;
            document.getElementById('edit_client_id').value = this.dataset.clientId || '';
            document.getElementById('edit_plan_month').value = this.dataset.month || '1';
            document.getElementById('edit_plan_year').value = this.dataset.year || new Date().getFullYear();
            document.getElementById('edit_title').value = this.dataset.title || '';
            document.getElementById('edit_status').value = this.dataset.status || 'draft';
            document.getElementById('edit_notes').value = this.dataset.notes || '';
            if (editModal) {
                editModal.show();
            } else if (window.$) {
                $('#editPlanModal').modal('show');
            }
        });
    });

    var selectAllFestivals = document.getElementById('selectAllFestivals');
    var festivalCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.festival-checkbox'));
    var bulkFestivalDeleteButton = document.getElementById('bulkFestivalDeleteButton');
    function refreshFestivalSelection() {
        var selected = festivalCheckboxes.filter(function (checkbox) { return checkbox.checked; });
        if (bulkFestivalDeleteButton) bulkFestivalDeleteButton.disabled = selected.length === 0;
        if (selectAllFestivals) {
            selectAllFestivals.checked = festivalCheckboxes.length > 0 && selected.length === festivalCheckboxes.length;
            selectAllFestivals.indeterminate = selected.length > 0 && selected.length < festivalCheckboxes.length;
        }
    }
    if (selectAllFestivals) {
        selectAllFestivals.addEventListener('change', function () {
            festivalCheckboxes.forEach(function (checkbox) { checkbox.checked = selectAllFestivals.checked; });
            refreshFestivalSelection();
        });
    }
    festivalCheckboxes.forEach(function (checkbox) { checkbox.addEventListener('change', refreshFestivalSelection); });
    refreshFestivalSelection();
});
</script>
@endpush
