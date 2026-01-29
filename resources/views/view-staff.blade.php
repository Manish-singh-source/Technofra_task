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
							<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
							</button>
							<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
								<a class="dropdown-item" href="javascript:;">Another action</a>
								<a class="dropdown-item" href="javascript:;">Something else here</a>
								<div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
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

				<!-- Performance Details Table -->
				<div class="row mb-4">
					<div class="col-12">
						<div class="card radius-10">
							<div class="card-header">
								<h5>Detailed Performance Metrics</h5>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-striped table-hover mb-0">
										<thead class="table-light">
											<tr>
												<th>Metric</th>
												<th>Current Month</th>
												<th>Last Month</th>
												<th>Change</th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Tasks Completed</td>
												<td>47</td>
												<td>42</td>
												<td><span class="text-success">+11.9%</span></td>
												<td><span class="badge bg-success">Excellent</span></td>
											</tr>
											<tr>
												<td>Average Response Time</td>
												<td>2.3 hours</td>
												<td>2.5 hours</td>
												<td><span class="text-success">-8.0%</span></td>
												<td><span class="badge bg-success">Improved</span></td>
											</tr>
											<tr>
												<td>Code Review Feedback</td>
												<td>4.8/5</td>
												<td>4.6/5</td>
												<td><span class="text-success">+4.3%</span></td>
												<td><span class="badge bg-success">Excellent</span></td>
											</tr>
											<tr>
												<td>Bug Fix Rate</td>
												<td>95%</td>
												<td>92%</td>
												<td><span class="text-success">+3.3%</span></td>
												<td><span class="badge bg-success">Good</span></td>
											</tr>
											<tr>
												<td>Client Satisfaction</td>
												<td>4.9/5</td>
												<td>4.7/5</td>
												<td><span class="text-success">+4.3%</span></td>
												<td><span class="badge bg-success">Outstanding</span></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="container">
					<div class="main-body">
						<div class="row">
							<div class="col-lg-4">
								<div class="card">
									<div class="card-body">
										<div class="d-flex flex-column align-items-center text-center">
											<img src="{{ asset('uploads/staff/' . $staff->profile_image) }}" alt="Admin" class="rounded-circle p-1 bg-primary" width="110">
											<div class="mt-3">
												<h4>{{ $staff->first_name . ' ' . $staff->last_name }}</h4>
												<p class="text-secondary mb-1">{{ ucwords(str_replace('_', ' ', $staff->role)) }}</p>
												<p class="text-muted font-size-sm">{{ $staff->email }}</p>
												
											</div>
										</div>
										<hr class="my-4" />
										<ul class="list-group list-group-flush">
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe me-2 icon-inline"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>Website</h6>
												<span class="text-secondary">https://codervent.com</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github me-2 icon-inline"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>Github</h6>
												<span class="text-secondary">codervent</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-twitter me-2 icon-inline text-info"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>Twitter</h6>
												<span class="text-secondary">@codervent</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-instagram me-2 icon-inline text-danger"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>Instagram</h6>
												<span class="text-secondary">codervent</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-facebook me-2 icon-inline text-primary"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>Facebook</h6>
												<span class="text-secondary">codervent</span>
											</li>
										</ul>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-body">
												<h5 class="d-flex align-items-center mb-3">Project Status</h5>
												<p>Web Design</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-primary" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>Website Markup</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-danger" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>One Page</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-success" role="progressbar" style="width: 89%" aria-valuenow="89" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>Mobile Template</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-warning" role="progressbar" style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>Backend API</p>
												<div class="progress" style="height: 5px">
													<div class="progress-bar bg-info" role="progressbar" style="width: 66%" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
											</div>
										</div>
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
													<input type="text" name="first_name" class="form-control" value="{{ $staff->first_name }}" placeholder="First Name" required />
													<input type="text" name="last_name" class="form-control mt-2" value="{{ $staff->last_name }}" placeholder="Last Name" required />
												</div>
											</div>
											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Email</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="email" name="email" class="form-control" value="{{ $staff->email }}" required />
												</div>
											</div>
											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Phone</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="text" name="phone" class="form-control" value="{{ $staff->phone }}" required />
												</div>
											</div>
											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Role</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<select name="role" class="form-control" required>
														<option value="web_developers" {{ $staff->role == 'web_developers' ? 'selected' : '' }}>Web Developers</option>
														<option value="design_and_graphics" {{ $staff->role == 'design_and_graphics' ? 'selected' : '' }}>Design and Graphics</option>
														<option value="seo_developer" {{ $staff->role == 'seo_developer' ? 'selected' : '' }}>Seo Developer</option>
													</select>
												</div>
											</div>
											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Status</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<select name="status" class="form-control" required>
														<option value="active" {{ $staff->status == 'active' ? 'selected' : '' }}>Active</option>
														<option value="inactive" {{ $staff->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
													</select>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-3"></div>
												<div class="col-sm-9 text-secondary">
													<input type="submit" class="btn btn-primary px-4" value="Save Changes" />
												</div>
											</div>
										</div>
									</div>
								</form>
								
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-body">
												<h5 class="d-flex align-items-center mb-3">Recent Projects</h5>
												<div class="table-responsive">
													<table class="table table-striped">
														<thead>
															<tr>
																<th>Project Name</th>
																<th>Start Date</th>
																<th>Deadline</th>
																<th>Status</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>Project Alpha</td>
																<td>2024-01-15</td>
																<td>2024-07-15</td>
																<td><span class="badge bg-success">Completed</span></td>
															</tr>
															<tr>
																<td>Project Beta</td>
																<td>2024-03-01</td>
																<td>2024-09-01</td>
																<td><span class="badge bg-warning text-dark">In Progress</span></td>
															</tr>
															<tr>
																<td>Project Gamma</td>
																<td>2024-05-20</td>
																<td>2024-12-20</td>
																<td><span class="badge bg-danger">Pending</span></td>
															</tr>
														</tbody>
													</table>
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
</div>

@push('scripts')
<script>
$(function() {
	   "use strict";

	   // Performance Trend Chart (Line)
	   if (document.getElementById("performanceChart")) {
	       var ctx = document.getElementById('performanceChart').getContext('2d');

	       var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
	       gradientStroke1.addColorStop(0, '#00b09b');
	       gradientStroke1.addColorStop(1, '#96c93d');

	       var myChart = new Chart(ctx, {
	           type: 'line',
	           data: {
	               labels: ['Jan', 'Feb', 'Mar', 'Apr'],
	               datasets: [{
	                   label: 'Performance Score',
	                   data: [82, 85, 88, 92],
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
	                       beginAtZero: true,
	                       max: 100
	                   }
	               }
	           }
	       });
	   }

	   // Task Breakdown Chart (Doughnut)
	   if (document.getElementById("taskBreakdownChart")) {
	       var ctx = document.getElementById("taskBreakdownChart").getContext('2d');

	       var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
	       gradientStroke1.addColorStop(0, '#fc4a1a');
	       gradientStroke1.addColorStop(1, '#f7b733');

	       var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
	       gradientStroke2.addColorStop(0, '#4776e6');
	       gradientStroke2.addColorStop(1, '#8e54e9');

	       var gradientStroke3 = ctx.createLinearGradient(0, 0, 0, 300);
	       gradientStroke3.addColorStop(0, '#ee0979');
	       gradientStroke3.addColorStop(1, '#ff6a00');

	       var myChart = new Chart(ctx, {
	           type: 'doughnut',
	           data: {
	               labels: ["High Priority", "Medium Priority", "Low Priority"],
	               datasets: [{
	                   backgroundColor: [
	                       gradientStroke1,
	                       gradientStroke2,
	                       gradientStroke3
	                   ],
	                   hoverBackgroundColor: [
	                       gradientStroke1,
	                       gradientStroke2,
	                       gradientStroke3
	                   ],
	                   data: [65, 25, 10],
	                   borderWidth: [1, 1, 1]
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
});
</script>
@endpush

@endsection