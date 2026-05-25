@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Meta Leads</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <form method="POST" action="{{ route('leads.sync') }}"
                        onsubmit="return confirm('Sync latest leads from Meta? This may take a few seconds.')">
                        @csrf
                        <button type="submit" class="btn btn-primary radius-30">
                            <i class="bx bx-refresh"></i>Sync Now
                        </button>
                    </form>
                </div>
            </div>

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

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Meta Leads</h5>
                            <p class="text-muted mb-0">{{ $leads->total() }} leads found</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('leads.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search name, email, phone"
                                value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control"
                                value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control"
                                value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <select name="form_id" class="form-select">
                                <option value="">All Forms</option>
                                @foreach ($formIds as $formId)
                                    <option value="{{ $formId }}" @selected(($filters['form_id'] ?? '') == $formId)>
                                        {{ $formId }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                            <a href="{{ route('leads.index') }}" class="btn btn-light w-100">Clear</a>
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
                                    <th>Form ID</th>
                                    <th>City/State</th>
                                    <th>Status</th>
                                    <th>Lead Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leads as $lead)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Str::limit($lead->lead_id, 10, '...') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($lead->email && $lead->phone)
                                                    <span class="badge bg-success rounded-pill" title="Complete contact">&bull;</span>
                                                @endif
                                                <span>{{ $lead->full_name ?: '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($lead->email)
                                                <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($lead->phone)
                                                <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $lead->form_id ?: '-' }}</td>
                                        <td>{{ trim(($lead->city ?: '') . (isset($lead->state) && $lead->city ? ', ' : '') . ($lead->state ?: '')) ?: '-' }}</td>
                                        <td><span class="badge bg-secondary rounded-pill text-uppercase">{{ $lead->status ?? 'new' }}</span></td>
                                        <td>{{ $lead->created_time?->format('d M Y, H:i') ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex order-actions align-items-center">
                                                <a href="{{ route('leads.show', $lead) }}" class="text-primary" title="View">
                                                    <i class='bx bxs-show'></i>
                                                </a>
                                                @can('edit_leads')
                                                    <button type="button" class="text-warning border-0 bg-transparent ms-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#statusLeadModal-{{ $lead->id }}"
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
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class='bx bx-user-x' style="font-size: 48px; color: #ccc;"></i>
                                                <h6 class="mt-2 text-muted">No leads found.</h6>
                                                <p class="text-muted mb-0">Try syncing from Meta or adjust your filters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $leads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('edit_leads')
        @foreach ($leads as $lead)
            <div class="modal fade" id="statusLeadModal-{{ $lead->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('leads.status', $lead) }}">
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
