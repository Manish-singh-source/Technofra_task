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
                <div class="breadcrumb-title pe-3">My Projects</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">My Projects</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Welcome Message for Customer -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Welcome, {{ $customer->client_name ?? 'Customer' }}!</h5>
                    <p class="card-text">Here are your projects. You can only view projects that belong to your account.</p>
                </div>
            </div>

            <!-- Project Status Cards -->
            <div class="row mb-3">
                <div class="col-lg-2">
                    <div class="card radius-10 border-start border-0 border-4 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">All</p>
                                    <h4 class="my-1 text-info">{{ $allProjects ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Your projects</p>
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
                                    <p class="mb-0 font-13">Finished successfully</p>
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
                                    <input type="text" class="form-control ps-5 radius-30" placeholder="Search Your Projects" id="searchProjects"> <span
                                        class="position-absolute top-50 product-show translate-middle-y"><i
                                            class="bx bx-search"></i></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0" id="projectsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
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
                                                    <a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No projects found for your account.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Not Started Tab -->
                        <div class="tab-pane fade" id="not-started" role="tabpanel" aria-labelledby="not-started-tab">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
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
                                                    <a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- In Progress Tab -->
                        <div class="tab-pane fade" id="in-progress" role="tabpanel" aria-labelledby="in-progress-tab">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
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
                                                    <a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- On Hold Tab -->
                        <div class="tab-pane fade" id="on-hold" role="tabpanel" aria-labelledby="on-hold-tab">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
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
                                                    <i class='bx bxs-circle me-1'></i>On Hold
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    <a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Finished Tab -->
                        <div class="tab-pane fade" id="finished" role="tabpanel" aria-labelledby="finished-tab">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
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
                                                                <img src="{{ $staff[$memberId]->project_image ? asset('uploads/staff/' . $staff[$memberId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-1" alt="Member" width="30" height="30" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $staff[$memberId]->first_name }} {{ $staff[$memberId]->last_name }}">
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
                                                    <i class='bx bxs-circle me-1'></i>Finished
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    <a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Cancelled Tab -->
                        <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project name</th>
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
                                                <div class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>Cancelled
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    <a href="{{ route('project-details', $project->id) }}" class=""><i class='bx bxs-show'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No projects found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchProjects').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('projectsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        const text = cells[j].textContent || cells[j].innerText;
                        if (text.toLowerCase().indexOf(searchValue) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                if (found) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    </script>
@endsection
