@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">

            @include('layout.errors')

            @php
                $isDeleted = $staff->trashed();
                $staffDepartments = is_array($staff->departments)
                    ? $staff->departments
                    : (json_decode($staff->departments, true) ?:
                    []);
                $currentSpatieRole = old('role', optional($staff->roles->first())->name);
            @endphp

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Staff</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">View Staff</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <a href="{{ route('staff') }}" class="btn btn-outline-secondary">Back To Staff</a>
                    @if ($isDeleted)
                        <form method="POST" action="{{ route('staff.restore', $staff->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">Restore Staff</button>
                        </form>
                        <form method="POST" action="{{ route('staff.force-delete', $staff->id) }}"
                            onsubmit="return confirm('Are you sure you want to permanently delete this staff member? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Permanent Delete</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('staff.destroy', $staff->id) }}"
                            onsubmit="return confirm('Are you sure you want to delete this staff member?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Delete Staff</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card radius-10 border-start border-0 border-4 border-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Logged Time</p>
                                    <h4 class="my-1 text-primary">{{ $loggedTimeStats['total_formatted'] ?? '00:00' }}</h4>
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
                                    <h4 class="my-1 text-info">{{ $loggedTimeStats['last_month_formatted'] ?? '00:00' }}
                                    </h4>
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
                                    <h4 class="my-1 text-success">{{ $loggedTimeStats['this_month_formatted'] ?? '00:00' }}
                                    </h4>
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
                                    <h4 class="my-1 text-warning">{{ $loggedTimeStats['this_week_formatted'] ?? '00:00' }}
                                    </h4>
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

            <div class="row">
                <div class="main-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <img src="{{ $staff->profile_image ? asset($staff->profile_image) : 'https://placehold.co/100x100' }}"
                                            alt="Admin" class="rounded-circle p-1 bg-primary" width="100"
                                            height="100">
                                        <div class="mt-3">
                                            <h4>{{ $staff->first_name . ' ' . $staff->last_name }}</h4>
                                            <p class="text-secondary mb-1">
                                                {{ $currentSpatieRole ? ucwords(str_replace('_', ' ', $currentSpatieRole)) : 'N/A' }}
                                            </p>
                                            <p class="text-muted font-size-sm">{{ $staff->email }}</p>

                                        </div>
                                    </div>
                                    <hr class="my-4" />
                                    <ul class="list-group list-group-flush">
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-user me-2'></i>Role</h6>
                                            <span
                                                class="text-secondary">{{ $currentSpatieRole ? ucwords(str_replace('_', ' ', $currentSpatieRole)) : 'N/A' }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-group me-2'></i>Team</h6>
                                            <span class="text-secondary">{{ $staff->team ?? 'N/A' }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-check-circle me-2'></i>Status</h6>
                                            <span class="text-secondary">
                                                @if ($isDeleted)
                                                    <span class="badge bg-secondary">Deleted</span>
                                                @elseif ($staff->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-phone me-2'></i>Phone</h6>
                                            <span class="text-secondary">{{ $staff->phone }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class='bx bx-buildings me-2'></i>Departments</h6>
                                            <span class="text-secondary">
                                                @if (!empty($staffDepartments))
                                                    {{ implode(', ', $staffDepartments) }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-8">
                            <form method="POST" action="{{ route('staff.update', $staff->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="card">
                                    <div class="card-body">
                                        @if ($isDeleted)
                                            <div class="alert alert-warning">
                                                This staff member is soft deleted. Editing is disabled until the record is
                                                restored.
                                            </div>
                                        @endif

                                        <fieldset {{ $isDeleted ? 'disabled' : '' }}>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Profile Image</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <input type="file" name="profileImage" class="form-control"
                                                        accept="image/*" />
                                                    <div class="form-text">JPG, PNG, GIF, WEBP. Max 2MB.</div>
                                                    @error('profileImage')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                    @if ($staff->profile_image)
                                                        <div class="mt-2">
                                                            <img src="{{ asset($staff->profile_image) }}"
                                                                alt="Current Profile Image" class="img-thumbnail"
                                                                style="max-height: 90px;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Full Name</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <input type="text" name="first_name" class="form-control"
                                                        value="{{ $staff->first_name }}" placeholder="First Name"
                                                        required />
                                                    <input type="text" name="last_name" class="form-control mt-2"
                                                        value="{{ $staff->last_name }}" placeholder="Last Name"
                                                        required />
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Email</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ $staff->email }}" required />
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Phone</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <input type="text" name="phone" class="form-control"
                                                        value="{{ $staff->phone }}" required />
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Role</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <select name="role" class="form-control" required>
                                                        <option value="">Select Role</option>
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->name }}"
                                                                {{ $currentSpatieRole == $role->name ? 'selected' : '' }}>
                                                                {{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Status</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <select name="status" class="form-control" required>
                                                        <option value="active"
                                                            {{ $staff->status == 'active' ? 'selected' : '' }}>Active
                                                        </option>
                                                        <option value="inactive"
                                                            {{ $staff->status == 'inactive' ? 'selected' : '' }}>Inactive
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Team</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    <select name="team" class="form-control">
                                                        <option value="">Select Team</option>
                                                        @foreach ($teams as $team)
                                                            <option value="{{ $team }}"
                                                                {{ $staff->team == $team ? 'selected' : '' }}>
                                                                {{ $team }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-3">
                                                    <h6 class="mb-0">Departments</h6>
                                                </div>
                                                <div class="col-sm-9 text-secondary">
                                                    @forelse ($departments as $department)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="departments[]" value="{{ $department }}"
                                                                {{ in_array($department, $staffDepartments, true) ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $department }}</label>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted small">No departments found.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-3"></div>
                                                <div class="col-sm-9 text-secondary">
                                                    <input type="submit" class="btn btn-primary px-4"
                                                        value="Save Changes" />
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </form>



                        </div>



                    </div>
                </div>


            </div>


            <div class="card mb-4">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="staffInsightsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="leads-tab" data-bs-toggle="tab"
                                data-bs-target="#leadsTabPane" type="button" role="tab"
                                aria-controls="leadsTabPane" aria-selected="true">
                                Leads
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="projects-tab" data-bs-toggle="tab"
                                data-bs-target="#projectsTabPane" type="button" role="tab"
                                aria-controls="projectsTabPane" aria-selected="false">
                                Projects
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="staffInsightsTabsContent">
                        <div class="tab-pane fade show active" id="leadsTabPane" role="tabpanel"
                            aria-labelledby="leads-tab" tabindex="0">
                            @php
                                $analytics = $staffLeadAnalytics ?? ['kpis' => [], 'charts' => [], 'overdue_followups' => [], 'recent_activities' => [], 'top_metrics' => []];
                                $kpis = $analytics['kpis'] ?? [];
                                $topMetrics = $analytics['top_metrics'] ?? [];
                            @endphp
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div
                                                class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                                <h5 class="mb-0">Lead & Followup KPI Dashboard</h5>
                                                <form method="GET" action="{{ route('view-staff', $staff->id) }}"
                                                    class="d-flex flex-wrap gap-2">
                                                    <select name="period" class="form-select form-select-sm"
                                                        style="min-width: 180px;">
                                                        <option value="7d"
                                                            {{ ($analyticsFilters['period'] ?? '30d') === '7d' ? 'selected' : '' }}>
                                                            Last 7 days</option>
                                                        <option value="30d"
                                                            {{ ($analyticsFilters['period'] ?? '30d') === '30d' ? 'selected' : '' }}>
                                                            Last 30 days</option>
                                                        <option value="this_month"
                                                            {{ ($analyticsFilters['period'] ?? '30d') === 'this_month' ? 'selected' : '' }}>
                                                            This month</option>
                                                        <option value="this_quarter"
                                                            {{ ($analyticsFilters['period'] ?? '30d') === 'this_quarter' ? 'selected' : '' }}>
                                                            This quarter</option>
                                                        <option value="this_year"
                                                            {{ ($analyticsFilters['period'] ?? '30d') === 'this_year' ? 'selected' : '' }}>
                                                            This year</option>
                                                        <option value="custom"
                                                            {{ ($analyticsFilters['period'] ?? '30d') === 'custom' ? 'selected' : '' }}>
                                                            Custom Range</option>
                                                    </select>
                                                    <input type="date" name="from"
                                                        value="{{ $analyticsFilters['from'] ?? '' }}"
                                                        class="form-control form-control-sm">
                                                    <input type="date" name="to"
                                                        value="{{ $analyticsFilters['to'] ?? '' }}"
                                                        class="form-control form-control-sm">
                                                    <button class="btn btn-sm btn-primary">Apply</button>
                                                </form>
                                            </div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-primary h-100">
                                                        <div class="card-body p-3"><small>Total Leads</small>
                                                            <h5 class="mb-0">{{ $kpis['total_leads_assigned'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-info h-100">
                                                        <div class="card-body p-3"><small>Active Leads</small>
                                                            <h5 class="mb-0">{{ $kpis['active_leads'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-success h-100">
                                                        <div class="card-body p-3"><small>Converted</small>
                                                            <h5 class="mb-0">{{ $kpis['converted_leads'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-danger h-100">
                                                        <div class="card-body p-3"><small>Lost Leads</small>
                                                            <h5 class="mb-0">{{ $kpis['lost_leads'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-warning h-100">
                                                        <div class="card-body p-3"><small>Total Followups</small>
                                                            <h5 class="mb-0">{{ $kpis['total_followups'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-secondary h-100">
                                                        <div class="card-body p-3"><small>Pending Followups</small>
                                                            <h5 class="mb-0">{{ $kpis['pending_followups'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-warning h-100">
                                                        <div class="card-body p-3"><small>Overdue</small>
                                                            <h5 class="mb-0">{{ $kpis['overdue_followups'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-primary h-100">
                                                        <div class="card-body p-3"><small>Today's Followups</small>
                                                            <h5 class="mb-0">{{ $kpis['todays_followups'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-info h-100">
                                                        <div class="card-body p-3"><small>Meetings</small>
                                                            <h5 class="mb-0">{{ $kpis['meetings_scheduled'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div
                                                        class="card border-start border-0 border-3 border-success h-100">
                                                        <div class="card-body p-3"><small>Conversion %</small>
                                                            <h5 class="mb-0">{{ $kpis['conversion_rate'] ?? 0 }}%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-dark h-100">
                                                        <div class="card-body p-3"><small>Avg Response</small>
                                                            <h5 class="mb-0">{{ $kpis['avg_response_time_hours'] ?? 0 }}h
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-dark h-100">
                                                        <div class="card-body p-3"><small>Avg Conversion</small>
                                                            <h5 class="mb-0">{{ $kpis['avg_conversion_time_days'] ?? 0 }}d
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Monthly Lead Conversion</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffMonthlyLeadChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Monthly Followup Activity</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffFollowupActivityChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Lead Status Distribution</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffLeadStatusChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Followup Outcome Distribution</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffOutcomeChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Daily Activity Timeline (30d)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffDailyActivityChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Assigned vs Converted (Monthly)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffAssignedVsConvertedChart"
                                                                style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- 
                                                <div class="col-lg-12">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Overdue Followup Trend</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffOverdueTrendChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-7">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Overdue Followups</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Lead</th>
                                                            <th>Next Followup</th>
                                                            <th>Overdue Days</th>
                                                            <th>Priority</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse(($analytics['overdue_followups'] ?? []) as $of)
                                                            <tr>
                                                                <td>{{ $of['name'] }}</td>
                                                                <td>{{ $of['next_followup_at'] }}</td>
                                                                <td><span
                                                                        class="badge bg-warning text-dark">{{ $of['overdue_days'] }}</span>
                                                                </td>
                                                                <td>{{ ucfirst($of['priority']) }}</td>
                                                                <td>{{ strtoupper($of['status']) }}</td>
                                                                <td><a class="btn btn-sm btn-outline-primary"
                                                                        href="{{ route('lead-management.show', ['source' => 'lead', 'id' => $of['id']]) }}">Open</a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center">No overdue
                                                                    followups.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0">Top Performance Metrics</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Fastest Response</span><strong>{{ $topMetrics['fastest_response_time_hours'] ?? 0 }}h</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Highest Conversion
                                                        Lead</span><strong>{{ $topMetrics['highest_conversion_lead'] ?? '-' }}</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Best Conversion
                                                        Month</span><strong>{{ $topMetrics['best_conversion_month'] ?? '-' }}</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Most Active Day</span><strong>{{ $topMetrics['most_active_day'] ?? '-' }}</strong>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between">
                                                    <span>Avg Followups / Lead</span><strong>{{ $topMetrics['avg_followups_per_lead'] ?? 0 }}</strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Recent Lead Activities</h6>
                                        </div>
                                        <div class="card-body">
                                            @forelse(($analytics['recent_activities'] ?? []) as $activity)
                                                <div class="border-start border-3 border-primary ps-3 mb-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <div class="fw-semibold">
                                                                {{ ucwords(str_replace('_', ' ', $activity['activity_type'])) }}
                                                            </div>
                                                            <div class="text-muted small">{{ $activity['description'] }}
                                                            </div>
                                                            @if (!empty($activity['lead_name']))
                                                                <div class="small">Lead: {{ $activity['lead_name'] }}</div>
                                                            @endif
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ $activity['created_at'] }}</small>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-muted mb-0">No recent lead activities found.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        <div class="tab-pane fade" id="projectsTabPane" role="tabpanel"
                            aria-labelledby="projects-tab" tabindex="0">
                            @php
                                $projectAnalytics = $staffProjectAnalytics ?? ['kpis' => [], 'charts' => []];
                                $projectKpis = $projectAnalytics['kpis'] ?? [];
                            @endphp
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                                <h5 class="mb-0">Project & Task Analytics</h5>
                                                <span class="text-muted small">Overview of projects and workload assigned to this staff member</span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-primary h-100">
                                                        <div class="card-body p-3">
                                                            <small>Total Projects</small>
                                                            <h5 class="mb-0">{{ $projectKpis['total_projects'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-info h-100">
                                                        <div class="card-body p-3">
                                                            <small>Active Projects</small>
                                                            <h5 class="mb-0">{{ $projectKpis['active_projects'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-success h-100">
                                                        <div class="card-body p-3">
                                                            <small>Completed Projects</small>
                                                            <h5 class="mb-0">{{ $projectKpis['completed_projects'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-warning h-100">
                                                        <div class="card-body p-3">
                                                            <small>Overdue Projects</small>
                                                            <h5 class="mb-0">{{ $projectKpis['overdue_projects'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-secondary h-100">
                                                        <div class="card-body p-3">
                                                            <small>Total Tasks</small>
                                                            <h5 class="mb-0">{{ $projectKpis['total_tasks'] ?? 0 }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-2 col-md-4 col-sm-6">
                                                    <div class="card border-start border-0 border-3 border-dark h-100">
                                                        <div class="card-body p-3">
                                                            <small>Avg Progress</small>
                                                            <h5 class="mb-0">{{ $projectKpis['avg_progress'] ?? 0 }}%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-1">
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Project Status Distribution</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffProjectStatusChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Task Status Distribution</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffTaskStatusChart" style="height:300px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Monthly Project Timeline</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffMonthlyProjectActivityChart" style="min-height:320px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                                                                <span><i class="bx bxs-circle text-secondary me-1"></i>Not Started / Pending</span>
                                                                <span><i class="bx bxs-circle text-primary me-1"></i>In Progress</span>
                                                                <span><i class="bx bxs-circle text-danger me-1"></i>Overdue</span>
                                                                <span><i class="bx bxs-circle text-success me-1"></i>Finished</span>
                                                            </div>
                                                            <div class="table-responsive mt-3">
                                                                <table class="table table-sm align-middle mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Project</th>
                                                                            <th>Start Date</th>
                                                                            <th>End Date</th>
                                                                            <th>Status Snapshot</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse ($projectTimelineRows as $timelineRow)
                                                                            <tr>
                                                                                <td class="fw-semibold">{{ $timelineRow['project_name'] }}</td>
                                                                                <td>{{ $timelineRow['start_label'] }}</td>
                                                                                <td>{{ $timelineRow['end_label'] }}</td>
                                                                                <td class="{{ $timelineRow['status_text_class'] ?? ($timelineRow['is_overdue'] ? 'text-danger' : 'text-success') }}">
                                                                                    {{ $timelineRow['difference_label'] }}
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="4" class="text-muted">No projects with both a start date and end date were found.</td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="card h-100">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Task Status Overview</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="staffTaskCompletionChart" style="min-height:320px;">
                                                                <div class="text-muted small">Charts loading...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="d-flex align-items-center mb-3">Recent Projects</h5>
                                            <div class="table-responsive">
                                                <table id="table" class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Project Name</th>
                                                            <th>Start Date</th>
                                                            <th>Deadline</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($projects as $project)
                                                            <tr>
                                                                <td>{{ $project->project_name }}</td>
                                                                <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}
                                                                </td>
                                                                <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    @if ($project->status == 'completed')
                                                                        <span
                                                                            class="badge bg-success">Completed</span>
                                                                    @elseif($project->status == 'in_progress')
                                                                        <span
                                                                            class="badge bg-warning text-dark">In
                                                                            Progress</span>
                                                                    @elseif($project->status == 'pending')
                                                                        <span class="badge bg-danger">Pending</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center">No projects found
                                                                    for this staff member.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="d-flex align-items-center mb-3">Recent Tasks</h5>
                                            <div class="table-responsive">
                                                <table id="tasks-table" class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Task Name</th>
                                                            <th>Project</th>
                                                            <th>Due Date</th>
                                                            <th>Status</th>
                                                            <th>Priority</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($tasks ?? [] as $task)
                                                            <tr>
                                                                <td>{{ $task->title ?? 'N/A' }}</td>
                                                                <td>{{ $task->project->project_name ?? 'N/A' }}</td>
                                                                <td>{{ $task->deadline ? $task->deadline->format('Y-m-d') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    @if ($task->status == 'completed')
                                                                        <span
                                                                            class="badge bg-success">Completed</span>
                                                                    @elseif($task->status == 'in_progress')
                                                                        <span
                                                                            class="badge bg-warning text-dark">In
                                                                            Progress</span>
                                                                    @elseif($task->status == 'pending')
                                                                        <span class="badge bg-danger">Pending</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($task->priority == 'high')
                                                                        <span class="badge bg-danger">High</span>
                                                                    @elseif($task->priority == 'medium')
                                                                        <span
                                                                            class="badge bg-warning text-dark">Medium</span>
                                                                    @elseif($task->priority == 'low')
                                                                        <span class="badge bg-success">Low</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-secondary">{{ ucfirst($task->priority ?? 'N/A') }}</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center">No tasks found for
                                                                    this staff member.</td>
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
                </div>
            </div>
        </div>

    </div>

    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            // Visible sanity marker: if this never updates, this script isn't running.
            document.documentElement.setAttribute('data-staff-charts-js', 'running');

            // If something breaks later (missing libs, blocked CDN, fetch errors),
            // we still want to show a visible hint inside the chart containers.
            const chartEls = [
                '#staffMonthlyLeadChart',
                '#staffFollowupActivityChart',
                '#staffLeadStatusChart',
                '#staffOutcomeChart',
                '#staffDailyActivityChart',
                '#staffAssignedVsConvertedChart',
                '#staffOverdueTrendChart',
                '#staffProjectStatusChart',
                '#staffTaskStatusChart',
                '#staffMonthlyProjectActivityChart',
                '#staffTaskCompletionChart',
            ];
            chartEls.forEach((sel) => {
                const el = document.querySelector(sel);
                if (el && !el.innerHTML.trim()) {
                    el.innerHTML = '<div class="text-muted small">Loading charts...</div>';
                }
            });

            const $ = window.jQuery;

            // Monthly Performance Trend Chart
            if ($ && $('#performanceChart').length && window.Chart) {
                var ctx = document.getElementById('performanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Performance',
                            data: [82, 85, 88, 92, 89, 95],
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
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

            // Task Completion Breakdown Chart
            if ($ && $('#taskBreakdownChart').length && window.Chart) {
                var ctx2 = document.getElementById('taskBreakdownChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['High Priority', 'Medium Priority', 'Low Priority'],
                        datasets: [{
                            data: [65, 25, 10],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Initialize DataTable for Projects
            if ($ && $.fn && $.fn.DataTable && $('#table').length) {
                try {
                    $('#table').DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                } catch (e) {
                    console.error('DataTables init failed for #table:', e);
                }
            }

            // Initialize DataTable for Tasks
            if ($ && $.fn && $.fn.DataTable && $('#tasks-table').length) {
                try {
                    $('#tasks-table').DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                } catch (e) {
                    console.error('DataTables init failed for #tasks-table:', e);
                }
            }

            // Fix DataTable column sizing when project tab becomes visible.
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tabButton) {
                tabButton.addEventListener('shown.bs.tab', function() {
                    if ($ && $.fn && $.fn.DataTable) {
                        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                    }
                });
            });

            // Filter functionality
            if ($) {
                $('.filter-option').on('click', function() {
                    var filterType = $(this).data('filter');
                    console.log('Filter applied: ' + filterType);
                    // Add your filter logic here
                    // You can filter the table data based on the selected filter type
                });
            }

            const analyticsUrl = @json(route('staff.analytics', $staff->id));
            const initialAnalytics = @json($analytics);
            const initialProjectAnalytics = @json($staffProjectAnalytics);
            const projectAnalyticsData = initialProjectAnalytics || {};
            const projectTimelineData = @json($projectTimelineRows);
            const params = new URLSearchParams(window.location.search);
            const charts = {};

            function renderOrUpdateChart(key, el, options) {
                if (!document.querySelector(el) || !window.ApexCharts) return;
                if (charts[key]) {
                    charts[key].updateOptions(options, true, true);
                    return;
                }
                charts[key] = new ApexCharts(document.querySelector(el), options);
                charts[key].render();
            }

            function normalizeDonutData(dataset, fallbackLabel) {
                let labels = Array.isArray(dataset?.labels) ? dataset.labels : [];
                let series = Array.isArray(dataset?.series) ? dataset.series : [];

                // Support API payloads where series may arrive as an object map.
                if (!series.length && dataset?.series && typeof dataset.series === 'object') {
                    labels = Object.keys(dataset.series);
                    series = Object.values(dataset.series);
                }

                labels = labels.map((label) => String(label ?? 'Unknown'));
                series = series.map((value) => Number(value) || 0);

                const minLen = Math.min(labels.length, series.length);
                labels = labels.slice(0, minLen);
                series = series.slice(0, minLen);

                if (!labels.length || !series.some((v) => v > 0)) {
                    return { labels: [fallbackLabel], series: [1] };
                }

                return { labels, series };
            }

            function normalizeTaskStatusOverview(dataset) {
                const rows = Array.isArray(dataset?.data) ? dataset.data : [];
                const labels = rows.map((item) => String(item?.status ?? 'Unknown'));
                const series = rows.map((item) => Number(item?.count) || 0);
                const colors = Array.isArray(dataset?.colors) && dataset.colors.length
                    ? dataset.colors
                    : ['#6c757d', '#0d6efd', '#dc3545', '#198754'];

                return {
                    rows,
                    labels,
                    series,
                    colors,
                };
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderMonthlyProjectTimeline() {
                const target = '#staffMonthlyProjectActivityChart';
                const el = document.querySelector(target);

                if (!el) {
                    return;
                }

                const timelineRows = Array.isArray(projectTimelineData) ? projectTimelineData : [];

                if (!timelineRows.length) {
                    el.innerHTML = '<div class="text-muted small">No dated projects available for the timeline chart.</div>';
                    return;
                }

                const series = [{
                    name: 'Project Duration',
                    data: timelineRows.map((item) => ({
                        x: item.project_name,
                        y: [new Date(item.start_date).getTime(), new Date(item.end_date).getTime()],
                        fillColor: item.bar_color || (item.is_overdue ? '#dc3545' : '#198754'),
                    })),
                }];

                renderOrUpdateChart('monthlyProjectActivity', target, {
                    chart: {
                        type: 'rangeBar',
                        height: Math.max(300, (timelineRows.length * 38) + 60),
                        toolbar: { show: false },
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            rangeBarGroupRows: true,
                            barHeight: '70%',
                        },
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    grid: {
                        strokeDashArray: 4,
                    },
                    series,
                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false,
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '12px',
                            },
                        },
                    },
                    tooltip: {
                        custom: function({ dataPointIndex }) {
                            const item = timelineRows[dataPointIndex];
                            if (!item) {
                                return '';
                            }

                            return `
                                <div class="p-2">
                                    <div class="fw-semibold mb-1">${escapeHtml(item.project_name)}</div>
                                    <div class="small text-muted">Start: ${escapeHtml(item.start_label)}</div>
                                    <div class="small text-muted">End: ${escapeHtml(item.end_label)}</div>
                                    <div class="small ${item.is_overdue ? 'text-danger' : 'text-success'}">${escapeHtml(item.difference_label)}</div>
                                </div>
                            `;
                        },
                    },
                    colors: timelineRows.map((item) => item.bar_color || (item.is_overdue ? '#dc3545' : '#198754')),
                });
            }

            function renderTaskStatusOverview() {
                const target = '#staffTaskCompletionChart';
                const el = document.querySelector(target);

                if (!el) {
                    return;
                }

                const taskStatusOverview = normalizeTaskStatusOverview(projectAnalyticsData.charts?.task_status_overview);

                if (!taskStatusOverview.labels.length) {
                    el.innerHTML = '<div class="text-muted small">No task status data available for the overview chart.</div>';
                    return;
                }

                const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark'
                    || document.body.classList.contains('dark-mode')
                    || document.body.classList.contains('dark-theme');
                const chartTextColor = isDarkMode ? '#e9ecef' : '#495057';
                const gridColor = isDarkMode ? '#495057' : '#edf2f7';
                const tooltipTheme = isDarkMode ? 'dark' : 'light';

                renderOrUpdateChart('taskStatusOverview', target, {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: { show: false },
                        foreColor: chartTextColor,
                    },
                    theme: {
                        mode: isDarkMode ? 'dark' : 'light',
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            distributed: true,
                            columnWidth: '54%',
                            borderRadius: 10,
                            borderRadiusApplication: 'end',
                        },
                    },
                    dataLabels: {
                        enabled: true,
                        offsetY: -18,
                        formatter: function(val) {
                            return `${val}`;
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 700,
                            colors: [chartTextColor],
                        },
                    },
                    stroke: {
                        width: 0,
                    },
                    grid: {
                        borderColor: gridColor,
                        strokeDashArray: 4,
                        padding: {
                            top: 8,
                        },
                    },
                    series: [{
                        name: 'Tasks',
                        data: taskStatusOverview.series,
                    }],
                    colors: taskStatusOverview.colors,
                    xaxis: {
                        categories: taskStatusOverview.labels,
                        labels: {
                            style: {
                                colors: taskStatusOverview.labels.map(() => chartTextColor),
                                fontWeight: 600,
                            },
                        },
                        axisBorder: {
                            color: gridColor,
                        },
                        axisTicks: {
                            color: gridColor,
                        },
                    },
                    yaxis: {
                        min: 0,
                        title: {
                            text: 'Number of Tasks',
                            style: {
                                color: chartTextColor,
                            },
                        },
                        labels: {
                            style: {
                                colors: [chartTextColor],
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        custom: function({ dataPointIndex }) {
                            const item = taskStatusOverview.rows[dataPointIndex];

                            if (!item) {
                                return '';
                            }

                            return `
                                <div class="p-2">
                                    <div class="fw-semibold mb-1">${escapeHtml(item.status)}</div>
                                    <div class="small text-muted">Tasks: ${escapeHtml(item.count)}</div>
                                </div>
                            `;
                        },
                    },
                    legend: {
                        show: false,
                    },
                    fill: {
                        opacity: 1,
                    },
                    responsive: [{
                        breakpoint: 576,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '66%',
                                },
                            },
                            dataLabels: {
                                offsetY: -14,
                            },
                        },
                    }],
                });
            }

            function renderAnalytics(payload) {
                const c = payload.charts || {};
                const statusDistribution = normalizeDonutData(
                    c.lead_status_distribution,
                    'No lead status data'
                );

                // Clear loading placeholders once we have ApexCharts.
                chartEls.forEach((sel) => {
                    const el = document.querySelector(sel);
                    if (el && el.textContent && (el.textContent.includes('Loading charts') || el.textContent.includes('Charts loading') || el.textContent.includes('Loading chart library'))) {
                        el.innerHTML = '';
                    }
                });

                renderOrUpdateChart('leadConversion', '#staffMonthlyLeadChart', {
                    chart: { type: 'line', height: 300, toolbar: { show: false } },
                    series: [
                        { name: 'Assigned', data: c.monthly_lead_conversion?.assigned || [] },
                        { name: 'Converted', data: c.monthly_lead_conversion?.converted || [] }
                    ],
                    xaxis: { categories: c.monthly_lead_conversion?.labels || [] },
                    colors: ['#0d6efd', '#15ca20'],
                    stroke: { curve: 'smooth', width: 3 }
                });

                renderOrUpdateChart('followupActivity', '#staffFollowupActivityChart', {
                    chart: { type: 'bar', height: 300, stacked: false, toolbar: { show: false } },
                    series: c.monthly_followup_activity?.series || [],
                    xaxis: { categories: c.monthly_followup_activity?.labels || [] }
                });

                renderOrUpdateChart('statusDistribution', '#staffLeadStatusChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: statusDistribution.labels,
                    series: statusDistribution.series,
                    colors: ['#0d6efd', '#6c757d', '#17a2b8', '#198754', '#ffc107', '#212529', '#fd7e14', '#28a745', '#dc3545', '#adb5bd']
                });

                renderOrUpdateChart('outcomeDistribution', '#staffOutcomeChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: c.followup_outcome_distribution?.labels || [],
                    series: c.followup_outcome_distribution?.series || [],
                });

                renderOrUpdateChart('dailyActivity', '#staffDailyActivityChart', {
                    chart: { type: 'area', height: 300, toolbar: { show: false } },
                    series: [
                        { name: 'Followups', data: c.daily_activity_timeline?.followups || [] },
                        { name: 'Activities', data: c.daily_activity_timeline?.activities || [] }
                    ],
                    xaxis: { categories: c.daily_activity_timeline?.labels || [] },
                    colors: ['#198754', '#0dcaf0']
                });

                renderOrUpdateChart('assignedVsConverted', '#staffAssignedVsConvertedChart', {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [
                        { name: 'Assigned', data: c.monthly_assigned_vs_converted?.assigned || [] },
                        { name: 'Converted', data: c.monthly_assigned_vs_converted?.converted || [] }
                    ],
                    xaxis: { categories: c.monthly_assigned_vs_converted?.labels || [] },
                    colors: ['#0d6efd', '#28a745']
                });

                renderOrUpdateChart('overdueTrend', '#staffOverdueTrendChart', {
                    chart: { type: 'line', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Overdue Followups', data: c.overdue_followup_trend?.series || [] }],
                    xaxis: { categories: c.overdue_followup_trend?.labels || [] },
                    colors: ['#fd7e14'],
                    stroke: { curve: 'smooth', width: 2 }
                });

                renderMonthlyProjectTimeline();
                renderTaskStatusOverview();
            }

            function renderProjectAnalytics() {
                const c = projectAnalyticsData.charts || {};
                const projectStatus = normalizeDonutData(
                    c.project_status_distribution,
                    'No project status data'
                );
                const taskStatus = normalizeDonutData(
                    c.task_status_distribution,
                    'No task status data'
                );

                renderOrUpdateChart('projectStatus', '#staffProjectStatusChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: projectStatus.labels,
                    series: projectStatus.series,
                    colors: ['#0d6efd', '#17a2b8', '#198754', '#ffc107', '#dc3545'],
                });

                renderOrUpdateChart('taskStatus', '#staffTaskStatusChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: taskStatus.labels,
                    series: taskStatus.series,
                    colors: ['#0d6efd', '#17a2b8', '#198754', '#ffc107', '#dc3545'],
                });
            }

            function fetchAnalyticsAndRender() {
                fetch(analyticsUrl + (params.toString() ? '?' + params.toString() : ''), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(payload => {
                    renderAnalytics(payload);
                    renderProjectAnalytics();
                })
                .catch((error) => {
                    console.error('Failed loading staff analytics API, rendering initial payload:', error);
                    renderAnalytics(initialAnalytics || {});
                    renderProjectAnalytics();
                });
            }

            if (!window.ApexCharts) {
                console.error('ApexCharts library not loaded.');
                chartEls.forEach((sel) => {
                    const el = document.querySelector(sel);
                    if (el) {
                        el.innerHTML = '<div class="text-muted small">ApexCharts library not loaded (local asset missing).</div>';
                    }
                });
                return;
            }

            fetchAnalyticsAndRender();

            // If the library never loads (blocked network/CSP) and the Promise doesn't resolve,
            // ensure the user sees something actionable.
            setTimeout(() => {
                if (window.ApexCharts) return;
                chartEls.forEach((sel) => {
                    const el = document.querySelector(sel);
                    if (el && el.textContent && (el.textContent.includes('Charts loading') || el.textContent.includes('Loading chart library'))) {
                        el.innerHTML = '<div class="text-muted small">Charts not loading. Check browser console/network for blocked scripts.</div>';
                    }
                });
            }, 7000);
        }, { once: true });
    </script>
@endsection
