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
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Tasks</li>
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
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                            <a class="dropdown-item cursor-pointer" id="delete-selected">Delete All</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->
            
            <!-- Task Summary Cards -->
            <div class="row mb-3">
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Running Tasks</p>
                                    <h4 class="my-1 text-primary">{{ $runningTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Currently in progress</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                    <i class='bx bx-play'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Completed Tasks</p>
                                    <h4 class="my-1 text-success">{{ $completedTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Finished successfully</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class='bx bx-check'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Late Tasks</p>
                                    <h4 class="my-1 text-warning">{{ $lateTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Overdue but not delayed</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                    <i class='bx bx-time-five'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Delayed Tasks</p>
                                    <h4 class="my-1 text-danger">{{ $delayedTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Significantly behind</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                                    <i class='bx bx-error'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
            
            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="ms-auto d-flex gap-2">
                            <div class="btn-group">
                               
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                        <i class="bx bx-upload"></i> Upload Excel File
                                    </a></li>
                                    <li><a class="dropdown-item" href="#">
                                        <i class="bx bx-download"></i> Download Template
                                    </a></li>
                                </ul>
                            </div>
                            <a href="{{route('add-task')}}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
                                <i class="bx bxs-plus-square"></i>Add New Task
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th><input class="form-check-input" type="checkbox" id="select-all"></th>
                                    <th>Task ID</th>
                                    <th>Project & Task</th>
                                    <th>Created On</th>
                                    <th>Total Hours</th>
                                    <th>Priority</th>
                                    <th>Assignee</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="1"></td>
                                    <td>#T001</td>
                                    <td>
                                        <strong>Project:</strong> Website Redesign <br>
                                        <strong>Task:</strong> Design Homepage
                                    </td>
                                    <td>2024-07-15</td>
                                    <td>25</td>
                                    <td><span class="badge bg-danger">High</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/30x30" class="rounded-circle me-2" alt="Assignee">
                                            John Doe
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('task-details') }}"><i class='bx bxs-show'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-edit'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-trash'></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="2"></td>
                                    <td>#T002</td>
                                    <td>
                                        <strong>Project:</strong> Mobile App Development <br>
                                        <strong>Task:</strong> Develop Login Screen
                                    </td>
                                    <td>2024-07-16</td>
                                    <td>18</td>
                                    <td><span class="badge bg-warning text-dark">Medium</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/30x30" class="rounded-circle me-2" alt="Assignee">
                                            Jane Smith
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('task-details', 2) }}"><i class='bx bxs-show'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-edit'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-trash'></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="3"></td>
                                    <td>#T003</td>
                                    <td>
                                        <strong>Project:</strong> CRM System <br>
                                        <strong>Task:</strong> API Integration
                                    </td>
                                    <td>2024-07-17</td>
                                    <td>32</td>
                                    <td><span class="badge bg-danger">High</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/30x30" class="rounded-circle me-2" alt="Assignee">
                                            Peter Jones
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('task-details', 3) }}"><i class='bx bxs-show'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-edit'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-trash'></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="4"></td>
                                    <td>#T004</td>
                                    <td>
                                        <strong>Project:</strong> Website Redesign <br>
                                        <strong>Task:</strong> Create About Us Page
                                    </td>
                                    <td>2024-07-18</td>
                                    <td>12</td>
                                    <td><span class="badge bg-success">Low</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/30x30" class="rounded-circle me-2" alt="Assignee">
                                            Mary Jane
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('task-details', 4) }}"><i class='bx bxs-show'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-edit'></i></a>
                                            <a href="javascript:;" class="ms-2"><i class='bx bxs-trash'></i></a>
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
    <!--end page wrapper -->
@endsection
