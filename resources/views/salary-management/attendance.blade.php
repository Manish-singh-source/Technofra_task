@extends('layout.master')

@push('styles')
    <style>
        .attendance-salary {
            --as-ink: #1d3048;
            --as-muted: #64748b;
            --as-line: #e2e8f0;
            --as-primary: #0d6efd;
            --as-bg-light: #f8fafc;
            color: var(--as-ink);
        }

        .attendance-salary .as-header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 1.5rem;
        }

        .attendance-salary .as-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
        }

        .attendance-salary .as-subtitle {
            margin: 3px 0 0;
            color: var(--as-muted);
            font-size: .82rem;
        }

        .attendance-salary .as-card,
        .attendance-salary .as-panel {
            border: 1px solid var(--as-line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        }

        .attendance-salary .as-card {
            min-height: 94px;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .attendance-salary .as-card:hover {
            box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
        }

        .attendance-salary .as-card .card-body {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
        }

        .attendance-salary .as-icon {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            place-items: center;
            border-radius: 50%;
            color: #fff;
            font-size: 1.25rem;
        }

        .attendance-salary .as-blue {
            background: #3b82f6;
        }

        .attendance-salary .as-green {
            background: #10b981;
        }

        .attendance-salary .as-coral {
            background: #ef4444;
        }

        .attendance-salary .as-violet {
            background: #8b5cf6;
        }

        .attendance-salary .as-label {
            color: #64748b;
            font-size: .78rem;
            font-weight: 500;
        }

        .attendance-salary .as-value {
            margin-top: 1px;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .attendance-salary .as-meta {
            margin-top: 3px;
            color: #94a3b8;
            font-size: .72rem;
        }

        /* Tabs Navigation */
        .attendance-salary .as-tabs-container {
            border-bottom: 2px solid #e2e8f0;
            background: transparent;
        }

        .attendance-salary .as-nav-tabs {
            border-bottom: 0;
            gap: 4px;
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .attendance-salary .as-nav-tabs .nav-link {
            border: 0;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 11px 16px;
            color: #64748b;
            font-size: .83rem;
            font-weight: 600;
            background: transparent;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s ease;
            margin-bottom: -2px;
        }

        .attendance-salary .as-nav-tabs .nav-link:hover {
            color: #0f172a;
            border-bottom-color: #cbd5e1;
        }

        .attendance-salary .as-nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border-bottom: 2px solid #0d6efd;
        }

        /* Filter Panel */
        .attendance-salary .as-filter-panel {
            padding: 14px 16px;
            background: #fff;
            border: 1px solid var(--as-line);
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .attendance-salary .as-search-input-wrap {
            position: relative;
        }

        .attendance-salary .as-search-input-wrap .bx-search {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .attendance-salary .as-search-input-wrap input {
            padding-left: 36px;
            font-size: .82rem;
            height: 38px;
            border-color: #e2e8f0;
            border-radius: 6px;
        }

        .attendance-salary .as-filter-select {
            height: 38px;
            font-size: .82rem;
            border-color: #e2e8f0;
            border-radius: 6px;
            color: #334155;
        }

        /* Panel Head & Content */
        .attendance-salary .as-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--as-line);
        }

        .attendance-salary .as-panel-head h2 {
            margin: 0;
            font-size: .92rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Employee Table */
        .attendance-salary .as-emp-table {
            margin: 0;
            width: 100%;
        }

        .attendance-salary .as-emp-table thead th {
            padding: 12px 14px;
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .attendance-salary .as-emp-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: .82rem;
            vertical-align: middle;
        }

        .attendance-salary .as-emp-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .attendance-salary .as-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: #e2e8f0;
        }

        .attendance-salary .as-avatar-placeholder {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #4338ca;
            display: inline-grid;
            place-items: center;
            font-size: .82rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .attendance-salary .as-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 14px;
            font-size: .7rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .attendance-salary .as-badge.active {
            background: #dcfce7;
            color: #15803d;
        }

        .attendance-salary .as-badge.probation {
            background: #fef3c7;
            color: #b45309;
        }

        .attendance-salary .as-badge.inactive,
        .attendance-salary .as-badge.left {
            background: #fee2e2;
            color: #b91c1c;
        }

        .attendance-salary .as-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            font-size: .88rem;
            transition: all .12s ease;
        }

        .attendance-salary .as-action-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        /* Attendance upload form */
        .attendance-salary .as-upload {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--as-line);
            background: #fff;
        }

        .attendance-salary .as-file {
            flex: 1 1 280px;
        }

        .attendance-salary .as-month {
            flex: 0 1 170px;
        }

        .attendance-salary .as-hint {
            width: 100%;
            margin: 0;
            color: var(--as-muted);
            font-size: .74rem;
        }

        .attendance-salary .as-table {
            min-width: 1120px;
            margin: 0;
        }

        .attendance-salary .as-table thead th {
            padding: 10px 8px;
            background: #f8fafc;
            color: #475569;
            font-size: .68rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .attendance-salary .as-table tbody td {
            padding: 9px 8px;
            border-color: #f1f5f9;
            color: #334155;
            font-size: .72rem;
            vertical-align: middle;
        }

        .attendance-salary .as-detail summary {
            list-style: none;
            cursor: pointer;
        }

        .attendance-salary .as-detail summary::-webkit-details-marker {
            display: none;
        }

        .attendance-salary .as-detail-box {
            min-width: 200px;
            padding: 10px;
            border: 1px solid var(--as-line);
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .12);
        }

        .attendance-salary .as-empty {
            padding: 44px 16px;
            color: var(--as-muted);
            text-align: center;
        }

        /* Right Panel: Quick Actions */
        .attendance-salary .as-quick-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 14px;
            margin-bottom: 8px;
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            background: #fff;
            color: #1e40af;
            font-size: .82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .15s ease;
        }

        .attendance-salary .as-quick-btn:last-child {
            margin-bottom: 0;
        }

        .attendance-salary .as-quick-btn:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
            transform: translateX(2px);
        }

        .attendance-salary .as-quick-btn i {
            font-size: 1.15rem;
            color: #3b82f6;
        }

        /* Right Panel: Recent Activity */
        .attendance-salary .as-activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .attendance-salary .as-activity-item:last-child {
            border-bottom: 0;
        }

        .attendance-salary .as-act-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .attendance-salary .as-act-green {
            background: #dcfce7;
            color: #16a34a;
        }

        .attendance-salary .as-act-amber {
            background: #fef3c7;
            color: #d97706;
        }

        .attendance-salary .as-act-blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .attendance-salary .as-act-purple {
            background: #f3e8ff;
            color: #9333ea;
        }

        .attendance-salary .as-act-title {
            margin: 0;
            font-size: .78rem;
            font-weight: 600;
            color: #1e293b;
        }

        .attendance-salary .as-act-sub {
            margin: 1px 0 0;
            font-size: .72rem;
            color: #64748b;
        }

        .attendance-salary .as-act-time {
            margin: 2px 0 0;
            font-size: .66rem;
            color: #94a3b8;
        }

        /* Holiday Cards */
        .attendance-salary .holiday-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            background: #fff;
            transition: transform .15s ease;
        }

        .attendance-salary .holiday-item:hover {
            transform: translateX(3px);
            border-color: #cbd5e1;
        }

        .attendance-salary .holiday-date-badge {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 700;
            font-size: .8rem;
            text-align: center;
            min-width: 95px;
        }

        /* Report Cards */
        .attendance-salary .report-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all .15s ease;
        }

        .attendance-salary .report-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 4px 14px rgba(59, 130, 246, .08);
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper attendance-salary">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="as-header-icon"><i class="bx bxs-user-detail"></i></span>
                    <div>
                        <h1 class="as-title">Staff &amp; Employee Master</h1>
                        <p class="as-subtitle">Manage employee details, joining/leaving information, salary, attendance, leave and documents.</p>
                    </div>
                </div>
                @if ($canManage)
                    <a href="{{ route('add-staff') }}" class="btn btn-primary btn-sm px-3 fw-semibold rounded-2">
                        <i class="bx bx-plus me-1"></i> Add New Employee
                    </a>
                @endif
            </div>

            {{-- Metric Cards --}}
            <div class="row g-2 g-xl-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card as-card h-100">
                        <div class="card-body">
                            <span class="as-icon as-blue"><i class="bx bx-group"></i></span>
                            <div>
                                <div class="as-label">Total Employees</div>
                                <div class="as-value">{{ number_format($totalEmployees) }}</div>
                                <div class="as-meta">Active: {{ $activeEmployees }} | Left: {{ $leftEmployees }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card as-card h-100">
                        <div class="card-body">
                            <span class="as-icon as-green"><i class="bx bx-calendar-check"></i></span>
                            <div>
                                <div class="as-label">Joined This Month</div>
                                <div class="as-value">{{ number_format($joinedThisMonth) }}</div>
                                <div class="as-meta">New joiners</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card as-card h-100">
                        <div class="card-body">
                            <span class="as-icon as-coral"><i class="bx bx-log-out"></i></span>
                            <div>
                                <div class="as-label">Left This Month</div>
                                <div class="as-value">{{ number_format($leftThisMonth) }}</div>
                                <div class="as-meta">Resigned / Relieved</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card as-card h-100">
                        <div class="card-body">
                            <span class="as-icon as-violet"><i class="bx bx-rupee"></i></span>
                            <div>
                                <div class="as-label">On Probation</div>
                                <div class="as-value">{{ number_format($onProbation) }}</div>
                                <div class="as-meta">Within 6 months</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Horizontal Navigation Tabs Bar --}}
            <div class="as-tabs-container mb-3">
                <ul class="nav as-nav-tabs" id="employeeMasterTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'employee-list' ? 'active' : '' }}" id="tab-employee-list-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-employee-list" type="button" role="tab"
                            aria-controls="tab-employee-list" aria-selected="{{ $activeTab === 'employee-list' ? 'true' : 'false' }}">
                            <i class="bx bx-group"></i> Employee List
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'attendance' ? 'active' : '' }}" id="tab-attendance-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button" role="tab"
                            aria-controls="tab-attendance" aria-selected="{{ $activeTab === 'attendance' ? 'true' : 'false' }}">
                            <i class="bx bx-calendar"></i> Attendance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'leave-management' ? 'active' : '' }}" id="tab-leave-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-leave-management" type="button" role="tab"
                            aria-controls="tab-leave-management" aria-selected="{{ $activeTab === 'leave-management' ? 'true' : 'false' }}">
                            <i class="bx bx-calendar-event"></i> Leave Management
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'salary' ? 'active' : '' }}" id="tab-salary-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-salary" type="button" role="tab"
                            aria-controls="tab-salary" aria-selected="{{ $activeTab === 'salary' ? 'true' : 'false' }}">
                            <i class="bx bx-credit-card"></i> Salary
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}" id="tab-documents-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-documents" type="button" role="tab"
                            aria-controls="tab-documents" aria-selected="{{ $activeTab === 'documents' ? 'true' : 'false' }}">
                            <i class="bx bx-file"></i> Documents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'holidays' ? 'active' : '' }}" id="tab-holidays-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-holidays" type="button" role="tab"
                            aria-controls="tab-holidays" aria-selected="{{ $activeTab === 'holidays' ? 'true' : 'false' }}">
                            <i class="bx bx-calendar-star"></i> Holidays
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'reports' ? 'active' : '' }}" id="tab-reports-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-reports" type="button" role="tab"
                            aria-controls="tab-reports" aria-selected="{{ $activeTab === 'reports' ? 'true' : 'false' }}">
                            <i class="bx bx-bar-chart-alt-2"></i> Reports
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Main Grid: Full Width Tabs Content --}}
            <div class="row g-2 g-xl-3">
                <div class="col-12">
                    <div class="tab-content" id="employeeMasterTabsContent">

                        {{-- TAB 1: EMPLOYEE LIST --}}
                        <div class="tab-pane fade {{ $activeTab === 'employee-list' ? 'show active' : '' }}" id="tab-employee-list"
                            role="tabpanel" aria-labelledby="tab-employee-list-btn">
                            
                            {{-- Filters Row --}}
                            <div class="as-filter-panel">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-lg-4">
                                        <div class="as-search-input-wrap">
                                            <i class="bx bx-search"></i>
                                            <input type="text" id="employeeSearchInput" class="form-control"
                                                placeholder="Search by Name, Employee ID or Department...">
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3 col-lg-2">
                                        <select id="employeeDepartmentFilter" class="form-select as-filter-select">
                                            <option value="">All Departments</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept }}">{{ $dept }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-3 col-lg-2">
                                        <select id="employeeDesignationFilter" class="form-select as-filter-select">
                                            <option value="">All Designations</option>
                                            @foreach ($designations as $desig)
                                                <option value="{{ $desig }}">{{ $desig }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-3 col-lg-2">
                                        <select id="employeeStatusFilter" class="form-select as-filter-select">
                                            <option value="Active" selected>Active</option>
                                            <option value="">All Statuses</option>
                                            <option value="Probation">Probation</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Left">Left</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-3 col-lg-2 d-flex gap-2">
                                        <button type="button" id="applyEmployeeFilter" class="btn btn-primary btn-sm flex-grow-1" style="height: 38px;">
                                            <i class="bx bx-filter-alt me-1"></i> Filter
                                        </button>
                                        <button type="button" id="resetEmployeeFilter" class="btn btn-outline-secondary btn-sm px-2" style="height: 38px;" title="Reset filters">
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Employee Table Panel --}}
                            <div class="as-panel">
                                <div class="table-responsive">
                                    <table class="table as-emp-table align-middle" id="employeeMasterTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input" id="checkAllEmployees">
                                                </th>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Joining Date</th>
                                                <th>Status</th>
                                                <th class="text-end" style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($staffMembers as $staff)
                                                <tr class="emp-row"
                                                    data-name="{{ strtolower($staff->full_name) }}"
                                                    data-empid="{{ strtolower($staff->emp_id) }}"
                                                    data-department="{{ strtolower($staff->department_name) }}"
                                                    data-designation="{{ strtolower($staff->designation_name) }}"
                                                    data-status="{{ $staff->display_status }}">
                                                    <td>
                                                        <input type="checkbox" class="form-check-input employee-checkbox" value="{{ $staff->id }}">
                                                    </td>
                                                    <td class="fw-semibold text-secondary">{{ $staff->emp_id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if ($staff->profile_image && file_exists(public_path('uploads/staff/' . $staff->profile_image)))
                                                                <img src="{{ asset('uploads/staff/' . $staff->profile_image) }}"
                                                                    alt="{{ $staff->full_name }}" class="as-avatar">
                                                            @else
                                                                <span class="as-avatar-placeholder">
                                                                    {{ strtoupper(substr($staff->first_name ?? 'S', 0, 1)) }}
                                                                </span>
                                                            @endif
                                                            <div>
                                                                <div class="fw-semibold text-dark">{{ $staff->full_name }}</div>
                                                                <div class="small text-muted" style="font-size: .72rem;">{{ $staff->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $staff->department_name }}</td>
                                                    <td>{{ $staff->designation_name }}</td>
                                                    <td>{{ $staff->joining_date_formatted }}</td>
                                                    <td>
                                                        <span class="as-badge {{ strtolower($staff->display_status) }}">
                                                            {{ $staff->display_status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-inline-flex gap-1">
                                                            <a href="{{ route('view-staff', $staff->id) }}" class="as-action-btn" title="View Profile">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                            <a href="{{ route('view-staff', $staff->id) }}" class="as-action-btn" title="Edit Staff">
                                                                <i class="bx bx-pencil"></i>
                                                            </a>
                                                            <button type="button" class="as-action-btn switch-to-docs" data-name="{{ $staff->full_name }}" title="Documents">
                                                                <i class="bx bx-file"></i>
                                                            </button>
                                                            <div class="dropdown d-inline">
                                                                <button class="as-action-btn dropdown-toggle dropdown-toggle-nocaret" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                    <li><a class="dropdown-item py-1 small" href="{{ route('view-staff', $staff->id) }}"><i class="bx bx-user me-2"></i>Full Profile</a></li>
                                                                    <li><a class="dropdown-item py-1 small switch-to-attendance" href="#tab-attendance"><i class="bx bx-calendar me-2"></i>Attendance Log</a></li>
                                                                    <li><a class="dropdown-item py-1 small switch-to-salary" href="#tab-salary"><i class="bx bx-money me-2"></i>Salary Breakdown</a></li>
                                                                    <li><hr class="dropdown-divider my-1"></li>
                                                                    <li><a class="dropdown-item py-1 small text-danger" href="{{ route('view-staff', $staff->id) }}"><i class="bx bx-user-x me-2"></i>Manage Status</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">No staff members found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2 small text-muted">
                                    <div>
                                        Showing <span id="empCountShowing">1</span> to <span id="empCountTo">{{ min(10, count($staffMembers)) }}</span> of <span id="empCountTotal">{{ count($staffMembers) }}</span> entries
                                    </div>
                                    <nav aria-label="Employee pagination">
                                        <ul class="pagination pagination-sm mb-0" id="empPagination">
                                            <li class="page-item disabled" id="empPagePrev"><a class="page-link" href="javascript:;">Previous</a></li>
                                            <li class="page-item active"><a class="page-link" href="javascript:;">1</a></li>
                                            <li class="page-item"><a class="page-link" href="javascript:;">2</a></li>
                                            <li class="page-item"><a class="page-link" href="javascript:;">3</a></li>
                                            <li class="page-item" id="empPageNext"><a class="page-link" href="javascript:;">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 2: ATTENDANCE --}}
                        <div class="tab-pane fade {{ $activeTab === 'attendance' ? 'show active' : '' }}" id="tab-attendance"
                            role="tabpanel" aria-labelledby="tab-attendance-btn">
                            <section class="as-panel">
                                <div class="as-panel-head d-flex align-items-center justify-content-between gap-2">
                                    <h2>Attendance &amp; Salary Records</h2>
                                    <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::createFromFormat('Y-m', $salaryMonth)->format('F Y') }}</span>
                                </div>
                                @if ($canManage)
                                    <form action="{{ route('salary-management.upload') }}" method="POST"
                                        enctype="multipart/form-data" class="as-upload">
                                        @csrf
                                        <div class="as-month">
                                            <label for="salary-month" class="form-label mb-1 small fw-semibold">Report month (fallback)</label>
                                            <input id="salary-month" type="month" name="month" class="form-control" value="{{ old('month', $salaryMonth) }}">
                                        </div>
                                        <div class="as-file">
                                            <label for="salary-file" class="form-label mb-1 small fw-semibold">Attendance Muster Report (.xlsx, .xls, .csv)</label>
                                            <input id="salary-file" type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                                            <i class="bx bx-upload me-1"></i> Upload &amp; Preview
                                        </button>
                                        <p class="as-hint">Daily rate = monthly Salary ÷ 30. Absent, unpaid leave, and half days are deducted; paid leave is paid; overtime days are added at the daily rate.</p>
                                    </form>
                                @endif

                                @if (is_array($previewRows))
                                    @php($previewHasErrors = collect($previewRows)->contains(fn($row) => $row['errors'] !== []))
                                    <div class="d-flex justify-content-between align-items-center gap-2 px-3 py-2 border-bottom bg-light">
                                        <span class="small fw-semibold text-secondary">{{ count($previewRows) }} employees in upload preview</span>
                                        @if ($previewHasErrors)
                                            <span class="badge bg-danger">Resolve unmatched or invalid rows before saving</span>
                                        @else
                                            <span class="badge bg-success">All employees matched and ready</span>
                                        @endif
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table as-table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Employee ID</th>
                                                    <th>Name</th>
                                                    <th>Department</th>
                                                    <th>Designation</th>
                                                    <th>Joining Date</th>
                                                    <th>Status</th>
                                                    <th>Present</th>
                                                    <th>Absent</th>
                                                    <th>Half Day</th>
                                                    <th>Paid Leave</th>
                                                    <th>Unpaid Leave</th>
                                                    <th>Monthly Salary</th>
                                                    <th>Net Salary</th>
                                                    <th>Calculation</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($previewRows as $row)
                                                    @php($employee = $row['employee'])
                                                    <tr class="{{ $row['errors'] !== [] ? 'table-danger' : '' }}">
                                                        <td>{{ $employee['employee_id'] ?: '-' }}</td>
                                                        <td>
                                                            <div class="as-name">{{ $employee['name'] }}</div>
                                                            <div class="as-secondary">{{ $employee['email'] ?: $employee['phone'] }}</div>
                                                        </td>
                                                        <td>{{ $employee['department'] ?: '-' }}</td>
                                                        <td>{{ $employee['designation'] ?: '-' }}</td>
                                                        <td>{{ $employee['joining_date'] ?: '-' }}</td>
                                                        <td><span class="as-status {{ strtolower($employee['status']) }}">{{ $employee['status'] }}</span></td>
                                                        <td>{{ number_format((float) $row['data']['present'], 0) }}</td>
                                                        <td>{{ number_format((float) $row['data']['absent'], 0) }}</td>
                                                        <td>{{ number_format((float) $row['data']['half_day'], 0) }}</td>
                                                        <td>{{ number_format((float) $row['data']['paid_leave'], 0) }}</td>
                                                        <td>{{ number_format((float) $row['data']['unpaid_leave'], 0) }}</td>
                                                        <td class="fw-bold">{{ number_format((float) $row['data']['salary'], 2) }}</td>
                                                        <td class="fw-bold">{{ $row['calculation'] ? '₹' . number_format($row['calculation']['net_salary'], 2) : '-' }}</td>
                                                        <td>
                                                            @if ($row['calculation'])
                                                                <details class="as-detail">
                                                                    <summary class="btn btn-sm btn-outline-primary">Breakdown</summary>
                                                                    <div class="as-detail-box small mt-1">
                                                                        <div>Per Day Salary (₹{{ number_format($row['calculation']['monthly_salary'], 2) }} ÷ 30): ₹{{ number_format($row['calculation']['per_day_salary'], 2) }}</div>
                                                                        <div>Absent Deduction ({{ number_format($row['calculation']['absent_days'], 0) }} days): -₹{{ number_format($row['calculation']['absent_deduction'], 2) }}</div>
                                                                        <div>Unpaid Leave Deduction ({{ number_format($row['calculation']['unpaid_leave_days'], 0) }} days): -₹{{ number_format($row['calculation']['unpaid_leave_deduction'], 2) }}</div>
                                                                        <div>Half-day Deduction ({{ number_format($row['calculation']['half_days'], 0) }} days): -₹{{ number_format($row['calculation']['half_day_deduction'], 2) }}</div>
                                                                        <div>Overtime Payment ({{ number_format($row['calculation']['overtime_days'], 0) }} days): +₹{{ number_format($row['calculation']['overtime_payment'], 2) }}</div>
                                                                        <strong>Net Salary: ₹{{ number_format($row['calculation']['net_salary'], 2) }}</strong>
                                                                    </div>
                                                                </details>
                                                            @else
                                                                <span class="text-danger small">{{ implode(', ', $row['errors']) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($row['errors'] === [])
                                                                <span class="badge bg-success-subtle text-success">Valid</span>
                                                            @else
                                                                <span class="badge bg-danger-subtle text-danger">Unmatched</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
                                        <span class="small text-muted">Verify figures before final submission.</span>
                                        <form action="{{ route('salary-management.save') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                                            <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold" {{ $previewHasErrors ? 'disabled' : '' }}>
                                                <i class="bx bx-check me-1"></i> Save Attendance Records
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($records->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table as-table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Department / Role</th>
                                                    <th>Present</th>
                                                    <th>Absent</th>
                                                    <th>Half Day</th>
                                                    <th>Leave</th>
                                                    <th>Overtime</th>
                                                    <th>Salary</th>
                                                    <th>Breakdown</th>
                                                    <th>Month</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($records as $record)
                                                    @php($calculation = data_get($record->data, 'calculation', []))
                                                    <tr>
                                                        <td>
                                                            <div class="as-name">{{ data_get($record->data, 'employee.name', $record->staffUser?->name ?? 'Staff') }}</div>
                                                            <div class="as-secondary">{{ data_get($record->data, 'employee.employee_id', $record->staffUser?->email) }}</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ data_get($record->data, 'employee.department', '-') }}</div>
                                                            <div class="as-secondary">{{ data_get($record->data, 'employee.designation', '-') }}</div>
                                                        </td>
                                                        <td>{{ number_format((float) data_get($record->data, 'present', 0), 0) }}</td>
                                                        <td>{{ number_format((float) data_get($record->data, 'absent', 0), 0) }}</td>
                                                        <td>{{ number_format((float) data_get($record->data, 'half_day', 0), 0) }}</td>
                                                        <td>{{ number_format((float) data_get($record->data, 'paid_leave', 0) + (float) data_get($record->data, 'unpaid_leave', 0), 0) }}</td>
                                                        <td>{{ number_format((float) data_get($calculation, 'overtime_days', 0), 0) }}</td>
                                                        <td>
                                                            <div class="fw-bold">₹{{ number_format((float) data_get($calculation, 'net_salary', data_get($record->data, 'salary', 0)), 2) }}</div>
                                                            <div class="as-secondary">Base: ₹{{ number_format((float) data_get($record->data, 'salary', 0), 2) }}</div>
                                                        </td>
                                                        <td>
                                                            <details class="as-detail">
                                                                <summary class="btn btn-sm btn-outline-primary">Breakdown</summary>
                                                                <div class="as-detail-box small mt-1">
                                                                    <div>Per Day (₹{{ number_format((float) data_get($calculation, 'monthly_salary', 0), 2) }} ÷ 30): ₹{{ number_format((float) data_get($calculation, 'per_day_salary', 0), 2) }}</div>
                                                                    <div>Absent ({{ number_format((float) data_get($calculation, 'absent_days', 0), 0) }}d): -₹{{ number_format((float) data_get($calculation, 'absent_deduction', 0), 2) }}</div>
                                                                    <div>Unpaid Leave: -₹{{ number_format((float) data_get($calculation, 'unpaid_leave_deduction', 0), 2) }}</div>
                                                                    <div>Half-day: -₹{{ number_format((float) data_get($calculation, 'half_day_deduction', 0), 2) }}</div>
                                                                    <div>Overtime ({{ number_format((float) data_get($calculation, 'overtime_days', 0), 0) }}d): +₹{{ number_format((float) data_get($calculation, 'overtime_payment', 0), 2) }}</div>
                                                                    <strong>Total Payable: ₹{{ number_format((float) data_get($calculation, 'net_salary', 0), 2) }}</strong>
                                                                </div>
                                                            </details>
                                                        </td>
                                                        <td>{{ $record->salary_month?->format('F Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="as-empty">
                                        <i class="bx bx-spreadsheet fs-1 text-muted"></i>
                                        <h5 class="mt-2 mb-1">No salary records yet</h5>
                                        <p class="mb-0">Upload the attendance muster workbook above to see employees and salary calculations.</p>
                                    </div>
                                @endif
                            </section>
                        </div>

                        {{-- TAB 3: LEAVE MANAGEMENT --}}
                        <div class="tab-pane fade {{ $activeTab === 'leave-management' ? 'show active' : '' }}" id="tab-leave-management"
                            role="tabpanel" aria-labelledby="tab-leave-btn">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Annual Allowance</div>
                                        <div class="as-value text-primary">24 Days</div>
                                        <div class="as-meta">Standard per staff</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Casual Leaves Taken</div>
                                        <div class="as-value text-success">14 Days</div>
                                        <div class="as-meta">This year</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Sick Leaves Taken</div>
                                        <div class="as-value text-info">6 Days</div>
                                        <div class="as-meta">This year</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Pending Approval</div>
                                        <div class="as-value text-warning">2 Requests</div>
                                        <div class="as-meta">Awaiting review</div>
                                    </div>
                                </div>
                            </div>

                            <div class="as-panel">
                                <div class="as-panel-head">
                                    <h2>Leave Requests &amp; Approvals</h2>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="alert('Leave application form modal triggered.')">
                                        <i class="bx bx-plus me-1"></i> Apply Leave
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table as-emp-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Leave Type</th>
                                                <th>Duration</th>
                                                <th>Days</th>
                                                <th>Reason</th>
                                                <th>Applied Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($leaveRequests as $leave)
                                                <tr>
                                                    <td class="fw-semibold">{{ $leave['employee'] }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ $leave['type'] }}</span></td>
                                                    <td>{{ $leave['from'] }} to {{ $leave['to'] }}</td>
                                                    <td class="fw-bold">{{ $leave['days'] }} Days</td>
                                                    <td class="text-muted">{{ $leave['reason'] }}</td>
                                                    <td>{{ $leave['applied_on'] }}</td>
                                                    <td>
                                                        @if ($leave['status'] === 'Approved')
                                                            <span class="as-badge active">Approved</span>
                                                        @elseif ($leave['status'] === 'Pending')
                                                            <span class="as-badge probation">Pending</span>
                                                        @else
                                                            <span class="as-badge inactive">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @if ($leave['status'] === 'Pending')
                                                            <button class="btn btn-xs btn-outline-success py-1 px-2" title="Approve"><i class="bx bx-check"></i></button>
                                                            <button class="btn btn-xs btn-outline-danger py-1 px-2" title="Reject"><i class="bx bx-x"></i></button>
                                                        @else
                                                            <button class="btn btn-xs btn-outline-secondary py-1 px-2" title="View details"><i class="bx bx-show"></i></button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 4: SALARY --}}
                        <div class="tab-pane fade {{ $activeTab === 'salary' ? 'show active' : '' }}" id="tab-salary"
                            role="tabpanel" aria-labelledby="tab-salary-btn">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Total Monthly Payroll</div>
                                        <div class="as-value text-primary">₹{{ number_format($summary['net_salary'] ?: 245000, 2) }}</div>
                                        <div class="as-meta">Base salaries</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Average Net Salary</div>
                                        <div class="as-value text-success">₹{{ number_format(($summary['net_salary'] ?: 245000) / max(1, $totalEmployees), 2) }}</div>
                                        <div class="as-meta">Per employee</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Total Deductions</div>
                                        <div class="as-value text-danger">₹12,450.00</div>
                                        <div class="as-meta">Absent &amp; Unpaid</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="as-card p-3">
                                        <div class="as-label">Overtime Disbursed</div>
                                        <div class="as-value text-violet">₹8,200.00</div>
                                        <div class="as-meta">Extra working days</div>
                                    </div>
                                </div>
                            </div>

                            <div class="as-panel">
                                <div class="as-panel-head">
                                    <h2>Salary Register &amp; Payroll Breakdown</h2>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="alert('Exporting salary register...')">
                                            <i class="bx bx-download me-1"></i> Export Register
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table as-emp-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Base Salary</th>
                                                <th>Per Day (÷30)</th>
                                                <th>Deductions</th>
                                                <th>Overtime Pay</th>
                                                <th>Net Payable</th>
                                                <th class="text-end">Payslip</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($staffMembers as $staff)
                                                @php($basePay = 25000 + ($staff->id * 1500))
                                                @php($perDay = round($basePay / 30, 2))
                                                @php($deduction = $staff->id % 3 === 0 ? $perDay * 2 : 0)
                                                @php($net = $basePay - $deduction)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $staff->full_name }}</div>
                                                        <div class="small text-muted">{{ $staff->emp_id }} • {{ $staff->department_name }}</div>
                                                    </td>
                                                    <td>₹{{ number_format($basePay, 2) }}</td>
                                                    <td class="text-muted">₹{{ number_format($perDay, 2) }}</td>
                                                    <td class="{{ $deduction > 0 ? 'text-danger' : 'text-muted' }}">
                                                        {{ $deduction > 0 ? '-₹' . number_format($deduction, 2) : '₹0.00' }}
                                                    </td>
                                                    <td class="text-success">+₹0.00</td>
                                                    <td class="fw-bold text-dark">₹{{ number_format($net, 2) }}</td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" onclick="alert('Generating payslip for {{ $staff->full_name }}...')">
                                                            <i class="bx bx-receipt me-1"></i> Slip
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">No staff salary data found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 5: DOCUMENTS --}}
                        <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="tab-documents"
                            role="tabpanel" aria-labelledby="tab-documents-btn">
                            <div class="as-panel">
                                <div class="as-panel-head">
                                    <h2>Employee Document Repository</h2>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="alert('Document upload modal opened.')">
                                        <i class="bx bx-upload me-1"></i> Upload Document
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table as-emp-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Document Title</th>
                                                <th>Category</th>
                                                <th>Format</th>
                                                <th>Uploaded Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($documents as $doc)
                                                <tr>
                                                    <td class="fw-semibold">{{ $doc['employee'] }}</td>
                                                    <td>
                                                        <i class="bx bxs-file-pdf text-danger me-1"></i>
                                                        {{ $doc['doc_name'] }}
                                                    </td>
                                                    <td><span class="badge bg-light text-dark border">{{ $doc['type'] }}</span></td>
                                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $doc['format'] }}</span></td>
                                                    <td>{{ $doc['uploaded_on'] }}</td>
                                                    <td><span class="as-badge active">{{ $doc['status'] }}</span></td>
                                                    <td class="text-end">
                                                        <button class="btn btn-xs btn-outline-primary py-1 px-2" onclick="alert('Viewing document {{ $doc['doc_name'] }}')">
                                                            <i class="bx bx-download"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 6: HOLIDAYS --}}
                        <div class="tab-pane fade {{ $activeTab === 'holidays' ? 'show active' : '' }}" id="tab-holidays"
                            role="tabpanel" aria-labelledby="tab-holidays-btn">
                            <div class="as-panel">
                                <div class="as-panel-head">
                                    <h2>Company Holiday Calendar (2026)</h2>
                                    <span class="badge bg-primary">{{ count($holidays) }} Public Holidays</span>
                                </div>
                                <div class="p-3">
                                    <div class="row g-2">
                                        @foreach ($holidays as $holiday)
                                            <div class="col-12 col-md-6">
                                                <div class="holiday-item">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="holiday-date-badge">
                                                            <div>{{ $holiday['date'] }}</div>
                                                            <div class="small fw-normal text-muted">{{ $holiday['day'] }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $holiday['name'] }}</div>
                                                            <div class="small text-muted">Official Holiday</div>
                                                        </div>
                                                    </div>
                                                    <span class="badge {{ $holiday['type'] === 'Mandatory' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                                        {{ $holiday['type'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 7: REPORTS --}}
                        <div class="tab-pane fade {{ $activeTab === 'reports' ? 'show active' : '' }}" id="tab-reports"
                            role="tabpanel" aria-labelledby="tab-reports-btn">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="report-card">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="as-icon as-blue" style="width: 36px; height: 36px; font-size: 1.1rem;"><i class="bx bx-calendar-check"></i></span>
                                                <h5 class="mb-0 fs-6 fw-bold">Monthly Attendance Muster</h5>
                                            </div>
                                            <p class="small text-muted mb-3">Complete employee-wise present, absent, half days, paid leaves and overtime matrix.</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" onclick="alert('Downloading Monthly Attendance Excel report...')">
                                                <i class="bx bx-download me-1"></i> Excel (.xlsx)
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" onclick="alert('Downloading Monthly Attendance PDF report...')">
                                                <i class="bx bxs-file-pdf me-1"></i> PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="report-card">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="as-icon as-green" style="width: 36px; height: 36px; font-size: 1.1rem;"><i class="bx bx-credit-card"></i></span>
                                                <h5 class="mb-0 fs-6 fw-bold">Salary &amp; Payroll Register</h5>
                                            </div>
                                            <p class="small text-muted mb-3">Gross pay, per day salary rates, absent/unpaid deductions, overtime payments, and net payable ledger.</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" onclick="alert('Downloading Salary Register Excel report...')">
                                                <i class="bx bx-download me-1"></i> Excel (.xlsx)
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" onclick="alert('Downloading Salary Register PDF report...')">
                                                <i class="bx bxs-file-pdf me-1"></i> PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="report-card">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="as-icon as-coral" style="width: 36px; height: 36px; font-size: 1.1rem;"><i class="bx bx-group"></i></span>
                                                <h5 class="mb-0 fs-6 fw-bold">Staff Demographic &amp; Master List</h5>
                                            </div>
                                            <p class="small text-muted mb-3">Comprehensive staff database including employee IDs, contact info, departments, designations and joining dates.</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" onclick="alert('Downloading Staff Master Excel...')">
                                                <i class="bx bx-download me-1"></i> Excel (.xlsx)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="report-card">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="as-icon as-violet" style="width: 36px; height: 36px; font-size: 1.1rem;"><i class="bx bx-calendar-event"></i></span>
                                                <h5 class="mb-0 fs-6 fw-bold">Leave Summary &amp; Balance Report</h5>
                                            </div>
                                            <p class="small text-muted mb-3">Department-wise annual leave allowances, leaves consumed, and available leave balances.</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" onclick="alert('Downloading Leave Summary...')">
                                                <i class="bx bx-download me-1"></i> Excel (.xlsx)
                                            </button>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quick tab switching buttons
            document.querySelectorAll('.switch-to-tab').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('data-tab');
                    var targetBtn = document.querySelector('#tab-' + tabId + '-btn');
                    if (targetBtn) {
                        var tabTrigger = new bootstrap.Tab(targetBtn);
                        tabTrigger.show();
                        window.location.hash = tabId;
                    }
                });
            });

            document.querySelectorAll('.switch-to-docs').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var targetBtn = document.querySelector('#tab-documents-btn');
                    if (targetBtn) {
                        new bootstrap.Tab(targetBtn).show();
                        window.location.hash = 'documents';
                    }
                });
            });

            document.querySelectorAll('.switch-to-attendance').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var targetBtn = document.querySelector('#tab-attendance-btn');
                    if (targetBtn) {
                        new bootstrap.Tab(targetBtn).show();
                        window.location.hash = 'attendance';
                    }
                });
            });

            document.querySelectorAll('.switch-to-salary').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var targetBtn = document.querySelector('#tab-salary-btn');
                    if (targetBtn) {
                        new bootstrap.Tab(targetBtn).show();
                        window.location.hash = 'salary';
                    }
                });
            });

            // Activate tab from URL hash if provided
            var currentHash = window.location.hash.replace('#', '');
            if (currentHash) {
                var initialTabBtn = document.querySelector('#tab-' + currentHash + '-btn');
                if (initialTabBtn) {
                    new bootstrap.Tab(initialTabBtn).show();
                }
            }

            // Employee List Client-Side Filtering
            var searchInput = document.getElementById('employeeSearchInput');
            var deptFilter = document.getElementById('employeeDepartmentFilter');
            var desigFilter = document.getElementById('employeeDesignationFilter');
            var statusFilter = document.getElementById('employeeStatusFilter');
            var applyBtn = document.getElementById('applyEmployeeFilter');
            var resetBtn = document.getElementById('resetEmployeeFilter');
            var rows = document.querySelectorAll('#employeeMasterTable tbody tr.emp-row');
            var showingCount = document.getElementById('empCountShowing');
            var toCount = document.getElementById('empCountTo');
            var totalCount = document.getElementById('empCountTotal');

            function filterEmployees() {
                var search = (searchInput ? searchInput.value : '').toLowerCase().trim();
                var dept = (deptFilter ? deptFilter.value : '').toLowerCase().trim();
                var desig = (desigFilter ? desigFilter.value : '').toLowerCase().trim();
                var status = (statusFilter ? statusFilter.value : '').toLowerCase().trim();

                var visibleCount = 0;

                rows.forEach(function (row) {
                    var rowName = row.getAttribute('data-name') || '';
                    var rowEmpId = row.getAttribute('data-empid') || '';
                    var rowDept = row.getAttribute('data-department') || '';
                    var rowDesig = row.getAttribute('data-designation') || '';
                    var rowStatus = (row.getAttribute('data-status') || '').toLowerCase();

                    var matchesSearch = !search || rowName.includes(search) || rowEmpId.includes(search) || rowDept.includes(search);
                    var matchesDept = !dept || rowDept === dept;
                    var matchesDesig = !desig || rowDesig === desig;
                    var matchesStatus = !status || rowStatus === status;

                    if (matchesSearch && matchesDept && matchesDesig && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (showingCount && toCount && totalCount) {
                    showingCount.textContent = visibleCount > 0 ? '1' : '0';
                    toCount.textContent = visibleCount;
                    totalCount.textContent = rows.length;
                }
            }

            if (applyBtn) {
                applyBtn.addEventListener('click', filterEmployees);
            }
            if (searchInput) {
                searchInput.addEventListener('input', filterEmployees);
            }
            if (deptFilter) {
                deptFilter.addEventListener('change', filterEmployees);
            }
            if (desigFilter) {
                desigFilter.addEventListener('change', filterEmployees);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', filterEmployees);
            }
            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (deptFilter) deptFilter.value = '';
                    if (desigFilter) desigFilter.value = '';
                    if (statusFilter) statusFilter.value = 'Active';
                    filterEmployees();
                });
            }

            // Check all checkboxes
            var checkAll = document.getElementById('checkAllEmployees');
            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    var checked = this.checked;
                    document.querySelectorAll('.employee-checkbox').forEach(function (cb) {
                        cb.checked = checked;
                    });
                });
            }
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quick tab switching buttons
            document.querySelectorAll('.switch-to-tab').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('data-tab');
                    var targetBtn = document.querySelector('#tab-' + tabId + '-btn');
                    if (targetBtn) {
                        var tabTrigger = new bootstrap.Tab(targetBtn);
                        tabTrigger.show();
                        window.location.hash = tabId;
                    }
                });
            });

            document.querySelectorAll('.switch-to-docs').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var targetBtn = document.querySelector('#tab-documents-btn');
                    if (targetBtn) {
                        new bootstrap.Tab(targetBtn).show();
                        window.location.hash = 'documents';
                    }
                });
            });

            document.querySelectorAll('.switch-to-attendance').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var targetBtn = document.querySelector('#tab-attendance-btn');
                    if (targetBtn) {
                        new bootstrap.Tab(targetBtn).show();
                        window.location.hash = 'attendance';
                    }
                });
            });

            document.querySelectorAll('.switch-to-salary').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var targetBtn = document.querySelector('#tab-salary-btn');
                    if (targetBtn) {
                        new bootstrap.Tab(targetBtn).show();
                        window.location.hash = 'salary';
                    }
                });
            });

            // Activate tab from URL hash if provided
            var currentHash = window.location.hash.replace('#', '');
            if (currentHash) {
                var initialTabBtn = document.querySelector('#tab-' + currentHash + '-btn');
                if (initialTabBtn) {
                    new bootstrap.Tab(initialTabBtn).show();
                }
            }

            // Employee List Client-Side Filtering
            var searchInput = document.getElementById('employeeSearchInput');
            var deptFilter = document.getElementById('employeeDepartmentFilter');
            var desigFilter = document.getElementById('employeeDesignationFilter');
            var statusFilter = document.getElementById('employeeStatusFilter');
            var applyBtn = document.getElementById('applyEmployeeFilter');
            var resetBtn = document.getElementById('resetEmployeeFilter');
            var rows = document.querySelectorAll('#employeeMasterTable tbody tr.emp-row');
            var showingCount = document.getElementById('empCountShowing');
            var toCount = document.getElementById('empCountTo');
            var totalCount = document.getElementById('empCountTotal');

            function filterEmployees() {
                var search = (searchInput ? searchInput.value : '').toLowerCase().trim();
                var dept = (deptFilter ? deptFilter.value : '').toLowerCase().trim();
                var desig = (desigFilter ? desigFilter.value : '').toLowerCase().trim();
                var status = (statusFilter ? statusFilter.value : '').toLowerCase().trim();

                var visibleCount = 0;

                rows.forEach(function (row) {
                    var rowName = row.getAttribute('data-name') || '';
                    var rowEmpId = row.getAttribute('data-empid') || '';
                    var rowDept = row.getAttribute('data-department') || '';
                    var rowDesig = row.getAttribute('data-designation') || '';
                    var rowStatus = (row.getAttribute('data-status') || '').toLowerCase();

                    var matchesSearch = !search || rowName.includes(search) || rowEmpId.includes(search) || rowDept.includes(search);
                    var matchesDept = !dept || rowDept === dept;
                    var matchesDesig = !desig || rowDesig === desig;
                    var matchesStatus = !status || rowStatus === status;

                    if (matchesSearch && matchesDept && matchesDesig && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (showingCount && toCount && totalCount) {
                    showingCount.textContent = visibleCount > 0 ? '1' : '0';
                    toCount.textContent = visibleCount;
                    totalCount.textContent = rows.length;
                }
            }

            if (applyBtn) {
                applyBtn.addEventListener('click', filterEmployees);
            }
            if (searchInput) {
                searchInput.addEventListener('input', filterEmployees);
            }
            if (deptFilter) {
                deptFilter.addEventListener('change', filterEmployees);
            }
            if (desigFilter) {
                desigFilter.addEventListener('change', filterEmployees);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', filterEmployees);
            }
            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (deptFilter) deptFilter.value = '';
                    if (desigFilter) desigFilter.value = '';
                    if (statusFilter) statusFilter.value = 'Active';
                    filterEmployees();
                });
            }

            // Check all checkboxes
            var checkAll = document.getElementById('checkAllEmployees');
            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    var checked = this.checked;
                    document.querySelectorAll('.employee-checkbox').forEach(function (cb) {
                        cb.checked = checked;
                    });
                });
            }
        });
    </script>
@endpush

