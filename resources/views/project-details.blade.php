@extends('layout.master')
@section('content')

<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="{{ route('project') }}"><i class="bx bx-home-alt"></i></a></li>
						<li class="breadcrumb-item"><a href="{{ route('project') }}">Projects</a></li>
						<li class="breadcrumb-item active" aria-current="page">{{ $project->project_name }}</li>
					</ol>
				</nav>
			</div>
			<div class="ms-auto">
				<a href="{{ route('project') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-arrow-back"></i> Back</a>
				@can('view_raise_issue')
				<a href="{{ route('client-issue') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-plus"></i> Raise Issue</a>
				@endcan
				@can('edit_projects')
				<div class="btn-group">
					<a href="{{ route('edit-project', $project->id) }}" class="btn btn-primary">Edit Project</a>
					<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span></button>
					<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
						<a class="dropdown-item" href="javascript:;">Archive</a>
						<a class="dropdown-item" href="javascript:;">Duplicate</a>
						<a class="dropdown-item" href="javascript:;">Delete</a>
					</div>
				</div>
				@endcan
			</div>
		</div>
		<!--end breadcrumb-->

		<!-- Project Header -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<h4 class="card-title mb-1">{{ $project->project_name }}</h4>
								<span class="badge @if($project->priority == 'high') bg-danger @elseif($project->priority == 'medium') bg-warning @else bg-success @endif">{{ ucfirst($project->priority) }} Priority</span>
								<p class="text-muted mt-2 font-13">Project ID: {{ $project->id }} | Status:
									@if($project->status == 'not_started')
										<span class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
											<i class='bx bxs-circle me-1'></i>Not Started
										</span>
									@elseif($project->status == 'in_progress')
										<span class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
											<i class='bx bxs-circle me-1'></i>In Progress
										</span>
									@elseif($project->status == 'on_hold')
										<span class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
											<i class='bx bxs-circle me-1'></i>On Hold
										</span>
									@elseif($project->status == 'completed')
										<span class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
											<i class='bx bxs-circle me-1'></i>Finished
										</span>
									@elseif($project->status == 'cancelled')
										<span class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
											<i class='bx bxs-circle me-1'></i>Cancelled
										</span>
									@else
										<span class="badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3">
											<i class='bx bxs-circle me-1'></i>{{ ucfirst(str_replace('_', ' ', $project->status)) }}
										</span>
									@endif
								</p>
							</div>
							<div class="d-flex align-items-center">
								<div class="me-3">
									<small class="text-muted">Assignees</small><br>
									<div class="avatar-group">
										@if($project->members)
											@foreach(array_slice($project->members, 0, 3) as $memberId)
												@if(isset($staff[$memberId]))
													<img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/32x32' }}" class="rounded-circle me-1" alt="Member" width="32" height="32" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
												@endif
											@endforeach
											@if(count($project->members) > 3)
												<span>+{{ count($project->members) - 3 }}</span>
											@endif
										@endif
									</div>
								</div>
								<div class="me-3">
									<small class="text-muted">Start Date</small><br>
									<span>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</span>
								</div>
								<div>
									<small class="text-muted">End Date</small><br>
									<span>{{ $project->deadline ? $project->deadline->format('M d, Y') : 'N/A' }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Project Description -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card radius-10">
					<div class="card-header">
						<h5 class="mb-0">Project Description</h5>
					</div>
					<div class="card-body">
						<p class="mb-3">{!! $project->description ?? 'No description available.' !!}</p>
						<div class="row">
							<div class="col-md-6">
								@if($project->tags)
									<h6>Tags:</h6>
									<div class="d-flex flex-wrap gap-2 mb-3">
										@foreach($project->tags as $tag)
											<span class="badge bg-primary">{{ $tag }}</span>
										@endforeach
									</div>
								@endif
							</div>
							<div class="col-md-6">
								@if($project->technologies)
									<h6>Technologies:</h6>
									<div class="d-flex flex-wrap gap-2">
										@foreach($project->technologies as $tech)
											<span class="badge bg-info">{{ $tech }}</span>
										@endforeach
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Client Information -->
		<div class="row mb-4">
			<div class="col-md-6">
				<div class="card radius-10 h-100">
					<div class="card-header">
						<h5 class="mb-0">Client Information</h5>
					</div>
					<div class="card-body">
						<div class="d-flex align-items-center mb-3">
							<img src="https://placehold.co/60x60" alt="Client Logo" class="rounded-circle me-3">
							<div>
								<h6 class="mb-0">{{ $project->customer->client_name ?? 'N/A' }}</h6>
								<small class="text-muted font-13">{{ $project->customer->company ?? 'N/A' }}</small>
							</div>
						</div>
						<div class="row">
							<div class="col-6">
								<small class="text-muted">Contact Person</small>
								<p class="mb-1 font-13">{{ $project->customer->contact_person ?? 'N/A' }}</p>
							</div>
							<div class="col-6">
								<small class="text-muted">Email</small>
								<p class="mb-1 font-13">{{ $project->customer->email ?? 'N/A' }}</p>
							</div>
							<div class="col-6">
								<small class="text-muted">Phone</small>
								<p class="mb-1 font-13">{{ $project->customer->phone ?? 'N/A' }}</p>
							</div>
							<div class="col-6">
								<small class="text-muted">Address</small>
								<p class="mb-1 font-13">{{ $project->customer->address ?? 'N/A' }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card radius-10 h-100">
					<div class="card-header">
						<h5 class="mb-0">Project Progress</h5>
					</div>
					<div class="card-body">
						<div class="mb-3">
							<div class="d-flex justify-content-between mb-1">
								<span class="font-13">Overall Progress</span>
								<span class="font-13">75%</span>
							</div>
							<div class="progress" style="height: 8px;">
								<div class="progress-bar bg-success" style="width: 75%"></div>
							</div>
						</div>
						<div class="row text-center">
							<div class="col-4">
								<div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white mx-auto mb-2" style="width: 40px; height: 40px;">
									<i class='bx bx-check'></i>
								</div>
								<h4 class="text-success mb-0">15</h4>
								<small class="text-muted font-13">Tasks Completed</small>
							</div>
							<div class="col-4">
								<div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white mx-auto mb-2" style="width: 40px; height: 40px;">
									<i class='bx bx-loader-alt'></i>
								</div>
								<h4 class="text-warning mb-0">5</h4>
								<small class="text-muted font-13">Tasks In Progress</small>
							</div>
							<div class="col-4">
								<div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white mx-auto mb-2" style="width: 40px; height: 40px;">
									<i class='bx bx-time-five'></i>
								</div>
								<h4 class="text-danger mb-0">2</h4>
								<small class="text-muted font-13">Tasks Overdue</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Action Buttons -->
		
		<!-- Statistic Cards -->
		<div class="row mb-4">
			<div class="col-md-3 col-sm-6">
				<div class="card radius-10 border-start border-0 border-4 border-primary">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Time Spent</p>
								<h4 class="my-1 text-primary">{{ number_format($projectElapsedHours ?? 0, 1) }}h</h4>
								<p class="mb-0 font-13">Working hours (Mon-Sat, 9:00-18:00)</p>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-time-five'></i></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6">
				<div class="card radius-10 border-start border-0 border-4 border-success">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Income</p>
								<h4 class="my-1 text-success">₹50,000</h4>
								<p class="mb-0 font-13">+12.5% from last month</p>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bx-dollar'></i></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6">
				<div class="card radius-10 border-start border-0 border-4 border-warning">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Labor Cost</p>
								<h4 class="my-1 text-warning">₹30,000</h4>
								<p class="mb-0 font-13">+3.1% from last month</p>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto"><i class='bx bx-money'></i></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6">
				<div class="card radius-10 border-start border-0 border-4 border-info">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Utilization</p>
								<h4 class="my-1 text-info">{{ number_format($projectUtilization ?? 0, 1) }}%</h4>
								<p class="mb-0 font-13">Against full deadline working window</p>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto"><i class='bx bx-bar-chart'></i></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Navigation Tabs -->
		<ul class="nav nav-tabs mb-4" id="projectTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button" role="tab" aria-controls="employees" aria-selected="true">Employees</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab" aria-controls="tasks" aria-selected="false">Tasks</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">Files</button>
			</li>
			
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usage" type="button" role="tab" aria-controls="usage" aria-selected="false">Usage</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="milestones-tab" data-bs-toggle="tab" data-bs-target="#milestones" type="button" role="tab" aria-controls="milestones" aria-selected="false">Milestones</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="issues-tab" data-bs-toggle="tab" data-bs-target="#issues" type="button" role="tab" aria-controls="issues" aria-selected="false">Issues</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false">Comments</button>
			</li>
			
		</ul>

		<div class="tab-content" id="projectTabsContent">
			<div class="tab-pane fade show active" id="employees" role="tabpanel" aria-labelledby="employees-tab">
				<!-- Employees Table -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Assigned Employees</h5>
							<input type="text" class="form-control w-25 radius-30" placeholder="Search employees..." id="searchInput">
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0">
								<thead class="table-light">
									<tr>
										<th>Name</th>
										<th>Team</th>
										<th>Start Date</th>
										<th>Deadline</th>
										<th>Total Time (H)</th>
										<th>Utilization</th>
									</tr>
								</thead>
								<tbody>
									@if($project->members)
										@foreach($project->members as $memberId)
											@if(isset($staff[$memberId]))
												<tr>
													<td>
														<div class="d-flex align-items-center">
															<img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/40x40' }}" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
															<span>{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}</span>
														</div>
													</td>
													<td>{{ $staff[$memberId]->team->team_name ?? 'N/A' }}</td>
													<td>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</td>
													<td>{{ $project->deadline ? $project->deadline->format('M d, Y') : 'N/A' }}</td>
													<td>{{ number_format($memberMetrics[$memberId]['total_hours'] ?? 0, 1) }}h</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="progress me-2" style="width: 100px;">
																<div class="progress-bar bg-{{ ($memberMetrics[$memberId]['utilization'] ?? 0) >= 80 ? 'success' : (($memberMetrics[$memberId]['utilization'] ?? 0) >= 50 ? 'warning' : 'danger') }}" style="width: {{ $memberMetrics[$memberId]['utilization'] ?? 0 }}%"></div>
															</div>
															<span>{{ number_format($memberMetrics[$memberId]['utilization'] ?? 0, 1) }}%</span>
														</div>
													</td>
												</tr>
											@endif
										@endforeach
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
				<!-- Tasks Table -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Project Tasks</h5>
							@can('create_tasks')
							<a href="{{ route('add-task', ['project_id' => $project->id]) }}" class="btn btn-primary radius-30 btn-sm"><i class='bx bx-plus'></i> Create Task</a>
							@endcan
						</div>
					</div>
					<div class="card-body">
						@if($tasks && $tasks->count() > 0)
						<div class="table-responsive">
							<table class="table mb-0">
								<thead class="table-light">
									<tr>
										<th>Task ID</th>
										<th>Task</th>
										<th>Created On</th>
										<th>Priority</th>
										<th>Status</th>
										<th>Assignee</th>
									</tr>
								</thead>
								<tbody>
									@foreach($tasks as $task)
									<tr>
										<td>#T{{ $task->id }}</td>
										<td>
											<a href="{{ route('task-details', $task->id) }}" class="text-decoration-none">
												{{ $task->title }}
											</a>
										</td>
										<td>{{ $task->created_at->format('M d, Y') }}</td>
										<td>
											<span class="badge @if($task->priority == 'high') bg-danger @elseif($task->priority == 'medium') bg-warning @else bg-success @endif">
												{{ ucfirst($task->priority) }}
											</span>
										</td>
										<td>
											<span class="badge @if($task->status == 'completed') bg-success @elseif($task->status == 'in_progress') bg-primary @elseif($task->status == 'pending') bg-warning @else bg-secondary @endif">
												{{ ucfirst(str_replace('_', ' ', $task->status)) }}
											</span>
										</td>
										<td>
											@if($task->assignees && is_array($task->assignees) && count($task->assignees) > 0)
												@php $firstAssigneeId = $task->assignees[0]; @endphp
												@if(isset($staff[$firstAssigneeId]))
												<div class="d-flex align-items-center">
													<img src="{{ $staff[$firstAssigneeId]->profile_image ? asset('uploads/staff/' . $staff[$firstAssigneeId]->profile_image) : 'https://placehold.co/32x32' }}" class="rounded-circle me-2" alt="Assignee" width="32" height="32">
													<span>{{ $staff[$firstAssigneeId]->first_name }} {{ $staff[$firstAssigneeId]->last_name }}</span>
												</div>
												@if(count($task->assignees) > 1)
													<span class="badge bg-light text-dark ms-1">+{{ count($task->assignees) - 1 }} more</span>
												@endif
												@else
													<span class="text-muted">Unassigned</span>
												@endif
											@else
												<span class="text-muted">Unassigned</span>
											@endif
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
						@else
						<div class="text-center py-4">
							<i class='bx bx-task bx-lg text-muted mb-3'></i>
							<p class="text-muted">No tasks created for this project yet.</p>
							@can('create_tasks')
							<a href="{{ route('add-task', ['project_id' => $project->id]) }}" class="btn btn-primary radius-30">Create First Task</a>
							@endcan
						</div>
						@endif
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
				<!-- Files and Documents -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Project Files & Documents</h5>
							@can('edit_projects')
							<button class="btn btn-primary radius-30 btn-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
								<i class='bx bx-upload'></i> Upload File
							</button>
							@endcan
						</div>
					</div>
					<div class="card-body">
						@if($projectFiles && $projectFiles->count() > 0)
						<div class="row">
							@foreach($projectFiles as $file)
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											@if($file->isImage())
												@if(file_exists(public_path($file->file_path)))
													<img src="{{ asset($file->file_path) }}" class="mb-2" style="max-width: 100%; max-height: 120px; object-fit: contain;" alt="{{ $file->original_name }}">
												@else
													<i class="bx bx-image bx-lg text-success mb-2"></i>
												@endif
											@elseif($file->isPdf())
												<i class="bx bx-file-pdf bx-lg text-danger mb-2"></i>
											@else
												<i class="bx {{ $file->file_icon }} bx-lg text-primary mb-2"></i>
											@endif
											<h6 class="mb-1">{{ $file->original_name }}</h6>
											<small class="text-muted">{{ $file->formatted_size }} • Uploaded: {{ $file->created_at->format('M d, Y') }}</small>
										</div>
										<div class="mt-3">
											<a href="{{ asset($file->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary radius-30">
												<i class='bx bx-download'></i> Download
											</a>
											@can('delete_projects')
											<button class="btn btn-sm btn-outline-danger radius-30" onclick="deleteFile({{ $file->id }})">
												<i class='bx bx-trash'></i>
											</button>
											@endcan
										</div>
									</div>
								</div>
							</div>
							@endforeach
						</div>
						@else
						<div class="text-center py-4">
							<i class='bx bx-folder-open bx-lg text-muted mb-3'></i>
							<p class="text-muted">No files uploaded for this project yet.</p>
							@can('edit_projects')
							<button class="btn btn-primary radius-30" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
								<i class='bx bx-upload'></i> Upload First File
							</button>
							@endcan
						</div>
						@endif
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="usage" role="tabpanel" aria-labelledby="usage-tab">
				<!-- Usage Statistics -->
				<div class="card radius-10">
					<div class="card-header">
						<h5>Project Usage Statistics</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<h6>Time Distribution</h6>
								<div class="chart-container" style="position: relative; height:300px;">
									<canvas id="timeChart"></canvas>
								</div>
								<div class="row text-center mt-3">
									<div class="col-3">
										<span class="badge bg-primary p-2">Development</span>
										<p class="mt-2 font-20">45%</p>
									</div>
									<div class="col-3">
										<span class="badge bg-info p-2">Design</span>
										<p class="mt-2 font-20">25%</p>
									</div>
									<div class="col-3">
										<span class="badge bg-danger p-2">Testing</span>
										<p class="mt-2 font-20">20%</p>
									</div>
									<div class="col-3">
										<span class="badge bg-warning p-2">Meetings</span>
										<p class="mt-2 font-20">10%</p>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<h6>Weekly Activity</h6>
								<div class="chart-container" style="position: relative; height:300px;">
									<canvas id="activityChart"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="milestones" role="tabpanel" aria-labelledby="milestones-tab">
				<!-- Milestones -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Project Milestones</h5>
							@can('edit_projects')
								<button class="btn btn-primary radius-30 btn-sm" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
									<i class="bx bx-plus"></i> Add Milestone
								</button>
							@endcan
						</div>
					</div>
					<div class="card-body">
						<div class="row g-3 mb-4">
							<div class="col-md-3 col-6">
								<div class="milestone-stat-box">
									<small class="text-muted d-block">Total</small>
									<h5 class="mb-0">{{ $milestoneStats['total'] ?? 0 }}</h5>
								</div>
							</div>
							<div class="col-md-3 col-6">
								<div class="milestone-stat-box">
									<small class="text-muted d-block">Completed</small>
									<h5 class="mb-0 text-success">{{ $milestoneStats['completed'] ?? 0 }}</h5>
								</div>
							</div>
							<div class="col-md-3 col-6">
								<div class="milestone-stat-box">
									<small class="text-muted d-block">In Progress</small>
									<h5 class="mb-0 text-primary">{{ $milestoneStats['in_progress'] ?? 0 }}</h5>
								</div>
							</div>
							<div class="col-md-3 col-6">
								<div class="milestone-stat-box">
									<small class="text-muted d-block">Pending</small>
									<h5 class="mb-0 text-secondary">{{ $milestoneStats['pending'] ?? 0 }}</h5>
								</div>
							</div>
						</div>

						@if($milestones->count() > 0)
							<div class="timeline">
								@foreach($milestones as $milestone)
									@php
										$itemClass = $milestone->status === 'completed' ? 'completed' : ($milestone->status === 'in_progress' ? 'active' : 'pending');
										$statusBadgeClass = $milestone->status === 'completed' ? 'bg-success' : ($milestone->status === 'in_progress' ? 'bg-primary' : 'bg-secondary');
										$statusLabel = $milestone->status === 'in_progress' ? 'In Progress' : ucfirst($milestone->status);
									@endphp
									<div class="timeline-item {{ $itemClass }}">
										<div class="timeline-marker"></div>
										<div class="timeline-content">
											<div class="d-flex justify-content-between align-items-start gap-3">
												<div>
													<h6 class="mb-1">{{ $milestone->title }}</h6>
													@if($milestone->description)
														<p class="mb-1">{{ $milestone->description }}</p>
													@endif
													<small class="text-muted">
														@if($milestone->due_date)
															Due: {{ $milestone->due_date->format('M d, Y') }}
														@else
															No due date
														@endif
														@if($milestone->completed_at)
															| Completed: {{ $milestone->completed_at->format('M d, Y h:i A') }}
														@endif
													</small>
												</div>
												<span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
											</div>
										</div>
									</div>
								@endforeach
							</div>
						@else
							<div class="text-center py-4">
								<i class='bx bx-flag bx-lg text-muted mb-3'></i>
								<p class="text-muted mb-2">No milestones added for this project yet.</p>
								@can('edit_projects')
									<button class="btn btn-primary radius-30 btn-sm" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
										<i class="bx bx-plus"></i> Create First Milestone
									</button>
								@endcan
							</div>
						@endif
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="issues" role="tabpanel" aria-labelledby="issues-tab">
				<!-- Issues and Risks -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Issues & Risks</h5>
							@can('edit_projects')
							<button class="btn btn-danger radius-30 btn-sm" data-bs-toggle="modal" data-bs-target="#addIssueModal">
								<i class="bx bx-plus me-1"></i>Report New Issue
							</button>
							@endcan
						</div>
					</div>
					<div class="card-body">
						@if($issues && $issues->count() > 0)
						<div class="row mb-3">
							<div class="col-md-3 col-6">
								<div class="issue-stat-box">
									<small class="text-muted d-block">Total</small>
									<h5 class="mb-0">{{ $issueStats['total'] ?? 0 }}</h5>
								</div>
							</div>
							<div class="col-md-3 col-6">
								<div class="issue-stat-box">
									<small class="text-muted d-block">Open</small>
									<h5 class="mb-0 text-warning">{{ $issueStats['open'] ?? 0 }}</h5>
								</div>
							</div>
							<div class="col-md-3 col-6">
								<div class="issue-stat-box">
									<small class="text-muted d-block">In Progress</small>
									<h5 class="mb-0 text-primary">{{ $issueStats['in_progress'] ?? 0 }}</h5>
								</div>
							</div>
							<div class="col-md-3 col-6">
								<div class="issue-stat-box">
									<small class="text-muted d-block">Resolved</small>
									<h5 class="mb-0 text-success">{{ $issueStats['resolved'] ?? 0 }}</h5>
								</div>
							</div>
						</div>

						<div class="accordion" id="issuesAccordion">
							@foreach($issues as $issue)
							<div class="accordion-item">
								<h2 class="accordion-header" id="heading{{ $issue->id }}">
									<button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $issue->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $issue->id }}">
										<span class="badge @if($issue->priority == 'high') bg-danger @elseif($issue->priority == 'medium') bg-warning @else bg-info @endif me-2">{{ ucfirst($issue->priority) }}</span>
										Issue #{{ $issue->id }}
										<span class="badge @if($issue->status == 'open') bg-warning @elseif($issue->status == 'in_progress') bg-primary @elseif($issue->status == 'resolved') bg-success @else bg-secondary @endif ms-2">{{ ucfirst(str_replace('_', ' ', $issue->status)) }}</span>
									</button>
								</h2>
								<div id="collapse{{ $issue->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $issue->id }}" data-bs-parent="#issuesAccordion">
									<div class="accordion-body">
										<p><strong>Description:</strong> {{ $issue->issue_description }}</p>
										<p><strong>Status:</strong>
											<span class="badge @if($issue->status == 'open') bg-warning @elseif($issue->status == 'in_progress') bg-primary @elseif($issue->status == 'resolved') bg-success @else bg-secondary @endif">
												{{ ucfirst(str_replace('_', ' ', $issue->status)) }}
											</span>
										</p>
										<p><strong>Priority:</strong>
											<span class="badge @if($issue->priority == 'high') bg-danger @elseif($issue->priority == 'medium') bg-warning @else bg-info @endif">
												{{ ucfirst($issue->priority) }}
											</span>
										</p>
										@if($issue->tasks && $issue->tasks->count() > 0)
										<p><strong>Tasks:</strong> {{ $issue->tasks->count() }}</p>
										@endif
										<small class="text-muted">Reported: {{ $issue->created_at->format('M d, Y') }} | Updated: {{ $issue->updated_at->format('M d, Y') }}</small>
										@can('edit_projects')
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-2" onclick="editIssue({{ $issue->id }}, '{{ addslashes($issue->issue_description) }}', '{{ $issue->priority }}', '{{ $issue->status }}')">
												<i class="bx bx-edit"></i> Edit
											</button>
											<form action="{{ route('project.issues.destroy', [$project->id, $issue->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this issue?')">
												@csrf
												@method('DELETE')
												<button type="submit" class="btn btn-sm btn-outline-danger radius-30">
													<i class="bx bx-trash"></i> Delete
												</button>
											</form>
										</div>
										@endcan
									</div>
								</div>
							</div>
							@endforeach
						</div>
						@else
						<div class="text-center py-4">
							<i class='bx bx-error bx-lg text-muted mb-3'></i>
							<p class="text-muted">No issues reported for this project yet.</p>
							@can('edit_projects')
							<button class="btn btn-danger radius-30" data-bs-toggle="modal" data-bs-target="#addIssueModal">
								<i class="bx bx-plus me-1"></i>Report First Issue
							</button>
							@endcan
						</div>
						@endif
					</div>
				</div>
			</div>
			<!-- Comments Tab -->
			<div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
				<div class="card radius-10">
					<div class="card-header">
						<h5 class="mb-0">Comments</h5>
					</div>
					<div class="card-body">
						<div id="comments-list" class="mb-3">
							@forelse($projectComments as $comment)
								<div class="d-flex align-items-start mb-3 comment-item">
									<img src="{{ $comment->user && $comment->user->staff && $comment->user->staff->profile_image ? asset('uploads/staff/' . $comment->user->staff->profile_image) : 'https://placehold.co/40x40' }}" class="rounded-circle me-3" alt="User" width="40" height="40">
									<div class="flex-grow-1">
										<h6 class="mb-1">{{ $comment->user->name ?? 'Unknown User' }}</h6>
										<p class="mb-1">{{ $comment->comment }}</p>
										<small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
									</div>
								</div>
							@empty
								<p class="text-muted">No comments yet.</p>
							@endforelse
						</div>
						<div class="border-top pt-3">
							<form action="{{ route('project.comment.store', $project->id) }}" method="POST">
								@csrf
								<div class="mb-3">
									<textarea class="form-control" name="comment" rows="3" placeholder="Add a comment..." required></textarea>
								</div>
								<button type="submit" class="btn btn-primary">Post Comment</button>
							</form>
						</div>
					</div>
				</div>
			</div>
			
		</div>

	</div>
</div>
<!--end page wrapper -->

<!-- Add Milestone Modal -->
@can('edit_projects')
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Milestone</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="{{ route('project.milestones.store', $project->id) }}" method="POST">
				@csrf
				<div class="modal-body">
					<div class="mb-3">
						<label for="milestone_title" class="form-label">Title</label>
						<input type="text" name="title" id="milestone_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Enter milestone title" required>
						@error('title')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<div class="mb-3">
						<label for="milestone_description" class="form-label">Description</label>
						<textarea name="description" id="milestone_description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Add milestone details...">{{ old('description') }}</textarea>
						@error('description')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<div class="row g-3">
						<div class="col-md-6">
							<label for="milestone_due_date" class="form-label">Due Date</label>
							<input type="date" name="due_date" id="milestone_due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
							@error('due_date')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6">
							<label for="milestone_status" class="form-label">Status</label>
							<select name="status" id="milestone_status" class="form-select @error('status') is-invalid @enderror" required>
								<option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
								<option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
								<option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
							</select>
							@error('status')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Milestone</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endcan

<!-- Upload File Modal -->
@can('edit_projects')
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Upload File</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="{{ route('project.upload-file', $project->id) }}" method="POST" enctype="multipart/form-data" id="uploadFileForm">
				@csrf
				<div class="modal-body">
					<div class="mb-3">
						<label for="file_upload" class="form-label">Select File</label>
						<input type="file" name="file" class="form-control @error('file') is-invalid @enderror" id="file_upload" accept=".jpg,.jpeg,.png,.gif,.svg,.webp,.bmp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" required>
						<small class="text-muted">Supported formats: JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR (Max 10MB)</small>
						@error('file')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<div class="mb-3">
						<label for="file_description" class="form-label">Description (Optional)</label>
						<textarea class="form-control" name="description" id="file_description" rows="3" placeholder="Add a description for this file..."></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary" id="uploadBtn">
						<i class='bx bx-upload'></i> Upload
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endcan

<!-- Delete Confirmation Modal -->
@can('delete_projects')
<div class="modal fade" id="deleteFileModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete File</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this file? This action cannot be undone.</p>
				<input type="hidden" id="deleteFileId">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" onclick="confirmDelete()">
					<i class='bx bx-trash'></i> Delete
				</button>
			</div>
		</div>
	</div>
</div>
@endcan

<!-- Add Issue Modal -->
@can('edit_projects')
<div class="modal fade" id="addIssueModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Report New Issue</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="{{ route('project.issues.store', $project->id) }}" method="POST">
				@csrf
				<div class="modal-body">
					<div class="mb-3">
						<label for="issue_description" class="form-label">Issue Description</label>
						<textarea name="issue_description" id="issue_description" class="form-control @error('issue_description') is-invalid @enderror" rows="4" placeholder="Describe the issue in detail..." required>{{ old('issue_description') }}</textarea>
						@error('issue_description')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<div class="row">
						<div class="col-md-6">
							<label for="priority" class="form-label">Priority</label>
							<select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
								<option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
								<option value="medium" {{ old('priority') === 'medium' || !old('priority') ? 'selected' : '' }}>Medium</option>
								<option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
							</select>
							@error('priority')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6">
							<label for="status" class="form-label">Status</label>
							<select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
								<option value="open" {{ old('status') === 'open' || !old('status') ? 'selected' : '' }}>Open</option>
								<option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
								<option value="resolved" {{ old('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
								<option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
							</select>
							@error('status')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger">Report Issue</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endcan

<!-- Edit Issue Modal -->
@can('edit_projects')
<div class="modal fade" id="editIssueModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Issue</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="editIssueForm" method="POST">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<div class="mb-3">
						<label for="edit_issue_description" class="form-label">Issue Description</label>
						<textarea name="issue_description" id="edit_issue_description" class="form-control" rows="4" placeholder="Describe the issue in detail..." required></textarea>
					</div>
					<div class="row">
						<div class="col-md-6">
							<label for="edit_priority" class="form-label">Priority</label>
							<select name="priority" id="edit_priority" class="form-select" required>
								<option value="low">Low</option>
								<option value="medium">Medium</option>
								<option value="high">High</option>
							</select>
						</div>
						<div class="col-md-6">
							<label for="edit_status" class="form-label">Status</label>
							<select name="status" id="edit_status" class="form-select" required>
								<option value="open">Open</option>
								<option value="in_progress">In Progress</option>
								<option value="resolved">Resolved</option>
								<option value="closed">Closed</option>
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Update Issue</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endcan

<style>
.timeline {
	position: relative;
	padding-left: 30px;
}

.timeline::before {
	content: '';
	position: absolute;
	left: 15px;
	top: 0;
	bottom: 0;
	width: 2px;
	background: #e9ecef;
}

.timeline-item {
	position: relative;
	margin-bottom: 30px;
}

.timeline-marker {
	position: absolute;
	left: -22px;
	top: 5px;
	width: 12px;
	height: 12px;
	border-radius: 50%;
	border: 2px solid #fff;
	box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-item.completed .timeline-marker {
	background-color: #198754;
}

.timeline-item.active .timeline-marker {
	background-color: #0d6efd;
}

.timeline-item.pending .timeline-marker {
	background-color: #6c757d;
}

.timeline-content {
	background: #f8f9fa;
	padding: 15px;
	border-radius: 8px;
	border-left: 4px solid #dee2e6;
}

.timeline-item.completed .timeline-content {
	border-left-color: #198754;
}

.timeline-item.active .timeline-content {
	border-left-color: #0d6efd;
}

.timeline-item.pending .timeline-content {
	border-left-color: #6c757d;
}

.milestone-stat-box {
	background: #f8f9fa;
	border: 1px solid #e9ecef;
	border-radius: 10px;
	padding: 12px 14px;
}

.comments-list .comment-item {
	border-bottom: 1px solid #e9ecef;
	padding-bottom: 20px;
	margin-bottom: 20px;
}

.comments-list .comment-item:last-child {
	border-bottom: none;
	padding-bottom: 0;
	margin-bottom: 0;
}

.comment-actions {
	margin-top: 10px;
}

.issue-stat-box {
	background: #f8f9fa;
	border: 1px solid #e9ecef;
	border-radius: 10px;
	padding: 12px 14px;
}
</style>

@push('scripts')
<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
$(function() {
    "use strict";

    @if($errors->has('title') || $errors->has('description') || $errors->has('status') || $errors->has('due_date'))
        $('#addMilestoneModal').modal('show');
    @endif

    // Time Distribution Chart (Doughnut)
    if (document.getElementById("timeChart")) {
        var ctx = document.getElementById("timeChart").getContext('2d');

        var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
        gradientStroke1.addColorStop(0, '#fc4a1a');
        gradientStroke1.addColorStop(1, '#f7b733');

        var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
        gradientStroke2.addColorStop(0, '#4776e6');
        gradientStroke2.addColorStop(1, '#8e54e9');

        var gradientStroke3 = ctx.createLinearGradient(0, 0, 0, 300);
        gradientStroke3.addColorStop(0, '#ee0979');
        gradientStroke3.addColorStop(1, '#ff6a00');

        var gradientStroke4 = ctx.createLinearGradient(0, 0, 0, 300);
        gradientStroke4.addColorStop(0, '#42e695');
        gradientStroke4.addColorStop(1, '#3bb2b8');

        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Development", "Design", "Testing", "Meetings"],
                datasets: [{
                    backgroundColor: [
                        gradientStroke1,
                        gradientStroke2,
                        gradientStroke3,
                        gradientStroke4
                    ],
                    hoverBackgroundColor: [
                        gradientStroke1,
                        gradientStroke2,
                        gradientStroke3,
                        gradientStroke4
                    ],
                    data: [45, 25, 20, 10],
                    borderWidth: [1, 1, 1, 1]
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: 82,
                plugins: {
                    legend: {
                        display: false,
                    }
                }
            }
        });
    }

    // Weekly Activity Chart (Line)
    if (document.getElementById("activityChart")) {
        var ctx = document.getElementById('activityChart').getContext('2d');

        var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
        gradientStroke1.addColorStop(0, '#00b09b');
        gradientStroke1.addColorStop(1, '#96c93d');

        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: 'Hours',
                    data: [8.5, 7.2, 9.1, 6.8, 8.3, 0],
                    backgroundColor: [
                        gradientStroke1
                    ],
                    fill: {
                        target: 'origin',
                        above: 'rgb(21 202 32 / 15%)',
                    },
                    tension: 0.4,
                    borderColor: [
                        gradientStroke1
                    ],
                    borderWidth: 3
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Initialize Select2 for followers, assignees, and tags dropdowns
    $('#followers').select2({
        placeholder: "Select followers",
        allowClear: true
    });

    $('#assignees').select2({
        placeholder: "Select assignees",
        allowClear: true
    });

    $('#tags').select2({
        placeholder: "Select or add tags",
        tags: true,
        allowClear: true
    });

    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('.ckeditor'))
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Refresh timer values every minute on project details page.
    setInterval(function() {
        window.location.reload();
    }, 60000);

    // File upload form submission
    $('#uploadFileForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#uploadBtn');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm"></span> Uploading...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#uploadFileModal').modal('hide');
                $('#uploadFileForm')[0].reset();
                toastr.success('File uploaded successfully!');
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                btn.html('<i class=\'bx bx-upload\'></i> Upload');
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error('Error uploading file. Please try again.');
                }
            }
        });
    });
});

// Delete file function
function deleteFile(fileId) {
    $('#deleteFileId').val(fileId);
    $('#deleteFileModal').modal('show');
}

// Confirm delete
function confirmDelete() {
    var fileId = $('#deleteFileId').val();
    $.ajax({
        url: '/project/file/' + fileId + '/delete',
        type: 'DELETE',
        data: {
            '_token': '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#deleteFileModal').modal('hide');
            toastr.success('File deleted successfully!');
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        },
        error: function(xhr) {
            toastr.error('Error deleting file. Please try again.');
        }
    });
}

// Edit issue function
function editIssue(issueId, description, priority, status) {
    $('#edit_issue_description').val(description);
    $('#edit_priority').val(priority);
    $('#edit_status').val(status);
    $('#editIssueForm').attr('action', '{{ route("project.issues.update", [$project->id, ":issueId"]) }}'.replace(':issueId', issueId));
    $('#editIssueModal').modal('show');
}
</script>
@endpush

@endsection
