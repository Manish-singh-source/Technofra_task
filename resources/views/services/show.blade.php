@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Services</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Service Details</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Service Details</h5>
                                <div>
                                    <a href="{{ route('services.edit', $service->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="bx bx-arrow-back"></i> Back to List
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Company Name:</b>
                                        <p class="mb-0">{{ $service->company?->company_name ?: ($service->client?->businessDetail?->company_name ?: 'N/A') }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Client Email:</b>
                                        <p class="mb-0">{{ $service->client->email ?? 'N/A' }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Vendor Name:</b>
                                        <p class="mb-0">{{ $service->vendor->name ?? 'N/A' }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Vendor Email:</b>
                                        <p class="mb-0">{{ $service->vendor->email ?? 'N/A' }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Service Name:</b>
                                        <p class="mb-0">{{ $service->service_name }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Plan Type:</b>
                                        <p class="mb-0">{{ $service->plan_type ? ucwords(str_replace('_', ' ', $service->plan_type)) : 'N/A' }}</p>
                                    </li>
                                    @if($service->service_details)
                                    <li class="list-group-item">
                                        <b>Service Details:</b>
                                        <div class="mt-2">
                                            {!! $service->service_details !!}
                                        </div>
                                    </li>
                                    @endif
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Remark:</b>
                                        <p class="mb-0">
                                            @if($service->remark_text)
                                                <span class="badge border" style="{{ $service->remark_badge_style }}">{{ $service->remark_text }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Start Date:</b>
                                        <p class="mb-0">{{ $service->start_date->format('d M Y') }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>End Date:</b>
                                        <p class="mb-0">{{ $service->end_date->format('d M Y') }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Duration:</b>
                                        <p class="mb-0">{{ $service->start_date->diffInDays($service->end_date) + 1 }} days</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Billing Date:</b>
                                        <p class="mb-0">{{ $service->billing_date->format('d M Y') }}</p>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <b>Status:</b>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $service->status_badge }}">
                                                {{ ucfirst($service->status) }}
                                            </span>
                                        </p>
                                    </li>
                                </ul>

                                @if ($service->amcService)
                                    <div class="card mt-4 border-warning">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">AMC Details</h6>
                                                <small class="text-muted">Visits are generated automatically and start in pending state.</small>
                                            </div>
                                            <span class="badge bg-warning text-dark">AMC Enabled</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-3">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">Total Visits</div>
                                                        <div class="fs-5 fw-semibold">{{ $service->amcService->total_visits }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">AMC Start Date</div>
                                                        <div class="fs-6 fw-semibold">{{ $service->amcService->amc_start_date?->format('d M Y') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">AMC End Date</div>
                                                        <div class="fs-6 fw-semibold">{{ $service->amcService->amc_end_date?->format('d M Y') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">Completed Visits</div>
                                                        <div class="fs-5 fw-semibold">
                                                            {{ $service->amcService->amcServiceDetails?->where('status', 'completed')->count() ?? 0 }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Visit</th>
                                                            <th>Visit Date</th>
                                                            <th>Status</th>
                                                            <th>Details</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($service->amcService->amcServiceDetails as $amcDetail)
                                                            <tr>
                                                                <td>Visit {{ $amcDetail->visit_number }}</td>
                                                                <td>{{ $amcDetail->visit_date?->format('d M Y') }}</td>
                                                                <td>
                                                                    <span class="badge {{ $amcDetail->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                        {{ ucfirst($amcDetail->status) }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $amcDetail->details ?: 'N/A' }}</td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#amcVisitModal-{{ $amcDetail->id }}">
                                                                        Update
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @foreach ($service->amcService->amcServiceDetails as $amcDetail)
                                                <div class="modal fade" id="amcVisitModal-{{ $amcDetail->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Update Visit {{ $amcDetail->visit_number }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST" action="{{ route('services.amc-visits.update', ['service' => $service->id, 'detail' => $amcDetail->id]) }}">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Status</label>
                                                                        <select name="status" class="form-select" required>
                                                                            <option value="pending" {{ $amcDetail->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                            <option value="completed" {{ $amcDetail->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Details</label>
                                                                        <textarea name="details" class="form-control" rows="4" placeholder="Add visit remarks...">{{ old('details', $amcDetail->details) }}</textarea>
                                                                    </div>
                                                                    <div class="alert alert-info mb-0">
                                                                        <strong>Visit date:</strong> {{ $amcDetail->visit_date?->format('d M Y') }}
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay toggle-icon"></div>
<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection
