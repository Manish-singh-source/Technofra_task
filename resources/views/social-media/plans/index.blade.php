@extends('layout.master')

@push('styles')
<style>
.plan-badge-draft{background:#6c757d;color:#fff}
.plan-badge-pending_client_review{background:#fd7e14;color:#fff}
.plan-badge-client_approved{background:#198754;color:#fff}
.plan-badge-changes_requested{background:#dc3545;color:#fff}

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
<div class="page-wrapper"><div class="page-content">
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

    @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
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
            <small class="text-muted">Total: {{ $plans->total() }}</small>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Plan Title</th>
                        <th>Month & Year</th>
                        <th>Posts Count</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr>
                        <td><strong>{{ $plan->client->name ?? '-' }}</strong></td>
                        <td>{{ $plan->title ?: 'Content Plan' }}</td>
                        <td>{{ \Carbon\Carbon::create($plan->plan_year, $plan->plan_month, 1)->format('F Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $plan->posts_count }} posts</span></td>
                        <td>
                            @php
                                $statusBadge = match($plan->status) {
                                    'client_approved' => 'bg-success',
                                    'pending_client_review' => 'bg-warning text-dark',
                                    'changes_requested' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ', $plan->status ?? 'draft')) }}</span>
                        </td>
                        <td><small class="text-muted">{{ Str::limit($plan->notes ?: '-', 45) }}</small></td>
                        <td class="text-end">
                            @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
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
                            @else
                                <span class="text-muted small">-</span>
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
        <div class="mt-3">
            {{ $plans->links() }}
        </div>
    </div></div>
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
});
</script>
@endpush
