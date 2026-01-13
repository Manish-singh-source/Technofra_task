@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Projects</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Projects</li>
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

            <!-- Status Cards -->
            <div class="row">
                <div class="col-12 col-lg-2">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-light-secondary text-secondary px-3 py-2 rounded-3">
                                        <i class="bx bx-circle fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-fill">
                                    <p class="mb-1">Not Started</p>
                                    <h4 class="mb-0">5</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-light-primary text-primary px-3 py-2 rounded-3">
                                        <i class="bx bx-loader-alt fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-fill">
                                    <p class="mb-1">In Progress</p>
                                    <h4 class="mb-0">3</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-light-warning text-warning px-3 py-2 rounded-3">
                                        <i class="bx bx-pause fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-fill">
                                    <p class="mb-1">On Hold</p>
                                    <h4 class="mb-0">2</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-light-success text-success px-3 py-2 rounded-3">
                                        <i class="bx bx-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-fill">
                                    <p class="mb-1">Finished</p>
                                    <h4 class="mb-0">8</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-light-danger text-danger px-3 py-2 rounded-3">
                                        <i class="bx bx-x fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-fill">
                                    <p class="mb-1">Cancelled</p>
                                    <h4 class="mb-0">1</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Status Cards -->

            <!-- Status Tabs -->
            <div class="card mt-4">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="projectTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="not-started-tab" data-bs-toggle="tab" data-bs-target="#not-started" type="button" role="tab" aria-controls="not-started" aria-selected="true">Not Started</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="in-progress-tab" data-bs-toggle="tab" data-bs-target="#in-progress" type="button" role="tab" aria-controls="in-progress" aria-selected="false">In Progress</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="on-hold-tab" data-bs-toggle="tab" data-bs-target="#on-hold" type="button" role="tab" aria-controls="on-hold" aria-selected="false">On Hold</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="finished-tab" data-bs-toggle="tab" data-bs-target="#finished" type="button" role="tab" aria-controls="finished" aria-selected="false">Finished</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab" aria-controls="cancelled" aria-selected="false">Cancelled</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="projectTabsContent">
                        <!-- Not Started Tab -->
                        <div class="tab-pane fade show active" id="not-started" role="tabpanel" aria-labelledby="not-started-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                            <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
                                            <th>Customer</th>
                                            <th>Tags</th>
                                            <th>Start date</th>
                                            <th>Deadline</th>
                                            <th>Members</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>CRM System</td>
                                            <td>Global Enterprises</td>
                                            <td><span class="badge bg-warning">CRM</span> <span class="badge bg-primary">System</span></td>
                                            <td>2023-11-01</td>
                                            <td>2024-01-01</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>Not Started
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    <a href="{{ route('project-details') }}" class=""><i class='bx bxs-show'></i></a>
                                                    <a href="javascript:;" class="ms-3"><i class='bx bxs-edit'></i></a>
                                                    <a href="javascript:;" class="ms-3"><i class='bx bxs-trash'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- In Progress Tab -->
                        <div class="tab-pane fade" id="in-progress" role="tabpanel" aria-labelledby="in-progress-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                                <div class="ms-auto"><a href="#" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
                                            <th>Customer</th>
                                            <th>Tags</th>
                                            <th>Start date</th>
                                            <th>Deadline</th>
                                            <th>Members</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Website Redesign</td>
                                            <td>ABC Corp</td>
                                            <td><span class="badge bg-primary">Web</span> <span class="badge bg-secondary">Design</span></td>
                                            <td>2023-10-01</td>
                                            <td>2023-12-01</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                    <span>+2</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>In Progress
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- On Hold Tab -->
                        <div class="tab-pane fade" id="on-hold" role="tabpanel" aria-labelledby="on-hold-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                                <div class="ms-auto"><a href="#" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
                                            <th>Customer</th>
                                            <th>Tags</th>
                                            <th>Start date</th>
                                            <th>Deadline</th>
                                            <th>Members</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Mobile App Development</td>
                                            <td>XYZ Ltd</td>
                                            <td><span class="badge bg-info">Mobile</span> <span class="badge bg-success">App</span></td>
                                            <td>2023-09-15</td>
                                            <td>2023-11-15</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                    <span>+1</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>On Hold
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Finished Tab -->
                        <div class="tab-pane fade" id="finished" role="tabpanel" aria-labelledby="finished-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                                <div class="ms-auto"><a href="#" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
                                            <th>Customer</th>
                                            <th>Tags</th>
                                            <th>Start date</th>
                                            <th>Deadline</th>
                                            <th>Members</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>E-commerce Platform</td>
                                            <td>Tech Solutions</td>
                                            <td><span class="badge bg-danger">E-commerce</span></td>
                                            <td>2023-08-01</td>
                                            <td>2023-10-01</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>Finished
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Cancelled Tab -->
                        <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                                <div class="ms-auto"><a href="#" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
                                            <th>Customer</th>
                                            <th>Tags</th>
                                            <th>Start date</th>
                                            <th>Deadline</th>
                                            <th>Members</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Data Analytics Dashboard</td>
                                            <td>Data Corp</td>
                                            <td><span class="badge bg-info">Analytics</span> <span class="badge bg-success">Dashboard</span></td>
                                            <td>2023-07-01</td>
                                            <td>2023-09-01</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                    <img src="https://placehold.co/30x30" class="rounded-circle me-1" alt="Member">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>Cancelled
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Status Tabs -->

        </div>
    </div>
    <!--end page wrapper -->
@endsection