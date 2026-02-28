@extends('/layout/master')
@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @auth
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Welcome, {{ Auth::user()->name }}!</h5>
                                <p class="card-text">You are successfully logged in to the Technofra Renewal Master dashboard.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Summary Cards -->

            <!--end summary row-->

            <!-- Renewal Statistics Cards -->
            <div class="row">
                <div class="col-lg-4">
                    <div class="card radius-10 border-start border-0 border-4 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Servies</p>
                                    <h4 class="my-1 text-info">{{ $totalRenewals ?? 0 }}</h4>
                                    <p class="mb-0 font-13">All services in system</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                    <i class='bx bx-list-ul'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card radius-10 border-start border-0 border-4 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Renewals Due This Week</p>
                                    <h4 class="my-1 text-warning">{{ $renewalsDueThisWeek ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Expiring within 7 days</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                    <i class='bx bx-time-five'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card radius-10 border-start border-0 border-4 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Overdue Renewals</p>
                                    <h4 class="my-1 text-danger">{{ $overdueRenewals ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Already expired</p>
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
            <!-- Critical Renewals Table (Overdue + Upcoming) -->
            <div class="card radius-10 mt-4">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">Upcoming Renewals</h6>
                            <p class="mb-0 text-muted font-13">Overdue services and services expiring within the next 5 days
                            </p>
                        </div>
                        <div class="ms-auto d-flex align-items-center gap-2">
                            <span class="badge bg-danger">
                                <p class="mb-0 p-2">{{ $overdueRenewals ?? 0 }} Overdue</p>
                            </span>
                            <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-list-ul"></i> View All Services
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Priority</th>
                                    <th>Service ID</th>
                                    <th>Client Name</th>
                                    <th>Vendor Name</th>
                                    <th>Service Name</th>
                                    <th>Start Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Billing Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($criticalRenewals as $service)
                                    @php
                                        $today = \Carbon\Carbon::today();
                                        $daysLeft = $today->diffInDays($service->end_date, false);
                                        $isOverdue = $service->end_date < $today;

                                        if ($isOverdue) {
                                            $urgencyClass = 'text-danger';
                                            $priorityBadge = 'bg-danger';
                                            $priorityText = 'OVERDUE';
                                            $statusText = abs($daysLeft) . ' days overdue';
                                        } else {
                                            $urgencyClass =
                                                $daysLeft <= 1
                                                    ? 'text-danger'
                                                    : ($daysLeft <= 3
                                                        ? 'text-warning'
                                                        : 'text-info');
                                            $priorityBadge =
                                                $daysLeft <= 1
                                                    ? 'bg-danger'
                                                    : ($daysLeft <= 3
                                                        ? 'bg-warning'
                                                        : 'bg-info');
                                            $priorityText =
                                                $daysLeft <= 1 ? 'URGENT' : ($daysLeft <= 3 ? 'HIGH' : 'MEDIUM');

                                            if ($daysLeft == 0) {
                                                $statusText = 'Today';
                                            } elseif ($daysLeft == 1) {
                                                $statusText = 'Tomorrow';
                                            } else {
                                                $statusText = $daysLeft . ' days left';
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                        <td>
                                            <span class="badge {{ $priorityBadge }} font-11">
                                                {{ $priorityText }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 font-14">#{{ $service->id }}</h6>
                                            </div>
                                        </td>
                                        <td>{{ $service->client->cname ?? 'N/A' }}</td>
                                        <td>{{ $service->vendor->name ?? 'N/A' }}</td>
                                        <td>{{ $service->service_name }}</td>
                                        <td>{{ $service->start_date->format('d M Y') }}</td>
                                        <td class="{{ $urgencyClass }}">
                                            <strong>{{ $service->end_date->format('d M Y') }}</strong>
                                            <br>
                                            <small class="{{ $urgencyClass }}">{{ $statusText }}</small>
                                        </td>
                                        <td>
                                            @if ($isOverdue)
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-{{ $service->status_badge }}">
                                                    {{ ucfirst($service->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $service->billing_date->format('d M Y') }}</td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('services.show', $service->id) }}" title="View">
                                                    <i class='bx bxs-show'></i>
                                                </a>
                                                <a href="{{ route('services.edit', $service->id) }}" class="ms-2"
                                                    title="Edit">
                                                    <i class='bx bxs-edit'></i>
                                                </a>
                                                <a href="{{ route('send-mail', $service->id) }}" class="ms-2 text-primary"
                                                    title="Send Renewal Email">
                                                    <i class='bx bx-mail-send'></i>
                                                </a>
                                                <form action="{{ route('send-whatsapp-renewal', $service->id) }}"
                                                    method="POST" class="ms-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link text-success p-0 m-0"
                                                        title="Send WhatsApp Reminder"
                                                        onclick="return confirm('Send WhatsApp renewal reminder to client?')">
                                                        <i class='bx bxl-whatsapp'></i>
                                                    </button>
                                                </form>
                                                @if ($isOverdue)
                                                    <a href="{{ route('services.edit', $service->id) }}"
                                                        class="ms-2 text-success" title="Renew Service">
                                                        <i class='bx bx-refresh'></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class='bx bx-calendar-check'
                                                    style="font-size: 48px; color: #28a745;"></i>
                                                <h6 class="mt-2 text-success">Excellent! No critical renewals</h6>
                                                <p class="text-muted">No overdue services and no renewals due in the next 5
                                                    days</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- CRM Dashboard Section -->
            <div class="row">
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
                                <canvas id="chart1" width="1031" height="260"
                                    style="display: block; box-sizing: border-box; height: 260px; width: 1031px;"></canvas>
                            </div>
                        </div>
                        <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                            <div class="col">
                                <div class="p-3">
                                    <h5 class="mb-0">24.15M</h5>
                                    <small class="mb-0">Overall Visitor <span> <i
                                                class="bx bx-up-arrow-alt align-middle"></i> 2.43%</span></small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3">
                                    <h5 class="mb-0">12:38</h5>
                                    <small class="mb-0">Visitor Duration <span> <i
                                                class="bx bx-up-arrow-alt align-middle"></i> 12.65%</span></small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3">
                                    <h5 class="mb-0">639.82</h5>
                                    <small class="mb-0">Pages/Visit <span> <i
                                                class="bx bx-up-arrow-alt align-middle"></i> 5.62%</span></small>
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
                                    <h6 class="mb-0">Tasks summary</h6>
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
                                <canvas id="chart2" width="487" height="220"
                                    style="display: block; box-sizing: border-box; height: 220px; width: 487px;"></canvas>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li
                                class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                                Manish <span class="badge bg-success rounded-pill">25</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Saurabh <span class="badge bg-danger rounded-pill">10</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Pradnya <span class="badge bg-primary rounded-pill">65</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Roshan <span class="badge bg-warning text-dark rounded-pill">14</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>



            <div class="row row-cols-1 row-cols-lg-3">
                <div class="col d-flex">
                    <div class="card radius-10 w-100">
                        <div class="card-body">
                            <p class="font-weight-bold mb-1 text-secondary">Revenue Snapshot</p>
                            <div class="d-flex align-items-center mb-4">
                                <div>
                                    <h4 class="mb-0">₹89,540</h4>
                                </div>
                                <div class="">
                                    <p class="mb-0 align-self-center font-weight-bold text-success ms-2">4.4% <i
                                            class="bx bxs-up-arrow-alt mr-2"></i>
                                    </p>
                                </div>
                            </div>
                            <div class="chart-container-0 mt-5">
                                <canvas id="chart3" width="487" height="320"
                                    style="display: block; box-sizing: border-box; height: 320px; width: 487px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col d-flex">
                    <div class="card radius-10 w-100">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">Team Availability</h6>
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
                                <canvas id="chart4" width="487" height="260"
                                    style="display: block; box-sizing: border-box; height: 260px; width: 487px;"></canvas>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li
                                class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                                Manish <span class="badge bg-gradient-quepal rounded-pill">25</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Roshan <span class="badge bg-gradient-ibiza rounded-pill">10</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Saurbh <span class="badge bg-gradient-deepblue rounded-pill">65</span>
                            </li>
                        </ul>
                    </div>
                </div>
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
                                                <p class="mb-1 text-secondary font-11">{{ $ticket->customer->client_name ?? 'N/A' }}</p>
                                                <h6 class="mb-1" title="{{ $ticket->issue_description }}">
                                                    {{ Str::limit($ticket->issue_description, 50) }}
                                                </h6>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">
                                                        📁 {{ $ticket->project->project_name ?? 'N/A' }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class='bx bx-time-five'></i> {{ $ticket->created_at->format('M d, Y H:i') }}
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    @if($ticket->status == 'open')
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
                                                @if($ticket->priority == 'high')
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
            </div>


            <!-- Calendar Widget -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header appointment-header">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">Calendar Appointments</h6>
                                    <p class="mb-0 text-muted font-13">Create meetings and auto-send WhatsApp message on selected time</p>
                                </div>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#addEventModal">
                                        <i class="bx bx-plus"></i> Add Appointments
                                    </button>
                                </div>
                            </div>
                            <div class="appointment-meta mt-3">
                                <span class="badge bg-light text-dark border">Exact event-time alert</span>
                                <span class="badge bg-light text-dark border">K3 WhatsApp template based</span>
                                <span class="badge bg-light text-dark border">30-minute slot protection</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end calendar row-->







        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button-->
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->

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
                            <strong>Notification Flow:</strong> WhatsApp template message automatically at selected meeting time.
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
                                <small class="form-text text-muted">Choose a slot with minimum 30 minutes difference from existing meetings.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email_recipients" class="form-label">Email Recipients (Optional)</label>
                            <input type="text" class="form-control" id="email_recipients" name="email_recipients"
                                placeholder="email1@example.com, email2@example.com">
                            <small class="form-text text-muted">Comma-separated emails. You can keep this empty if WhatsApp numbers are added.</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="whatsapp_recipients" class="form-label">WhatsApp Recipients (Phone
                                Numbers) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="whatsapp_recipients"
                                name="whatsapp_recipients" placeholder="919876543210, 919876543211">
                            <small class="form-text text-muted">Use international format. Multiple numbers comma separated.</small>
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
                                <small class="form-text text-muted">30 minutes gap rule applies while rescheduling too.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email_recipients" class="form-label">Email Recipients (Optional)</label>
                            <input type="text" class="form-control" id="edit_email_recipients"
                                name="email_recipients" placeholder="email1@example.com, email2@example.com">
                            <small class="form-text text-muted">Comma-separated emails. Optional when WhatsApp numbers exist.</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_whatsapp_recipients" class="form-label">WhatsApp Recipients (Phone
                                Numbers) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_whatsapp_recipients"
                                name="whatsapp_recipients" placeholder="919876543210, 919876543211">
                            <small class="form-text text-muted">International format. Multiple numbers comma separated.</small>
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
@endsection

@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }

        .appointment-header {
            background: linear-gradient(135deg, #f7fbff 0%, #eef5ff 100%);
            border-bottom: 1px solid #dde8fb;
        }

        .appointment-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .appointment-tip {
            border: 1px solid #d8e7ff;
            background: #f2f7ff;
            color: #173d75;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.875rem;
        }

        .fc-event {
            cursor: pointer;
        }

        .fc-daygrid-day:hover {
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .appointment-meta .badge {
                font-size: 0.72rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'bootstrap5',
                editable: false,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                events: '{{ route('calendar.events') }}',

                // When user clicks on a date
                dateClick: function(info) {
                    $('#event_date').val(info.dateStr);
                    $('#addEventModal').modal('show');
                },

                // When user clicks on an event
                eventClick: function(info) {
                    var eventId = info.event.id;
                    loadEventDetails(eventId);
                },

                eventDidMount: function(info) {
                    // Add tooltip
                    $(info.el).tooltip({
                        title: info.event.title,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            });

            calendar.render();

            // Save new event
            $('#saveEventBtn').click(function() {
                // Clear previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                if (!hasAnyRecipient($('#email_recipients').val(), $('#whatsapp_recipients').val())) {
                    showAlert('error', 'Please add at least one email or WhatsApp recipient.');
                    return;
                }

                if (hasCalendarConflict($('#event_date').val(), $('#event_time').val())) {
                    showAlert('error',
                        'Another meeting is already scheduled within 30 minutes of this slot. Please select another time.');
                    return;
                }

                var formData = {
                    title: $('#event_title').val(),
                    description: $('#event_description').val(),
                    event_date: $('#event_date').val(),
                    event_time: $('#event_time').val(),
                    email_recipients: $('#email_recipients').val(),
                    whatsapp_recipients: $('#whatsapp_recipients').val(),
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

                if (!hasAnyRecipient($('#edit_email_recipients').val(), $('#edit_whatsapp_recipients').val())) {
                    showAlert('error', 'Please add at least one email or WhatsApp recipient.');
                    return;
                }

                var eventId = $('#edit_event_id').val();

                if (hasCalendarConflict($('#edit_event_date').val(), $('#edit_event_time').val(), eventId)) {
                    showAlert('error',
                        'Another meeting is already scheduled within 30 minutes of this slot. Please select another time.');
                    return;
                }

                var formData = {
                    title: $('#edit_event_title').val(),
                    description: $('#edit_event_description').val(),
                    event_date: $('#edit_event_date').val(),
                    event_time: $('#edit_event_time').val(),
                    email_recipients: $('#edit_email_recipients').val(),
                    whatsapp_recipients: $('#edit_whatsapp_recipients').val(),
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
