@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('google-leads.index') }}">Google Ads Leads</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Lead Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('google-leads.index') }}" class="btn btn-light">Back to Leads</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0">Lead Detail</h5>
                        @if($lead->is_test)
                            <span class="badge bg-warning text-dark rounded-pill">Test</span>
                        @else
                            <span class="badge bg-success rounded-pill">Real</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Lead Info</h6>
                            <p class="mb-2"><strong>Full Name:</strong> {{ $lead->full_name ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Email:</strong> {{ $lead->email ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Phone:</strong> {{ $lead->phone ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Company:</strong> {{ $lead->company ?? 'N/A' }}</p>
                            <p class="mb-0">
                                <strong>Lead Stage:</strong>
                                <span class="badge bg-info rounded-pill">{{ $lead->lead_stage ?? 'N/A' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Ad Info</h6>
                            <p class="mb-2"><strong>Form ID:</strong> {{ $lead->form_id ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Campaign ID:</strong> {{ $lead->campaign_id ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Google Click ID:</strong> {{ $lead->gcl_id ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>Submitted At:</strong> {{ $lead->lead_submit_time?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                            <p class="mb-0">
                                <strong>Is Test:</strong>
                                @if($lead->is_test)
                                    <span class="badge bg-warning text-dark rounded-pill">Test</span>
                                @else
                                    <span class="badge bg-success rounded-pill">Real</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Raw Payload</h6>
                    <p>
                        <a class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" href="#rawPayloadCollapse" role="button" aria-expanded="false" aria-controls="rawPayloadCollapse">
                            Toggle JSON Payload
                        </a>
                    </p>
                    <div class="collapse" id="rawPayloadCollapse">
                        <pre class="mb-0">{{ json_encode($lead->raw_payload, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
