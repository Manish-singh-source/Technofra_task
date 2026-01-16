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
                        <div class="row">
                            <!-- Project Issue Card 1 -->
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card project-card">
                                    <a href="{{ route('client-issue-details') }}">
                                    <div class="card-body text-center">
                                        <div class="project-logo mb-3">
                                            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="Project Logo" class="img-fluid" style="max-width: 80px;">
                                        </div>
                                        <h5 class="card-title">E-commerce Platform</h5>
                                        <p class="card-text text-muted">Online shopping website with payment integration</p>
                                        <div class="project-status">
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Project Issue Card 2 -->
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card project-card">
                                    <div class="card-body text-center">
                                        <div class="project-logo mb-3">
                                            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="Project Logo" class="img-fluid" style="max-width: 80px;">
                                        </div>
                                        <h5 class="card-title">CRM System</h5>
                                        <p class="card-text text-muted">Customer relationship management solution</p>
                                        <div class="project-status">
                                            <span class="badge bg-warning">In Progress</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Issue Card 3 -->
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card project-card">
                                    <div class="card-body text-center">
                                        <div class="project-logo mb-3">
                                            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="Project Logo" class="img-fluid" style="max-width: 80px;">
                                        </div>
                                        <h5 class="card-title">Mobile App</h5>
                                        <p class="card-text text-muted">Cross-platform mobile application</p>
                                        <div class="project-status">
                                            <span class="badge bg-info">Planning</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                <form id="addIssueForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="projectName" class="form-label">Project</label>
                            <select class="form-select" id="projectName" required>
                                <option value="">Select Project</option>
                                <option value="ecommerce">E-commerce Platform</option>
                                <option value="crm">CRM System</option>
                                <option value="mobile">Mobile App</option>
                                <option value="web">Web Application</option>
                                <option value="api">API Development</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="clientName" class="form-label">Client Name</label>
                            <input type="text" disabled class="form-control" value="Manish" id="clientName" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="issueDescription" class="form-label">Issue Description</label>
                        <textarea class="form-control" id="issueDescription" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" required>
                                <option value="">Select Status</option>
                                <option value="open">Open</option>
                                <option value="in-progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
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
.project-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e9ecef;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.project-logo {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.project-status {
    margin-top: 15px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

@endsection