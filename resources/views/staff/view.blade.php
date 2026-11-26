@extends('layout.master')

@push('styles')
    <style>
        .crm-staff-profile .breadcrumb {
            --bs-breadcrumb-divider: "›";
            font-size: 0.88rem;
        }

        .crm-staff-profile .breadcrumb-item + .breadcrumb-item::before {
            font-family: inherit;
            content: "\203A";
            color: #94a3b8;
            font-weight: 500;
            padding-right: 0.5rem;
        }

        .crm-staff-profile .crm-action-bar-btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .crm-staff-profile .crm-btn-soft {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            color: #1e293b;
        }

        .crm-staff-profile .crm-btn-soft:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .crm-staff-profile .crm-status-action-group {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding-left: 6px;
        }

        .crm-staff-profile .crm-status-label {
            font-size: 0.86rem;
            font-weight: 500;
            color: #64748b;
            white-space: nowrap;
        }

        .crm-staff-profile .crm-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 600;
            line-height: 1.25;
        }

        .crm-staff-profile .crm-status-badge.is-active {
            background: #eafaf1;
            color: #16a34a;
        }

        .crm-staff-profile .crm-status-badge.is-inactive {
            background: #fef2f2;
            color: #dc2626;
        }

        .crm-staff-profile .crm-status-dots-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #eef2f7;
            background: #f8fafc;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .crm-staff-profile .crm-status-dots-btn:hover {
            background: #eef2f7;
            border-color: #dbe3ef;
            color: #0f172a;
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-status-dots-btn,
        body.dark-theme .crm-staff-profile .crm-status-dots-btn {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
        }

        .crm-staff-profile .crm-status-dropdown {
            min-width: 210px;
            padding: 6px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        }

        .crm-staff-profile .crm-status-dropdown .dropdown-item {
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .crm-staff-profile .crm-hero-banner {
            position: relative;
            border-radius: 16px;
            background: linear-gradient(125deg, #071936 0%, #0b2d62 55%, #0b4a8c 100%);
            padding: 24px 28px;
            color: #ffffff;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(7, 25, 54, 0.16);
        }

        .crm-staff-profile .crm-hero-waves {
            position: absolute;
            right: -10px;
            top: -10px;
            bottom: -10px;
            width: 340px;
            pointer-events: none;
            opacity: 0.48;
        }

        .crm-staff-profile .crm-hero-avatar,
        .crm-staff-profile .crm-hero-initials {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.85);
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.22);
        }

        .crm-staff-profile .crm-hero-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: 0.03em;
        }

        .crm-staff-profile .crm-hero-code-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            color: #e2e8f0;
            font-size: 0.78rem;
            font-weight: 600;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .crm-staff-profile .crm-hero-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .crm-staff-profile .crm-hero-status-pill.is-active {
            background: #bbf7d0;
            color: #166534;
        }

        .crm-staff-profile .crm-hero-status-pill.is-inactive {
            background: #fecaca;
            color: #991b1b;
        }

        .crm-staff-profile .crm-hero-status-pill.is-deleted {
            background: #e2e8f0;
            color: #334155;
        }

        .crm-staff-profile .crm-section-card {
            border: 1px solid #e6ecf5;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .crm-staff-profile .crm-section-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 22px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .crm-staff-profile .crm-section-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .crm-staff-profile .crm-kv-list {
            padding: 6px 22px 16px;
        }

        .crm-staff-profile .crm-kv-row {
            display: grid;
            grid-template-columns: 205px minmax(0, 1fr);
            align-items: center;
            gap: 14px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f6fa;
        }

        .crm-staff-profile .crm-kv-row:last-child {
            border-bottom: 0;
        }

        @media (max-width: 575.98px) {
            .crm-staff-profile .crm-kv-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }

        .crm-staff-profile .crm-kv-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #64748b;
            font-size: 0.87rem;
            font-weight: 500;
        }

        .crm-staff-profile .crm-kv-label i {
            font-size: 1rem;
            color: #94a3b8;
            width: 16px;
            text-align: center;
        }

        .crm-staff-profile .crm-kv-value {
            color: #0f172a;
            font-size: 0.9rem;
            font-weight: 600;
            word-break: break-word;
        }

        .crm-staff-profile .crm-kv-value.is-empty {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }

        .crm-staff-profile .crm-doc-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 14px 22px 20px;
        }

        @media (max-width: 767.98px) {
            .crm-staff-profile .crm-doc-grid {
                grid-template-columns: 1fr;
            }
        }

        .crm-staff-profile .crm-doc-tile {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            overflow: hidden;
            text-align: left;
            padding: 0;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .crm-staff-profile button.crm-doc-tile {
            cursor: pointer;
            width: 100%;
        }

        .crm-staff-profile button.crm-doc-tile:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .crm-staff-profile .crm-doc-thumb {
            position: relative;
            height: 112px;
            background: #eef2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .crm-staff-profile .crm-doc-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .crm-staff-profile .crm-doc-caption {
            padding: 9px 11px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            background: #ffffff;
            border-top: 1px solid #eef2f7;
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-doc-tile,
        body.dark-theme .crm-staff-profile .crm-doc-tile {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-doc-caption,
        body.dark-theme .crm-staff-profile .crm-doc-caption {
            background: #1e293b;
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.08);
        }

        .crm-staff-profile .crm-dept-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #dbeafe;
            color: #1d4ed8;
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-section-card,
        body.dark-theme .crm-staff-profile .crm-section-card {
            background-color: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-section-head,
        body.dark-theme .crm-staff-profile .crm-section-head,
        html[data-bs-theme="dark"] .crm-staff-profile .crm-kv-row,
        body.dark-theme .crm-staff-profile .crm-kv-row {
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-kv-value,
        body.dark-theme .crm-staff-profile .crm-kv-value {
            color: #f1f5f9;
        }

        html[data-bs-theme="dark"] .crm-staff-profile .crm-btn-soft,
        body.dark-theme .crm-staff-profile .crm-btn-soft {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content crm-staff-profile">

            @include('layout.errors')

            @php
                $isDeleted = $staff->trashed();
                $staffDepartments = is_array($staff->departments)
                    ? $staff->departments
                    : (json_decode($staff->departments, true) ?: []);
                $currentSpatieRole = optional($staff->roles->first())->name;
                $displayRoleLabel = $currentSpatieRole ? ucwords(str_replace('_', ' ', $currentSpatieRole)) : 'Staff';
                $profileImageUrl = $staff->resolved_profile_image_url;
                $staffInitials = $staff->initials;

                $address = $staff->address;
                $fullAddressText = $address
                    ? collect([
                        $address->address_line_1,
                        $address->address_line_2,
                        $address->city,
                        $address->state,
                        $address->country,
                        $address->pincode,
                    ])->filter()->implode(', ')
                    : '';
                $currentAddressDisplay = $staff->current_address ?: $fullAddressText;

                $branchDisplay = 'Technofra';
                $employeeCodeDisplay = $staff->employee_code ?: (optional($staff->salaryProfile)->employee_code ?: 'TF' . str_pad((string) $staff->id, 3, '0', STR_PAD_LEFT));
                $designationDisplay = $staff->designation ?: ($staff->team ?: ($staffDepartments[0] ?? ''));
                $employeeTypeDisplay = str_replace('-', ' ', $staff->employee_type ?: 'Full Time');

                $leavingCarbon = $staff->date_of_leaving;
                $dobDisplay = $staff->date_of_birth ? $staff->date_of_birth->format('j M Y') : null;
            @endphp

            {{-- Top Breadcrumb & 3 Action Buttons --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 bg-transparent align-items-center">
                        <li class="breadcrumb-item"><a href="{{ route('staff') }}" class="text-muted"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff') }}" class="text-muted text-decoration-none">Staff</a></li>
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">Staff Profile</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if ($isDeleted)
                        <form method="POST" action="{{ route('staff.restore', $staff->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success crm-action-bar-btn">
                                <i class="bx bx-revision"></i>Restore Staff
                            </button>
                        </form>
                        <form method="POST" action="{{ route('staff.force-delete', $staff->id) }}"
                            onsubmit="return confirm('Are you sure you want to permanently delete this staff member? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger crm-action-bar-btn">
                                <i class="bx bx-trash"></i>Permanent Delete
                            </button>
                        </form>
                    @else
                        <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-primary crm-action-bar-btn" id="topEditStaffBtn">
                            <i class="bx bx-edit-alt"></i>Edit Profile
                        </a>
                        <a href="{{ route('staff.reports', $staff->id) }}" class="btn crm-btn-soft crm-action-bar-btn" id="topReportsBtn">
                            <i class="bx bx-bar-chart-alt-2"></i>Reports
                        </a>
                        <div class="crm-status-action-group">
                            <span class="crm-status-label">Staff Status</span>
                            <span class="crm-status-badge {{ $staff->status === 'active' ? 'is-active' : 'is-inactive' }}">
                                {{ $staff->status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                            <div class="dropdown">
                                <button type="button" class="crm-status-dots-btn" id="staffStatusMenuBtn"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    title="Staff Actions" aria-label="Staff Status Actions">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end crm-status-dropdown" aria-labelledby="staffStatusMenuBtn">
                                    <li>
                                        <button type="button" class="dropdown-item" id="topChangeStatusBtn"
                                            data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                            <i class="bx bx-transfer-alt text-warning"></i>
                                            <span>Change Status</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item" id="topDownloadSalarySlipBtn"
                                            data-bs-toggle="modal" data-bs-target="#downloadSalarySlipModal">
                                            <i class="bx bx-download text-primary"></i>
                                            <span>Download Salary Slip</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($isDeleted)
                <div class="alert alert-warning mb-4">
                    This staff member is soft deleted. Restore the record to edit details or change status.
                </div>
            @endif

            {{-- Full-Width Navy Hero Card --}}
            <div class="crm-hero-banner mb-4">
                <svg class="crm-hero-waves" viewBox="0 0 360 180" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M10 170C80 115 150 135 215 78C265 34 315 22 365 4" stroke="#38BDF8" stroke-width="1.3"/>
                    <path d="M32 175C100 124 166 142 228 86C276 42 322 30 370 14" stroke="#38BDF8" stroke-width="1.1"/>
                    <path d="M54 180C120 133 182 149 242 94C288 52 330 40 374 26" stroke="#38BDF8" stroke-width="1"/>
                    <path d="M78 184C142 141 198 156 256 104C300 62 338 50 378 38" stroke="#0EA5E9" stroke-width="0.9"/>
                    <path d="M102 188C164 149 214 162 270 114C312 74 346 62 382 50" stroke="#0EA5E9" stroke-width="0.8"/>
                </svg>

                <div class="d-flex align-items-center gap-3 flex-wrap position-relative" style="z-index: 1;">
                    @if ($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" alt="{{ $staff->name }}" class="crm-hero-avatar">
                    @else
                        <div class="crm-hero-initials">{{ $staffInitials }}</div>
                    @endif

                    <div>
                        <h3 class="mb-1 fw-bold text-white">{{ $staff->name }}</h3>
                        <div class="mb-2" style="color: rgba(255,255,255,0.82); font-size: 0.94rem;">
                            {{ $designationDisplay ?: $displayRoleLabel }}
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="crm-hero-code-pill">{{ $employeeCodeDisplay }}</span>
                            @if ($isDeleted)
                                <span class="crm-hero-status-pill is-deleted">
                                    <i class="bx bxs-circle" style="font-size: 8px;"></i>Deleted
                                </span>
                            @elseif ($staff->status === 'active')
                                <span class="crm-hero-status-pill is-active">
                                    <i class="bx bxs-circle" style="font-size: 8px;"></i>Active
                                </span>
                            @else
                                <span class="crm-hero-status-pill is-inactive">
                                    <i class="bx bxs-circle" style="font-size: 8px;"></i>Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2-Column Content Grid --}}
            <div class="row g-4 mb-4">
                {{-- Left Column (Personal Details) --}}
                <div class="col-lg-6">
                    <div class="crm-section-card h-100">
                        <div class="crm-section-head">
                            <div class="crm-section-icon"><i class="bx bx-user"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Personal Details</h6>
                            </div>
                        </div>
                        <div class="crm-kv-list">
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-user"></i>Full Name</span>
                                <span class="crm-kv-value">{{ $staff->name ?: '-' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-phone"></i>Mobile</span>
                                <span class="crm-kv-value {{ $staff->phone ? '' : 'is-empty' }}">{{ $staff->phone ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-envelope"></i>Personal Email</span>
                                <span class="crm-kv-value {{ $staff->email ? '' : 'is-empty' }}">{{ $staff->email ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-cake"></i>Date of Birth</span>
                                <span class="crm-kv-value {{ $dobDisplay ? '' : 'is-empty' }}">{{ $dobDisplay ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-male-female"></i>Gender</span>
                                <span class="crm-kv-value {{ $staff->gender ? '' : 'is-empty' }}">{{ $staff->gender ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-heart"></i>Marital Status</span>
                                <span class="crm-kv-value {{ $staff->marital_status ? '' : 'is-empty' }}">{{ $staff->marital_status ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-droplet"></i>Blood Group</span>
                                <span class="crm-kv-value {{ $staff->blood_group ? '' : 'is-empty' }}">{{ $staff->blood_group ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-map"></i>Address</span>
                                <span class="crm-kv-value {{ $currentAddressDisplay ? '' : 'is-empty' }}">{{ $currentAddressDisplay ?: 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column (Employment Details) --}}
                <div class="col-lg-6">
                    <div class="crm-section-card h-100">
                        <div class="crm-section-head">
                            <div class="crm-section-icon"><i class="bx bx-briefcase"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Employment Details</h6>
                            </div>
                        </div>
                        <div class="crm-kv-list">
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-buildings"></i>Branch</span>
                                <span class="crm-kv-value">{{ $branchDisplay }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-briefcase-alt-2"></i>Employee Type</span>
                                <span class="crm-kv-value">{{ $employeeTypeDisplay }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-barcode"></i>Employee ID</span>
                                <span class="crm-kv-value font-monospace">{{ $employeeCodeDisplay }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-badge-check"></i>Job Title</span>
                                <span class="crm-kv-value {{ $designationDisplay ? '' : 'is-empty' }}">{{ $designationDisplay ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-shield"></i>Role</span>
                                <span class="crm-kv-value">{{ $displayRoleLabel }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-layer"></i>Departments</span>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse ($staffDepartments as $dept)
                                        <span class="crm-dept-pill">{{ $dept }}</span>
                                    @empty
                                        <span class="crm-kv-value is-empty">Not assigned</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-envelope"></i>Official Email</span>
                                <span class="crm-kv-value {{ $staff->official_email ? '' : 'is-empty' }}">{{ $staff->official_email ?: 'Not provided' }}</span>
                            </div>
                            @php
                                $monthlySalaryRaw = optional($staff->salaryProfile)->monthly_salary;
                                $salaryDisplay = ($monthlySalaryRaw !== null && (float) $monthlySalaryRaw > 0)
                                    ? '₹' . number_format((float) $monthlySalaryRaw, fmod((float) $monthlySalaryRaw, 1.0) == 0.0 ? 0 : 2)
                                    : null;
                            @endphp
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-rupee"></i>Salary</span>
                                <span class="crm-kv-value {{ $salaryDisplay ? '' : 'is-empty' }}">{{ $salaryDisplay ?: 'Not provided' }}</span>
                            </div>
                            @if ($staff->status_reason)
                                <div class="crm-kv-row">
                                    <span class="crm-kv-label"><i class="bx bx-info-circle"></i>Status Reason</span>
                                    <span class="crm-kv-value">{{ $staff->status_reason }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @php
                $bankNameDisplay = $staff->bank_name ?: optional($staff->salaryProfile)->bank_name;
                $documentCards = [
                    [
                        'label' => 'Aadhaar Front',
                        'url' => $staff->aadhar_front_url,
                    ],
                    [
                        'label' => 'Aadhaar Back',
                        'url' => $staff->aadhar_back_url,
                    ],
                    [
                        'label' => 'PAN Front',
                        'url' => $staff->pan_front_url,
                    ],
                ];
            @endphp

            {{-- Account Details & Document Details Row --}}
            <div class="row g-4 mb-4">
                {{-- Account Details --}}
                <div class="col-lg-6">
                    <div class="crm-section-card h-100">
                        <div class="crm-section-head">
                            <div class="crm-section-icon"><i class="bx bx-credit-card"></i></div>
                            <h6 class="mb-0 fw-bold">Account Details</h6>
                        </div>
                        <div class="crm-kv-list">
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-hash"></i>Account Number</span>
                                <span class="crm-kv-value font-monospace {{ $staff->account_number ? '' : 'is-empty' }}">{{ $staff->account_number ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-buildings"></i>Bank Name</span>
                                <span class="crm-kv-value {{ $bankNameDisplay ? '' : 'is-empty' }}">{{ $bankNameDisplay ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-barcode"></i>IFSC Code</span>
                                <span class="crm-kv-value font-monospace {{ $staff->ifsc_code ? '' : 'is-empty' }}">{{ $staff->ifsc_code ?: 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Document Details --}}
                <div class="col-lg-6">
                    <div class="crm-section-card h-100">
                        <div class="crm-section-head">
                            <div class="crm-section-icon"><i class="bx bx-file"></i></div>
                            <h6 class="mb-0 fw-bold">Document Details</h6>
                        </div>
                        <div class="crm-kv-list pb-1">
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-id-card"></i>Aadhaar Number</span>
                                <span class="crm-kv-value font-monospace {{ $staff->aadhar_number ? '' : 'is-empty' }}">{{ $staff->aadhar_number ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-card"></i>PAN Number</span>
                                <span class="crm-kv-value font-monospace {{ $staff->pan_number ? '' : 'is-empty' }}">{{ $staff->pan_number ?: 'Not provided' }}</span>
                            </div>
                        </div>

                        <div class="crm-doc-grid">
                            @foreach ($documentCards as $doc)
                                @if ($doc['url'])
                                    <button type="button" class="crm-doc-tile"
                                        data-bs-toggle="modal" data-bs-target="#documentPreviewModal"
                                        data-doc-title="{{ $doc['label'] }}"
                                        data-doc-url="{{ $doc['url'] }}">
                                        <div class="crm-doc-thumb">
                                            <img src="{{ $doc['url'] }}" alt="{{ $doc['label'] }}">
                                        </div>
                                        <div class="crm-doc-caption">{{ $doc['label'] }}</div>
                                    </button>
                                @else
                                    <div class="crm-doc-tile">
                                        <div class="crm-doc-thumb text-muted">
                                            <div class="text-center">
                                                <i class="bx bx-image fs-3 d-block mb-1 opacity-50"></i>
                                                <span class="small">Not uploaded</span>
                                            </div>
                                        </div>
                                        <div class="crm-doc-caption">{{ $doc['label'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Footer Action Row --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 pb-4">
                <a href="{{ route('staff') }}" class="btn crm-btn-soft crm-action-bar-btn">
                    <i class="bx bx-arrow-back"></i>Back to Staff List
                </a>

                @if (! $isDeleted)
                    <form method="POST" action="{{ route('staff.destroy', $staff->id) }}"
                        onsubmit="return confirm('Are you sure you want to delete this staff member?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger text-decoration-none fw-semibold">
                            <i class="bx bx-trash me-1"></i>Delete Staff
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Change Status Modal --}}
    @if (! $isDeleted)
        <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="changeStatusModalLabel">
                            <i class="bx bx-transfer-alt text-warning me-2"></i>Change Staff Status
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('staff.status.update', $staff->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="modal_status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select id="modal_status" name="status" class="form-select" required>
                                    <option value="active" {{ old('status', $staff->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $staff->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="modal_date_of_leaving" class="form-label fw-semibold">Leaving Date</label>
                                <input type="date" id="modal_date_of_leaving" name="date_of_leaving" class="form-control"
                                    value="{{ old('date_of_leaving', $leavingCarbon ? $leavingCarbon->format('Y-m-d') : '') }}">
                            </div>
                            <div class="mb-0">
                                <label for="modal_status_reason" class="form-label fw-semibold">Reason</label>
                                <textarea id="modal_status_reason" name="status_reason" rows="3" class="form-control"
                                    placeholder="Enter reason for status change or leaving...">{{ old('status_reason', $staff->status_reason) }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check me-1"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Download Salary Slip Modal --}}
    @if (! $isDeleted)
        @php
            $slipMonths = [
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ];
            $selectedSlipMonth = (int) ($salarySlipDefaultMonth ?? now()->month);
            $selectedSlipYear = (int) ($salarySlipDefaultYear ?? now()->year);
            $slipYearOptions = $salarySlipYears ?? range((int) now()->year + 1, max(2020, (int) now()->year - 5));
        @endphp
        <div class="modal fade" id="downloadSalarySlipModal" tabindex="-1" aria-labelledby="downloadSalarySlipModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="downloadSalarySlipModalLabel">
                            <i class="bx bx-receipt text-primary me-2"></i>Download Salary Slip
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="GET" action="{{ route('staff.salary-slip.download', $staff->id) }}" id="downloadSalarySlipForm">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="salary_slip_month" class="form-label fw-semibold">Month <span class="text-danger">*</span></label>
                                    <select id="salary_slip_month" name="month" class="form-select" required>
                                        @foreach ($slipMonths as $monthNum => $monthName)
                                            <option value="{{ $monthNum }}" {{ $selectedSlipMonth === $monthNum ? 'selected' : '' }}>
                                                {{ $monthName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="salary_slip_year" class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                                    <select id="salary_slip_year" name="year" class="form-select" required>
                                        @foreach ($slipYearOptions as $yearOption)
                                            <option value="{{ $yearOption }}" {{ $selectedSlipYear === (int) $yearOption ? 'selected' : '' }}>
                                                {{ $yearOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="submitDownloadSalarySlipBtn">
                                <i class="bx bx-download me-1"></i>Download
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Document Image Preview Modal --}}
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="documentPreviewModalLabel">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 text-center bg-dark bg-opacity-10">
                    <img src="" alt="Document Preview" id="documentPreviewModalImage" class="img-fluid rounded-3 shadow-sm" style="max-height: 75vh; object-fit: contain;">
                </div>
                <div class="modal-footer border-top-0">
                    <a href="#" id="documentPreviewOpenLink" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-link-external me-1"></i>Open in New Tab
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const salarySlipForm = document.getElementById('downloadSalarySlipForm');
            const salarySlipModalEl = document.getElementById('downloadSalarySlipModal');
            if (salarySlipForm && salarySlipModalEl && window.bootstrap) {
                salarySlipForm.addEventListener('submit', function() {
                    setTimeout(function() {
                        const modalInstance = bootstrap.Modal.getInstance(salarySlipModalEl);
                        if (modalInstance) modalInstance.hide();
                    }, 150);
                });
            }

            const previewModal = document.getElementById('documentPreviewModal');
            if (!previewModal) return;

            const titleEl = document.getElementById('documentPreviewModalLabel');
            const imgEl = document.getElementById('documentPreviewModalImage');
            const linkEl = document.getElementById('documentPreviewOpenLink');

            previewModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;
                const docTitle = trigger.getAttribute('data-doc-title') || 'Document Preview';
                const docUrl = trigger.getAttribute('data-doc-url') || '';
                if (titleEl) titleEl.textContent = docTitle;
                if (imgEl) {
                    imgEl.src = docUrl;
                    imgEl.alt = docTitle;
                }
                if (linkEl) linkEl.href = docUrl;
            });

            previewModal.addEventListener('hidden.bs.modal', function() {
                if (imgEl) imgEl.src = '';
            });
        });
    </script>
@endsection
