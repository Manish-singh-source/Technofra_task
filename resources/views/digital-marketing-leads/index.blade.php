@extends('/layout/master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Digital Marketing Leads</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h5 class="mb-1">Digital Marketing Leads</h5>
                            <p class="text-muted mb-0">Switch tabs to view Digital Marketing leads and Web App leads.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary">Digital: {{ $leads->count() }}</span>
                            <span class="badge bg-info text-dark">Web App: {{ $webappLeads->count() }}</span>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" id="leadTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="digital-leads-tab" data-bs-toggle="tab"
                                data-bs-target="#digital-leads-pane" type="button" role="tab"
                                aria-controls="digital-leads-pane" aria-selected="true">
                                Digital Marketing Leads ({{ $leads->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="webapp-leads-tab" data-bs-toggle="tab"
                                data-bs-target="#webapp-leads-pane" type="button" role="tab"
                                aria-controls="webapp-leads-pane" aria-selected="false">
                                Web & App Leads ({{ $webappLeads->count() }})
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="leadTabsContent">
                        <div class="tab-pane fade show active" id="digital-leads-pane" role="tabpanel"
                            aria-labelledby="digital-leads-tab">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Company</th>
                                            <th>Website</th>
                                            <th>Source Page</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($leads as $lead)
                                            <tr>
                                                <td>{{ $lead->id }}</td>
                                                <td>{{ $lead->name }}</td>
                                                <td>{{ $lead->email }}</td>
                                                <td>{{ $lead->phone }}</td>
                                                <td>{{ $lead->company ?: 'N/A' }}</td>
                                                <td>{{ $lead->website ?: 'N/A' }}</td>
                                                <td>{{ $lead->source_page }}</td>
                                                <td>{{ optional($lead->created_at)->format('d M Y h:i A') }}</td>
                                                <td>
                                                    <div class="d-flex order-actions">
                                                        <form method="POST" action="{{ route('digital-marketing-leads.destroy', $lead->id) }}" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this lead?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link p-0 text-danger" title="Delete">
                                                                <i class='bx bxs-trash'></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class='bx bx-user-x' style="font-size: 48px; color: #ccc;"></i>
                                                        <h6 class="mt-2 text-muted">No leads found</h6>
                                                        <p class="text-muted mb-0">Data will appear here once records are added to the table.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="webapp-leads-pane" role="tabpanel" aria-labelledby="webapp-leads-tab">
                            <div class="table-responsive">
                                <table id="webapp-leads-table" class="table table-striped table-bordered align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Company</th>
                                            <th>Website</th>
                                            <th>Message</th>
                                            <th>Source Page</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($webappLeads as $lead)
                                            <tr>
                                                <td>{{ $lead->id }}</td>
                                                <td>{{ $lead->name }}</td>
                                                <td>{{ $lead->email }}</td>
                                                <td>{{ $lead->phone }}</td>
                                                <td>{{ $lead->company ?: 'N/A' }}</td>
                                                <td>{{ $lead->website ?: 'N/A' }}</td>
                                                <td>{{ $lead->message ?: 'N/A' }}</td>
                                                <td>{{ $lead->source_page }}</td>
                                                <td>{{ optional($lead->created_at)->format('d M Y h:i A') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class='bx bx-user-x' style="font-size: 48px; color: #ccc;"></i>
                                                        <h6 class="mt-2 text-muted">No web app leads found</h6>
                                                        <p class="text-muted mb-0">Web App lead data will appear here once records are added.</p>
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
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const $webappLeadsTable = $('#webapp-leads-table');

            function initializeWebappLeadsTable() {
                if (!$webappLeadsTable.length) {
                    return;
                }

                if (!$.fn.DataTable.isDataTable($webappLeadsTable)) {
                    $webappLeadsTable.DataTable({
                        order: []
                    });
                } else {
                    $webappLeadsTable.DataTable().columns.adjust();
                }
            }

            $('button[data-bs-target="#webapp-leads-pane"]').on('shown.bs.tab', function() {
                initializeWebappLeadsTable();
            });
        });
    </script>
@endpush
