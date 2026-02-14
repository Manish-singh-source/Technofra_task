@extends('/layout/master')
@section('content')

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Client Issue Management</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary">Settings</button>
                    <button type="button"
                        class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                        data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                        <a class="dropdown-item cursor-pointer" id="delete-selected">Delete All</a>
                    </div>
                </div>
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
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <div class="ms-auto">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIssueModal">
                                    <i class="bx bx-plus"></i> Add New Project Issue
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th><input class="form-check-input" type="checkbox" id="select-all"></th>
                                        <th>ID</th>
                                        <th>Project Name</th>
                                        <th>Client Name</th>
                                        <th>Issue</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clientIssues as $issue)
                                    <tr>
                                        <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="{{ $issue->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 font-14">{{ $issue->id }}</h6>
                                            </div>
                                        </td>
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
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('client-issue.show', $issue->id) }}"><i class='bx bxs-show'></i></a>
                                                <form action="{{ route('client-issue.destroy', $issue->id) }}" method="POST" class="d-inline ms-3" onsubmit="return confirm('Are you sure you want to delete this issue?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="border: none; background: none; color: #f54242;">
                                                        <i class='bx bxs-trash'></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No client issues found.</td>
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
    // Select All functionality
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    });

    // Delete Selected functionality
    document.getElementById('delete-selected').addEventListener('click', function() {
        let selected = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });
        if (selected.length === 0) {
            alert('Please select at least one record.');
            return;
        }
        if (confirm('Are you sure you want to delete selected records?')) {
            // Create a form and submit
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('delete.selected.client-issue') }}';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="ids" value="${selected.join(',')}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });

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
