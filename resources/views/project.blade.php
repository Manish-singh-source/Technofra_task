@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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

            <!-- Project Status Cards -->
            <div class="row mb-3">
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">All</p>
                                    <h4 class="my-1 text-info">{{ $allProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Total projects</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-info text-white ms-auto">
                                    <i class='bx bx-list-ul'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-secondary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Planning</p>
                                    <h4 class="my-1 text-secondary">{{ $planningProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Not yet started</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                    <i class='bx bx-circle'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">In Progress</p>
                                    <h4 class="my-1 text-primary">{{ $inProgressProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Currently active</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                    <i class='bx bx-loader-alt'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">On Hold</p>
                                    <h4 class="my-1 text-warning">{{ $onHoldProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Temporarily paused</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                    <i class='bx bx-pause'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Completed</p>
                                    <h4 class="my-1 text-success">{{ $completedProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Finished</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class='bx bx-check'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Cancelled</p>
                                    <h4 class="my-1 text-danger">{{ $cancelledProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Cancelled projects</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-danger text-white ms-auto">
                                    <i class='bx bx-x'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->

            <!-- Status Tabs -->
            <div class="card mt-4">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="projectTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="not-started-tab" data-bs-toggle="tab" data-bs-target="#not-started" type="button" role="tab" aria-controls="not-started" aria-selected="false">Not Started</button>
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
                        <!-- All Tab -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                                @can('create_projects')
                                <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                                @endcan
                            </div>
                            <div class="table-responsive">
                                <table id="projectsTable" class="table mb-0">
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
                                        @forelse($projects as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->customer->client_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->tags)
                                                    @foreach($project->tags as $tag)
                                                        <span class="badge bg-primary">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($project->members)
                                                        @foreach(array_slice($project->members, 0, 2) as $memberId)
                                                            @if(isset($staff[$memberId]))
                                                                <img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
                                                            @endif
                                                        @endforeach
                                                        @if(count($project->members) > 2)
                                                            <span>+{{ count($project->members) - 2 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($project->status == 'not_started')
                                                    <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Not Started
                                                    </div>
                                                @elseif($project->status == 'in_progress')
                                                    <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>In Progress
                                                    </div>
                                                @elseif($project->status == 'on_hold')
                                                    <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>On Hold
                                                    </div>
                                                @elseif($project->status == 'completed')
                                                    <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Finished
                                                    </div>
                                                @elseif($project->status == 'cancelled')
                                                    <div class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Cancelled
                                                    </div>
                                                @else
                                                    <div class="badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>{{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    @can('view_projects')<a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>@endcan
                                                    @can('edit_projects')<a href="{{ route('edit-project', $project->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>@endcan
                                                    @can('delete_projects')<a href="javascript:;" class="ms-3" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this project?')) { document.getElementById('delete-form-{{ $project->id }}').submit(); }"><i class='bx bxs-trash'></i></a>@endcan
                                                    <form id="delete-form-{{ $project->id }}" action="{{ route('project.destroy', $project->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Not Started Tab -->
                        <div class="tab-pane fade" id="not-started" role="tabpanel" aria-labelledby="not-started-tab">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Projects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                            @can('create_projects')
                            <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                            @endcan
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
                                        @forelse($projects->where('status', 'not_started') as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->customer->client_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->tags)
                                                    @foreach($project->tags as $tag)
                                                        <span class="badge bg-primary">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($project->members)
                                                        @foreach(array_slice($project->members, 0, 2) as $memberId)
                                                            @if(isset($staff[$memberId]))
                                                                <img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
                                                            @endif
                                                        @endforeach
                                                        @if(count($project->members) > 2)
                                                            <span>+{{ count($project->members) - 2 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>Not Started
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    @can('view_projects')<a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>@endcan
                                                    @can('edit_projects')<a href="{{ route('edit-project', $project->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>@endcan
                                                    @can('delete_projects')<a href="javascript:;" class="ms-3" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this project?')) { document.getElementById('delete-form-notstarted-{{ $project->id }}').submit(); }"><i class='bx bxs-trash'></i></a>@endcan
                                                    <form id="delete-form-notstarted-{{ $project->id }}" action="{{ route('project.destroy', $project->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
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
                                @can('create_projects')
                                <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                                @endcan
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
                                        @forelse($projects->where('status', 'in_progress') as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->customer->client_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->tags)
                                                    @foreach($project->tags as $tag)
                                                        <span class="badge bg-primary">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($project->members)
                                                        @foreach(array_slice($project->members, 0, 2) as $memberId)
                                                            @if(isset($staff[$memberId]))
                                                                <img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
                                                            @endif
                                                        @endforeach
                                                        @if(count($project->members) > 2)
                                                            <span>+{{ count($project->members) - 2 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>In Progress
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    @can('view_projects')<a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>@endcan
                                                    @can('edit_projects')<a href="{{ route('edit-project', $project->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>@endcan
                                                    @can('delete_projects')<a href="javascript:;" class="ms-3" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this project?')) { document.getElementById('delete-form-inprogress-{{ $project->id }}').submit(); }"><i class='bx bxs-trash'></i></a>@endcan
                                                    <form id="delete-form-inprogress-{{ $project->id }}" action="{{ route('project.destroy', $project->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
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
                                @can('create_projects')
                                <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                                @endcan
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
                                        @forelse($projects->where('status', 'on_hold') as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->customer->client_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->tags)
                                                    @foreach($project->tags as $tag)
                                                        <span class="badge bg-primary">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($project->members)
                                                        @foreach(array_slice($project->members, 0, 2) as $memberId)
                                                            @if(isset($staff[$memberId]))
                                                                <img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
                                                            @endif
                                                        @endforeach
                                                        @if(count($project->members) > 2)
                                                            <span>+{{ count($project->members) - 2 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($project->status == 'not_started')
                                                    <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Not Started
                                                    </div>
                                                @elseif($project->status == 'in_progress')
                                                    <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>In Progress
                                                    </div>
                                                @elseif($project->status == 'on_hold')
                                                    <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>On Hold
                                                    </div>
                                                @elseif($project->status == 'completed')
                                                    <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Finished
                                                    </div>
                                                @elseif($project->status == 'cancelled')
                                                    <div class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Cancelled
                                                    </div>
                                                @else
                                                    <div class="badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>{{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    @can('view_projects')<a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>@endcan
                                                    @can('edit_projects')<a href="{{ route('edit-project', $project->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>@endcan
                                                    @can('delete_projects')<a href="javascript:;" class="ms-3" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this project?')) { document.getElementById('delete-form-onhold-{{ $project->id }}').submit(); }"><i class='bx bxs-trash'></i></a>@endcan
                                                    <form id="delete-form-onhold-{{ $project->id }}" action="{{ route('project.destroy', $project->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
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
                                @can('create_projects')
                                <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                                @endcan
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
                                        @forelse($projects->where('status', 'completed') as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->customer->client_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->tags)
                                                    @foreach($project->tags as $tag)
                                                        <span class="badge bg-primary">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($project->members)
                                                        @foreach(array_slice($project->members, 0, 3) as $memberId)
                                                            @if(isset($staff[$memberId]))
                                                                <img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
                                                            @endif
                                                        @endforeach
                                                        @if(count($project->members) > 3)
                                                            <span>+{{ count($project->members) - 3 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>Finished
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    @can('view_projects')<a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>@endcan
                                                    @can('edit_projects')<a href="{{ route('edit-project', $project->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>@endcan
                                                    @can('delete_projects')<a href="javascript:;" class="ms-3" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this project?')) { document.getElementById('delete-form-finished-{{ $project->id }}').submit(); }"><i class='bx bxs-trash'></i></a>@endcan
                                                    <form id="delete-form-finished-{{ $project->id }}" action="{{ route('project.destroy', $project->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
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
                                @can('create_projects')
                                <div class="ms-auto"><a href="{{ route('add-project') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>Add New Project</a></div>
                                @endcan
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
                                        @forelse($projects->where('status', 'cancelled') as $project)
                                        <tr>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->customer->client_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->tags)
                                                    @foreach($project->tags as $tag)
                                                        <span class="badge bg-primary">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($project->members)
                                                        @foreach(array_slice($project->members, 0, 2) as $memberId)
                                                            @if(isset($staff[$memberId]))
                                                                <img src="{{ $staff[$memberId]->profile_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
                                                            @endif
                                                        @endforeach
                                                        @if(count($project->members) > 2)
                                                            <span>+{{ count($project->members) - 2 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($project->status == 'not_started')
                                                    <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Not Started
                                                    </div>
                                                @elseif($project->status == 'in_progress')
                                                    <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>In Progress
                                                    </div>
                                                @elseif($project->status == 'on_hold')
                                                    <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>On Hold
                                                    </div>
                                                @elseif($project->status == 'completed')
                                                    <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Finished
                                                    </div>
                                                @elseif($project->status == 'cancelled')
                                                    <div class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>Cancelled
                                                    </div>
                                                @else
                                                    <div class="badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3">
                                                        <i class='bx bxs-circle me-1'></i>{{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    @can('view_projects')<a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>@endcan
                                                    @can('edit_projects')<a href="{{ route('edit-project', $project->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>@endcan
                                                    @can('delete_projects')<a href="javascript:;" class="ms-3" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this project?')) { document.getElementById('delete-form-cancelled-{{ $project->id }}').submit(); }"><i class='bx bxs-trash'></i></a>@endcan
                                                    <form id="delete-form-cancelled-{{ $project->id }}" action="{{ route('project.destroy', $project->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
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

@section('scripts')
<script>
    // Initialize tooltips
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Check if DataTable exists before initializing
        if ($('#projectsTable').length) {
            try {
                // Initialize DataTable for projects
                $('#projectsTable').DataTable({
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: {
                        search: "Search Projects:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ projects",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            } catch (e) {
                console.error('DataTable initialization error:', e);
                // Fallback: show table without DataTable
                $('#projectsTable').show();
            }
        }
    });
</script>
@endsection
