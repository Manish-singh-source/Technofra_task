@extends('layout.master')

@section('content')
<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Lead Profile</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item"><a href="#">Leads</a></li>
								<li class="breadcrumb-item active" aria-current="page">View Lead</li>
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
				<div class="container">
					<div class="main-body">
						<div class="row">
							<div class="col-lg-4">
								<div class="card">
									<div class="card-body">
										<div class="d-flex flex-column align-items-center text-center">
											<div class="rounded-circle p-3 bg-primary text-white" style="width: 110px; height: 110px; display: flex; align-items: center; justify-content: center;">
												<i class="bx bx-user" style="font-size: 50px;"></i>
											</div>
											<div class="mt-3">
												<h4>John Doe</h4>
												<p class="text-secondary mb-1">ABC Corp</p>
												<p class="text-muted font-size-sm">john@example.com</p>
											</div>
										</div>
										<hr class="my-4" />
										<ul class="list-group list-group-flush">
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><i class="bx bx-phone me-2"></i>Phone</h6>
												<span class="text-secondary">+1234567890</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><i class="bx bx-globe me-2"></i>Website</h6>
												<span class="text-secondary">www.abccorp.com</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><i class="bx bx-map me-2"></i>Location</h6>
												<span class="text-secondary">New York, USA</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><i class="bx bx-dollar me-2"></i>Value</h6>
												<span class="text-secondary">$5000</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><i class="bx bx-tag me-2"></i>Tags</h6>
												<span class="text-secondary">Hot, Urgent</span>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="col-lg-8">
								<div class="card">
									<div class="card-body">
										<h5 class="card-title">Lead Details</h5>
										<hr />
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Name</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												John Doe
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Company</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												ABC Corp
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Email</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												john@example.com
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Phone</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												+1234567890
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Position</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												Manager
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Address</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												123 Main St, New York, NY 10001
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">City</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												New York
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">State</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												NY
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Country</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												USA
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Zip Code</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												10001
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Lead Value</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												$5000
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Source</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												Website
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Assigned</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												Alice Smith
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Status</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
													<i class='bx bxs-circle me-1'></i>Active
												</div>
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Tags</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												Hot, Urgent
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Last Contact</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												2023-10-01
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Created</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												2023-09-15
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Description</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												This is a sample lead description. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
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
@endsection