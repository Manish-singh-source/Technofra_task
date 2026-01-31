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
                        <li class="breadcrumb-item"><a href="{{ route('leads') }}"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('leads') }}">Leads</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $lead->name ?? 'View Lead' }}</li>
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
        
        <!-- Display success message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                                        <h4>{{ $lead->name ?? 'N/A' }}</h4>
                                        <p class="text-secondary mb-1">{{ $lead->company ?? 'No Company' }}</p>
                                        <p class="text-muted font-size-sm">{{ $lead->email ?? 'No Email' }}</p>
                                        <p class="text-muted font-size-sm">
                                            @switch($lead->status)
                                                @case('new')
                                                    <span class="badge bg-danger">New</span>
                                                    @break
                                                @case('contacted')
                                                    <span class="badge bg-warning">Contacted</span>
                                                    @break
                                                @case('qualified')
                                                    <span class="badge bg-info">Qualified</span>
                                                    @break
                                                @case('converted')
                                                    <span class="badge bg-success">Converted</span>
                                                    @break
                                                @case('lost')
                                                    <span class="badge bg-secondary">Lost</span>
                                                    @break
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                                <hr class="my-4" />
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bx bx-phone me-2"></i>Phone</h6>
                                        <span class="text-secondary">{{ $lead->phone ?? 'N/A' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bx bx-globe me-2"></i>Website</h6>
                                        <span class="text-secondary">{{ $lead->website ?? 'N/A' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bx bx-map me-2"></i>Location</h6>
                                        <span class="text-secondary">{{ $lead->city ?? '' }}{{ $lead->city && $lead->state ? ', ' : '' }}{{ $lead->state ?? '' }}{{ ($lead->city || $lead->state) && $lead->country ? ', ' : '' }}{{ $lead->country ?? '' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bx bx-dollar me-2"></i>Value</h6>
                                        <span class="text-secondary">${{ number_format($lead->lead_value ?? 0, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bx bx-tag me-2"></i>Tags</h6>
                                        <span class="text-secondary">
                                            @if($lead->tags)
                                                @foreach($lead->tags as $tag)
                                                    <span class="badge bg-primary me-1">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bx bx-user-plus me-2"></i>Assigned</h6>
                                        <span class="text-secondary">
                                            @if($lead->assigned)
                                                @foreach($lead->assigned as $assignedId)
                                                    @if(isset($staff[$assignedId]))
                                                        <span class="badge bg-info me-1">{{ $staff[$assignedId]->first_name }}</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                                <div class="mt-4">
                                    <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-primary w-100"><i class='bx bxs-edit'></i> Edit Lead</a>
                                </div>
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
                                        {{ $lead->name ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Company</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->company ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Email</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->email ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Phone</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->phone ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Position</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->position ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Address</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->address ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">City</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->city ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">State</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->state ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Country</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->country ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Zip Code</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->zipCode ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Lead Value</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        ${{ number_format($lead->lead_value ?? 0, 2) }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Source</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $lead->source ?? 'N/A')) }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Status</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        @switch($lead->status)
                                            @case('new')
                                                <span class="badge bg-danger">New</span>
                                                @break
                                            @case('contacted')
                                                <span class="badge bg-warning">Contacted</span>
                                                @break
                                            @case('qualified')
                                                <span class="badge bg-info">Qualified</span>
                                                @break
                                            @case('converted')
                                                <span class="badge bg-success">Converted</span>
                                                @break
                                            @case('lost')
                                                <span class="badge bg-secondary">Lost</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Tags</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        @if($lead->tags)
                                            @foreach($lead->tags as $tag)
                                                <span class="badge bg-primary me-1">{{ $tag }}</span>
                                            @endforeach
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Created</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->created_at->format('Y-m-d H:i:s') }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Last Updated</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->updated_at->format('Y-m-d H:i:s') }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Description</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $lead->description ?? 'No description provided.' }}
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
