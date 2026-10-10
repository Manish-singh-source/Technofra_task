@extends('layout.master')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        referrerpolicy="no-referrer" />
    <style>
        .lead-management-table-wrap {
            max-width: 100%;
            overflow-x: auto;
            overscroll-behavior-x: contain;
        }

        #example.lead-management-table {
            width: 100% !important;
            min-width: 1300px;
            table-layout: auto;
        }

        .lead-management-table th,
        .lead-management-table td {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .lead-management-table .order-actions {
            display: grid;
            grid-template-columns: repeat(4, 30px);
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            width: max-content;
            margin-inline: auto;
        }

        .lead-management-table .lead-actions-cell {
            width: 150px;
            min-width: 150px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .lead-management-table .lead-action-control {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            min-width: 30px;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
        }

        .lead-management-table .lead-action-control i {
            font-size: 18px;
            line-height: 1;
        }

        .lead-management-table .lead-actions-form {
            display: flex;
            grid-column: 4;
            margin: 0 !important;
        }

        .lead-management-table .lead-action-view {
            grid-column: 1;
        }

        .lead-management-table .lead-action-assign {
            grid-column: 2;
        }

        .lead-management-table .lead-action-edit {
            grid-column: 3;
        }

        @media (max-width: 767.98px) {
            .lead-management-table {
                font-size: 0.8rem;
            }

            .lead-management-table th,
            .lead-management-table td {
                padding: 0.35rem 0.25rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $activeSource = $filters['source'] ?? '';
        $tabColorClasses = [
            'all' => ['active' => 'btn-dark', 'inactive' => 'btn-outline-dark', 'badge' => 'bg-dark'],
            'lead' => [
                'active' => 'btn-warning text-dark',
                'inactive' => 'btn-outline-warning',
                'badge' => 'bg-warning text-dark',
            ],
            'digital_marketing' => [
                'active' => 'btn-success',
                'inactive' => 'btn-outline-success',
                'badge' => 'bg-success',
            ],
            'webapp' => ['active' => 'btn-danger', 'inactive' => 'btn-outline-danger', 'badge' => 'bg-danger'],
            'meta' => [
                'active' => 'btn-info text-dark',
                'inactive' => 'btn-outline-info',
                'badge' => 'bg-info text-dark',
            ],
            'google' => ['active' => 'btn-primary', 'inactive' => 'btn-outline-primary', 'badge' => 'bg-primary'],
            'indiamart' => ['active' => 'btn-danger', 'inactive' => 'btn-outline-danger', 'badge' => 'bg-danger'],
            'justdial' => ['active' => 'btn-primary', 'inactive' => 'btn-outline-primary', 'badge' => 'bg-primary'],
            'enquiryform' => [
                'active' => 'btn-secondary',
                'inactive' => 'btn-outline-secondary',
                'badge' => 'bg-secondary',
            ],
        ];
        $tabIcons = [
            'all' => 'bx-grid-alt',
            'lead' => 'bx-user',
            'digital_marketing' => 'bx-line-chart',
            'webapp' => 'bx-code-alt',
            'meta' => 'fa-brands fa-meta',
            'google' => 'bxl-google',
            'indiamart' => 'bx-store',
            'justdial' => 'bx-phone-call',
            'enquiryform' => 'bx-message-square-detail',
        ];
        $metaSyncReturnTo = route(
            'lead-management.index',
            array_filter(
                [
                    'source' => 'meta',
                    'search' => $filters['search'] ?? null,
                    'status' => $filters['status'] ?? null,
                ],
                fn($value) => filled($value),
            ),
        );
    @endphp
    <div class="page-wrapper">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i
                                        class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Lead Management</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <h5 class="mb-0">Lead Management</h5>
                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="{{ route('leads.sync') }}" id="metaSyncForm"
                                class="{{ $activeSource === 'meta' ? '' : '' }}"
                                onsubmit="return confirm('Sync latest leads from Meta? This may take a few seconds.')">
                                @csrf
                                <input type="hidden" name="return_to" value="{{ $metaSyncReturnTo }}">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bx bx-refresh me-1"></i> Sync Now
                                </button>
                            </form>
                            <a href="{{ route('lead-management.performance') }}" class="btn btn-outline-dark btn-sm">
                                <i class='bx bx-line-chart me-1'></i> Performance
                            </a>
                            @can('create_leads')
                                <a href="{{ route('add-lead') }}" class="btn btn-primary btn-sm">
                                    <i class='bx bxs-plus-square'></i> Add New Lead
                                </a>
                            @endcan
                            @if ($canManageLeadAssignments ?? false)
                                <button type="button" class="btn btn-primary btn-sm" id="openBulkAssignModalBtn">
                                    <i class='bx bxs-user-plus'></i> Bulk Assign
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @php($allTabStyle = $tabColorClasses['all'])
                        <button type="button"
                            class="btn source-filter-btn {{ $activeSource === '' ? $allTabStyle['active'] : $allTabStyle['inactive'] }}"
                            data-source-key="" data-source-label="" data-active-class="{{ $allTabStyle['active'] }}"
                            data-inactive-class="{{ $allTabStyle['inactive'] }}">
                            <i
                                class='{{ str_contains($tabIcons['all'] ?? '', 'fa-') ? $tabIcons['all'] ?? 'bx bx-grid-alt' : 'bx ' . ($tabIcons['all'] ?? 'bx-grid-alt') }} me-1'></i>
                            All
                            <span
                                class="badge source-filter-badge {{ $activeSource === '' ? 'bg-white text-dark' : $allTabStyle['badge'] }} ms-1"
                                data-active-badge-class="bg-white text-dark"
                                data-inactive-badge-class="{{ $allTabStyle['badge'] }}">
                                {{ $tabCounts['all'] ?? 0 }}
                            </span>
                        </button>

                        @foreach ($sources as $key => $label)
                            @php($tabStyle = $tabColorClasses[$key] ?? ['active' => 'btn-secondary', 'inactive' => 'btn-outline-secondary', 'badge' => 'bg-secondary'])
                            <button type="button"
                                class="btn source-filter-btn {{ $activeSource === $key ? $tabStyle['active'] : $tabStyle['inactive'] }}"
                                data-source-key="{{ $key }}" data-source-label="{{ $label }}"
                                data-active-class="{{ $tabStyle['active'] }}"
                                data-inactive-class="{{ $tabStyle['inactive'] }}">
                                <i
                                    class='{{ str_contains($tabIcons[$key] ?? '', 'fa-') ? $tabIcons[$key] ?? 'bx bx-category' : 'bx ' . ($tabIcons[$key] ?? 'bx-category') }} me-1'></i>
                                {{ $label }}
                                <span
                                    class="badge source-filter-badge {{ $activeSource === $key ? 'bg-white text-dark' : $tabStyle['badge'] }} ms-1"
                                    data-active-badge-class="bg-white text-dark"
                                    data-inactive-badge-class="{{ $tabStyle['badge'] }}">
                                    {{ $tabCounts[$key] ?? 0 }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    @if ($leads->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ $leads->total() }}
                                leads
                            </small>
                            <form method="GET" action="{{ route('lead-management.index') }}" class="d-flex gap-2">
                                <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    @foreach ($statusOptions ?? [] as $statusOption)
                                        <option value="{{ $statusOption['slug'] }}"
                                            {{ ($filters['status'] ?? '') === $statusOption['slug'] ? 'selected' : '' }}>
                                            {{ $statusOption['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @endif

                    <div class="table-responsive lead-management-table-wrap">

                        <table id="example" data-no-default-datatable="true"
                            class="table table-striped table-bordered align-middle lead-management-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="lead-col-select">
                                        @if ($canManageLeadAssignments ?? false)
                                            <input type="checkbox" id="selectAllLeads">
                                        @endif
                                    </th>
                                    <th class="lead-col-index">Sr No</th>
                                    <th class="lead-col-name">Name</th>
                                    <th class="lead-col-email">Email</th>
                                    <th class="lead-col-number">Number</th>
                                    <th class="lead-col-company">Company</th>
                                    <th class="lead-col-source">Source</th>
                                    <th class="d-none">Source Key</th>
                                    <th>Status</th>
                                    <th class="lead-col-assigned">Assigned To</th>
                                    <th class="lead-col-created">Created Date</th>
                                    <th class="lead-actions-cell">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td class="lead-col-select">
                                            @if ($canManageLeadAssignments ?? false)
                                                <input type="checkbox" class="lead-select-checkbox"
                                                    data-source="{{ $lead['source_type'] }}"
                                                    data-id="{{ $lead['source_id'] }}">
                                            @endif
                                        </td>
                                        <td class="lead-col-index">
                                            {{ ($leads->currentPage() - 1) * $leads->perPage() + $loop->iteration }}</td>
                                        <td class="lead-col-name">{{ $lead['name'] }}</td>
                                        <td class="lead-col-email">{{ $lead['email'] }}</td>
                                        <td class="lead-col-number">{{ $lead['number'] }}</td>
                                        <td class="lead-col-company">{{ $lead['company'] }}</td>
                                        <td class="lead-col-source">{{ $lead['source'] }}</td>
                                        <td class="d-none">{{ $lead['source_type'] }}</td>
                                        <td><span
                                                class="badge bg-secondary rounded-pill text-uppercase">{{ $lead['status'] ?? 'new' }}</span>
                                        </td>
                                        <td class="lead-col-assigned">{{ $lead['assigned_to'] ?? '-' }}</td>
                                        <td class="lead-col-created">{{ $lead['created_at'] }}</td>
                                        <td class="lead-actions-cell">
                                            <div class="order-actions">
                                                <a href="{{ route('lead-management.show', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}"
                                                    class="text-primary lead-action-control lead-action-view"
                                                    title="View"><i class='bx bxs-show'></i></a>

                                                @if ($canManageLeadAssignments ?? false)
                                                    <button type="button"
                                                        class="text-warning border-0 bg-transparent lead-action-control lead-action-assign"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#assignLeadModal-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}"
                                                        title="Assign">
                                                        <i class='bx bxs-user-plus'></i>
                                                    </button>

                                                    @if ($lead['source_type'] == 'lead')
                                                        {{-- <button type="button" class="text-info border-0 bg-transparent ms-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#statusLeadManagementModal-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}"
                                                            title="Change Status" style="cursor: pointer;">
                                                            <i class='bx bxs-edit-alt'></i>
                                                        </button> --}}
                                                        <a href="{{ route('lead.edit', $lead['source_id']) }}"
                                                            class="text-info border-0 bg-transparent lead-action-control lead-action-edit"><i
                                                                class='bx bxs-edit-alt'></i></a>
                                                    @endif
                                                @endif

                                                @can('delete_leads')
                                                    <form method="POST"
                                                        action="{{ route('lead-management.destroy', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}"
                                                        class="lead-actions-form lead-action-delete"
                                                        onsubmit="return confirm('Are you sure you want to delete this lead?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-danger border-0 bg-transparent lead-action-control"
                                                            title="Delete">
                                                            <i class='bx bxs-trash'></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">No leads found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canManageLeadAssignments ?? false)
        <div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('lead-management.bulk-assign') }}" id="bulkAssignForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Bulk Assign Leads</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2 text-muted">
                                Selected leads: <strong id="selectedLeadsCount">0</strong>
                            </div>
                            <label class="form-label">Assign To</label>
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary select-all-staff-btn"
                                    data-target="#bulkAssignStaffList">
                                    Select All
                                </button>
                            </div>
                            <div id="bulkAssignStaffList" class="border rounded p-2"
                                style="max-height: 220px; overflow-y: auto;">
                                @foreach ($staff as $member)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input staff-checkbox" type="checkbox"
                                            name="assigned_user_ids[]" value="{{ $member->id }}"
                                            id="bulk-staff-{{ $member->id }}">
                                        <label class="form-check-label" for="bulk-staff-{{ $member->id }}">
                                            {{ $member->name ?: $member->first_name . ' ' . $member->last_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div id="bulkAssignHiddenInputs"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Assign Selected</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach ($leads as $lead)
            <div class="modal fade" id="assignLeadModal-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}"
                tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST"
                            action="{{ route('lead-management.assign', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}"
                            data-role="assign-lead-form">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Assign Lead</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2"><strong>{{ $lead['name'] }}</strong></div>
                                <div class="text-muted mb-3">{{ $lead['email'] }} | {{ $lead['number'] }}</div>
                                <label class="form-label">Assign To</label>
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary select-all-staff-btn"
                                        data-target="#singleAssignStaffList-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}">
                                        Select All
                                    </button>
                                </div>
                                <div id="singleAssignStaffList-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}"
                                    class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                    @foreach ($staff as $member)
                                        <div class="form-check mb-1">
                                            <input class="form-check-input staff-checkbox" type="checkbox"
                                                name="assigned_user_ids[]" value="{{ $member->id }}"
                                                id="single-staff-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}-{{ $member->id }}">
                                            <label class="form-check-label"
                                                for="single-staff-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}-{{ $member->id }}">
                                                {{ $member->name ?: $member->first_name . ' ' . $member->last_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Assign</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusLeadManagementModal-{{ $lead['source_type'] }}-{{ $lead['source_id'] }}"
                tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST"
                            action="{{ route('lead-management.status', ['source' => $lead['source_type'], 'id' => $lead['source_id']]) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title">Change Lead Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2"><strong>{{ $lead['name'] }}</strong></div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    @foreach ($statusOptions ?? [] as $statusOption)
                                        <option value="{{ $statusOption['slug'] }}"
                                            {{ ($lead['status'] ?? 'new') === $statusOption['slug'] ? 'selected' : '' }}>
                                            {{ $statusOption['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <label class="form-label mt-2">Conversion Value (if converted)</label>
                                <input type="number" step="0.01" name="won_value" class="form-control">
                                <label class="form-label mt-2">Lost Reason (required if lost)</label>
                                <textarea name="lost_reason" class="form-control" rows="2"></textarea>
                                <label class="form-label mt-2">Remarks</label>
                                <input type="text" name="remarks" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var $table = $('#example');
            var dataTableInstance = null;

            if ($table.length && !$.fn.DataTable.isDataTable($table)) {
                dataTableInstance = $table.DataTable({
                    order: [],
                    autoWidth: false,
                    scrollX: false
                });
            } else if ($table.length) {
                dataTableInstance = $table.DataTable();
            }

            const sourceFilterButtons = document.querySelectorAll('.source-filter-btn');
            const sourceColumnIndex = 7;
            const metaSyncForm = document.getElementById('metaSyncForm');

            function escapeRegex(value) {
                return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function updateSourceFilterButtonState(activeButton) {
                sourceFilterButtons.forEach((button) => {
                    const isActive = button === activeButton;
                    const activeClass = button.dataset.activeClass || '';
                    const inactiveClass = button.dataset.inactiveClass || '';

                    if (activeClass) {
                        activeClass.split(' ').filter(Boolean).forEach((className) => {
                            button.classList.toggle(className, isActive);
                        });
                    }
                    if (inactiveClass) {
                        inactiveClass.split(' ').filter(Boolean).forEach((className) => {
                            button.classList.toggle(className, !isActive);
                        });
                    }

                    const badge = button.querySelector('.source-filter-badge');
                    if (!badge) return;

                    const activeBadgeClass = badge.dataset.activeBadgeClass || '';
                    const inactiveBadgeClass = badge.dataset.inactiveBadgeClass || '';

                    if (activeBadgeClass) {
                        activeBadgeClass.split(' ').filter(Boolean).forEach((className) => {
                            badge.classList.toggle(className, isActive);
                        });
                    }
                    if (inactiveBadgeClass) {
                        inactiveBadgeClass.split(' ').filter(Boolean).forEach((className) => {
                            badge.classList.toggle(className, !isActive);
                        });
                    }
                });
            }

            function setMetaSyncFormVisibility(sourceKey) {
                if (!metaSyncForm) return;

                metaSyncForm.classList.toggle('d-none', sourceKey !== 'meta');
            }

            function submitMetaSyncForm() {
                if (!metaSyncForm || metaSyncForm.dataset.autoSyncing === '1') return;

                metaSyncForm.dataset.autoSyncing = '1';
                const submitButton = metaSyncForm.querySelector('button[type="submit"]');

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Syncing...';
                }

                metaSyncForm.submit();
            }

            function applySourceFilter(sourceKey) {
                if (!dataTableInstance) return;

                if (sourceKey === '') {
                    dataTableInstance.column(sourceColumnIndex).search('').draw();
                } else {
                    dataTableInstance
                        .column(sourceColumnIndex)
                        .search(`^${escapeRegex(sourceKey)}$`, true, false)
                        .draw();
                }
            }

            sourceFilterButtons.forEach((button) => {
                button.addEventListener('click', function() {
                    const sourceKey = (this.dataset.sourceKey || '').trim();

                    applySourceFilter(sourceKey);
                    updateSourceFilterButtonState(this);
                    setMetaSyncFormVisibility(sourceKey);

                    if (sourceKey === 'meta') {
                        submitMetaSyncForm();
                    }
                });
            });

            const initialActiveButton =
                Array.from(sourceFilterButtons).find((button) => button.dataset.sourceKey ===
                    @json($activeSource)) ||
                Array.from(sourceFilterButtons).find((button) => button.dataset.sourceKey === '');

            if (initialActiveButton) {
                const initialSourceKey = (initialActiveButton.dataset.sourceKey || '').trim();

                applySourceFilter(initialSourceKey);
                updateSourceFilterButtonState(initialActiveButton);
                setMetaSyncFormVisibility(initialSourceKey);
            }

            const bulkAssignModalElement = document.getElementById('bulkAssignModal');
            const bulkAssignModal = bulkAssignModalElement ? new bootstrap.Modal(bulkAssignModalElement) : null;
            const openBulkAssignModalBtn = document.getElementById('openBulkAssignModalBtn');
            const selectAllLeads = document.getElementById('selectAllLeads');
            const selectedLeadsCount = document.getElementById('selectedLeadsCount');
            const bulkAssignHiddenInputs = document.getElementById('bulkAssignHiddenInputs');
            const bulkAssignForm = document.getElementById('bulkAssignForm');

            function selectedLeadCheckboxes() {
                return Array.from(document.querySelectorAll('.lead-select-checkbox:checked'));
            }

            function updateSelectedCount() {
                if (selectedLeadsCount) {
                    selectedLeadsCount.textContent = selectedLeadCheckboxes().length;
                }
            }

            function setHiddenInputsForSelectedLeads() {
                if (!bulkAssignHiddenInputs) return;
                bulkAssignHiddenInputs.innerHTML = '';
                selectedLeadCheckboxes().forEach((checkbox, index) => {
                    const sourceInput = document.createElement('input');
                    sourceInput.type = 'hidden';
                    sourceInput.name = `selected_leads[${index}][source]`;
                    sourceInput.value = checkbox.dataset.source;
                    bulkAssignHiddenInputs.appendChild(sourceInput);

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = `selected_leads[${index}][id]`;
                    idInput.value = checkbox.dataset.id;
                    bulkAssignHiddenInputs.appendChild(idInput);
                });
            }

            if (selectAllLeads) {
                selectAllLeads.addEventListener('change', function() {
                    document.querySelectorAll('.lead-select-checkbox').forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectedCount();
                });
            }

            document.querySelectorAll('.lead-select-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    if (!this.checked && selectAllLeads) {
                        selectAllLeads.checked = false;
                    }
                    updateSelectedCount();
                });
            });

            if (openBulkAssignModalBtn) {
                openBulkAssignModalBtn.addEventListener('click', function() {
                    const selected = selectedLeadCheckboxes();
                    if (selected.length === 0) {
                        alert('Please select at least one lead for bulk assign.');
                        return;
                    }

                    updateSelectedCount();
                    setHiddenInputsForSelectedLeads();
                    if (bulkAssignModal) {
                        bulkAssignModal.show();
                    }
                });
            }

            if (bulkAssignForm) {
                bulkAssignForm.addEventListener('submit', function(event) {
                    setHiddenInputsForSelectedLeads();

                    const checkedStaff = bulkAssignForm.querySelectorAll(
                        'input[name="assigned_user_ids[]"]:checked');
                    if (checkedStaff.length === 0) {
                        alert('Please select at least one staff member.');
                        event.preventDefault();
                    }
                });
            }

            document.querySelectorAll('.select-all-staff-btn').forEach((button) => {
                button.addEventListener('click', function() {
                    const targetSelector = this.getAttribute('data-target');
                    const container = document.querySelector(targetSelector);
                    if (!container) return;

                    const checkboxes = container.querySelectorAll(
                        'input[name="assigned_user_ids[]"]');
                    if (!checkboxes.length) return;

                    const allChecked = Array.from(checkboxes).every((cb) => cb.checked);
                    checkboxes.forEach((cb) => {
                        cb.checked = !allChecked;
                    });

                    this.textContent = allChecked ? 'Select All' : 'Unselect All';
                });
            });

            document.querySelectorAll('form[data-role=\"assign-lead-form\"]').forEach((form) => {
                form.addEventListener('submit', function(event) {
                    const checkedStaff = form.querySelectorAll(
                        'input[name="assigned_user_ids[]"]:checked');
                    if (checkedStaff.length === 0) {
                        alert('Please select at least one staff member.');
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush
