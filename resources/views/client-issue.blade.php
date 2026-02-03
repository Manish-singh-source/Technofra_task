@extends('/layout/master')
@section('content')

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Client Issue</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Client Issue Management</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Client Issues</h5>
                        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#addIssueModal">
                            <i class="bx bx-plus"></i> Add New Project Issue
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Project Name</th>
                                        <th>Client Name</th>
                                        <th>Issue</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clientIssues as $index => $issue)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $issue->project->project_name ?? 'N/A' }}</td>
                                        <td>{{ $issue->customer->client_name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($issue->issue_description, 50) }}</td>
                                        <td>
                                            @if($issue->priority == 'low')
                                                <span class="badge bg-secondary">Low</span>
                                            @elseif($issue->priority == 'medium')
                                                <span class="badge bg-primary">Medium</span>
                                            @elseif($issue->priority == 'high')
                                                <span class="badge bg-warning">High</span>
                                            @elseif($issue->priority == 'critical')
                                                <span class="badge bg-danger">Critical</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($issue->status == 'open')
                                                <span class="badge bg-danger">Open</span>
                                            @elseif($issue->status == 'in_progress')
                                                <span class="badge bg-warning">In Progress</span>
                                            @elseif($issue->status == 'resolved')
                                                <span class="badge bg-success">Resolved</span>
                                            @elseif($issue->status == 'closed')
                                                <span class="badge bg-info">Closed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('client-issue.show', $issue->id) }}" class="btn btn-sm btn-info text-white me-1">
                                                <i class="bx bx-eye"></i> View
                                            </a>
                                            <form action="{{ route('client-issue.destroy', $issue->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this issue?')">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No client issues found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add New Project Issue Modal -->
<div class="modal fade" id="addIssueModal" tabindex="-1" aria-labelledby="addIssueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIssueModalLabel">Add New Project Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIssueForm" action="{{ route('client-issue.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                            <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customer_id" class="form-label">Client Name <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">Select Client</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->client_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="issue_description" class="form-label">Issue Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('issue_description') is-invalid @enderror" id="issue_description" name="issue_description" rows="3" required>{{ old('issue_description') }}</textarea>
                        @error('issue_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ old('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveIssueBtn">Save Issue</button>
            </div>
        </div>
    </div>
</div>

<style>
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Save button click handler
    document.getElementById('saveIssueBtn').addEventListener('click', function() {
        var form = document.getElementById('addIssueForm');
        var projectSelect = document.getElementById('project_id');
        var customerSelect = document.getElementById('customer_id');
        var descriptionTextarea = document.getElementById('issue_description');
        
        // Reset validation
        projectSelect.classList.remove('is-invalid');
        customerSelect.classList.remove('is-invalid');
        descriptionTextarea.classList.remove('is-invalid');
        
        var isValid = true;
        
        // Validate project
        if (!projectSelect.value) {
            projectSelect.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate customer
        if (!customerSelect.value) {
            customerSelect.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate description
        if (!descriptionTextarea.value.trim()) {
            descriptionTextarea.classList.add('is-invalid');
            isValid = false;
        }
        
        if (isValid) {
            form.submit();
        }
    });
    
    // Show modal if there are validation errors
    @if($errors->any())
        var addIssueModal = new bootstrap.Modal(document.getElementById('addIssueModal'));
        addIssueModal.show();
    @endif
});
</script>

@endsection
