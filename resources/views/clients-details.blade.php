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
                                                <label class="form-label">Role</label>
                                                <select class="form-control" name="role">
                                                    <option value="">Select Role</option>
                                                    @foreach($roles as $role)
                                                    <option value="{{ $role->name }}" {{ $customer->role == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
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
                                    <h4 class="my-1 text-primary">{{ $customer->projects->count() }}</h4>
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
                                    <h4 class="my-1 text-success">
                                        @php
                                            $activeTasksCount = 0;
                                            foreach($customer->projects as $project) {
                                                $activeTasksCount += $project->tasks->where('status', '!=', 'Completed')->count();
                                            }
                                        @endphp
                                        {{ $activeTasksCount }}
                                    </h4>
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
                                    <h4 class="my-1 text-warning">
                                        @php
                                            $openIssuesCount = $customer->clientIssues
                                                ->whereIn('status', ['open', 'in_progress'])
                                                ->count();
                                        @endphp
                                        {{ $openIssuesCount }}
                                    </h4>
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
                                    <h4 class="my-1 text-info">₹150,000</h4>
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
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                            <th>Deadline</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->projects as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>
                                                @if($project->status == 'Completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($project->status == 'In Progress')
                                                    <span class="badge bg-warning">In Progress</span>
                                                @elseif($project->status == 'Planning')
                                                    <span class="badge bg-info">Planning</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $project->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                @if($project->priority == 'High')
                                                    <span class="badge bg-danger">High</span>
                                                @elseif($project->priority == 'Medium')
                                                    <span class="badge bg-warning">Medium</span>
                                                @elseif($project->priority == 'Low')
                                                    <span class="badge bg-success">Low</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $project->priority ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No projects found for this client.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
                                        @php
                                            $allTasks = collect();
                                            foreach($customer->projects as $project) {
                                                $allTasks = $allTasks->merge($project->tasks);
                                            }
                                        @endphp
                                        @forelse($allTasks as $task)
                                        <tr>
                                            <td>{{ $task->title }}</td>
                                            <td>{{ $task->project->project_name }}</td>
                                            <td>{{ $task->assignees ? implode(', ', $task->assignees) : 'Unassigned' }}</td>
                                            <td>
                                                @if($task->status == 'Completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($task->status == 'In Progress')
                                                    <span class="badge bg-warning">In Progress</span>
                                                @elseif($task->status == 'Pending')
                                                    <span class="badge bg-info">Pending</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $task->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $task->deadline ? $task->deadline->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No tasks found for this client.</td>
                                        </tr>
                                        @endforelse
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
                            {{-- <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#raiseIssueModal">Raise New Issue</button> --}}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Issue ID</th>
                                            <th>Project</th>
                                            <th>Issue</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th>Date Raised</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->clientIssues as $issue)
                                            @php
                                                $latestAssignment = $issue->teamAssignments
                                                    ->sortByDesc('created_at')
                                                    ->first();
                                                $assignedLabel = 'Unassigned';
                                                if ($latestAssignment && $latestAssignment->assignedStaff) {
                                                    $assignedLabel = $latestAssignment->assignedStaff->full_name;
                                                } elseif ($latestAssignment && $latestAssignment->team_name) {
                                                    $assignedLabel = $latestAssignment->team_name;
                                                }
                                            @endphp
                                            <tr>
                                                <td>#{{ $issue->id }}</td>
                                                <td>{{ $issue->project->project_name ?? 'N/A' }}</td>
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
                                                <td>{{ $assignedLabel }}</td>
                                                <td>{{ $issue->created_at ? $issue->created_at->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('client-issue.show', $issue->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No issues found for this client.</td>
                                            </tr>
                                        @endforelse
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
                            <form id="raiseIssueForm" action="{{ route('client-issue.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                        <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
                                            <option value="">Select Project</option>
                                            @foreach($customer->projects as $project)
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
                                </div>
                                <div class="mb-3">
                                    <label for="issue_description" class="form-label">Issue Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('issue_description') is-invalid @enderror" id="issue_description" name="issue_description" rows="4" required>{{ old('issue_description') }}</textarea>
                                    @error('issue_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="row">
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
                            <button type="button" class="btn btn-primary" id="submitIssueBtn">Submit Issue</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--end page wrapper -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var submitBtn = document.getElementById('submitIssueBtn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    var form = document.getElementById('raiseIssueForm');
                    var projectSelect = document.getElementById('project_id');
                    var descriptionTextarea = document.getElementById('issue_description');

                    projectSelect.classList.remove('is-invalid');
                    descriptionTextarea.classList.remove('is-invalid');

                    var isValid = true;
                    if (!projectSelect.value) {
                        projectSelect.classList.add('is-invalid');
                        isValid = false;
                    }
                    if (!descriptionTextarea.value.trim()) {
                        descriptionTextarea.classList.add('is-invalid');
                        isValid = false;
                    }

                    if (isValid) {
                        form.submit();
                    }
                });
            }

            @if($errors->any())
                var raiseIssueModal = new bootstrap.Modal(document.getElementById('raiseIssueModal'));
                raiseIssueModal.show();
            @endif
        });
    </script>
@endsection
