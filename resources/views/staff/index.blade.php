@extends('layout.master')

@push('styles')
    <style>
        .staff-directory-page {
            --sd-ink: #1d3150;
            --sd-muted: #75839a;
            --sd-line: #e7edf5;
            margin: auto;
            color: var(--sd-ink);
        }

        .staff-directory-page .breadcrumb {
            --bs-breadcrumb-divider: "›";
            font-size: 0.88rem;
        }

        .staff-directory-page .breadcrumb-item + .breadcrumb-item::before {
            font-family: inherit;
            content: "\203A";
            color: #94a3b8;
            font-weight: 500;
            padding-right: 0.5rem;
        }

        .staff-directory-page .sd-heading {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: var(--sd-ink);
        }

        .staff-directory-page .sd-subtitle {
            color: var(--sd-muted);
            font-size: 13px;
        }

        .staff-directory-page .sd-action-btn {
            min-height: 38px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 14px;
        }

        .staff-directory-page .sd-filter-card,
        .staff-directory-page .sd-table-card {
            border: 1px solid var(--sd-line);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 5px 20px rgba(27, 52, 89, 0.045);
        }

        .staff-directory-page .sd-filter-card {
            padding: 18px;
            margin-bottom: 20px;
        }

        .staff-directory-page .sd-filter-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #697991;
            margin-bottom: 6px;
            display: block;
        }

        .staff-directory-page .sd-filter-card .form-control,
        .staff-directory-page .sd-filter-card .form-select,
        .staff-directory-page .sd-filter-card .btn {
            min-height: 40px;
            border-radius: 8px;
            font-size: 13px;
        }

        .staff-directory-page .sd-filter-card .form-control,
        .staff-directory-page .sd-filter-card .form-select {
            border-color: #dfe6f0;
        }

        .staff-directory-page .sd-stat-meta {
            font-size: 12px;
            color: var(--sd-muted);
            font-weight: 500;
        }

        .staff-directory-page .sd-table-card {
            overflow: hidden;
        }

        .staff-directory-page .sd-table-card .card-header {
            padding: 15px 18px;
            font-weight: 700;
            background: #ffffff;
            border-bottom: 1px solid #edf0f5;
            color: var(--sd-ink);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .staff-directory-page .sd-table thead th {
            background: #f7f9fc;
            border: 0;
            border-bottom: 1px solid var(--sd-line);
            color: #65738a;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.35px;
            padding: 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .staff-directory-page .sd-table tbody td {
            border: 0;
            border-bottom: 1px solid #edf0f5;
            padding: 12px;
            vertical-align: middle;
            font-size: 13px;
        }

        .staff-directory-page .sd-table tbody tr:hover {
            background: #fafcff;
        }

        .staff-directory-page .sd-avatar-img,
        .staff-directory-page .sd-avatar-initials {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .staff-directory-page .sd-avatar-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #edf2ff;
            color: #0d6efd;
            font-size: 13px;
            font-weight: 800;
        }

        .staff-directory-page .sd-staff-name {
            font-weight: 700;
            color: var(--sd-ink);
            text-decoration: none;
        }

        .staff-directory-page .sd-staff-name:hover {
            color: #0d6efd;
        }

        .staff-directory-page .sd-staff-sub {
            font-size: 11px;
            color: var(--sd-muted);
            margin-top: 2px;
        }

        .staff-directory-page .sd-dept-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 600;
        }

        .staff-directory-page .sd-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .staff-directory-page .sd-status-pill.is-active {
            background: #eafaf1;
            color: #16a34a;
        }

        .staff-directory-page .sd-status-pill.is-inactive {
            background: #fef3c7;
            color: #b45309;
        }

        .staff-directory-page .sd-row-checkbox,
        .staff-directory-page #select-all {
            width: 1.05rem;
            height: 1.05rem;
            cursor: pointer;
        }

        .staff-directory-page .dataTables_wrapper .dataTables_filter {
            display: none;
        }

        .staff-directory-page .dataTables_wrapper .dataTables_length,
        .staff-directory-page .dataTables_wrapper .dataTables_info,
        .staff-directory-page .dataTables_wrapper .dataTables_paginate {
            padding: 12px 18px;
            font-size: 12px;
            color: var(--sd-muted);
        }

        html[data-bs-theme="dark"] .staff-directory-page .sd-filter-card,
        body.dark-theme .staff-directory-page .sd-filter-card,
        html[data-bs-theme="dark"] .staff-directory-page .sd-table-card,
        body.dark-theme .staff-directory-page .sd-table-card,
        html[data-bs-theme="dark"] .staff-directory-page .sd-table-card .card-header,
        body.dark-theme .staff-directory-page .sd-table-card .card-header {
            background-color: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }

        html[data-bs-theme="dark"] .staff-directory-page .sd-heading,
        body.dark-theme .staff-directory-page .sd-heading,
        html[data-bs-theme="dark"] .staff-directory-page .sd-staff-name,
        body.dark-theme .staff-directory-page .sd-staff-name {
            color: #f1f5f9;
        }
    </style>
@endpush

@section('content')
    @php
        $totalStaffCount = $staff->count();

        $departmentFilterOptions = $staff
            ->flatMap(function ($m) {
                return is_array($m->departments)
                    ? $m->departments
                    : (json_decode($m->departments, true) ?: []);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $roleFilterOptions = $staff
            ->map(function ($m) {
                $spatieRole = optional($m->roles->first())->name;
                $rawRole = $spatieRole ?: $m->role;
                return $rawRole ? ucwords(str_replace('_', ' ', $rawRole)) : 'Staff';
            })
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div class="page-wrapper">
        <div class="page-content staff-directory-page">
            @include('layout.errors')
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Breadcrumb --}}
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item">Employee Master</li>
                            <li class="breadcrumb-item active" aria-current="page">Staff</li>
                        </ol>
                    </nav>
                </div>
            </div>

            {{-- Workspace Header --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <div class="small text-primary fw-bold mb-1">EMPLOYEE WORKSPACE</div>
                    <h1 class="sd-heading mb-0">Staff</h1>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('staff.activity-log') }}" class="btn btn-outline-secondary sd-action-btn">
                        <i class="bx bx-history"></i>Activity Log
                    </a>
                    <button type="button" class="btn btn-outline-danger sd-action-btn" id="bulk-delete-btn">
                        <i class="bx bx-trash"></i>Delete Selected
                    </button>
                    <a href="{{ route('add-staff') }}" class="btn btn-primary sd-action-btn">
                        <i class="bx bx-plus"></i>Add New Staff
                    </a>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="sd-filter-card">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-lg-3">
                        <label for="staffFilterDepartment" class="sd-filter-label">Department</label>
                        <select id="staffFilterDepartment" class="form-select">
                            <option value="">All departments</option>
                            @foreach ($departmentFilterOptions as $deptOption)
                                <option value="{{ $deptOption }}">{{ $deptOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="staffFilterRole" class="sd-filter-label">Role</label>
                        <select id="staffFilterRole" class="form-select">
                            <option value="">All roles</option>
                            @foreach ($roleFilterOptions as $roleOption)
                                <option value="{{ $roleOption }}">{{ $roleOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="staffFilterStatus" class="sd-filter-label">Status</label>
                        <select id="staffFilterStatus" class="form-select">
                            <option value="">All statuses</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="staffFilterSearch" class="sd-filter-label">Search staff</label>
                        <input id="staffFilterSearch" type="search" class="form-control" placeholder="Name, email, phone, employee ID...">
                    </div>
                    <div class="col-6 col-lg-1 d-grid">
                        <button type="button" id="staffFilterResetBtn" class="btn btn-outline-secondary">Reset</button>
                    </div>
                </div>
            </div>

            {{-- Staff Table Card --}}
            <div class="card sd-table-card">
                <div class="card-header">
                    <span>
                        <i class="bx bx-spreadsheet text-primary me-1"></i>
                        Staff List
                        <span class="sd-stat-meta ms-1" id="staffVisibleCount">{{ $totalStaffCount }} member(s)</span>
                    </span>
                </div>
                <div class="table-responsive">
                    <table id="example" class="table sd-table mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 36px;">
                                    <input class="form-check-input m-0" type="checkbox" id="select-all" aria-label="Select all staff">
                                </th>
                                <th>Staff</th>
                                <th>Department / Job Title</th>
                                <th>Role</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($staff as $member)
                                @php
                                    $memberDepts = is_array($member->departments)
                                        ? $member->departments
                                        : (json_decode($member->departments, true) ?: []);
                                    $spatieRole = optional($member->roles->first())->name;
                                    $memberRoleLabel = ucwords(str_replace('_', ' ', $spatieRole ?: ($member->role ?: 'staff')));
                                    $memberCode = $member->employee_code
                                        ?: (optional($member->salaryProfile)->employee_code ?: 'TF' . str_pad((string) $member->id, 3, '0', STR_PAD_LEFT));
                                    $memberStatusLabel = $member->status === 'active' ? 'Active' : 'Inactive';
                                    $memberImageUrl = $member->resolved_profile_image_url;
                                @endphp
                                <tr data-departments="{{ implode('|', $memberDepts) }}"
                                    data-role="{{ $memberRoleLabel }}"
                                    data-status="{{ $memberStatusLabel }}">
                                    <td>
                                        <input class="form-check-input row-checkbox sd-row-checkbox m-0" type="checkbox"
                                            name="ids[]" value="{{ $member->id }}" aria-label="Select {{ $member->name }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($memberImageUrl)
                                                <img src="{{ $memberImageUrl }}" alt="{{ $member->name }}" class="sd-avatar-img">
                                            @else
                                                <span class="sd-avatar-initials">{{ $member->initials }}</span>
                                            @endif
                                            <div>
                                                <a href="{{ route('view-staff', $member->id) }}" class="sd-staff-name d-block">
                                                    {{ $member->name }}
                                                </a>
                                                <div class="sd-staff-sub">
                                                    {{ $memberCode }} · {{ $member->email ?: ($member->phone ?: 'No email') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if (! empty($memberDepts))
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($memberDepts as $deptName)
                                                    <span class="sd-dept-pill">{{ $deptName }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                        @if ($member->designation)
                                            <div class="sd-staff-sub">{{ $member->designation }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $memberRoleLabel }}</td>
                                    <td>
                                        @if ($member->activity_logs_max_logged_in_at)
                                            {{ \Carbon\Carbon::parse($member->activity_logs_max_logged_in_at)->format('d M Y, h:i A') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="sd-status-pill {{ $member->status === 'active' ? 'is-active' : 'is-inactive' }}">
                                            <i class="bx bxs-circle" style="font-size: 7px;"></i>{{ $memberStatusLabel }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-flex justify-content-end order-actions">
                                            <a href="{{ route('view-staff', $member->id) }}" title="View Staff">
                                                <i class="bx bxs-show"></i>
                                            </a>
                                            <form method="POST" action="{{ route('staff.destroy', $member->id) }}"
                                                class="d-inline ms-3"
                                                onsubmit="return confirm('Are you sure you want to delete this staff member?')">
                                                @csrf
                                                @method('DELETE')
                                                <a>
                                                    <button type="submit" style="border: none; background: transparent; padding: 0;"
                                                        title="Delete Staff" aria-label="Delete {{ $member->name }}">
                                                        <i class="bx bxs-trash"></i>
                                                    </button>
                                                </a>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const deptSelect = document.getElementById('staffFilterDepartment');
            const roleSelect = document.getElementById('staffFilterRole');
            const statusSelect = document.getElementById('staffFilterStatus');
            const searchInput = document.getElementById('staffFilterSearch');
            const resetBtn = document.getElementById('staffFilterResetBtn');
            const visibleCountEl = document.getElementById('staffVisibleCount');
            const selectAll = document.getElementById('select-all');

            function getStaffDataTable() {
                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#example')) {
                    return $('#example').DataTable();
                }
                return null;
            }

            if (window.jQuery && $.fn.DataTable && $.fn.dataTable) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (!settings || settings.nTable.id !== 'example') return true;
                    const rowNode = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
                    if (!rowNode) return true;

                    const selectedDept = deptSelect ? deptSelect.value : '';
                    const selectedRole = roleSelect ? roleSelect.value : '';
                    const selectedStatus = statusSelect ? statusSelect.value : '';

                    if (selectedDept) {
                        const rowDepts = (rowNode.getAttribute('data-departments') || '').split('|').filter(Boolean);
                        if (!rowDepts.includes(selectedDept)) return false;
                    }
                    if (selectedRole && rowNode.getAttribute('data-role') !== selectedRole) return false;
                    if (selectedStatus && rowNode.getAttribute('data-status') !== selectedStatus) return false;

                    return true;
                });
            }

            function updateVisibleCount() {
                const table = getStaffDataTable();
                if (table && visibleCountEl) {
                    const count = table.rows({ search: 'applied' }).count();
                    visibleCountEl.textContent = `${count} member(s)`;
                }
            }

            function applyFilters() {
                const table = getStaffDataTable();
                if (table) {
                    table.search(searchInput ? searchInput.value : '').draw();
                    updateVisibleCount();
                }
            }

            [deptSelect, roleSelect, statusSelect].forEach(function(el) {
                if (el) el.addEventListener('change', applyFilters);
            });
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (deptSelect) deptSelect.value = '';
                    if (roleSelect) roleSelect.value = '';
                    if (statusSelect) statusSelect.value = '';
                    if (searchInput) searchInput.value = '';
                    applyFilters();
                });
            }

            function getSelectedStaffIds() {
                const selected = [];
                const table = getStaffDataTable();
                if (table) {
                    $(table.rows().nodes()).find('.row-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });
                } else {
                    document.querySelectorAll('.row-checkbox:checked').forEach(cb => selected.push(cb.value));
                }
                return selected;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const table = getStaffDataTable();
                    if (table) {
                        $(table.rows({ search: 'applied' }).nodes()).find('.row-checkbox').prop('checked', isChecked);
                    } else {
                        document.querySelectorAll('.row-checkbox').forEach(cb => { cb.checked = isChecked; });
                    }
                });
            }

            if (window.jQuery) {
                $('#example').on('change', '.row-checkbox', function() {
                    const table = getStaffDataTable();
                    const allCbs = table ? $(table.rows().nodes()).find('.row-checkbox') : $('.row-checkbox');
                    const total = allCbs.length;
                    const checked = allCbs.filter(':checked').length;
                    if (selectAll) {
                        selectAll.checked = total > 0 && checked === total;
                        selectAll.indeterminate = checked > 0 && checked < total;
                    }
                });
                setTimeout(updateVisibleCount, 50);
            }

            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const selected = getSelectedStaffIds();
                    if (selected.length === 0) {
                        alert('Please select at least one staff member.');
                        return;
                    }
                    if (confirm('Are you sure you want to delete selected staff members?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('delete.selected.staff') }}';
                        form.innerHTML = `
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="ids" value="${selected.join(',')}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    </script>
@endpush
