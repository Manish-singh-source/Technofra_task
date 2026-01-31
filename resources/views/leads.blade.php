@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Leads</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">All Leads</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Settings</button>
                        <button type="button"
                            class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Action</a>
                            <a class="dropdown-item" href="javascript:;">Another action</a>
                            <a class="dropdown-item" href="javascript:;">Something else here</a>
                            <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated
                                link</a>
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

            <!-- Statistics Cards -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Leads</p>
                                    <h4 class="my-1 text-info">{{ $allLeads }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto"><i class='bx bxs-group'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">New Leads</p>
                                    <h4 class="my-1 text-danger">{{ $newLeads }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto"><i class='bx bxs-message-add'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Contacted</p>
                                    <h4 class="my-1 text-warning">{{ $contactedLeads }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-phone-call'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Converted</p>
                                    <h4 class="my-1 text-success">{{ $convertedLeads }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto"><i class='bx bxs-check-circle'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <input type="text" class="form-control ps-5 radius-30" id="searchLeads" placeholder="Search Leads"> <span
                                class="position-absolute top-50 product-show translate-middle-y"><i
                                    class="bx bx-search"></i></span>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('add-lead') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
                                <i class="bx bxs-plus-square"></i>Add New Lead
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="leadsTable" class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Value</th>
                                    <th>Tags</th>
                                    <th>Assigned</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td><input type="checkbox" class="lead-checkbox" value="{{ $lead->id }}"></td>
                                        <td>#{{ $lead->id }}</td>
                                        <td>
                                            <a href="{{ route('lead.show', $lead->id) }}" class="text-decoration-none">
                                                {{ $lead->name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $lead->company ?? 'N/A' }}</td>
                                        <td>{{ $lead->email ?? 'N/A' }}</td>
                                        <td>{{ $lead->phone ?? 'N/A' }}</td>
                                        <td>${{ number_format($lead->lead_value ?? 0, 2) }}</td>
                                        <td>
                                            @if($lead->tags)
                                                @foreach($lead->tags as $tag)
                                                    <span class="badge bg-primary me-1">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($lead->assigned)
                                                @foreach($lead->assigned as $assignedId)
                                                    @if(isset($staff[$assignedId]))
                                                        <span class="badge bg-info me-1">{{ $staff[$assignedId]->first_name }}</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $lead->source ?? 'N/A')) }}</td>
                                        <td>{{ $lead->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('lead.show', $lead->id) }}" class="text-primary" title="View"><i class='bx bxs-show'></i></a>
                                                <a href="{{ route('lead.edit', $lead->id) }}" class="text-warning ms-3" title="Edit"><i class='bx bxs-edit'></i></a>
                                                <form action="{{ route('lead.destroy', $lead->id) }}" method="POST" class="d-inline ms-3" onsubmit="return confirm('Are you sure you want to delete this lead?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-danger border-0 bg-transparent" title="Delete" style="cursor: pointer;">
                                                        <i class='bx bxs-trash'></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">No leads found. <a href="{{ route('add-lead') }}">Add a new lead</a></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Delete Selected Button -->
                    <div class="mt-3">
                        <button type="button" id="deleteSelectedLeads" class="btn btn-danger" style="display: none;">
                            <i class='bx bxs-trash'></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#leadsTable').DataTable({
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            pageLength: 10,
            order: [[1, 'desc']],
            language: {
                search: "Search Leads:",
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

        // Search functionality using DataTable search
        $('#searchLeads').on('keyup', function() {
            table.search($(this).val()).draw();
        });

        // Select All checkbox
        $('#selectAll').on('change', function() {
            $('.lead-checkbox').prop('checked', $(this).prop('checked'));
            toggleDeleteButton();
        });

        // Individual checkbox change
        $('.lead-checkbox').on('change', function() {
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            if ($('.lead-checkbox:checked').length > 0) {
                $('#deleteSelectedLeads').show();
            } else {
                $('#deleteSelectedLeads').hide();
            }
        }

        // Delete selected leads
        $('#deleteSelectedLeads').on('click', function() {
            var selectedIds = [];
            $('.lead-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length > 0 && confirm('Are you sure you want to delete selected leads?')) {
                $.ajax({
                    url: '{{ route("lead.delete-selected") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });
</script>
@endsection
