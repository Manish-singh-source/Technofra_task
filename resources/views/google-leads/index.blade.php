@extends('layout.master')

@section('content')
    @php
        $totalLeads = \Illuminate\Support\Facades\DB::table('google_leads')->count();
        $realLeads = \Illuminate\Support\Facades\DB::table('google_leads')->where('is_test', false)->count();
        $testLeads = \Illuminate\Support\Facades\DB::table('google_leads')->where('is_test', true)->count();
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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Google Ads Leads</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Leads</p>
                                    <h4 class="my-1 text-primary">{{ $totalLeads }}</h4>
                                </div>
                                <div class="text-primary ms-auto font-35"><i class='bx bx-list-ul'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Real Leads</p>
                                    <h4 class="my-1 text-success">{{ $realLeads }}</h4>
                                </div>
                                <div class="text-success ms-auto font-35"><i class='bx bx-check-circle'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Test Leads</p>
                                    <h4 class="my-1 text-warning">{{ $testLeads }}</h4>
                                </div>
                                <div class="text-warning ms-auto font-35"><i class='bx bx-test-tube'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Google Ads Leads</h5>
                            <p class="text-muted mb-0">{{ $leads->total() }} leads found</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('google-leads.index') }}" class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone"
                                value="{{ request('search') }}">
                            @if ($type)
                                <input type="hidden" name="type" value="{{ $type }}">
                            @endif
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group" aria-label="Lead type filters">
                                <a href="{{ route('google-leads.index', array_filter(['search' => $search])) }}"
                                    class="btn {{ !$type ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                                <a href="{{ route('google-leads.index', array_filter(['search' => $search, 'type' => 'real'])) }}"
                                    class="btn {{ $type === 'real' ? 'btn-success' : 'btn-outline-success' }}">Real</a>
                                <a href="{{ route('google-leads.index', array_filter(['search' => $search, 'type' => 'test'])) }}"
                                    class="btn {{ $type === 'test' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">Test</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if ($leads->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ $leads->total() }} leads
                            </small>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>Campaign ID</th>
                                    <th>Lead Stage</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td>{{ $leads->firstItem() + $loop->index }}</td>
                                        <td>{{ $lead->full_name ?? 'N/A' }}</td>
                                        <td>{{ $lead->email ?? 'N/A' }}</td>
                                        <td>{{ $lead->phone ?? 'N/A' }}</td>
                                        <td>{{ $lead->company ?? 'N/A' }}</td>
                                        <td>{{ $lead->campaign_id ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info rounded-pill">{{ $lead->lead_stage ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill text-uppercase">{{ $lead->status ?? 'new' }}</span>
                                        </td>
                                        <td>{{ $lead->lead_submit_time?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                                        <td>
                                            @if($lead->is_test)
                                                <span class="badge bg-warning text-dark rounded-pill">Test</span>
                                            @else
                                                <span class="badge bg-success rounded-pill">Real</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex order-actions align-items-center">
                                                <a href="{{ route('google-leads.show', $lead) }}" class="text-primary" title="View">
                                                    <i class='bx bxs-show'></i>
                                                </a>
                                                @can('edit_leads')
                                                    <button type="button" class="text-warning border-0 bg-transparent ms-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#statusGoogleLeadModal-{{ $lead->id }}"
                                                        title="Change Status"
                                                        style="cursor: pointer;">
                                                        <i class='bx bxs-edit-alt'></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5">
                                            <p class="text-muted mb-0">No Google Ads leads found for the selected filters.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $leads->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('edit_leads')
        @foreach($leads as $lead)
            <div class="modal fade" id="statusGoogleLeadModal-{{ $lead->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('google-leads.status', $lead) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title">Change Lead Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    @foreach (['new', 'contacted', 'qualified', 'converted', 'loss'] as $status)
                                        <option value="{{ $status }}" {{ ($lead->status ?? 'new') === $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
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
    @endcan
@endsection
