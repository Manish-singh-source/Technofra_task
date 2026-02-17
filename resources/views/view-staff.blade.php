@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">User Profile</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">User Profilep</li>
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
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Action</a>
                            <a class="dropdown-item" href="javascript:;">Another action</a>
                            <a class="dropdown-item" href="javascript:;">Something else here</a>
                            <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated
                                link</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card radius-10 border-start border-0 border-4 border-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Logged Time</p>
                                    <h4 class="my-1 text-primary">29:23</h4>
                                    <p class="mb-0 font-13">All time tracking</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                    <i class='bx bx-time-five'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card radius-10 border-start border-0 border-4 border-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Last Month Logged Time</p>
                                    <h4 class="my-1 text-info">00:00</h4>
                                    <p class="mb-0 font-13">Previous month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                    <i class='bx bx-calendar'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card radius-10 border-start border-0 border-4 border-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">This Month Logged Time</p>
                                    <h4 class="my-1 text-success">29:23</h4>
                                    <p class="mb-0 font-13">Current month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class='bx bx-trending-up'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card radius-10 border-start border-0 border-4 border-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">This Week Logged Time</p>
                                    <h4 class="my-1 text-warning">00:00</h4>
                                    <p class="mb-0 font-13">Current week</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                    <i class='bx bx-week'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="">
                <div class="main-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <img src="{{ asset('uploads/staff/' . $staff->profile_image) }}" alt="Admin"
                                            class="rounded-circle p-1 bg-primary" width="110">
                                        <div class="mt-3">
                                            <h4>{{ $staff->first_name . ' ' . $staff->last_name }}</h4>
                                            <p class="text-secondary mb-1">
                                                {{ ucwords(str_replace('_', ' ', $staff->role)) }}</p>
                                            <p class="text-muted font-size-sm">{{ $staff->email }}</p>

                                        </div>
                                    </div>
                                    <hr class="my-4" />
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-user me-2'></i>Role</h6>
                                            <span class="text-secondary">{{ ucwords(str_replace('_', ' ', $staff->role)) }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-group me-2'></i>Team</h6>
                                            <span class="text-secondary">{{ $staff->team ?? 'N/A' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-check-circle me-2'></i>Status</h6>
                                            <span class="text-secondary">
                                                @if($staff->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-phone me-2'></i>Phone</h6>
                                            <span class="text-secondary">{{ $staff->phone }}</span>
                                        </li>
                                        @php
                                            $staffDepartments = is_array($staff->departments) ? $staff->departments : json_decode($staff->departments, true);
                                        @endphp
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-buildings me-2'></i>Departments</h6>
                                            <span class="text-secondary">
                                                @if(!empty($staffDepartments))
                                                    {{ implode(', ', $staffDepartments) }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-8">
                            <form method="POST" action="{{ route('staff.update', $staff->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Full Name</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="text" name="first_name" class="form-control"
                                                    value="{{ $staff->first_name }}" placeholder="First Name" required />
                                                <input type="text" name="last_name" class="form-control mt-2"
                                                    value="{{ $staff->last_name }}" placeholder="Last Name" required />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Email</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="email" name="email" class="form-control"
                                                    value="{{ $staff->email }}" required />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Phone</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="text" name="phone" class="form-control"
                                                    value="{{ $staff->phone }}" required />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Role</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <select name="role" class="form-control" required>
                                                    <option value="">Select Role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            {{ $staff->role == $role->name ? 'selected' : '' }}>
                                                            {{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Status</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <select name="status" class="form-control" required>
                                                    <option value="active"
                                                        {{ $staff->status == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive"
                                                        {{ $staff->status == 'inactive' ? 'selected' : '' }}>Inactive
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Team</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <select name="team" class="form-control">
                                                    <option value="">Select Team</option>
                                                    <option value="Web Team" {{ $staff->team == 'Web Team' ? 'selected' : '' }}>Web Team</option>
                                                    <option value="Graphic Team" {{ $staff->team == 'Graphic Team' ? 'selected' : '' }}>Graphic Team</option>
                                                    <option value="Social Media Team" {{ $staff->team == 'Social Media Team' ? 'selected' : '' }}>Social Media Team</option>
                                                    <option value="Accounts Team" {{ $staff->team == 'Accounts Team' ? 'selected' : '' }}>Accounts Team</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Departments</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                @php
                                                    $staffDepartments = is_array($staff->departments) ? $staff->departments : json_decode($staff->departments, true);
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="departments[]" value="Admin" {{ in_array('Admin', $staffDepartments ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Admin</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="departments[]" value="Web Developers" {{ in_array('Web Developers', $staffDepartments ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Web Developers</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="departments[]" value="Design and Graphics" {{ in_array('Design and Graphics', $staffDepartments ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Design and Graphics</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="departments[]" value="Seo Developer" {{ in_array('Seo Developer', $staffDepartments ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Seo Developer</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-3"></div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="submit" class="btn btn-primary px-4"
                                                    value="Save Changes" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>



                        </div>
                    </div>


                </div>
            </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="d-flex align-items-center mb-3">Recent Projects
                                    <div class="ms-auto">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class='bx bx-filter-alt'></i> Filter
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item filter-option" href="javascript:;" data-filter="weekly">Weekly</a>
                                                <a class="dropdown-item filter-option" href="javascript:;" data-filter="monthly">Monthly</a>
                                                <a class="dropdown-item filter-option" href="javascript:;" data-filter="yearly">Yearly</a>
                                            </div>
                                        </div>
                                    </div>
                                </h5>
                                <div class="table-responsive">
                                    <table id="table" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project Name</th>
                                                <th>Start Date</th>
                                                <th>Deadline</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($projects as $project)
                                                <tr>
                                                    <td>{{ $project->project_name }}</td>
                                                    <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}
                                                    </td>
                                                    <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        @if ($project->status == 'completed')
                                                            <span class="badge bg-success">Completed</span>
                                                        @elseif($project->status == 'in_progress')
                                                            <span class="badge bg-warning text-dark">In
                                                                Progress</span>
                                                        @elseif($project->status == 'pending')
                                                            <span class="badge bg-danger">Pending</span>
                                                        @else
                                                            <span
                                                                class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No projects found
                                                        for this staff member.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Recent Tasks Card -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="d-flex align-items-center mb-3">Recent Tasks
                                    <div class="ms-auto">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class='bx bx-filter-alt'></i> Filter
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item filter-option" href="javascript:;" data-filter="weekly">Weekly</a>
                                                <a class="dropdown-item filter-option" href="javascript:;" data-filter="monthly">Monthly</a>
                                                <a class="dropdown-item filter-option" href="javascript:;" data-filter="yearly">Yearly</a>
                                            </div>
                                        </div>
                                    </div>
                                </h5>
                                <div class="table-responsive">
                                    <table id="tasks-table" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Task Name</th>
                                                <th>Project</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($tasks ?? [] as $task)
                                                <tr>
                                                    <td>{{ $task->title ?? 'N/A' }}</td>
                                                    <td>{{ $task->project->project_name ?? 'N/A' }}</td>
                                                    <td>{{ $task->deadline ? $task->deadline->format('Y-m-d') : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        @if ($task->status == 'completed')
                                                            <span class="badge bg-success">Completed</span>
                                                        @elseif($task->status == 'in_progress')
                                                            <span class="badge bg-warning text-dark">In
                                                                Progress</span>
                                                        @elseif($task->status == 'pending')
                                                            <span class="badge bg-danger">Pending</span>
                                                        @else
                                                            <span
                                                                class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($task->priority == 'high')
                                                            <span class="badge bg-danger">High</span>
                                                        @elseif($task->priority == 'medium')
                                                            <span class="badge bg-warning text-dark">Medium</span>
                                                        @elseif($task->priority == 'low')
                                                            <span class="badge bg-success">Low</span>
                                                        @else
                                                            <span
                                                                class="badge bg-secondary">{{ ucfirst($task->priority ?? 'N/A') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No tasks found for
                                                        this staff member.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>





            <!-- Performance Reports Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Performance Reports</h4>
                </div>
            </div>

            <!-- Performance Metrics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Productivity Score</p>
                                    <h4 class="my-1 text-primary">85%</h4>
                                    <p class="mb-0 font-13">+5.2% from last month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                    <i class='bx bx-trending-up'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Tasks Completed</p>
                                    <h4 class="my-1 text-success">47</h4>
                                    <p class="mb-0 font-13">+12.5% from last month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class='bx bx-check-circle'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Average Response Time</p>
                                    <h4 class="my-1 text-warning">2.3h</h4>
                                    <p class="mb-0 font-13">-8.1% improvement</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                    <i class='bx bx-time-five'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-4 border-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Code Quality Score</p>
                                    <h4 class="my-1 text-info">92%</h4>
                                    <p class="mb-0 font-13">+3.7% from last month</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                    <i class='bx bx-star'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card radius-10 h-100">
                        <div class="card-header">
                            <h5>Monthly Performance Trend</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container-1">
                                <canvas id="performanceChart" width="400" height="250"></canvas>
                            </div>
                            <div class="mt-3">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <small>Jan</small>
                                        <h6 class="text-primary">82%</h6>
                                    </div>
                                    <div class="col-3">
                                        <small>Feb</small>
                                        <h6 class="text-primary">85%</h6>
                                    </div>
                                    <div class="col-3">
                                        <small>Mar</small>
                                        <h6 class="text-primary">88%</h6>
                                    </div>
                                    <div class="col-3">
                                        <small>Apr</small>
                                        <h6 class="text-primary">92%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card radius-10 h-100">
                        <div class="card-header">
                            <h5>Task Completion Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container-1">
                                <canvas id="taskBreakdownChart" width="400" height="250"></canvas>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-around">
                                    <div class="text-center">
                                        <div class="badge bg-success p-2 mb-1">High Priority</div>
                                        <h6 class="text-success">65%</h6>
                                    </div>
                                    <div class="text-center">
                                        <div class="badge bg-warning p-2 mb-1">Medium Priority</div>
                                        <h6 class="text-warning">25%</h6>
                                    </div>
                                    <div class="text-center">
                                        <div class="badge bg-danger p-2 mb-1">Low Priority</div>
                                        <h6 class="text-danger">10%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © 2023. All right reserved.</p>
    </footer>
    </div>
    <!--end wrapper-->

    @push('scripts')
        <script>
            $(function() {
                "use strict";

                // Monthly Performance Trend Chart
                if ($('#performanceChart').length) {
                    var ctx = document.getElementById('performanceChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            datasets: [{
                                label: 'Performance',
                                data: [82, 85, 88, 92, 89, 95],
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                }

                // Task Completion Breakdown Chart
                if ($('#taskBreakdownChart').length) {
                    var ctx2 = document.getElementById('taskBreakdownChart').getContext('2d');
                    new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: ['High Priority', 'Medium Priority', 'Low Priority'],
                            datasets: [{
                                data: [65, 25, 10],
                                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }

                // Initialize DataTable for Projects
                if ($('#table').length) {
                    $('#table').DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                }

                // Initialize DataTable for Tasks
                if ($('#tasks-table').length) {
                    $('#tasks-table').DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                }

                // Filter functionality
                $('.filter-option').on('click', function() {
                    var filterType = $(this).data('filter');
                    console.log('Filter applied: ' + filterType);
                    // Add your filter logic here
                    // You can filter the table data based on the selected filter type
                });
            });
        </script>
    @endpush
