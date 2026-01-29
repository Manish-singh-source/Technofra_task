@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Clients</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('clients') }}"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $customer->client_name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('clients') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-arrow-back"></i> Back</a>
                    <a href="{{ route('client-issue') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-plus"></i> Raise Issue</a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Edit Client</button>
                        <button type="button"
                            class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Archive</a>
                            <a class="dropdown-item" href="javascript:;">Duplicate</a>
                            <a class="dropdown-item" href="javascript:;">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Client Profile Card -->
                <div class="main-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        {{-- <img src="https://placehold.co/110x110" alt="Client Logo" class="rounded-circle p-1 bg-primary" width="110"> --}}
                                        <div class="mt-3">
                                            <h4>{{ $customer->client_name }}</h4>
                                            <p class="text-secondary mb-1">{{ $customer->industry }} Company</p>
                                            <p class="text-muted font-size-sm">{{ $customer->status }} Client</p>
                                            
                                        </div>
                                    </div>
                                    <hr class="my-4" />
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class="bx bx-envelope me-2"></i>Email</h6>
                                            <span class="text-secondary">{{ $customer->email }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class="bx bx-phone me-2"></i>Phone</h6>
                                            <span class="text-secondary">{{ $customer->phone ?? 'N/A' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class="bx bx-globe me-2"></i>Website</h6>
                                            <span class="text-secondary">{{ $customer->website ?? 'N/A' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class="bx bx-user me-2"></i>Manager</h6>
                                            <span class="text-secondary">{{ $customer->assigned_manager_id ? 'Assigned' : 'Not Assigned' }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <form action="{{ route('clients.update', $customer->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Client Name</label>
                                                <input type="text" class="form-control" name="client_name" value="{{ $customer->client_name }}" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Contact Person</label>
                                                <input type="text" class="form-control" name="contact_person" value="{{ $customer->contact_person }}" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" value="{{ $customer->email }}" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Phone</label>
                                                <input type="text" class="form-control" name="phone" value="{{ $customer->phone }}">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Website</label>
                                                <input type="url" class="form-control" name="website" value="{{ $customer->website }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Client Type</label>
                                                <select class="form-control" name="client_type" required>
                                                    <option value="Individual" {{ $customer->client_type == 'Individual' ? 'selected' : '' }}>Individual</option>
                                                    <option value="Company" {{ $customer->client_type == 'Company' ? 'selected' : '' }}>Company</option>
                                                    <option value="Organization" {{ $customer->client_type == 'Organization' ? 'selected' : '' }}>Organization</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Industry</label>
                                                <input type="text" class="form-control" name="industry" value="{{ $customer->industry }}" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Status</label>
                                                <select class="form-control" name="status" required>
                                                    <option value="Active" {{ $customer->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                    <option value="Inactive" {{ $customer->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="Suspended" {{ $customer->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Priority Level</label>
                                                <select class="form-control" name="priority_level">
                                                    <option value="">Select Priority</option>
                                                    <option value="Low" {{ $customer->priority_level == 'Low' ? 'selected' : '' }}>Low</option>
                                                    <option value="Medium" {{ $customer->priority_level == 'Medium' ? 'selected' : '' }}>Medium</option>
                                                    <option value="High" {{ $customer->priority_level == 'High' ? 'selected' : '' }}>High</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Address Line 1</label>
                                                <input type="text" class="form-control" name="address_line1" value="{{ $customer->address_line1 }}" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Address Line 2</label>
                                                <input type="text" class="form-control" name="address_line2" value="{{ $customer->address_line2 }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" name="city" value="{{ $customer->city }}" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">State</label>
                                                <input type="text" class="form-control" name="state" value="{{ $customer->state }}" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" name="postal_code" value="{{ $customer->postal_code }}" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Country</label>
                                                <input type="text" class="form-control" name="country" value="{{ $customer->country }}" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Default Due Days</label>
                                                <input type="number" class="form-control" name="default_due_days" value="{{ $customer->default_due_days }}">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Billing Type</label>
                                                <select class="form-control" name="billing_type">
                                                    <option value="">Select Billing Type</option>
                                                    <option value="Hourly" {{ $customer->billing_type == 'Hourly' ? 'selected' : '' }}>Hourly</option>
                                                    <option value="Fixed" {{ $customer->billing_type == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                                    <option value="Retainer" {{ $customer->billing_type == 'Retainer' ? 'selected' : '' }}>Retainer</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <!-- Empty for balance -->
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-12">
                                                <button type="submit" class="btn btn-primary">Update Client</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Statistic Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Projects</p>
                                    <h4 class="my-1 text-primary">5</h4>
                                    <p class="mb-0 font-13">+2 from last month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-briefcase'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Active Tasks</p>
                                    <h4 class="my-1 text-success">12</h4>
                                    <p class="mb-0 font-13">+3 from last week</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bx-task'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Open Issues</p>
                                    <h4 class="my-1 text-warning">3</h4>
                                    <p class="mb-0 font-13">-1 from last week</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto"><i class='bx bx-error'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Revenue</p>
                                    <h4 class="my-1 text-info">$150,000</h4>
                                    <p class="mb-0 font-13">+15% from last month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto"><i class='bx bx-dollar'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Projects</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">Office Management System</h6>
                                            <p class="card-text">Complete office automation project.</p>
                                            <span class="badge bg-success">Completed</span>
                                            <div class="mt-2">
                                                <small class="text-muted">Due: Dec 31, 2023</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">E-commerce Platform</h6>
                                            <p class="card-text">Online shopping website development.</p>
                                            <span class="badge bg-warning">In Progress</span>
                                            <div class="mt-2">
                                                <small class="text-muted">Due: Mar 15, 2024</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">Mobile App</h6>
                                            <p class="card-text">iOS and Android app development.</p>
                                            <span class="badge bg-info">Planning</span>
                                            <div class="mt-2">
                                                <small class="text-muted">Due: Jun 30, 2024</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Tasks</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Project</th>
                                            <th>Assignee</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Setup Database</td>
                                            <td>Office Management</td>
                                            <td>John Doe</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td>2023-10-15</td>
                                        </tr>
                                        <tr>
                                            <td>Design UI</td>
                                            <td>E-commerce Platform</td>
                                            <td>Jane Smith</td>
                                            <td><span class="badge bg-warning">In Progress</span></td>
                                            <td>2023-11-20</td>
                                        </tr>
                                        <tr>
                                            <td>Implement Features</td>
                                            <td>Mobile App</td>
                                            <td>Bob Johnson</td>
                                            <td><span class="badge bg-info">Pending</span></td>
                                            <td>2023-12-01</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raise Issue Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Issues</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#raiseIssueModal">Raise New Issue</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Issue ID</th>
                                            <th>Title</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th>Date Raised</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ISS-001</td>
                                            <td>Database Connection Issue</td>
                                            <td><span class="badge bg-danger">High</span></td>
                                            <td><span class="badge bg-warning">In Progress</span></td>
                                            <td>John Doe</td>
                                            <td>2023-10-15</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ISS-002</td>
                                            <td>UI Responsiveness Problem</td>
                                            <td><span class="badge bg-warning">Medium</span></td>
                                            <td><span class="badge bg-success">Resolved</span></td>
                                            <td>Jane Smith</td>
                                            <td>2023-09-20</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ISS-003</td>
                                            <td>Payment Gateway Error</td>
                                            <td><span class="badge bg-danger">High</span></td>
                                            <td><span class="badge bg-info">Pending</span></td>
                                            <td>Bob Johnson</td>
                                            <td>2023-11-01</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raise Issue Modal -->
            <div class="modal fade" id="raiseIssueModal" tabindex="-1" aria-labelledby="raiseIssueModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="raiseIssueModalLabel">Raise New Issue</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="issue_title" class="form-label">Issue Title</label>
                                        <input type="text" class="form-control" id="issue_title" placeholder="Enter issue title">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="issue_priority" class="form-label">Priority</label>
                                        <select class="form-select" id="issue_priority">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <label for="issue_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="issue_description" rows="4" placeholder="Describe the issue"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary">Submit Issue</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--end page wrapper -->
@endsection
