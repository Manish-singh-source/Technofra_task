@extends('/layout/master')
@section('content')
    <style>
        .dashboard-kpi-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(0, 140, 255, 0.12);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(32, 52, 72, 0.08);
        }

        .dashboard-kpi-item {
            position: relative;
            display: flex;
            align-items: center;
            min-width: 0;
            min-height: 82px;
            padding: 12px 16px;
            isolation: isolate;
        }

        .dashboard-kpi-item:not(:last-child) {
            border-right: 1px solid rgba(0, 140, 255, 0.1);
        }

        .dashboard-kpi-item:first-child {
            z-index: 3;
            color: #fff;
            background: linear-gradient(135deg, #008cff 0%, #0d6efd 100%);
        }

        .dashboard-kpi-item:nth-child(2) {
            z-index: 2;
            background: #fff;
        }

        .dashboard-kpi-item:nth-child(3) {
            z-index: 1;
            background: #fff;
        }

        .dashboard-kpi-item:not(:first-child) {
            overflow: hidden;
            transition: box-shadow 220ms ease;
        }

        .dashboard-kpi-item:not(:first-child)::before {
            content: '';
            position: absolute;
            z-index: 0;
            inset: 0;
            background: linear-gradient(135deg, #008cff 0%, #0d6efd 100%);
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 260ms ease;
        }

        .dashboard-kpi-item:not(:first-child):hover {
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16);
        }

        .dashboard-kpi-item:not(:first-child):hover::before {
            transform: scaleX(1);
        }

        .dashboard-kpi-content {
            position: relative;
            z-index: 2;
            min-width: 0;
        }

        .dashboard-kpi-title {
            margin: 0 0 4px;
            color: #172033;
            font-size: clamp(16px, 1.5vw, 22px);
            font-weight: 700;
            line-height: 1.15;
            white-space: nowrap;
            transition: color 200ms ease;
        }

        .dashboard-kpi-value {
            color: #008cff;
            transition: color 200ms ease;
        }

        .dashboard-kpi-caption {
            margin: 0;
            color: #64748b;
            font-size: clamp(11px, 1vw, 13px);
            line-height: 1.3;
            white-space: nowrap;
            transition: color 200ms ease;
        }

        .dashboard-kpi-item:first-child .dashboard-kpi-title,
        .dashboard-kpi-item:first-child .dashboard-kpi-value,
        .dashboard-kpi-item:first-child .dashboard-kpi-caption {
            color: #fff;
        }

        .dashboard-kpi-item:not(:first-child):hover .dashboard-kpi-title,
        .dashboard-kpi-item:not(:first-child):hover .dashboard-kpi-value,
        .dashboard-kpi-item:not(:first-child):hover .dashboard-kpi-caption {
            color: #fff;
        }

        .dashboard-kpi-icon {
            position: absolute;
            z-index: 1;
            right: 16px;
            color: #008cff;
            font-size: 72px;
            line-height: 1;
            opacity: 0.055;
            transform: rotate(-8deg);
            transition: color 200ms ease, opacity 200ms ease, transform 220ms ease;
        }

        .dashboard-kpi-item:first-child .dashboard-kpi-icon {
            color: #fff;
            opacity: 0.08;
        }

        .dashboard-kpi-item:not(:first-child):hover .dashboard-kpi-icon {
            color: #fff;
            opacity: 0.12;
            transform: rotate(0deg) scale(1.06);
        }

        @media (max-width: 575.98px) {
            .dashboard-kpi-item {
                min-height: 68px;
                padding: 8px;
            }

            .dashboard-kpi-title {
                font-size: 11px;
                line-height: 1.2;
                white-space: normal;
            }

            .dashboard-kpi-caption {
                margin-top: 4px;
                font-size: 9px;
                white-space: normal;
            }

            .dashboard-kpi-icon {
                right: 4px;
                font-size: 44px;
            }
        }
    </style>

    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @can('view_dashboard')
                <!-- Summary Cards -->

                <!--end summary row-->

                <!-- Renewal Statistics Cards -->
                @can('view_renewals')
                    <div class="dashboard-kpi-strip">
                        <div class="dashboard-kpi-item">
                            <div class="dashboard-kpi-content">
                                <h4 class="dashboard-kpi-title"><span class="dashboard-kpi-value">{{ $totalRenewals ?? 0 }}</span> Total Services</h4>
                                <p class="dashboard-kpi-caption">All services in the system</p>
                            </div>
                            <i class="bx bx-list-ul dashboard-kpi-icon" aria-hidden="true"></i>
                        </div>
                        <div class="dashboard-kpi-item">
                            <div class="dashboard-kpi-content">
                                <h4 class="dashboard-kpi-title"><span class="dashboard-kpi-value">{{ $renewalsDueThisWeek ?? 0 }}</span> Due This Week</h4>
                                <p class="dashboard-kpi-caption">Renewals expiring within 7 days</p>
                            </div>
                            <i class="bx bx-time-five dashboard-kpi-icon" aria-hidden="true"></i>
                        </div>
                        <div class="dashboard-kpi-item">
                            <div class="dashboard-kpi-content">
                                <h4 class="dashboard-kpi-title"><span class="dashboard-kpi-value">{{ $overdueRenewals ?? 0 }}</span> Expired Renewals</h4>
                                <p class="dashboard-kpi-caption">Services already expired</p>
                            </div>
                            <i class="bx bx-error dashboard-kpi-icon" aria-hidden="true"></i>
                        </div>
                    </div>
                @endcan
            @endcan

            @canany(['view_calendar'])
                <!-- Calendar Widget -->
                <div class="row mt-4 justify-content-center">
                    <div class="col-12">
                        <div class="card radius-10">
                            <div class="card-header appointment-header">
                                <div>
                                    <h6 class="mb-0">Calendar Appointments</h6>
                                    <p class="mb-0 text-muted font-13">Plan meetings and appointments</p>
                                </div>
                                <a href="{{ route('calendar.index') }}" class="btn btn-outline-primary calendar-view-all">
                                    <i class="bx bx-table me-1"></i> View Calendar List
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="calendar-dashboard-layout">
                                    <div id="calendar"></div>
                                    <aside class="calendar-upcoming" aria-label="Upcoming appointments">
                                        <button type="button" class="btn btn-primary calendar-add-event"
                                            data-bs-toggle="modal" data-bs-target="#addEventModal">
                                            <i class="bx bx-plus me-1"></i> Add New Event
                                        </button>
                                        <h6 class="calendar-upcoming-title">You are going to</h6>
                                        <p id="calendarSelectedDate" class="calendar-selected-date"></p>
                                        <div class="calendar-event-legend" aria-label="Calendar event categories">
                                            <span><i class="calendar-category-dot appointment"></i>Appointments</span>
                                            <span><i class="calendar-category-dot task"></i>Tasks</span>
                                            <span><i class="calendar-category-dot renewal"></i>Renewals</span>
                                            <span><i class="calendar-category-dot lead"></i>Lead Management</span>
                                            <span><i class="calendar-category-dot todo"></i>To-do</span>
                                            <span><i class="calendar-category-dot order"></i>Order receipt</span>
                                        </div>
                                        <div id="calendarUpcomingList" class="calendar-upcoming-list" aria-live="polite">
                                            <p class="calendar-upcoming-empty">Loading appointments...</p>
                                        </div>
                                        <button type="button" id="calendarUpcomingMore"
                                            class="btn btn-light calendar-upcoming-more" hidden>See More</button>
                                    </aside>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end calendar row-->
            @endcan

            @can('view_dashboard')
                @can('view_renewals')
                    <!--end row-->
                    <!-- Critical Renewals Table (Upcoming + Expired) -->
                    <div class="card radius-10 mt-4">
                        <div class="card-header">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div>
                                    <h6 class="mb-0">Upcoming Renewals</h6>
                                    <p class="mb-0 text-muted font-13">Split view of upcoming renewals and expired items</p>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="badge bg-danger">
                                        <p class="mb-0 p-2">{{ $overdueRenewals ?? 0 }} Total Expired</p>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-12 col-xl-6 d-flex">
                                    <div class="border rounded w-100 h-100">
                                        <div class="p-3 border-bottom bg-light">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <div>
                                                    <h6 class="mb-1">Client Renewals</h6>
                                                    <p class="mb-0 text-muted font-13">Client services that are upcoming or expired
                                                    </p>
                                                </div>
                                                <div class="ms-auto d-flex align-items-center gap-2">
                                                    <span class="badge bg-warning text-dark">{{ $clientRenewalsDueThisWeek ?? 0 }}
                                                        This Week</span>
                                                    <span class="badge bg-danger">{{ $clientExpiredRenewals ?? 0 }} Expired</span>
                                                    <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm">
                                                        <i class="bx bx-list-ul"></i> View All
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered align-middle mb-0"
                                                id="client-renewals-table" style="width:100%">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Company Name</th>
                                                        <th>Service Renewal</th>
                                                        <th>Plan Type</th>
                                                        <th>Remark</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Billing Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($clientCriticalRenewals->take(5) as $service)
                                                        @php
                                                            $today = \Carbon\Carbon::today();
                                                            $daysLeft = $today->diffInDays($service->end_date, false);
                                                            $isOverdue = $service->end_date < $today;
                                                            $urgencyClass =
                                                                $daysLeft <= 1
                                                                    ? 'text-danger'
                                                                    : ($daysLeft <= 3
                                                                        ? 'text-warning'
                                                                        : 'text-info');
                                                            $statusBadgeClass =
                                                                $service->effective_status === 'expired'
                                                                    ? 'bg-danger'
                                                                    : 'bg-' . $service->effective_status_badge;
                                                        @endphp
                                                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                                            <td>{{ $service->company?->company_name ?: ($service->client?->businessDetail?->company_name ?: 'N/A') }}
                                                            </td>
                                                            <td>{{ $service->service_name }}</td>
                                                            <td>{{ $service->plan_type ? ucwords(str_replace('_', ' ', $service->plan_type)) : 'N/A' }}
                                                            </td>
                                                            <td>
                                                                @if ($service->remark_text)
                                                                    <span class="badge border"
                                                                        style="{{ $service->remark_badge_style }}">{{ $service->remark_text }}</span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $service->start_date->format('d M Y') }}</td>
                                                            <td class="{{ $urgencyClass }}">
                                                                <div><strong>{{ $service->end_date->format('d M Y') }}</strong>
                                                                </div>
                                                                <small class="{{ $urgencyClass }}">
                                                                    @if ($daysLeft < 0)
                                                                        {{ abs($daysLeft) }} days overdue
                                                                    @elseif($daysLeft == 0)
                                                                        Expires today
                                                                    @elseif($daysLeft == 1)
                                                                        Expires tomorrow
                                                                    @else
                                                                        {{ $daysLeft }} days left
                                                                    @endif
                                                                </small>
                                                            </td>
                                                            <td>
                                                                @if ($service->billing_date)
                                                                    {{ $service->billing_date->format('d M Y') }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge {{ $statusBadgeClass }}">
                                                                    {{ $service->status_label }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex order-actions">
                                                                    <a href="{{ route('services.show', $service->id) }}"
                                                                        title="View"><i class='bx bxs-show'></i></a>
                                                                    <a href="{{ route('services.edit', $service->id) }}"
                                                                        class="ms-2" title="Edit"><i
                                                                            class='bx bxs-edit'></i></a>
                                                                    <a href="{{ route('send-mail', $service->id) }}"
                                                                        class="ms-2 text-primary" title="Send Renewal Email"><i
                                                                            class='bx bx-mail-send'></i></a>
                                                                    <form
                                                                        action="{{ route('send-whatsapp-renewal', $service->id) }}"
                                                                        method="POST" class="ms-2">
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="btn btn-link text-success p-0 m-0"
                                                                            title="Send WhatsApp Reminder"
                                                                            onclick="return confirm('Send WhatsApp renewal reminder to client?')">
                                                                            <i class='bx bxl-whatsapp'></i>
                                                                        </button>
                                                                    </form>
                                                                    {{-- 
                                                                    @if ($isOverdue)
                                                                        <a href="{{ route('services.edit', $service->id) }}"
                                                                            class="ms-2 text-success" title="Renew Service"><i
                                                                                class='bx bx-refresh'></i></a>
                                                                    @endif 
                                                                    --}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="9" class="text-center py-4">
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <i class='bx bx-calendar-check'
                                                                        style="font-size: 48px; color: #28a745;"></i>
                                                                    <h6 class="mt-2 text-success">No upcoming or expired client
                                                                        renewals</h6>
                                                                    <p class="text-muted mb-0">No client services are expiring soon
                                                                        or already expired</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-6 d-flex">
                                    <div class="border rounded w-100 h-100">
                                        <div class="p-3 border-bottom bg-light">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <div>
                                                    <h6 class="mb-1">Vendor Renewals</h6>
                                                    <p class="mb-0 text-muted font-13">Vendor services that are upcoming or expired
                                                    </p>
                                                </div>
                                                <div class="ms-auto d-flex align-items-center gap-2">
                                                    <span class="badge bg-warning text-dark">{{ $vendorRenewalsDueThisWeek ?? 0 }}
                                                        This Week</span>
                                                    <span class="badge bg-danger">{{ $vendorExpiredRenewals ?? 0 }} Expired</span>
                                                    <a href="{{ route('vendor-services.index') }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="bx bx-list-ul"></i> View All
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered align-middle mb-0"
                                                id="vendor-renewals-table" style="width:100%">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Vendor Name</th>
                                                        <th>Service Renewal</th>
                                                        <th>Remark</th>
                                                        <th>Plan Type</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Billing Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($vendorCriticalRenewals->take(5) as $service)
                                                        @php
                                                            $today = \Carbon\Carbon::today();
                                                            $daysLeft = $today->diffInDays($service->end_date, false);
                                                            $isOverdue = $service->end_date < $today;
                                                            $urgencyClass =
                                                                $daysLeft <= 1
                                                                    ? 'text-danger'
                                                                    : ($daysLeft <= 3
                                                                        ? 'text-warning'
                                                                        : 'text-info');
                                                            $statusBadgeClass =
                                                                $service->effective_status === 'expired'
                                                                    ? 'bg-danger'
                                                                    : 'bg-' . $service->effective_status_badge;
                                                        @endphp
                                                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                                            <td>{{ $service->vendor->name ?? 'N/A' }}</td>
                                                            <td>{{ $service->service_name }}</td>
                                                            <td>
                                                                @if ($service->remark_text)
                                                                    <span class="badge border"
                                                                        style="{{ $service->remark_badge_style }}">{{ $service->remark_text }}</span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $service->plan_type ? ucwords(str_replace('_', ' ', $service->plan_type)) : 'N/A' }}
                                                            </td>
                                                            <td>{{ $service->start_date->format('d M Y') }}</td>
                                                            <td class="{{ $urgencyClass }}">
                                                                <div><strong>{{ $service->end_date->format('d M Y') }}</strong>
                                                                </div>
                                                                <small class="{{ $urgencyClass }}">
                                                                    @if ($daysLeft < 0)
                                                                        {{ abs($daysLeft) }} days overdue
                                                                    @elseif($daysLeft == 0)
                                                                        Expires today
                                                                    @elseif($daysLeft == 1)
                                                                        Expires tomorrow
                                                                    @else
                                                                        {{ $daysLeft }} days left
                                                                    @endif
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <span class="badge {{ $statusBadgeClass }}">
                                                                    {{ $service->status_label }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $service->billing_date ? $service->billing_date->format('d M Y') : 'N/A' }}
                                                            </td>
                                                            <td>
                                                                <div class="d-flex order-actions">
                                                                    <a href="{{ route('vendor-services.show', $service->id) }}"
                                                                        title="View"><i class='bx bxs-show'></i></a>
                                                                    <a href="{{ route('vendor-services.edit', $service->id) }}"
                                                                        class="ms-2" title="Edit"><i
                                                                            class='bx bxs-edit'></i></a>
                                                                    {{-- 
                                                                    @if ($isOverdue)
                                                                        <a href="{{ route('vendor-services.edit', $service->id) }}"
                                                                            class="ms-2 text-success" title="Renew Service"><i
                                                                                class='bx bx-refresh'></i></a>
                                                                    @endif 
                                                                    --}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="9" class="text-center py-4">
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <i class='bx bx-calendar-check'
                                                                        style="font-size: 48px; color: #28a745;"></i>
                                                                    <h6 class="mt-2 text-success">No upcoming or expired vendor
                                                                        renewals</h6>
                                                                    <p class="text-muted mb-0">No vendor services are expiring soon
                                                                        or already expired</p>
                                                                </div>
                                                            </td>
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
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const initDashboardTable = (selector) => {
                                if (!window.jQuery || !$.fn.DataTable) {
                                    return;
                                }

                                const $table = $(selector);
                                if (!$table.length || $.fn.DataTable.isDataTable($table)) {
                                    return;
                                }

                                $table.DataTable({
                                    order: [],
                                    lengthChange: false,
                                    searching: false,
                                    info: false,
                                    paging: false,
                                    autoWidth: false,
                                    columnDefs: [{
                                            orderable: false,
                                            targets: [8]
                                        },
                                        {
                                            searchable: false,
                                            targets: [8]
                                        }
                                    ]
                                });
                            };

                            initDashboardTable('#client-renewals-table');
                            initDashboardTable('#vendor-renewals-table');
                        });
                    </script>
                @endcan

                @can('view_projects')
                    <!-- CRM Dashboard Section -->
                    <div class="row mt-4">
                        <div class="col-12 col-lg-8 d-flex">
                            <div class="card radius-10 w-100">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">Projects Summary</h6>
                                        </div>
                                        <div class="dropdown ms-auto">
                                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-horizontal-rounded font-22 text-option"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="javascript:;">Action</a>
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Another action</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
                                        <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                                style="color: #14abef"></i>Projects</span>
                                        <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                                style="color: #ffc107"></i>Tasks</span>
                                    </div>
                                    <div class="chart-container-1">
                                        <canvas id="chart1" data-dashboard-labels='@json($projectSummaryLabels ?? [])'
                                            data-dashboard-projects='@json($projectSummaryProjects ?? [])'
                                            data-dashboard-tasks='@json($projectSummaryTasks ?? [])' width="1031" height="260"
                                            style="display: block; box-sizing: border-box; height: 260px; width: 1031px;"></canvas>
                                    </div>
                                </div>
                                <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                                    <div class="col">
                                        <div class="p-3">
                                            <h5 class="mb-0">{{ $totalTasks ?? 0 }}</h5>
                                            <small class="mb-0">Total Task</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3">
                                            <h5 class="mb-0">{{ $totalProjects ?? 0 }}</h5>
                                            <small class="mb-0">Total Project</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 d-flex">
                            <div class="card radius-10 w-100">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">Tasks Summary</h6>
                                        </div>
                                        <div class="dropdown ms-auto">
                                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-horizontal-rounded font-22 text-option"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="javascript:;">Action</a>
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Another action</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container-2">
                                        <canvas id="chart2" data-task-summary-labels='@json($taskSummaryLabels ?? [])'
                                            data-task-summary-counts='@json($taskSummaryCounts ?? [])' width="487" height="220"
                                            style="display: block; box-sizing: border-box; height: 220px; width: 487px;"></canvas>
                                    </div>
                                </div>
                                <ul class="list-group list-group-flush">
                                    @forelse ($taskSummaryBreakdown ?? [] as $statusItem)
                                        <li
                                            class="list-group-item d-flex bg-transparent justify-content-between align-items-center {{ $loop->first ? 'border-top' : '' }}">
                                            {{ $statusItem['label'] }}
                                            <span
                                                class="badge {{ $statusItem['badge'] }} rounded-pill">{{ $statusItem['count'] }}</span>
                                        </li>
                                    @empty
                                        <li
                                            class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                                            No Tasks <span class="badge bg-secondary rounded-pill">0</span>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                @endcan


                <div class="row row-cols-1 row-cols-lg-3">
                    <div class="col d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-body">

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h6 class="mb-0">Team Availability</h6>
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Team availability period">
                                        <button type="button" class="btn btn-outline-primary team-period-btn active"
                                            data-period="weekly">Weekly</button>
                                        <button type="button" class="btn btn-outline-primary team-period-btn"
                                            data-period="monthly">Monthly</button>
                                        <button type="button" class="btn btn-outline-primary team-period-btn"
                                            data-period="yearly">Yearly</button>
                                    </div>
                                </div>
                                <p class="text-muted mb-2"><b>Completed tasks by staff</b></p>
                                <div class="chart-container-0 mt-5">
                                    <canvas id="chart3" data-team-labels='@json($teamAvailabilityLabels ?? [])'
                                        data-team-weekly='@json($teamAvailabilityWeekly ?? [])'
                                        data-team-monthly='@json($teamAvailabilityMonthly ?? [])'
                                        data-team-yearly='@json($teamAvailabilityYearly ?? [])' width="487" height="320"
                                        style="display: block; box-sizing: border-box; height: 320px; width: 487px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    @can('view_leads')
                        <div class="col d-flex">
                            <div class="card radius-10 w-100">
                                <div class="card-header bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">Leads Overview</h6>
                                        </div>
                                        <div class="dropdown ms-auto">
                                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-horizontal-rounded font-22 text-option"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="javascript:;">Action</a>
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Another action</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container-1 mt-3">
                                        <canvas id="chart4" data-lead-summary-labels='@json($leadSummaryLabels ?? [])'
                                            data-lead-summary-counts='@json($leadSummaryCounts ?? [])' width="487"
                                            height="260"
                                            style="display: block; box-sizing: border-box; height: 260px; width: 487px;"></canvas>
                                    </div>
                                </div>
                                <ul class="list-group list-group-flush">
                                    @forelse ($leadSummaryBreakdown ?? [] as $leadStatusItem)
                                        <li
                                            class="list-group-item d-flex bg-transparent justify-content-between align-items-center {{ $loop->first ? 'border-top' : '' }}">
                                            {{ $leadStatusItem['label'] }}
                                            <span
                                                class="badge {{ $leadStatusItem['badge'] }} rounded-pill">{{ $leadStatusItem['count'] }}</span>
                                        </li>
                                    @empty
                                        <li
                                            class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                                            No Leads <span class="badge bg-secondary rounded-pill">0</span>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    @endcan
                    @can('view_raise_issue')
                        <div class="col d-flex">
                            <div class="card radius-10 w-100">
                                <div class="card-header bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">Support Tickets Summary</h6>
                                        </div>
                                        <div class="dropdown ms-auto">
                                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-horizontal-rounded font-22 text-option"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="javascript:;">Refresh</a>
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">Export</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="javascript:;">View All</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @forelse($supportTickets as $ticket)
                                        <div class="card radius-10 border shadow-none mb-2">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <div style="flex: 1;">
                                                        <p class="mb-1 text-secondary font-11">
                                                            {{ $ticket->customer->client_name ?? 'N/A' }}</p>
                                                        <h6 class="mb-1" title="{{ $ticket->issue_description }}">
                                                            {{ Str::limit($ticket->issue_description, 50) }}
                                                        </h6>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted">
                                                                ðŸ“ {{ $ticket->project->project_name ?? 'N/A' }}
                                                            </small>
                                                            <small class="text-muted">
                                                                <i class='bx bx-time-five'></i>
                                                                {{ $ticket->created_at->format('M d, Y H:i') }}
                                                            </small>
                                                        </div>
                                                        <div class="d-flex gap-1 flex-wrap">
                                                            @if ($ticket->status == 'open')
                                                                <span class="badge bg-danger">Open</span>
                                                            @elseif($ticket->status == 'in_progress')
                                                                <span class="badge bg-warning text-dark">In Progress</span>
                                                            @elseif($ticket->status == 'resolved')
                                                                <span class="badge bg-info">Resolved</span>
                                                            @elseif($ticket->status == 'closed')
                                                                <span class="badge bg-success">Closed</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="ms-2">
                                                        @if ($ticket->priority == 'high')
                                                            <span class="badge bg-danger">High</span>
                                                        @elseif($ticket->priority == 'medium')
                                                            <span class="badge bg-warning text-dark">Med</span>
                                                        @else
                                                            <span class="badge bg-info">Low</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-3">
                                            <i class='bx bx-check-circle' style="font-size: 32px; color: #28a745;"></i>
                                            <p class="mt-2 text-muted">No support tickets raised</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            @endcan


        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button-->
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->

    @canany(['view_dashboard', 'view_calendar'])
        <!-- Add Event Modal -->
        <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEventModalLabel">Add Calendar Appointments</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addEventForm">
                            @csrf
                            <div class="appointment-tip mb-3">
                                <strong>Notification Flow:</strong> WhatsApp template message automatically at selected meeting
                                time.
                                <br>
                                <small>Note: Same day meetings require at least 30 minutes gap between time slots.</small>
                            </div>
                            <div class="mb-3">
                                <label for="event_title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="event_title" name="title" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="event_description" class="form-label">Description</label>
                                <textarea class="form-control" id="event_description" name="description" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="event_date" class="form-label">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="event_time" class="form-label">Time <span
                                            class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="event_time" name="event_time" required>
                                    <small class="form-text text-muted">Choose a slot with minimum 30 minutes difference from
                                        existing meetings.</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notification Platforms <span class="text-danger">*</span></label>
                                <div class="row g-2 notification-channel-group">
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-check">
                                            <input class="form-check-input notification-channel notification-channel-all"
                                                type="checkbox" id="notification_channel_all" name="notification_channels[]"
                                                value="all" checked>
                                            <label class="form-check-label" for="notification_channel_all">All
                                                platforms</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-check">
                                            <input class="form-check-input notification-channel notification-channel-item"
                                                type="checkbox" id="notification_channel_mail" name="notification_channels[]"
                                                value="mail" checked>
                                            <label class="form-check-label" for="notification_channel_mail">Mail</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-check">
                                            <input class="form-check-input notification-channel notification-channel-item"
                                                type="checkbox" id="notification_channel_whatsapp"
                                                name="notification_channels[]" value="whatsapp" checked>
                                            <label class="form-check-label"
                                                for="notification_channel_whatsapp">WhatsApp</label>
                                        </div>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Choose email delivery, WhatsApp, or both. App and web
                                    notifications are sent automatically.</small>
                                <div class="invalid-feedback d-block" id="notification_channels_error"></div>
                            </div>
                            <div class="mb-3">
                                <label for="email_recipients" class="form-label">Email Recipients (Optional)</label>
                                <input type="text" class="form-control" id="email_recipients" name="email_recipients"
                                    placeholder="email1@example.com, email2@example.com">
                                <small class="form-text text-muted">Comma-separated emails for direct delivery. Admin and
                                    creator accounts are also notified on the selected channels.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="whatsapp_recipients" class="form-label">WhatsApp Recipients (Phone Numbers,
                                    Optional)</label>
                                <input type="text" class="form-control" id="whatsapp_recipients"
                                    name="whatsapp_recipients" placeholder="919876543210, 919876543211">
                                <small class="form-text text-muted">Use international format. Multiple numbers comma
                                    separated.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveEventBtn">Save Appointment</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Event Modal -->
        <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editEventModalLabel">Edit Calendar Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editEventForm">
                            @csrf
                            <input type="hidden" id="edit_event_id" name="event_id">
                            <div class="mb-3">
                                <label for="edit_event_title" class="form-label">Title <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_event_title" name="title" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_event_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_event_description" name="description" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_event_date" class="form-label">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_event_date" name="event_date"
                                        required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_event_time" class="form-label">Time <span
                                            class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="edit_event_time" name="event_time"
                                        required>
                                    <small class="form-text text-muted">30 minutes gap rule applies while rescheduling
                                        too.</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notification Platforms <span class="text-danger">*</span></label>
                                <div class="row g-2 notification-channel-group">
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input edit-notification-channel edit-notification-channel-all"
                                                type="checkbox" id="edit_notification_channel_all"
                                                name="notification_channels[]" value="all" checked>
                                            <label class="form-check-label" for="edit_notification_channel_all">All
                                                platforms</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input edit-notification-channel edit-notification-channel-item"
                                                type="checkbox" id="edit_notification_channel_mail"
                                                name="notification_channels[]" value="mail" checked>
                                            <label class="form-check-label" for="edit_notification_channel_mail">Mail</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input edit-notification-channel edit-notification-channel-item"
                                                type="checkbox" id="edit_notification_channel_whatsapp"
                                                name="notification_channels[]" value="whatsapp" checked>
                                            <label class="form-check-label"
                                                for="edit_notification_channel_whatsapp">WhatsApp</label>
                                        </div>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Choose email delivery, WhatsApp, or both. App and web
                                    notifications are sent automatically.</small>
                                <div class="invalid-feedback d-block" id="edit_notification_channels_error"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email_recipients" class="form-label">Email Recipients (Optional)</label>
                                <input type="text" class="form-control" id="edit_email_recipients"
                                    name="email_recipients" placeholder="email1@example.com, email2@example.com">
                                <small class="form-text text-muted">Comma-separated emails for direct delivery.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_whatsapp_recipients" class="form-label">WhatsApp Recipients (Phone Numbers,
                                    Optional)</label>
                                <input type="text" class="form-control" id="edit_whatsapp_recipients"
                                    name="whatsapp_recipients" placeholder="919876543210, 919876543211">
                                <small class="form-text text-muted">International format. Multiple numbers comma
                                    separated.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1"><strong>Event-Time Notification:</strong> <span
                                        id="event_time_status"></span></p>
                                <p class="mb-0"><strong>Created By:</strong> <span id="created_by"></span></p>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="updateEventBtn">Update Event</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@canany(['view_dashboard', 'view_calendar'])
    @push('styles')
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
        <style>
            #calendar {
                min-width: 0;
            }

            #calendar .fc-toolbar-title {
                color: #263b53;
                font-size: 1.1rem;
                font-weight: 600;
            }

            #calendar .fc .fc-button {
                border-radius: 6px;
                box-shadow: none;
                text-transform: capitalize;
            }

            .calendar-dashboard-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 340px;
                gap: 1.25rem;
                align-items: stretch;
            }

            .calendar-upcoming {
                min-width: 0;
                padding-left: 1.25rem;
                border-left: 1px solid #e8edf2;
                display: flex;
                flex-direction: column;
            }

            .calendar-add-event {
                min-height: 42px;
                margin-bottom: 1.25rem;
                border-radius: 6px;
            }

            .calendar-view-all {
                min-height: 36px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 600;
            }

            .calendar-upcoming-title {
                margin: 0 0 0.4rem;
                color: #25374a;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .calendar-selected-date {
                margin: 0 0 0.4rem;
                color: #7b8794;
                font-size: 0.75rem;
            }

            .calendar-event-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem 0.65rem;
                margin: 0.25rem 0 0.35rem;
            }

            .calendar-event-legend span {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                color: #6d7885;
                font-size: 0.68rem;
            }

            .calendar-category-dot {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
            }

            .calendar-category-dot.task {
                background: #167d9a;
            }

            .calendar-category-dot.appointment {
                background: #64748b;
            }

            .calendar-category-dot.renewal {
                background: #bc7616;
            }

            .calendar-category-dot.lead {
                background: #7954a5;
            }

            .calendar-category-dot.todo {
                background: #29845b;
            }

            .calendar-category-dot.order {
                background: #176ee2;
            }

            .calendar-event-kind {
                display: inline-block;
                margin-top: 0.25rem;
                padding: 0.12rem 0.38rem;
                border-radius: 999px;
                font-size: 0.62rem;
                line-height: 1.35;
            }

            #calendar .calendar-category-marker {
                display: block;
                width: 12px;
                height: 12px;
                min-height: 12px;
                margin: 2px;
                border-radius: 3px;
            }

            #calendar .fc-daygrid-event {
                display: none !important;
            }

            #calendar .fc-daygrid-day-frame {
                position: relative;
                padding-bottom: 18px;
            }

            #calendar .calendar-day-markers {
                display: flex;
                position: absolute;
                right: 4px;
                bottom: 4px;
                left: 4px;
                height: 10px;
                align-items: center;
                justify-content: center;
                gap: 3px;
                overflow: hidden;
                pointer-events: none;
            }

            #calendar .calendar-day-marker {
                display: block;
                flex: 0 0 8px;
                width: 8px;
                height: 8px;
                border-radius: 2px;
            }

            .calendar-upcoming-list {
                max-height: 460px;
                overflow-y: auto;
            }

            .calendar-upcoming-item {
                display: flex;
                width: 100%;
                gap: 0.65rem;
                align-items: flex-start;
                margin-bottom: 0.5rem;
                padding: 0.75rem;
                border: 1px solid transparent;
                border-radius: 8px;
                text-align: left;
                color: inherit;
                transition: filter 180ms ease, transform 180ms ease;
            }

            .calendar-upcoming-item:hover {
                filter: brightness(0.97);
                transform: translateY(-1px);
            }

            .calendar-event-mark {
                display: inline-flex;
                flex: 0 0 34px;
                width: 34px;
                height: 34px;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .calendar-event-copy {
                min-width: 0;
            }

            .calendar-event-name {
                display: block;
                overflow-wrap: anywhere;
                color: inherit;
                font-size: 0.84rem;
                font-weight: 600;
            }

            .calendar-event-time,
            .calendar-event-description {
                display: block;
                margin-top: 0.2rem;
                color: inherit;
                opacity: 0.72;
                font-size: 0.72rem;
                line-height: 1.4;
            }

            .calendar-event-description {
                overflow: hidden;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
            }

            .calendar-upcoming-empty {
                padding: 1rem 0;
                color: #7b8794;
                font-size: 0.82rem;
            }

            .calendar-upcoming-more {
                align-self: center;
                margin-top: 0.75rem;
                padding-inline: 1.5rem;
                color: #35475a;
                background: #edf3f9;
                border: 0;
            }

            .appointment-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 1.1rem 1.25rem;
                background: #fff;
                border-bottom: 1px solid #e8edf2;
            }

            @media (max-width: 575.98px) {
                .appointment-header {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }

            #calendar .fc .fc-daygrid-day-top {
                padding: 4px 6px;
            }

            #calendar .fc .fc-day-today {
                background-color: #f0f7ff;
            }

            .fc-event {
                cursor: pointer;
            }

            .fc-daygrid-day:hover {
                background-color: #f8f9fa;
            }

            @media (max-width: 991.98px) {
                .calendar-dashboard-layout {
                    grid-template-columns: minmax(0, 1fr);
                }

                .calendar-upcoming {
                    padding: 1.25rem 0 0;
                    border-top: 1px solid #e8edf2;
                    border-left: 0;
                }

                .calendar-upcoming-list {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    column-gap: 1rem;
                }
            }

            @media (max-width: 768px) {
                #calendar .fc-toolbar-title {
                    font-size: 1rem;
                }

                #calendar .fc-header-toolbar {
                    flex-direction: column;
                    gap: 0.65rem;
                }

                #calendar .fc-toolbar-chunk {
                    display: flex;
                    justify-content: center;
                }
            }

            @media (max-width: 575.98px) {
                .calendar-upcoming-list {
                    grid-template-columns: minmax(0, 1fr);
                }
            }
        </style>
    @endpush
@endcan

@canany(['view_dashboard', 'view_calendar'])
    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var upcomingListEl = document.getElementById('calendarUpcomingList');
                var upcomingMoreButton = document.getElementById('calendarUpcomingMore');
                var selectedDateEl = document.getElementById('calendarSelectedDate');
                var upcomingEvents = [];
                var showAllUpcomingEvents = false;
                var selectedCalendarDate = calendarDateKey(new Date());
                var calendarEventStyles = {
                    appointment: {
                        label: 'Appointment',
                        background: '#edf1f5',
                        color: '#536171'
                    },
                    task: {
                        label: 'Task',
                        background: '#e5f3f6',
                        color: '#12677e'
                    },
                    renewal: {
                        label: 'Renewal',
                        background: '#fff3df',
                        color: '#965b0b'
                    },
                    lead: {
                        label: 'Lead Management',
                        background: '#f1eafb',
                        color: '#684391'
                    },
                    todo: {
                        label: 'To-do',
                        background: '#e6f4eb',
                        color: '#236c49'
                    },
                    order: {
                        label: 'Order receipt',
                        background: '#e7f0ff',
                        color: '#176ee2'
                    }
                };

                function openCalendarItem(event) {
                    var eventType = event.extendedProps.event_type || 'appointment';

                    if (eventType === 'appointment') {
                        loadEventDetails(event.id);
                        return;
                    }

                    var destination = event.extendedProps.url;
                    if (destination) {
                        window.location.href = destination;
                    }
                }

                function calendarDateKey(date) {
                    return date.getFullYear() + '-' +
                        String(date.getMonth() + 1).padStart(2, '0') + '-' +
                        String(date.getDate()).padStart(2, '0');
                }

                function formatCalendarDate(dateKey) {
                    return new Intl.DateTimeFormat(undefined, {
                        weekday: 'long',
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    }).format(new Date(dateKey + 'T12:00:00'));
                }

                function renderCalendarMarkers(events) {
                    var markersByDate = new Map();

                    events.forEach(function(event) {
                        if (!event.start) return;

                        var dateKey = calendarDateKey(event.start);
                        var eventType = event.extendedProps.event_type || 'appointment';
                        var dayMarkers = markersByDate.get(dateKey) || new Map();

                        if (!dayMarkers.has(eventType)) {
                            dayMarkers.set(eventType, {
                                color: event.backgroundColor || '#64748b',
                                label: event.extendedProps.event_type_label ||
                                    (calendarEventStyles[eventType] || calendarEventStyles.appointment)
                                    .label
                            });
                        }

                        markersByDate.set(dateKey, dayMarkers);
                    });

                    document.querySelectorAll('#calendar .fc-daygrid-day').forEach(function(dayCell) {
                        var frame = dayCell.querySelector('.fc-daygrid-day-frame');
                        if (!frame) return;

                        var markerRow = frame.querySelector('.calendar-day-markers');
                        if (!markerRow) {
                            markerRow = document.createElement('div');
                            markerRow.className = 'calendar-day-markers';
                            markerRow.setAttribute('aria-hidden', 'true');
                            frame.appendChild(markerRow);
                        }

                        markerRow.replaceChildren();
                        var dayMarkers = markersByDate.get(dayCell.dataset.date);
                        if (!dayMarkers) return;

                        dayMarkers.forEach(function(markerData) {
                            var marker = document.createElement('span');
                            marker.className = 'calendar-day-marker';
                            marker.style.backgroundColor = markerData.color;
                            marker.title = markerData.label;
                            markerRow.appendChild(marker);
                        });
                    });
                }

                function renderUpcomingEvents(events) {
                    if (!upcomingListEl) return;

                    upcomingEvents = events
                        .filter(function(event) {
                            return event.start && calendarDateKey(event.start) === selectedCalendarDate;
                        })
                        .sort(function(first, second) {
                            return first.start - second.start;
                        });

                    var visibleEvents = showAllUpcomingEvents ? upcomingEvents : upcomingEvents.slice(0, 4);
                    upcomingListEl.replaceChildren();
                    if (selectedDateEl) {
                        selectedDateEl.textContent = formatCalendarDate(selectedCalendarDate);
                    }

                    if (visibleEvents.length === 0) {
                        var emptyMessage = document.createElement('p');
                        emptyMessage.className = 'calendar-upcoming-empty';
                        emptyMessage.textContent = 'No scheduled items for this date.';
                        upcomingListEl.appendChild(emptyMessage);
                    }

                    visibleEvents.forEach(function(event, index) {
                        var eventType = event.extendedProps.event_type || 'appointment';
                        var eventStyle = calendarEventStyles[eventType] || calendarEventStyles.appointment;
                        var item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'calendar-upcoming-item';
                        item.style.backgroundColor = eventStyle.background;
                        item.style.color = eventStyle.color;
                        item.setAttribute('aria-label', 'Open ' + eventStyle.label + ': ' + event.title);

                        var mark = document.createElement('span');
                        mark.className = 'calendar-event-mark';
                        mark.style.backgroundColor = eventStyle.background;
                        mark.style.color = eventStyle.color;
                        mark.textContent = (event.title || 'E').trim().charAt(0).toUpperCase();

                        var copy = document.createElement('span');
                        copy.className = 'calendar-event-copy';

                        var name = document.createElement('span');
                        name.className = 'calendar-event-name';
                        name.textContent = event.title || 'Untitled appointment';

                        var time = document.createElement('span');
                        time.className = 'calendar-event-time';
                        var dateLabel = calendarDateKey(event.start) === calendarDateKey(new Date()) ?
                            'Today' :
                            new Intl.DateTimeFormat(undefined, {
                                month: 'short',
                                day: 'numeric'
                            }).format(event.start);
                        var timeLabel = new Intl.DateTimeFormat(undefined, {
                            hour: 'numeric',
                            minute: '2-digit'
                        }).format(event.start);
                        time.textContent = dateLabel + (event.allDay ? '' : ' at ' + timeLabel);

                        var kind = document.createElement('span');
                        kind.className = 'calendar-event-kind';
                        kind.style.backgroundColor = eventStyle.background;
                        kind.style.color = eventStyle.color;
                        kind.textContent = event.extendedProps.event_type_label || eventStyle.label;

                        copy.appendChild(name);
                        copy.appendChild(time);
                        copy.appendChild(kind);

                        var description = event.extendedProps.description || event.extendedProps
                            .event_description;
                        if (description) {
                            var descriptionText = document.createElement('span');
                            descriptionText.className = 'calendar-event-description';
                            descriptionText.textContent = description;
                            copy.appendChild(descriptionText);
                        }

                        item.appendChild(mark);
                        item.appendChild(copy);
                        item.addEventListener('click', function() {
                            openCalendarItem(event);
                        });
                        upcomingListEl.appendChild(item);
                    });

                    if (upcomingMoreButton) {
                        upcomingMoreButton.hidden = upcomingEvents.length <= 4;
                        upcomingMoreButton.textContent = showAllUpcomingEvents ? 'Show Less' : 'See More';
                    }
                }

                if (upcomingMoreButton) {
                    upcomingMoreButton.addEventListener('click', function() {
                        showAllUpcomingEvents = !showAllUpcomingEvents;
                        renderUpcomingEvents(calendar.getEvents());
                    });
                }

                var addEventButton = document.querySelector('.calendar-add-event');
                if (addEventButton) {
                    addEventButton.addEventListener('click', function() {
                        $('#event_date').val(selectedCalendarDate);
                    });
                }

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    aspectRatio: 1.75,
                    expandRows: true,
                    themeSystem: 'bootstrap5',
                    editable: false,
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                    eventsSet: function(events) {
                        renderCalendarMarkers(events);
                        renderUpcomingEvents(events);
                    },
                    events: '{{ route('calendar.events') }}',
                    eventContent: function(info) {
                        var marker = document.createElement('span');
                        marker.className = 'calendar-category-marker';
                        marker.setAttribute('aria-hidden', 'true');
                        marker.style.backgroundColor = info.event.backgroundColor || '#64748b';

                        return {
                            domNodes: [marker]
                        };
                    },

                    // When user clicks on a date
                    dateClick: function(info) {
                        selectedCalendarDate = info.dateStr;
                        showAllUpcomingEvents = false;
                        $('#event_date').val(info.dateStr);
                        renderUpcomingEvents(calendar.getEvents());
                    },

                    // When user clicks on an event
                    eventClick: function(info) {
                        openCalendarItem(info.event);
                    },

                    eventDidMount: function(info) {
                        var eventType = info.event.extendedProps.event_type || 'appointment';
                        var eventStyle = calendarEventStyles[eventType] || calendarEventStyles.appointment;
                        var categoryLabel = info.event.extendedProps.event_type_label || eventStyle.label;

                        info.el.setAttribute('aria-label', categoryLabel);
                        $(info.el).tooltip({
                            title: categoryLabel,
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    }
                });

                calendar.render();

                $(document).on('change', '.notification-channel, .edit-notification-channel', function() {
                    var isEdit = $(this).hasClass('edit-notification-channel');
                    var isAll = $(this).hasClass(isEdit ? 'edit-notification-channel-all' :
                        'notification-channel-all');
                    syncNotificationChannelGroup(isEdit, isAll);
                });

                // Save new event
                $('#saveEventBtn').click(function() {
                    // Clear previous validation errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    if (getSelectedNotificationChannels(false).length === 0) {
                        showAlert('error', 'Please select at least one notification platform.');
                        return;
                    }

                    if (hasCalendarConflict($('#event_date').val(), $('#event_time').val())) {
                        showAlert('error',
                            'Another meeting is already scheduled within 30 minutes of this slot. Please select another time.'
                        );
                        return;
                    }

                    var formData = {
                        title: $('#event_title').val(),
                        description: $('#event_description').val(),
                        event_date: $('#event_date').val(),
                        event_time: $('#event_time').val(),
                        email_recipients: $('#email_recipients').val(),
                        whatsapp_recipients: $('#whatsapp_recipients').val(),
                        notification_channels: getSelectedNotificationChannels(false),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    // Disable button to prevent double submission
                    $('#saveEventBtn').prop('disabled', true).text('Saving...');

                    $.ajax({
                        url: '{{ route('calendar.store') }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            console.log('Event created response:', response);
                            if (response.success) {
                                $('#addEventModal').modal('hide');
                                $('#addEventForm')[0].reset();
                                calendar.refetchEvents();
                                showAlert('success', response.message ||
                                    'Event created successfully!');
                            } else {
                                showAlert('error', response.message || 'Failed to create event');
                            }
                        },
                        error: function(xhr) {
                            console.error('Event creation error:', xhr);
                            if (xhr.status === 422) {
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    var errors = xhr.responseJSON.errors;
                                    $.each(errors, function(key, value) {
                                        if (key.indexOf('notification_channels') === 0) {
                                            $('#notification_channels_error').text(value[
                                                0]);
                                            return;
                                        }
                                        var inputField = $('#event_' + key);
                                        if (inputField.length === 0) {
                                            inputField = $('[name="' + key + '"]');
                                        }
                                        inputField.addClass('is-invalid');
                                        inputField.next('.invalid-feedback').text(value[0]);
                                    });
                                    showAlert('error', 'Please fix the validation errors');
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    showAlert('error', xhr.responseJSON.message);
                                } else {
                                    showAlert('error', 'Validation error');
                                }
                            } else {
                                var errorMsg = 'Error creating event';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                showAlert('error', errorMsg);
                            }
                        },
                        complete: function() {
                            // Re-enable button
                            $('#saveEventBtn').prop('disabled', false).text('Save Appointment');
                        }
                    });
                });

                // Load event details for editing
                function loadEventDetails(eventId) {
                    $.ajax({
                        url: '{{ url('calendar/events') }}/' + eventId,
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                var event = response.event;
                                $('#edit_event_id').val(event.id);
                                $('#edit_event_title').val(event.title);
                                $('#edit_event_description').val(event.description);
                                $('#edit_event_date').val(event.event_date);
                                $('#edit_event_time').val(event.event_time);
                                $('#edit_email_recipients').val(event.email_recipients);
                                $('#edit_whatsapp_recipients').val(event.whatsapp_recipients || '');
                                setSelectedNotificationChannels(event.notification_channels || []);
                                $('#event_time_status').html(event.event_time_notification_sent ?
                                    '<span class="badge bg-success">Sent</span>' :
                                    '<span class="badge bg-warning">Pending</span>');
                                $('#created_by').text(event.created_by);
                                $('#editEventModal').modal('show');
                            }
                        },
                        error: function(xhr) {
                            showAlert('error', 'Error loading event details');
                        }
                    });
                }

                // Update event
                $('#updateEventBtn').click(function() {
                    // Clear previous validation errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    if (getSelectedNotificationChannels(true).length === 0) {
                        showAlert('error', 'Please select at least one notification platform.');
                        return;
                    }

                    var eventId = $('#edit_event_id').val();

                    if (hasCalendarConflict($('#edit_event_date').val(), $('#edit_event_time').val(),
                            eventId)) {
                        showAlert('error',
                            'Another meeting is already scheduled within 30 minutes of this slot. Please select another time.'
                        );
                        return;
                    }

                    var formData = {
                        title: $('#edit_event_title').val(),
                        description: $('#edit_event_description').val(),
                        event_date: $('#edit_event_date').val(),
                        event_time: $('#edit_event_time').val(),
                        email_recipients: $('#edit_email_recipients').val(),
                        whatsapp_recipients: $('#edit_whatsapp_recipients').val(),
                        notification_channels: getSelectedNotificationChannels(true),
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'PUT'
                    };

                    // Disable button to prevent double submission
                    $('#updateEventBtn').prop('disabled', true).text('Updating...');

                    $.ajax({
                        url: '{{ url('calendar/events') }}/' + eventId,
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            console.log('Event updated response:', response);
                            if (response.success) {
                                $('#editEventModal').modal('hide');
                                calendar.refetchEvents();
                                showAlert('success', response.message ||
                                    'Event updated successfully!');
                            } else {
                                showAlert('error', response.message || 'Failed to update event');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                if (xhr.responseJSON.errors) {
                                    var errors = xhr.responseJSON.errors;
                                    $.each(errors, function(key, value) {
                                        if (key.indexOf('notification_channels') === 0) {
                                            $('#edit_notification_channels_error').text(
                                                value[0]);
                                            return;
                                        }
                                        var inputField = $('#edit_event_' + key);
                                        if (inputField.length === 0) {
                                            inputField = $('#edit_' + key);
                                        }
                                        inputField.addClass('is-invalid');
                                        inputField.next('.invalid-feedback').text(value[0]);
                                    });
                                    showAlert('error', 'Please fix the validation errors');
                                } else {
                                    showAlert('error', xhr.responseJSON.message ||
                                        'Validation error');
                                }
                            } else {
                                showAlert('error', xhr.responseJSON?.message ||
                                    'Error updating event');
                            }
                        },
                        complete: function() {
                            // Re-enable button
                            $('#updateEventBtn').prop('disabled', false).text('Update Event');
                        }
                    });
                });

                // Delete event
                $('#deleteEventBtn').click(function() {
                    if (!confirm('Are you sure you want to delete this event?')) {
                        return;
                    }

                    var eventId = $('#edit_event_id').val();

                    // Disable button to prevent double submission
                    $('#deleteEventBtn').prop('disabled', true).text('Deleting...');

                    $.ajax({
                        url: '{{ url('calendar/events') }}/' + eventId,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#editEventModal').modal('hide');
                                calendar.refetchEvents();
                                showAlert('success', response.message ||
                                    'Event deleted successfully!');
                            }
                        },
                        error: function(xhr) {
                            showAlert('error', xhr.responseJSON?.message || 'Error deleting event');
                        },
                        complete: function() {
                            // Re-enable button
                            $('#deleteEventBtn').prop('disabled', false).text('Delete');
                        }
                    });
                });

                // Helper function to show alerts
                function showAlert(type, message) {
                    // Remove any existing alerts first
                    $('.page-content > .alert').remove();

                    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    var iconClass = type === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle';

                    var alertHtml = '<div class="alert ' + alertClass +
                        ' alert-dismissible fade show" role="alert" style="position: relative; z-index: 9999;">' +
                        '<i class="' + iconClass + ' me-2"></i>' +
                        '<strong>' + message + '</strong>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';

                    $('.page-content').prepend(alertHtml);

                    // Scroll to top to show alert
                    $('html, body').animate({
                        scrollTop: 0
                    }, 300);

                    // Auto-dismiss after 5 seconds
                    setTimeout(function() {
                        $('.page-content > .alert').fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 5000);
                }

                function getSelectedNotificationChannels(isEdit = false) {
                    var allSelector = isEdit ? '.edit-notification-channel-all' : '.notification-channel-all';
                    var itemSelector = isEdit ? '.edit-notification-channel-item' : '.notification-channel-item';

                    if ($(allSelector).is(':checked')) {
                        return ['all'];
                    }

                    var channels = [];
                    $(itemSelector).each(function() {
                        if ($(this).is(':checked')) {
                            channels.push($(this).val());
                        }
                    });

                    return channels;
                }

                function setSelectedNotificationChannels(channels) {
                    var selected = Array.isArray(channels) ? channels.map(function(channel) {
                        return String(channel).toLowerCase();
                    }) : [];

                    var allChannelKeys = ['mail', 'whatsapp'];
                    var allSelected = selected.length === 0 ||
                        selected.indexOf('all') !== -1 ||
                        allChannelKeys.every(function(channel) {
                            return selected.indexOf(channel) !== -1;
                        });

                    $('.edit-notification-channel-all').prop('checked', allSelected);
                    $('.edit-notification-channel-item').each(function() {
                        $(this).prop('checked', allSelected || selected.indexOf(String($(this).val())) !== -1);
                    });
                }

                function syncNotificationChannelGroup(isEdit = false, sourceIsAll = false) {
                    var allSelector = isEdit ? '.edit-notification-channel-all' : '.notification-channel-all';
                    var itemSelector = isEdit ? '.edit-notification-channel-item' : '.notification-channel-item';

                    if (sourceIsAll) {
                        if ($(allSelector).is(':checked')) {
                            $(itemSelector).prop('checked', true);
                        } else {
                            $(itemSelector).prop('checked', false);
                        }
                        return;
                    }

                    var checkedCount = $(itemSelector + ':checked').length;
                    var totalCount = $(itemSelector).length;

                    if (checkedCount === totalCount) {
                        $(allSelector).prop('checked', true);
                    } else if (checkedCount < totalCount) {
                        $(allSelector).prop('checked', false);
                    }
                }

                function hasAnyRecipient(emailRecipients, whatsappRecipients) {
                    return (emailRecipients && emailRecipients.trim().length > 0) ||
                        (whatsappRecipients && whatsappRecipients.trim().length > 0);
                }

                function hasCalendarConflict(dateValue, timeValue, excludeEventId = null) {
                    if (!dateValue || !timeValue) {
                        return false;
                    }

                    var selectedDateTime = new Date(dateValue + 'T' + timeValue + ':00');
                    if (isNaN(selectedDateTime.getTime())) {
                        return false;
                    }

                    var bufferMs = 30 * 60 * 1000;
                    var events = calendar.getEvents();

                    return events.some(function(event) {
                        if (!event.start) {
                            return false;
                        }

                        if (excludeEventId !== null && String(event.id) === String(excludeEventId)) {
                            return false;
                        }

                        var diff = Math.abs(event.start.getTime() - selectedDateTime.getTime());
                        return diff < bufferMs;
                    });
                }

                // Clear validation errors when modal is closed
                $('#addEventModal, #editEventModal').on('hidden.bs.modal', function() {
                    $(this).find('.is-invalid').removeClass('is-invalid');
                    $(this).find('.invalid-feedback').text('');
                });
            });
        </script>
    @endpush
@endcan
