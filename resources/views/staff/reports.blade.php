@extends('layout.master')

@push('styles')
    <style>
        .crm-reports-page .crm-card {
            border: 1px solid #e6ecf5;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(27, 59, 107, 0.05);
        }

        .crm-reports-page .crm-report-nav .nav-link {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.58rem 1.25rem;
            color: #475569;
            border: 1px solid #dce5f2;
            background: #ffffff;
        }

        .crm-reports-page .crm-report-nav .nav-link.active {
            background: #0d6efd;
            color: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.22);
        }

        .crm-reports-page .crm-avatar-sm,
        .crm-reports-page .crm-avatar-sm-initials {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            flex-shrink: 0;
        }

        .crm-reports-page .crm-avatar-sm-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e0edff;
            color: #0d6efd;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content crm-reports-page">

            @include('layout.errors')

            @php
                $analytics = $staffLeadAnalytics ?? ['kpis' => [], 'charts' => [], 'overdue_followups' => [], 'recent_activities' => [], 'top_metrics' => []];
                $kpis = $analytics['kpis'] ?? [];
                $topMetrics = $analytics['top_metrics'] ?? [];
                $projectAnalytics = $staffProjectAnalytics ?? ['kpis' => [], 'charts' => []];
                $projectKpis = $projectAnalytics['kpis'] ?? [];
                $profileImageUrl = $staff->resolved_profile_image_url;
                $staffInitials = $staff->initials;
                $activeReportTab = request()->query('tab') === 'projects' ? 'projects' : 'leads';
            @endphp

            <div class="page-breadcrumb d-flex align-items-center flex-wrap gap-2 mb-3">
                <div class="breadcrumb-title pe-3">Staff</div>
                <div class="ps-3 d-none d-sm-block">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('staff') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('view-staff', $staff->id) }}">{{ $staff->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <a href="{{ route('view-staff', $staff->id) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Profile
                    </a>
                    @if (! $staff->trashed())
                        <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-primary">
                            <i class="bx bx-edit-alt me-1"></i>Edit Staff
                        </a>
                    @endif
                </div>
            </div>

            <div class="card crm-card mb-4">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        @if ($profileImageUrl)
                            <img src="{{ $profileImageUrl }}" alt="{{ $staff->name }}" class="crm-avatar-sm">
                        @else
                            <div class="crm-avatar-sm-initials">{{ $staffInitials }}</div>
                        @endif
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $staff->name }} — Performance Reports</h5>
                            <div class="text-muted small">{{ $staff->email }} • Leads &amp; Projects Analytics</div>
                        </div>
                    </div>

                    <ul class="nav nav-pills crm-report-nav gap-2" id="staffReportsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeReportTab === 'leads' ? 'active' : '' }}" id="leads-report-tab"
                                data-bs-toggle="tab" data-bs-target="#leadsReportPane" type="button" role="tab"
                                aria-controls="leadsReportPane" aria-selected="{{ $activeReportTab === 'leads' ? 'true' : 'false' }}">
                                <i class="bx bx-trending-up me-1"></i>Leads Report
                                <span class="badge bg-light text-dark ms-1">{{ $kpis['total_leads_assigned'] ?? 0 }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeReportTab === 'projects' ? 'active' : '' }}" id="projects-report-tab"
                                data-bs-toggle="tab" data-bs-target="#projectsReportPane" type="button" role="tab"
                                aria-controls="projectsReportPane" aria-selected="{{ $activeReportTab === 'projects' ? 'true' : 'false' }}">
                                <i class="bx bx-briefcase me-1"></i>Projects Report
                                <span class="badge bg-light text-dark ms-1">{{ $projectKpis['total_projects'] ?? 0 }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="staffReportsTabsContent">
                {{-- LEADS REPORT TAB --}}
                <div class="tab-pane fade {{ $activeReportTab === 'leads' ? 'show active' : '' }}" id="leadsReportPane" role="tabpanel" aria-labelledby="leads-report-tab">
                    <div class="card crm-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0 fw-bold">Lead &amp; Followup KPI Dashboard</h5>
                                <form method="GET" action="{{ route('staff.reports', $staff->id) }}" class="d-flex flex-wrap gap-2">
                                    <select name="period" class="form-select form-select-sm" style="min-width: 170px;">
                                        <option value="7d" {{ ($analyticsFilters['period'] ?? '30d') === '7d' ? 'selected' : '' }}>Last 7 days</option>
                                        <option value="30d" {{ ($analyticsFilters['period'] ?? '30d') === '30d' ? 'selected' : '' }}>Last 30 days</option>
                                        <option value="this_month" {{ ($analyticsFilters['period'] ?? '30d') === 'this_month' ? 'selected' : '' }}>This month</option>
                                        <option value="this_quarter" {{ ($analyticsFilters['period'] ?? '30d') === 'this_quarter' ? 'selected' : '' }}>This quarter</option>
                                        <option value="this_year" {{ ($analyticsFilters['period'] ?? '30d') === 'this_year' ? 'selected' : '' }}>This year</option>
                                        <option value="custom" {{ ($analyticsFilters['period'] ?? '30d') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                    <input type="date" name="from" value="{{ $analyticsFilters['from'] ?? '' }}" class="form-control form-control-sm">
                                    <input type="date" name="to" value="{{ $analyticsFilters['to'] ?? '' }}" class="form-control form-control-sm">
                                    <button class="btn btn-sm btn-primary">Apply</button>
                                </form>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-primary h-100"><div class="card-body p-3"><small>Total Leads</small><h5 class="mb-0">{{ $kpis['total_leads_assigned'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-info h-100"><div class="card-body p-3"><small>Active Leads</small><h5 class="mb-0">{{ $kpis['active_leads'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-success h-100"><div class="card-body p-3"><small>Converted</small><h5 class="mb-0">{{ $kpis['converted_leads'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-danger h-100"><div class="card-body p-3"><small>Lost Leads</small><h5 class="mb-0">{{ $kpis['lost_leads'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-warning h-100"><div class="card-body p-3"><small>Total Followups</small><h5 class="mb-0">{{ $kpis['total_followups'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-secondary h-100"><div class="card-body p-3"><small>Pending Followups</small><h5 class="mb-0">{{ $kpis['pending_followups'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-warning h-100"><div class="card-body p-3"><small>Overdue</small><h5 class="mb-0">{{ $kpis['overdue_followups'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-primary h-100"><div class="card-body p-3"><small>Today's Followups</small><h5 class="mb-0">{{ $kpis['todays_followups'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-info h-100"><div class="card-body p-3"><small>Meetings</small><h5 class="mb-0">{{ $kpis['meetings_scheduled'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-success h-100"><div class="card-body p-3"><small>Conversion %</small><h5 class="mb-0">{{ $kpis['conversion_rate'] ?? 0 }}%</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-dark h-100"><div class="card-body p-3"><small>Avg Response</small><h5 class="mb-0">{{ $kpis['avg_response_time_hours'] ?? 0 }}h</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-dark h-100"><div class="card-body p-3"><small>Avg Conversion</small><h5 class="mb-0">{{ $kpis['avg_conversion_time_days'] ?? 0 }}d</h5></div></div></div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Monthly Lead Conversion</h6></div><div class="card-body"><div id="staffMonthlyLeadChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Monthly Followup Activity</h6></div><div class="card-body"><div id="staffFollowupActivityChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Lead Status Distribution</h6></div><div class="card-body"><div id="staffLeadStatusChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Followup Outcome Distribution</h6></div><div class="card-body"><div id="staffOutcomeChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Daily Activity Timeline (30d)</h6></div><div class="card-body"><div id="staffDailyActivityChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Assigned vs Converted (Monthly)</h6></div><div class="card-body"><div id="staffAssignedVsConvertedChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="card crm-card h-100">
                                <div class="card-header"><h6 class="mb-0">Overdue Followups</h6></div>
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
                                                        <td><span class="badge bg-warning text-dark">{{ $of['overdue_days'] }}</span></td>
                                                        <td>{{ ucfirst($of['priority']) }}</td>
                                                        <td>{{ strtoupper($of['status']) }}</td>
                                                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('lead-management.show', ['source' => 'lead', 'id' => $of['id']]) }}">Open</a></td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="6" class="text-center">No overdue followups.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card crm-card h-100">
                                <div class="card-header"><h6 class="mb-0">Top Performance Metrics</h6></div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between"><span>Fastest Response</span><strong>{{ $topMetrics['fastest_response_time_hours'] ?? 0 }}h</strong></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Highest Conversion Lead</span><strong>{{ $topMetrics['highest_conversion_lead'] ?? '-' }}</strong></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Best Conversion Month</span><strong>{{ $topMetrics['best_conversion_month'] ?? '-' }}</strong></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Most Active Day</span><strong>{{ $topMetrics['most_active_day'] ?? '-' }}</strong></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Avg Followups / Lead</span><strong>{{ $topMetrics['avg_followups_per_lead'] ?? 0 }}</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PROJECTS REPORT TAB --}}
                <div class="tab-pane fade {{ $activeReportTab === 'projects' ? 'show active' : '' }}" id="projectsReportPane" role="tabpanel" aria-labelledby="projects-report-tab">
                    <div class="card crm-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0 fw-bold">Project &amp; Task Analytics</h5>
                                <span class="text-muted small">Overview of projects and workload assigned to this staff member</span>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-primary h-100"><div class="card-body p-3"><small>Total Projects</small><h5 class="mb-0">{{ $projectKpis['total_projects'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-info h-100"><div class="card-body p-3"><small>Active Projects</small><h5 class="mb-0">{{ $projectKpis['active_projects'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-success h-100"><div class="card-body p-3"><small>Completed Projects</small><h5 class="mb-0">{{ $projectKpis['completed_projects'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-warning h-100"><div class="card-body p-3"><small>Overdue Projects</small><h5 class="mb-0">{{ $projectKpis['overdue_projects'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-secondary h-100"><div class="card-body p-3"><small>Total Tasks</small><h5 class="mb-0">{{ $projectKpis['total_tasks'] ?? 0 }}</h5></div></div></div>
                                <div class="col-xl-2 col-md-4 col-sm-6"><div class="card border-start border-0 border-3 border-dark h-100"><div class="card-body p-3"><small>Avg Progress</small><h5 class="mb-0">{{ $projectKpis['avg_progress'] ?? 0 }}%</h5></div></div></div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Project Status Distribution</h6></div><div class="card-body"><div id="staffProjectStatusChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Task Status Distribution</h6></div><div class="card-body"><div id="staffTaskStatusChart" style="height:300px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                                <div class="col-lg-6">
                                    <div class="card h-100">
                                        <div class="card-header"><h6 class="mb-0">Monthly Project Timeline</h6></div>
                                        <div class="card-body">
                                            <div id="staffMonthlyProjectActivityChart" style="min-height:320px;"><div class="text-muted small">Charts loading...</div></div>
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
                                                                <td class="{{ $timelineRow['status_text_class'] ?? ($timelineRow['is_overdue'] ? 'text-danger' : 'text-success') }}">{{ $timelineRow['difference_label'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-muted">No projects with both a start date and end date were found.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h6 class="mb-0">Task Status Overview</h6></div><div class="card-body"><div id="staffTaskCompletionChart" style="min-height:320px;"><div class="text-muted small">Charts loading...</div></div></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card crm-card mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3 fw-bold">Recent Projects</h5>
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
                                                <td>{{ $project->start_date ? $project->start_date->format('Y-m-d') : 'N/A' }}</td>
                                                <td>{{ $project->deadline ? $project->deadline->format('Y-m-d') : 'N/A' }}</td>
                                                <td>
                                                    @if ($project->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($project->status == 'in_progress')
                                                        <span class="badge bg-warning text-dark">In Progress</span>
                                                    @elseif($project->status == 'pending')
                                                        <span class="badge bg-danger">Pending</span>
                                                    @else
                                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">No projects found for this staff member.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card crm-card">
                        <div class="card-body p-4">
                            <h5 class="mb-3 fw-bold">Recent Tasks</h5>
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
                                                <td>{{ $task->deadline ? $task->deadline->format('Y-m-d') : 'N/A' }}</td>
                                                <td>
                                                    @if ($task->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($task->status == 'in_progress')
                                                        <span class="badge bg-warning text-dark">In Progress</span>
                                                    @elseif($task->status == 'pending')
                                                        <span class="badge bg-danger">Pending</span>
                                                    @else
                                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($task->priority == 'high')
                                                        <span class="badge bg-danger">High</span>
                                                    @elseif($task->priority == 'medium')
                                                        <span class="badge bg-warning text-dark">Medium</span>
                                                    @elseif($task->priority == 'low')
                                                        <span class="badge bg-success">Low</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($task->priority ?? 'N/A') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center">No tasks found for this staff member.</td></tr>
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
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const $ = window.jQuery;
            const chartEls = [
                '#staffMonthlyLeadChart',
                '#staffFollowupActivityChart',
                '#staffLeadStatusChart',
                '#staffOutcomeChart',
                '#staffDailyActivityChart',
                '#staffAssignedVsConvertedChart',
                '#staffProjectStatusChart',
                '#staffTaskStatusChart',
                '#staffMonthlyProjectActivityChart',
                '#staffTaskCompletionChart',
            ];

            if ($ && $.fn && $.fn.DataTable) {
                ['#table', '#tasks-table'].forEach(function(sel) {
                    if ($(sel).length) {
                        try {
                            $(sel).DataTable({
                                paging: true,
                                searching: true,
                                ordering: true,
                                info: true,
                                lengthChange: true,
                                pageLength: 10
                            });
                        } catch (e) {
                            console.error('DataTables init failed for ' + sel + ':', e);
                        }
                    }
                });
            }

            const analyticsUrl = @json(route('staff.analytics', $staff->id));
            const initialAnalytics = @json($analytics);
            let latestAnalyticsPayload = initialAnalytics || {};
            const projectAnalyticsData = @json($staffProjectAnalytics) || {};
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
                if (!series.length && dataset?.series && typeof dataset.series === 'object') {
                    labels = Object.keys(dataset.series);
                    series = Object.values(dataset.series);
                }
                labels = labels.map((l) => String(l ?? 'Unknown'));
                series = series.map((v) => Number(v) || 0);
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
                return {
                    rows,
                    labels: rows.map((item) => String(item?.status ?? 'Unknown')),
                    series: rows.map((item) => Number(item?.count) || 0),
                    colors: Array.isArray(dataset?.colors) && dataset.colors.length ? dataset.colors : ['#6c757d', '#0d6efd', '#dc3545', '#198754'],
                };
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function renderMonthlyProjectTimeline() {
                const target = '#staffMonthlyProjectActivityChart';
                const el = document.querySelector(target);
                if (!el) return;
                const timelineRows = Array.isArray(projectTimelineData) ? projectTimelineData : [];
                if (!timelineRows.length) {
                    el.innerHTML = '<div class="text-muted small">No dated projects available for the timeline chart.</div>';
                    return;
                }
                renderOrUpdateChart('monthlyProjectActivity', target, {
                    chart: { type: 'rangeBar', height: Math.max(300, (timelineRows.length * 38) + 60), toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, rangeBarGroupRows: true, barHeight: '70%' } },
                    dataLabels: { enabled: false },
                    grid: { strokeDashArray: 4 },
                    series: [{
                        name: 'Project Duration',
                        data: timelineRows.map((item) => ({
                            x: item.project_name,
                            y: [new Date(item.start_date).getTime(), new Date(item.end_date).getTime()],
                            fillColor: item.bar_color || (item.is_overdue ? '#dc3545' : '#198754'),
                        })),
                    }],
                    xaxis: { type: 'datetime', labels: { datetimeUTC: false } },
                    yaxis: { labels: { style: { fontSize: '12px' } } },
                    tooltip: {
                        custom: function({ dataPointIndex }) {
                            const item = timelineRows[dataPointIndex];
                            if (!item) return '';
                            return `<div class="p-2"><div class="fw-semibold mb-1">${escapeHtml(item.project_name)}</div><div class="small text-muted">Start: ${escapeHtml(item.start_label)}</div><div class="small text-muted">End: ${escapeHtml(item.end_label)}</div><div class="small ${item.is_overdue ? 'text-danger' : 'text-success'}">${escapeHtml(item.difference_label)}</div></div>`;
                        }
                    },
                    colors: timelineRows.map((item) => item.bar_color || (item.is_overdue ? '#dc3545' : '#198754')),
                });
            }

            function renderTaskStatusOverview() {
                const target = '#staffTaskCompletionChart';
                const el = document.querySelector(target);
                if (!el) return;
                const overview = normalizeTaskStatusOverview(projectAnalyticsData.charts?.task_status_overview);
                if (!overview.labels.length) {
                    el.innerHTML = '<div class="text-muted small">No task status data available for the overview chart.</div>';
                    return;
                }
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.body.classList.contains('dark-theme');
                const textColor = isDark ? '#e9ecef' : '#495057';
                const gridColor = isDark ? '#495057' : '#edf2f7';
                renderOrUpdateChart('taskStatusOverview', target, {
                    chart: { type: 'bar', height: 320, toolbar: { show: false }, foreColor: textColor },
                    theme: { mode: isDark ? 'dark' : 'light' },
                    plotOptions: { bar: { horizontal: false, distributed: true, columnWidth: '54%', borderRadius: 10, borderRadiusApplication: 'end' } },
                    dataLabels: { enabled: true, offsetY: -18, style: { fontSize: '12px', fontWeight: 700, colors: [textColor] } },
                    grid: { borderColor: gridColor, strokeDashArray: 4, padding: { top: 8 } },
                    series: [{ name: 'Tasks', data: overview.series }],
                    colors: overview.colors,
                    xaxis: { categories: overview.labels, labels: { style: { colors: overview.labels.map(() => textColor), fontWeight: 600 } } },
                    yaxis: { min: 0, title: { text: 'Number of Tasks', style: { color: textColor } }, labels: { style: { colors: [textColor] } } },
                    legend: { show: false },
                });
            }

            function renderLeadCharts(payload) {
                const c = payload.charts || {};
                const statusDist = normalizeDonutData(c.lead_status_distribution, 'No lead status data');
                chartEls.forEach((sel) => {
                    const el = document.querySelector(sel);
                    if (el && el.textContent.includes('Charts loading')) el.innerHTML = '';
                });
                renderOrUpdateChart('leadConversion', '#staffMonthlyLeadChart', {
                    chart: { type: 'line', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Assigned', data: c.monthly_lead_conversion?.assigned || [] }, { name: 'Converted', data: c.monthly_lead_conversion?.converted || [] }],
                    xaxis: { categories: c.monthly_lead_conversion?.labels || [] },
                    colors: ['#0d6efd', '#15ca20'],
                    stroke: { curve: 'smooth', width: 3 }
                });
                renderOrUpdateChart('followupActivity', '#staffFollowupActivityChart', {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: c.monthly_followup_activity?.series || [],
                    xaxis: { categories: c.monthly_followup_activity?.labels || [] }
                });
                renderOrUpdateChart('statusDistribution', '#staffLeadStatusChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: statusDist.labels,
                    series: statusDist.series,
                    colors: ['#0d6efd', '#6c757d', '#17a2b8', '#198754', '#ffc107', '#212529', '#fd7e14', '#28a745', '#dc3545', '#adb5bd']
                });
                renderOrUpdateChart('outcomeDistribution', '#staffOutcomeChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: c.followup_outcome_distribution?.labels || [],
                    series: c.followup_outcome_distribution?.series || [],
                });
                renderOrUpdateChart('dailyActivity', '#staffDailyActivityChart', {
                    chart: { type: 'area', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Followups', data: c.daily_activity_timeline?.followups || [] }, { name: 'Activities', data: c.daily_activity_timeline?.activities || [] }],
                    xaxis: { categories: c.daily_activity_timeline?.labels || [] },
                    colors: ['#198754', '#0dcaf0']
                });
                renderOrUpdateChart('assignedVsConverted', '#staffAssignedVsConvertedChart', {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Assigned', data: c.monthly_assigned_vs_converted?.assigned || [] }, { name: 'Converted', data: c.monthly_assigned_vs_converted?.converted || [] }],
                    xaxis: { categories: c.monthly_assigned_vs_converted?.labels || [] },
                    colors: ['#0d6efd', '#28a745']
                });
            }

            function renderProjectCharts() {
                const c = projectAnalyticsData.charts || {};
                const projStatus = normalizeDonutData(c.project_status_distribution, 'No project status data');
                const taskStatus = normalizeDonutData(c.task_status_distribution, 'No task status data');
                renderOrUpdateChart('projectStatus', '#staffProjectStatusChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: projStatus.labels,
                    series: projStatus.series,
                    colors: ['#0d6efd', '#17a2b8', '#198754', '#ffc107', '#dc3545'],
                });
                renderOrUpdateChart('taskStatus', '#staffTaskStatusChart', {
                    chart: { type: 'donut', height: 300 },
                    labels: taskStatus.labels,
                    series: taskStatus.series,
                    colors: ['#0d6efd', '#17a2b8', '#198754', '#ffc107', '#dc3545'],
                });
                renderMonthlyProjectTimeline();
                renderTaskStatusOverview();
            }

            function renderVisibleTabCharts() {
                if (!window.ApexCharts) return;
                const leadsPane = document.getElementById('leadsReportPane');
                const projectsPane = document.getElementById('projectsReportPane');
                if (leadsPane && leadsPane.classList.contains('active')) {
                    renderLeadCharts(latestAnalyticsPayload || {});
                }
                if (projectsPane && projectsPane.classList.contains('active')) {
                    renderProjectCharts();
                    if ($ && $.fn && $.fn.DataTable) {
                        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                    }
                }
                window.dispatchEvent(new Event('resize'));
            }

            document.querySelectorAll('#staffReportsTabs button[data-bs-toggle="tab"]').forEach(function(tabBtn) {
                tabBtn.addEventListener('shown.bs.tab', function() {
                    renderVisibleTabCharts();
                });
            });

            if (!window.ApexCharts) {
                chartEls.forEach((sel) => {
                    const el = document.querySelector(sel);
                    if (el) el.innerHTML = '<div class="text-muted small">ApexCharts library not loaded.</div>';
                });
                return;
            }

            fetch(analyticsUrl + (params.toString() ? '?' + params.toString() : ''), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(payload => {
                latestAnalyticsPayload = payload || {};
                renderVisibleTabCharts();
            })
            .catch(() => {
                latestAnalyticsPayload = initialAnalytics || {};
                renderVisibleTabCharts();
            });
        }, { once: true });
    </script>
@endsection
