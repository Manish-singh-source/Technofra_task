@extends('/layout/master')
@section('content')

<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="#"><i class="bx bx-home-alt"></i></a></li>
						<li class="breadcrumb-item"><a href="#">Projects</a></li>
						<li class="breadcrumb-item active" aria-current="page">Office Management</li>
					</ol>
				</nav>
			</div>
			<div class="ms-auto">
				<a href="#" class="btn btn-outline-secondary me-2"><i class="bx bx-arrow-back"></i> Back</a>
				<div class="btn-group">
					<button type="button" class="btn btn-primary">Edit Project</button>
					<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span></button>
					<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
						<a class="dropdown-item" href="javascript:;">Archive</a>
						<a class="dropdown-item" href="javascript:;">Duplicate</a>
						<a class="dropdown-item" href="javascript:;">Delete</a>
					</div>
				</div>
			</div>
		</div>
		<!--end breadcrumb-->

		<!-- Project Header -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<h4 class="card-title mb-1">Office Management</h4>
								<span class="badge bg-danger">High Urgency</span>
							</div>
							<div class="d-flex align-items-center">
								<div class="me-3">
									<small class="text-muted">Assignees</small><br>
									<div class="avatar-group">
										<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-1" width="32" height="32">
										<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-1" width="32" height="32">
										<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle" width="32" height="32">
									</div>
								</div>
								<div class="me-3">
									<small class="text-muted">Start Date</small><br>
									<span>Jan 1, 2023</span>
								</div>
								<div>
									<small class="text-muted">End Date</small><br>
									<span>Dec 31, 2023</span>
								</div>
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
								<h4 class="my-1 text-primary">120h</h4>
								<p class="mb-0 font-13">+5.2% from last month</p>
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
								<h4 class="my-1 text-success">$50,000</h4>
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
								<h4 class="my-1 text-warning">$30,000</h4>
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
								<h4 class="my-1 text-info">85%</h4>
								<p class="mb-0 font-13">+7.8% from last month</p>
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
				<button class="nav-link" id="screenshots-tab" data-bs-toggle="tab" data-bs-target="#screenshots" type="button" role="tab" aria-controls="screenshots" aria-selected="false">Screenshots</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usage" type="button" role="tab" aria-controls="usage" aria-selected="false">Usage</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="milestones-tab" data-bs-toggle="tab" data-bs-target="#milestones" type="button" role="tab" aria-controls="milestones" aria-selected="false">Milestones</button>
			</li>
		</ul>

		<div class="tab-content" id="projectTabsContent">
			<div class="tab-pane fade show active" id="employees" role="tabpanel" aria-labelledby="employees-tab">
				<!-- Employees Table -->
				<div class="card">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Assigned Employees</h5>
							<input type="text" class="form-control w-25" placeholder="Search employees..." id="searchInput">
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped table-hover" id="employeesTable">
								<thead>
									<tr>
										<th>Name</th>
										<th>Team</th>
										<th>Total Time (H)</th>
										<th>Clocked Time (H)</th>
										<th>Computer (H)</th>
										<th>Manual (H)</th>
										<th>Productive (H)</th>
										<th>Utilization</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<div class="d-flex align-items-center">
												<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
												<span>John Doe</span>
											</div>
										</td>
										<td>Development</td>
										<td>160</td>
										<td>140</td>
										<td>120</td>
										<td>20</td>
										<td>110</td>
										<td>
											<div class="d-flex align-items-center">
												<div class="progress me-2" style="width: 100px;">
													<div class="progress-bar" style="width: 80%"></div>
												</div>
												80%
											</div>
										</td>
										<td>
											<div class="dropdown">
												<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="#">View Profile</a></li>
													<li><a class="dropdown-item" href="#">Edit</a></li>
													<li><a class="dropdown-item" href="#">Remove</a></li>
												</ul>
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<div class="d-flex align-items-center">
												<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
												<span>Jane Smith</span>
											</div>
										</td>
										<td>Design</td>
										<td>150</td>
										<td>130</td>
										<td>100</td>
										<td>30</td>
										<td>95</td>
										<td>
											<div class="d-flex align-items-center">
												<div class="progress me-2" style="width: 100px;">
													<div class="progress-bar" style="width: 70%"></div>
												</div>
												70%
											</div>
										</td>
										<td>
											<div class="dropdown">
												<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="#">View Profile</a></li>
													<li><a class="dropdown-item" href="#">Edit</a></li>
													<li><a class="dropdown-item" href="#">Remove</a></li>
												</ul>
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<div class="d-flex align-items-center">
												<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
												<span>Bob Johnson</span>
											</div>
										</td>
										<td>QA</td>
										<td>140</td>
										<td>120</td>
										<td>90</td>
										<td>30</td>
										<td>80</td>
										<td>
											<div class="d-flex align-items-center">
												<div class="progress me-2" style="width: 100px;">
													<div class="progress-bar" style="width: 60%"></div>
												</div>
												60%
											</div>
										</td>
										<td>
											<div class="dropdown">
												<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="#">View Profile</a></li>
													<li><a class="dropdown-item" href="#">Edit</a></li>
													<li><a class="dropdown-item" href="#">Remove</a></li>
												</ul>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
				<!-- Tasks Table -->
				<div class="card">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Project Tasks</h5>
							<input type="text" class="form-control w-25" placeholder="Search tasks..." id="searchTasks">
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped table-hover" id="tasksTable">
								<thead>
									<tr>
										<th>Task ID</th>
										<th>Project & Task</th>
										<th>Created On</th>
										<th>Total Hours</th>
										<th>Priority</th>
										<th>Assignee</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>T001</td>
										<td>Office Management - Setup Database</td>
										<td>2023-01-15</td>
										<td>20h</td>
										<td><span class="badge bg-danger">High</span></td>
										<td>
											<div class="d-flex align-items-center">
												<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
												<span>John Doe</span>
											</div>
										</td>
									</tr>
									<tr>
										<td>T002</td>
										<td>Office Management - Design UI</td>
										<td>2023-01-20</td>
										<td>15h</td>
										<td><span class="badge bg-warning">Medium</span></td>
										<td>
											<div class="d-flex align-items-center">
												<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
												<span>Jane Smith</span>
											</div>
										</td>
									</tr>
									<tr>
										<td>T003</td>
										<td>Office Management - Implement Features</td>
										<td>2023-02-01</td>
										<td>30h</td>
										<td><span class="badge bg-success">Low</span></td>
										<td>
											<div class="d-flex align-items-center">
												<img src="https://placehold.co/40x40" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
												<span>Bob Johnson</span>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="screenshots" role="tabpanel" aria-labelledby="screenshots-tab">
				<p>Screenshots content here.</p>
			</div>
			<div class="tab-pane fade" id="usage" role="tabpanel" aria-labelledby="usage-tab">
				<p>Usage content here.</p>
			</div>
			<div class="tab-pane fade" id="milestones" role="tabpanel" aria-labelledby="milestones-tab">
				<p>Milestones content here.</p>
			</div>
		</div>


@endsection