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
				<a href="{{ route('client-issue') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-plus"></i> Raise Issue</a>
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
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<h4 class="card-title mb-1">Office Management</h4>
								<span class="badge bg-danger">High Urgency</span>
								<p class="text-muted mt-2 font-13">Project ID: PROJ-2023-001 | Status: In Progress</p>
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

		<!-- Project Description -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card radius-10">
					<div class="card-header">
						<h5 class="mb-0">Project Description</h5>
					</div>
					<div class="card-body">
						<p class="mb-3">This comprehensive office management system aims to streamline administrative processes, improve productivity, and enhance communication within the organization. The project includes development of a web-based platform for employee management, task tracking, document management, and reporting functionalities.</p>
						<div class="row">
							<div class="col-md-6">
								<h6>Key Objectives:</h6>
								<ul class="list-unstyled">
									<li><i class="bx bx-check text-success me-2"></i>Automate routine administrative tasks</li>
									<li><i class="bx bx-check text-success me-2"></i>Centralize employee information and records</li>
									<li><i class="bx bx-check text-success me-2"></i>Implement real-time task tracking and reporting</li>
									<li><i class="bx bx-check text-success me-2"></i>Enhance inter-departmental communication</li>
								</ul>
							</div>
							<div class="col-md-6">
								<h6>Technologies:</h6>
								<div class="d-flex flex-wrap gap-2">
									<span class="badge bg-primary">Laravel</span>
									<span class="badge bg-info">Vue.js</span>
									<span class="badge bg-success">MySQL</span>
									<span class="badge bg-warning">Bootstrap</span>
									<span class="badge bg-secondary">Docker</span>
								</div>
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
								<h6 class="mb-0">Global Enterprises Inc.</h6>
								<small class="text-muted font-13">Technology Solutions Provider</small>
							</div>
						</div>
						<div class="row">
							<div class="col-6">
								<small class="text-muted">Contact Person</small>
								<p class="mb-1 font-13">Sarah Johnson</p>
							</div>
							<div class="col-6">
								<small class="text-muted">Email</small>
								<p class="mb-1 font-13">sarah.johnson@globalent.com</p>
							</div>
							<div class="col-6">
								<small class="text-muted">Phone</small>
								<p class="mb-1 font-13">+1 (555) 123-4567</p>
							</div>
							<div class="col-6">
								<small class="text-muted">Address</small>
								<p class="mb-1 font-13">123 Business Ave, Suite 100<br>Tech City, TC 12345</p>
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
				<button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">Files</button>
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
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Project Tasks</h5>
							<input type="text" class="form-control w-25 radius-30" placeholder="Search tasks..." id="searchTasks">
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0">
								<thead class="table-light">
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
			<div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
				<!-- Files and Documents -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Project Files & Documents</h5>
							<button class="btn btn-primary radius-30 btn-sm">Upload File</button>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											<i class="bx bx-file-text bx-lg text-primary mb-2"></i>
											<h6>Project_Requirements.pdf</h6>
											<small class="text-muted">2.3 MB • Uploaded: Jan 5, 2023</small>
										</div>
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-1">Download</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">View</button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											<i class="bx bx-image bx-lg text-success mb-2"></i>
											<h6>UI_Mockups.zip</h6>
											<small class="text-muted">15.7 MB • Uploaded: Feb 10, 2023</small>
										</div>
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-1">Download</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">View</button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											<i class="bx bx-spreadsheet bx-lg text-warning mb-2"></i>
											<h6>Budget_Tracking.xlsx</h6>
											<small class="text-muted">1.2 MB • Uploaded: Mar 15, 2023</small>
										</div>
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-1">Download</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">View</button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											<i class="bx bx-code bx-lg text-info mb-2"></i>
											<h6>Source_Code_v1.0.zip</h6>
											<small class="text-muted">45.8 MB • Uploaded: Jul 20, 2023</small>
										</div>
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-1">Download</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">View</button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											<i class="bx bx-video bx-lg text-danger mb-2"></i>
											<h6>Demo_Video.mp4</h6>
											<small class="text-muted">125.3 MB • Uploaded: Aug 5, 2023</small>
										</div>
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-1">Download</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">View</button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="card border h-100">
									<div class="card-body text-center d-flex flex-column justify-content-between">
										<div>
											<i class="bx bx-file-blank bx-lg text-secondary mb-2"></i>
											<h6>Test_Reports.docx</h6>
											<small class="text-muted">3.1 MB • Uploaded: Aug 10, 2023</small>
										</div>
										<div class="mt-3">
											<button class="btn btn-sm btn-outline-primary radius-30 me-1">Download</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">View</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="screenshots" role="tabpanel" aria-labelledby="screenshots-tab">
				<p>Screenshots content here.</p>
			</div>
			<div class="tab-pane fade" id="usage" role="tabpanel" aria-labelledby="usage-tab">
				<!-- Usage Analytics -->
				<div class="row">
					<div class="col-md-6">
						<div class="card radius-10 h-100">
							<div class="card-header">
								<h5>Time Distribution</h5>
							</div>
							<div class="card-body">
								<div class="chart-container-1">
									<canvas id="timeChart" width="400" height="300"></canvas>
								</div>
								<div class="mt-3">
									<div class="d-flex justify-content-between">
										<span>Development</span>
										<span>45%</span>
									</div>
									<div class="progress mb-2">
										<div class="progress-bar bg-primary" style="width: 45%"></div>
									</div>
									<div class="d-flex justify-content-between">
										<span>Design</span>
										<span>25%</span>
									</div>
									<div class="progress mb-2">
										<div class="progress-bar bg-info" style="width: 25%"></div>
									</div>
									<div class="d-flex justify-content-between">
										<span>Testing</span>
										<span>20%</span>
									</div>
									<div class="progress mb-2">
										<div class="progress-bar bg-success" style="width: 20%"></div>
									</div>
									<div class="d-flex justify-content-between">
										<span>Meetings</span>
										<span>10%</span>
									</div>
									<div class="progress">
										<div class="progress-bar bg-warning" style="width: 10%"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card radius-10 h-100">
							<div class="card-header">
								<h5>Weekly Activity</h5>
							</div>
							<div class="card-body">
								<div class="chart-container-1">
									<canvas id="activityChart" width="400" height="300"></canvas>
								</div>
								<div class="mt-3">
									<div class="row text-center">
										<div class="col-2">
											<small>Mon</small>
											<h6 class="text-primary">8.5h</h6>
										</div>
										<div class="col-2">
											<small>Tue</small>
											<h6 class="text-primary">7.2h</h6>
										</div>
										<div class="col-2">
											<small>Wed</small>
											<h6 class="text-primary">9.1h</h6>
										</div>
										<div class="col-2">
											<small>Thu</small>
											<h6 class="text-primary">6.8h</h6>
										</div>
										<div class="col-2">
											<small>Fri</small>
											<h6 class="text-primary">8.3h</h6>
										</div>
										<div class="col-2">
											<small>Sat</small>
											<h6 class="text-muted">0h</h6>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row mt-4">
					<div class="col-12">
						<div class="card radius-10">
							<div class="card-header">
								<h5>Resource Utilization</h5>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>Resource</th>
												<th>Allocated</th>
												<th>Used</th>
												<th>Utilization</th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Development Team</td>
												<td>5 members</td>
												<td>4.5 FTE</td>
												<td>
													<div class="d-flex align-items-center">
														<div class="progress me-2" style="width: 100px;">
															<div class="progress-bar bg-success" style="width: 90%"></div>
														</div>
														90%
													</div>
												</td>
												<td><span class="badge bg-success">Optimal</span></td>
											</tr>
											<tr>
												<td>Design Team</td>
												<td>2 members</td>
												<td>1.8 FTE</td>
												<td>
													<div class="d-flex align-items-center">
														<div class="progress me-2" style="width: 100px;">
															<div class="progress-bar bg-warning" style="width: 75%"></div>
														</div>
														75%
													</div>
												</td>
												<td><span class="badge bg-warning">Good</span></td>
											</tr>
											<tr>
												<td>QA Team</td>
												<td>3 members</td>
												<td>2.2 FTE</td>
												<td>
													<div class="d-flex align-items-center">
														<div class="progress me-2" style="width: 100px;">
															<div class="progress-bar bg-info" style="width: 60%"></div>
														</div>
														60%
													</div>
												</td>
												<td><span class="badge bg-info">Moderate</span></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="milestones" role="tabpanel" aria-labelledby="milestones-tab">
				<!-- Milestones Timeline -->
				<div class="card radius-10">
					<div class="card-header">
						<h5>Project Milestones</h5>
					</div>
					<div class="card-body">
						<div class="timeline">
							<div class="timeline-item completed">
								<div class="timeline-marker bg-success"></div>
								<div class="timeline-content">
									<h6>Project Kickoff</h6>
									<p>Initial planning and requirements gathering completed.</p>
									<small class="text-muted">Jan 1, 2023</small>
								</div>
							</div>
							<div class="timeline-item completed">
								<div class="timeline-marker bg-success"></div>
								<div class="timeline-content">
									<h6>Design Phase Complete</h6>
									<p>UI/UX designs and system architecture finalized.</p>
									<small class="text-muted">Feb 15, 2023</small>
								</div>
							</div>
							<div class="timeline-item completed">
								<div class="timeline-marker bg-success"></div>
								<div class="timeline-content">
									<h6>Development Started</h6>
									<p>Backend and frontend development initiated.</p>
									<small class="text-muted">Mar 1, 2023</small>
								</div>
							</div>
							<div class="timeline-item active">
								<div class="timeline-marker bg-primary"></div>
								<div class="timeline-content">
									<h6>Core Features Implementation</h6>
									<p>Implementing main functionalities and integrations.</p>
									<small class="text-muted">Current - Aug 15, 2023</small>
								</div>
							</div>
							<div class="timeline-item">
								<div class="timeline-marker bg-secondary"></div>
								<div class="timeline-content">
									<h6>Testing Phase</h6>
									<p>QA testing and bug fixing.</p>
									<small class="text-muted">Oct 1, 2023</small>
								</div>
							</div>
							<div class="timeline-item">
								<div class="timeline-marker bg-secondary"></div>
								<div class="timeline-content">
									<h6>Deployment</h6>
									<p>System deployment and user training.</p>
									<small class="text-muted">Nov 15, 2023</small>
								</div>
							</div>
							<div class="timeline-item">
								<div class="timeline-marker bg-secondary"></div>
								<div class="timeline-content">
									<h6>Project Completion</h6>
									<p>Final delivery and project closure.</p>
									<small class="text-muted">Dec 31, 2023</small>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="issues" role="tabpanel" aria-labelledby="issues-tab">
				<!-- Issues and Risks -->
				<div class="card radius-10">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h5>Issues & Risks</h5>
							<button class="btn btn-danger radius-30 btn-sm">Report New Issue</button>
						</div>
					</div>
					<div class="card-body">
						<div class="accordion" id="issuesAccordion">
							<div class="accordion-item">
								<h2 class="accordion-header" id="headingOne">
									<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
										<span class="badge bg-danger me-2">High</span> Database Performance Issues
									</button>
								</h2>
								<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#issuesAccordion">
									<div class="accordion-body">
										<p><strong>Description:</strong> Slow query performance affecting user experience during peak hours.</p>
										<p><strong>Impact:</strong> High - Users experiencing delays of 5-10 seconds for common operations.</p>
										<p><strong>Status:</strong> <span class="badge bg-warning">In Progress</span></p>
										<p><strong>Assigned to:</strong> John Doe</p>
										<p><strong>Resolution Plan:</strong> Optimize database queries, implement caching, and add database indexes.</p>
										<small class="text-muted">Reported: Aug 1, 2023 | Updated: Aug 10, 2023</small>
									</div>
								</div>
							</div>
							<div class="accordion-item">
								<h2 class="accordion-header" id="headingTwo">
									<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
										<span class="badge bg-warning me-2">Medium</span> Third-party API Integration Delay
									</button>
								</h2>
								<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#issuesAccordion">
									<div class="accordion-body">
										<p><strong>Description:</strong> Payment gateway integration delayed due to API changes from provider.</p>
										<p><strong>Impact:</strong> Medium - Affects payment processing functionality.</p>
										<p><strong>Status:</strong> <span class="badge bg-info">Monitoring</span></p>
										<p><strong>Assigned to:</strong> Jane Smith</p>
										<p><strong>Resolution Plan:</strong> Coordinate with payment provider for updated API documentation and testing.</p>
										<small class="text-muted">Reported: Jul 15, 2023 | Updated: Aug 5, 2023</small>
									</div>
								</div>
							</div>
							<div class="accordion-item">
								<h2 class="accordion-header" id="headingThree">
									<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
										<span class="badge bg-info me-2">Low</span> Mobile Responsiveness Issues
									</button>
								</h2>
								<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#issuesAccordion">
									<div class="accordion-body">
										<p><strong>Description:</strong> Minor layout issues on mobile devices for certain screen sizes.</p>
										<p><strong>Impact:</strong> Low - Affects user experience on mobile but doesn't break functionality.</p>
										<p><strong>Status:</strong> <span class="badge bg-success">Resolved</span></p>
										<p><strong>Assigned to:</strong> Bob Johnson</p>
										<p><strong>Resolution:</strong> Updated CSS media queries and tested across multiple devices.</p>
										<small class="text-muted">Reported: Jun 20, 2023 | Resolved: Aug 8, 2023</small>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
				<!-- Comments and Notes -->
				<div class="card radius-10">
					<div class="card-header">
						<h5>Project Comments & Notes</h5>
					</div>
					<div class="card-body">
						<!-- Add Comment Form -->
						<div class="mb-4">
							<div class="d-flex">
								<img src="https://placehold.co/40x40" alt="Current User" class="rounded-circle me-3" width="40" height="40">
								<div class="flex-grow-1">
									<textarea class="form-control" rows="3" placeholder="Add a comment or note..."></textarea>
									<div class="mt-2">
										<button class="btn btn-primary radius-30 btn-sm">Post Comment</button>
										<button class="btn btn-outline-secondary radius-30 btn-sm ms-2">Attach File</button>
									</div>
								</div>
							</div>
						</div>

						<!-- Comments List -->
						<div class="comments-list">
							<div class="comment-item mb-4">
								<div class="d-flex">
									<img src="https://placehold.co/40x40" alt="User" class="rounded-circle me-3" width="40" height="40">
									<div class="flex-grow-1">
										<div class="d-flex justify-content-between align-items-start">
											<div>
												<h6 class="mb-1">Sarah Johnson</h6>
												<small class="text-muted">Project Manager • Aug 15, 2023 at 2:30 PM</small>
											</div>
											<div class="dropdown">
												<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
													<i class="bx bx-dots-horizontal-rounded"></i>
												</button>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="#">Edit</a></li>
													<li><a class="dropdown-item" href="#">Delete</a></li>
												</ul>
											</div>
										</div>
										<p class="mt-2">Great progress on the core features! The team has been working efficiently. Let's schedule a review meeting for next week to discuss the testing phase preparations.</p>
										<div class="comment-actions">
											<button class="btn btn-sm btn-outline-primary radius-30 me-2">Like</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">Reply</button>
										</div>
									</div>
								</div>
							</div>

							<div class="comment-item mb-4">
								<div class="d-flex">
									<img src="https://placehold.co/40x40" alt="User" class="rounded-circle me-3" width="40" height="40">
									<div class="flex-grow-1">
										<div class="d-flex justify-content-between align-items-start">
											<div>
												<h6 class="mb-1">John Doe</h6>
												<small class="text-muted">Lead Developer • Aug 14, 2023 at 10:15 AM</small>
											</div>
											<div class="dropdown">
												<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
													<i class="bx bx-dots-horizontal-rounded"></i>
												</button>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="#">Edit</a></li>
													<li><a class="dropdown-item" href="#">Delete</a></li>
												</ul>
											</div>
										</div>
										<p class="mt-2">Database optimization completed. Query performance improved by 40%. Attached the performance report for review.</p>
										<div class="mt-2">
											<span class="badge bg-info">Performance_Report.pdf</span>
										</div>
										<div class="comment-actions">
											<button class="btn btn-sm btn-outline-primary radius-30 me-2">Like</button>
											<button class="btn btn-sm btn-outline-secondary radius-30">Reply</button>
										</div>
									</div>
								</div>
							</div>

							<div class="comment-item">
								<div class="d-flex">
									<img src="https://placehold.co/40x40" alt="User" class="rounded-circle me-3" width="40" height="40">
									<div class="flex-grow-1">
										<div class="d-flex justify-content-between align-items-start">
											<div>
												<h6 class="mb-1">Jane Smith</h6>
												<small class="text-muted">UI/UX Designer • Aug 12, 2023 at 4:45 PM</small>
											</div>
											<div class="dropdown">
												<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
													<i class="bx bx-dots-horizontal-rounded"></i>
												</button>
												<ul class="dropdown-menu">
													<li><a class="dropdown-item" href="#">Edit</a></li>
													<li><a class="dropdown-item" href="#">Delete</a></li>
												</ul>
											</div>
										</div>
										<p class="mt-2">Updated the mobile responsive design. All layouts now work properly on devices with screen sizes from 320px to 1440px. Ready for QA testing.</p>
										<div class="comment-actions">
											<button class="btn btn-sm btn-outline-primary me-2">Like</button>
											<button class="btn btn-sm btn-outline-secondary">Reply</button>
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
<!--end page wrapper -->

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
</style>

@push('scripts')
<script>
$(function() {
    "use strict";

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
});
</script>
@endpush

@endsection
