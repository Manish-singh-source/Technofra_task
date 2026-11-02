@extends('/layout/master')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('content')
    <style>
        .btn-orange {
            background-color: #ff7f00 !important;
            border-color: #ff7f00 !important;
            color: white !important;
        }

        .btn-outline-orange {
            border-color: #ff7f00 !important;
            color: #ff7f00 !important;
            background-color: transparent !important;
        }

        .btn-outline-orange:hover {
            background-color: #ff7f00 !important;
            color: white !important;
        }

        .bg-orange {
            background-color: #ff7f00 !important;
            color: white !important;
        }
    </style>
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            @php
                $tabLabels = [
                    'all' => 'All',
                    'upcoming' => 'Up Coming',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    // 'pending' => 'Hold',
                    'expired' => 'Expired',
                ];
                $tabColorClasses = [
                    'all' => ['active' => 'btn-dark', 'inactive' => 'btn-outline-dark', 'badge' => 'bg-dark'],
                    'upcoming' => [
                        'active' => 'btn-warning text-dark',
                        'inactive' => 'btn-outline-warning',
                        'badge' => 'bg-warning text-dark',
                    ],
                    'active' => [
                        'active' => 'btn-success',
                        'inactive' => 'btn-outline-success',
                        'badge' => 'bg-success',
                    ],
                    'inactive' => [
                        'active' => 'btn-danger',
                        'inactive' => 'btn-outline-danger',
                        'badge' => 'bg-danger',
                    ],
                    // 'pending' => ['active' => 'btn-info text-dark', 'inactive' => 'btn-outline-info', 'badge' => 'bg-info text-dark'],
                    'expired' => ['active' => 'btn-orange', 'inactive' => 'btn-outline-orange', 'badge' => 'bg-orange'],
                ];
                $visibleCount =
                    $activeTab === 'all' ? $services->count() : $services->where('tab_key', $activeTab)->count();
            @endphp

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Services</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('services.index') }}" class="row g-3 align-items-end"
                                id="dateFilterForm">
                                <input type="hidden" name="tab" id="activeTabInput" value="{{ $activeTab }}">
                                <div class="col-md-3">
                                    <label for="billing_date_range" class="form-label">Billing Date Range</label>
                                    <input type="text" class="form-control" id="billing_date_range"
                                        placeholder="Select billing date range" autocomplete="off">
                                    <input type="hidden" id="billing_from_date" name="billing_from_date"
                                        value="{{ request('billing_from_date') }}">
                                    <input type="hidden" id="billing_to_date" name="billing_to_date"
                                        value="{{ request('billing_to_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="service_date_range" class="form-label">Service Date Range</label>
                                    <input type="text" class="form-control" id="service_date_range"
                                        placeholder="Select start / end date range" autocomplete="off">
                                    <input type="hidden" id="service_from_date" name="service_from_date"
                                        value="{{ request('service_from_date') }}">
                                    <input type="hidden" id="service_to_date" name="service_to_date"
                                        value="{{ request('service_to_date') }}">
                                </div>
                                <div class="col-md-3">
                                    {{-- 
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search"></i> Filter
                                    </button> 
                                    --}}
                                    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="bx bx-refresh"></i> Clear
                                    </a>
                                </div>
                                <div class="col-md-3 text-end">
                                     @can('create_service')
                                    <a href="{{ route('services.create') }}" class="btn btn-primary radius-30">
                                        <i class="bx bxs-plus-square"></i>Add New Service
                                    </a>
                                     @endcan
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2" id="serviceTabs">
                                @foreach ($tabLabels as $tabKey => $tabLabel)
                                    @php($tabStyle = $tabColorClasses[$tabKey])
                                    <button type="button"
                                        class="btn {{ $activeTab === $tabKey ? $tabStyle['active'] : $tabStyle['inactive'] }} service-tab-btn"
                                        data-tab-target="{{ $tabKey }}" data-active-class="{{ $tabStyle['active'] }}"
                                        data-inactive-class="{{ $tabStyle['inactive'] }}"
                                        data-badge-class="{{ $tabStyle['badge'] }}">
                                        {{ $tabLabel }}
                                        <span
                                            class="badge {{ $activeTab === $tabKey ? 'bg-white text-dark' : $tabStyle['badge'] }} ms-1">
                                            {{ $tabCounts[$tabKey] ?? 0 }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h6 class="mb-0">
                                        Showing <span id="visibleServiceCount">{{ $visibleCount }}</span> service(s)
                                        in <span id="activeTabLabel">{{ $tabLabels[$activeTab] ?? 'All' }}</span>
                                        @if (request('billing_from_date') || request('billing_to_date'))
                                            <small class="text-muted">
                                                (billing date
                                                @if (request('billing_from_date'))
                                                    from {{ \Carbon\Carbon::parse(request('billing_from_date'))->format('d M Y') }}
                                                @endif
                                                @if (request('billing_to_date'))
                                                    to {{ \Carbon\Carbon::parse(request('billing_to_date'))->format('d M Y') }}
                                                @endif
                                                )
                                            </small>
                                        @endif
                                        @if (request('service_from_date') || request('service_to_date'))
                                            <small class="text-muted">
                                                (service start/end date
                                                @if (request('service_from_date'))
                                                    from {{ \Carbon\Carbon::parse(request('service_from_date'))->format('d M Y') }}
                                                @endif
                                                @if (request('service_to_date'))
                                                    to {{ \Carbon\Carbon::parse(request('service_to_date'))->format('d M Y') }}
                                                @endif
                                                )
                                            </small>
                                        @endif
                                    </h6>
                                </div>
                                <div>
                                    @can('delete_seleted_services') 
                                    <button type="button" class="btn btn-danger btn-sm" id="delete-selected">
                                        <i class="bx bx-trash"></i> Delete Selected
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="example" data-no-default-datatable="true"
                            class="table table-striped table-bordered align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th><input class="form-check-input" type="checkbox" id="select-all"></th>
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
                            <tbody id="servicesTableBody">
                                @forelse($services as $service)
                                    @php($daysLeft = \Carbon\Carbon::today()->diffInDays($service->end_date, false))
                                    <tr class="service-row {{ $daysLeft <= 5 ? 'table-danger' : '' }}"
                                        data-tab-group="{{ $service->tab_key }}">
                                        <td>
                                            <input class="form-check-input row-checkbox" type="checkbox" name="ids[]"
                                                value="{{ $service->id }}">
                                        </td>
                                        <td>{{ $service->company?->company_name ?: ($service->client?->businessDetail?->company_name ?: 'N/A') }}</td>
                                        <td>{{ $service->service_name }}</td>
                                        <td>{{ $service->plan_type ? ucwords(str_replace('_', ' ', $service->plan_type)) : 'N/A' }}</td>
                                        <td>
                                            @if ($service->remark_text)
                                                <span class="badge border"
                                                    style="{{ $service->remark_badge_style }}">{{ $service->remark_text }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $service->start_date->format('d M Y') }}</td>
                                        <td>
                                            <div>{{ $service->end_date->format('d M Y') }}</div>
                                            <small
                                                class="{{ $daysLeft <= 1 ? 'text-danger' : ($daysLeft <= 3 ? 'text-warning' : 'text-info') }}">
                                                <strong>
                                                    @if ($daysLeft < 0)
                                                        {{ abs($daysLeft) }} days overdue
                                                    @elseif($daysLeft == 0)
                                                        Expires today
                                                    @elseif($daysLeft == 1)
                                                        Expires tomorrow
                                                    @else
                                                        {{ $daysLeft }} days left
                                                    @endif
                                                </strong>
                                            </small>
                                        </td>
                                        <td>{{ $service->billing_date->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $service->effective_status_badge }}">
                                                {{ $service->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                 @can('view_services_detail')
                                                <a href="{{ route('services.show', $service->id) }}" title="View"><i
                                                        class='bx bxs-show'></i></a>
                                                  
                                                 @endcan    
                                                 
                                                @can('edit_services')
                                                <a href="{{ route('services.edit', $service->id) }}" class="ms-3"
                                                    title="Edit"><i class='bx bxs-edit'></i></a>
                                                @endcan

                                                 @can('send_mail_services')
                                                <a href="{{ route('send-mail', $service->id) }}" class="ms-3"
                                                    title="Send Renewal Email"><i class='bx bx-mail-send'></i></a>
                                                 @endcan


                                                @can('delete_services')
                                                <form method="POST"
                                                    action="{{ route('services.destroy', $service->id) }}"
                                                    class="d-inline ms-3"
                                                    onsubmit="return confirm('Are you sure you want to delete this service?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0 text-danger"
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
                                        <td colspan="9" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class='bx bx-folder-open' style="font-size: 48px; color: #ccc;"></i>
                                                <h6 class="mt-2 text-muted">No services found</h6>
                                                <p class="text-muted">Start by adding your first service</p>
                                                <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="bx bx-plus"></i> Add Service
                                                </a>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const deleteButton = document.getElementById('delete-selected');
            const billingDateRangeInput = document.getElementById('billing_date_range');
            const billingFromDateInput = document.getElementById('billing_from_date');
            const billingToDateInput = document.getElementById('billing_to_date');
            const serviceDateRangeInput = document.getElementById('service_date_range');
            const serviceFromDateInput = document.getElementById('service_from_date');
            const serviceToDateInput = document.getElementById('service_to_date');
            const dateFilterForm = document.getElementById('dateFilterForm');
            const activeTabInput = document.getElementById('activeTabInput');
            const tabButtons = document.querySelectorAll('.service-tab-btn');
            const visibleServiceCount = document.getElementById('visibleServiceCount');
            const activeTabLabel = document.getElementById('activeTabLabel');
            const tableElement = $('#example');
            let activeTab = activeTabInput.value || 'all';

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable !== tableElement.get(0)) {
                    return true;
                }

                if (activeTab === 'all') {
                    return true;
                }

                const rowNode = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
                return rowNode ? rowNode.dataset.tabGroup === activeTab : true;
            });

                    const dataTable = tableElement.DataTable({
                        pageLength: 10,
                        order: [],
                        columnDefs: [{
                                orderable: false,
                                targets: [0, 9]
                            },
                            {
                                searchable: false,
                                targets: [0, 9]
                            }
                        ]

            });

            function getVisibleCheckboxes() {
                return dataTable.rows({
                    search: 'applied'
                }).nodes().to$().find('.row-checkbox').toArray();
            }

            function syncSelectAllState() {
                const visibleCheckboxes = getVisibleCheckboxes();

                if (visibleCheckboxes.length === 0) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                    return;
                }

                const checkedCount = visibleCheckboxes.filter((checkbox) => checkbox.checked).length;
                selectAll.checked = checkedCount > 0 && checkedCount === visibleCheckboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < visibleCheckboxes.length;
            }

            function updateVisibleMeta() {
                const visibleRows = dataTable.rows({
                    search: 'applied'
                }).count();
                visibleServiceCount.textContent = visibleRows;

                const activeButton = document.querySelector('[data-tab-target="' + activeTab + '"]');
                activeTabLabel.textContent = activeButton ? activeButton.childNodes[0].textContent.trim() : 'All';
                syncSelectAllState();
            }

            function setButtonClasses(button, isActive) {
                const activeClass = button.dataset.activeClass;
                const inactiveClass = button.dataset.inactiveClass;
                const badgeClass = button.dataset.badgeClass;
                const badge = button.querySelector('.badge');

                button.className = 'btn service-tab-btn ' + (isActive ? activeClass : inactiveClass);

                if (badge) {
                    badge.className = 'badge ms-1 ' + (isActive ? 'bg-white text-dark' : badgeClass);
                }
            }

            function applyTabFilter(tabKey) {
                activeTab = tabKey;
                activeTabInput.value = tabKey;

                tabButtons.forEach((button) => {
                    setButtonClasses(button, button.dataset.tabTarget === tabKey);
                });

                dataTable.draw();
                updateVisibleMeta();
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    getVisibleCheckboxes().forEach((checkbox) => {
                        checkbox.checked = selectAll.checked;
                    });
                    syncSelectAllState();
                });
            }

            tableElement.on('change', '.row-checkbox', function() {
                syncSelectAllState();
            });

            dataTable.on('draw', function() {
                syncSelectAllState();
            });

            deleteButton.addEventListener('click', function() {
                let selected = [];
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    selected.push(cb.value);
                });

                if (selected.length === 0) {
                    alert('Please select at least one record.');
                    return;
                }

                if (confirm('Are you sure you want to delete selected records?')) {
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('delete.selected.service') }}';
                    form.innerHTML = `
                    @csrf
                    <input type="hidden" name="ids" value="${selected.join(',')}">
                `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });

            tabButtons.forEach((button) => {
                button.addEventListener('click', function() {
                    applyTabFilter(button.dataset.tabTarget);
                });
            });

            flatpickr(billingDateRangeInput, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                defaultDate: [billingFromDateInput.value, billingToDateInput.value].filter(Boolean),
                onChange: function(selectedDates, _dateString, instance) {
                    billingFromDateInput.value = selectedDates[0] ? instance.formatDate(selectedDates[0], 'Y-m-d') : '';
                    billingToDateInput.value = selectedDates[1] ? instance.formatDate(selectedDates[1], 'Y-m-d') : '';

                    if (selectedDates.length === 2) {
                        dateFilterForm.submit();
                    }
                }
            });

            flatpickr(serviceDateRangeInput, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                defaultDate: [serviceFromDateInput.value, serviceToDateInput.value].filter(Boolean),
                onChange: function(selectedDates, _dateString, instance) {
                    serviceFromDateInput.value = selectedDates[0] ? instance.formatDate(selectedDates[0], 'Y-m-d') : '';
                    serviceToDateInput.value = selectedDates[1] ? instance.formatDate(selectedDates[1], 'Y-m-d') : '';

                    if (selectedDates.length === 2) {
                        dateFilterForm.submit();
                    }
                }
            });

            applyTabFilter(activeTab);
        });
    </script>
    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection
