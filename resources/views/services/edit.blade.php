@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h6 class="text-uppercase">Service Form</h6>
        <hr>
        <div id="stepper1" class="bs-stepper">
            <div class="card">
                <div class="card-body p-4">
                    @php
                        $serviceAmc = $service->amcService;
                        $isAmcEnabled = (int) old('is_amc', $serviceAmc ? 1 : 0) === 1;
                        $amcTotalVisitsValue = old('amc_total_visits', $serviceAmc?->total_visits);
                        $amcStartDateValue = old('amc_start_date', optional($serviceAmc?->amc_start_date)->format('Y-m-d'));
                        $amcEndDateValue = old('amc_end_date', optional($serviceAmc?->amc_end_date)->format('Y-m-d'));
                        $amcCompletedVisits = $serviceAmc ? $serviceAmc->amcServiceDetails->where('status', 'completed')->count() : 0;
                        $amcPendingVisits = $serviceAmc ? $serviceAmc->amcServiceDetails->where('status', 'pending')->count() : 0;
                    @endphp

                    <h5 class="mb-4">Edit Service</h5>
                    <form class="row g-3" method="POST" action="{{ route('services.update', $service->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <label for="client_business_detail_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_business_detail_id') is-invalid @enderror" id="client_business_detail_id" name="client_business_detail_id" required>
                                <option value="">Choose a company...</option>
                                @foreach($clientCompanies as $company)
                                    <option value="{{ $company->id }}" {{ old('client_business_detail_id', $service->client_business_detail_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->company_name ?: ($company->user?->email ?: $company->user?->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_business_detail_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id" required>
                                <option value="">Choose a vendor...</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id', $service->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="service_name" class="form-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('service_name') is-invalid @enderror"
                                   id="service_name" name="service_name" value="{{ old('service_name', $service->service_name) }}"
                                   placeholder="Enter service name" required>
                            @error('service_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="plan_type" class="form-label">Plan Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('plan_type') is-invalid @enderror" id="plan_type" name="plan_type" required>
                                <option value="">Choose a plan type...</option>
                                @foreach($planTypes as $planValue => $planLabel)
                                    <option value="{{ $planValue }}" {{ old('plan_type', $service->plan_type) == $planValue ? 'selected' : '' }}>
                                        {{ $planLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="service_details" class="form-label">Service Details</label>
                            <textarea class="form-control ckeditor @error('service_details') is-invalid @enderror"
                                      id="service_details" name="service_details"
                                      placeholder="Enter detailed description of the service..." rows="6">{{ old('service_details', $service->service_details) }}</textarea>
                            @error('service_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="remark_text" class="form-label">Remark Text</label>
                            <input type="text" class="form-control @error('remark_text') is-invalid @enderror"
                                   id="remark_text" name="remark_text" value="{{ old('remark_text', $service->remark_text) }}"
                                   placeholder="Example: IMP">
                            @error('remark_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="remark_color" class="form-label">Remark Color</label>
                            <select class="form-select @error('remark_color') is-invalid @enderror" id="remark_color" name="remark_color">
                                <option value="">Choose a color...</option>
                                <option value="yellow" {{ old('remark_color', $service->remark_color) == 'yellow' ? 'selected' : '' }}>Yellow</option>
                                <option value="red" {{ old('remark_color', $service->remark_color) == 'red' ? 'selected' : '' }}>Red</option>
                                <option value="green" {{ old('remark_color', $service->remark_color) == 'green' ? 'selected' : '' }}>Green</option>
                                <option value="blue" {{ old('remark_color', $service->remark_color) == 'blue' ? 'selected' : '' }}>Blue</option>
                                <option value="gray" {{ old('remark_color', $service->remark_color) == 'gray' ? 'selected' : '' }}>Gray</option>
                            </select>
                            @error('remark_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date" value="{{ old('start_date', $service->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date" value="{{ old('end_date', $service->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="billing_date" class="form-label">Billing Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('billing_date') is-invalid @enderror"
                                   id="billing_date" name="billing_date" value="{{ old('billing_date', $service->billing_date->format('Y-m-d')) }}" required>
                            @error('billing_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $service->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $service->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                {{-- <option value="pending" {{ old('status', $service->status) == 'pending' ? 'selected' : '' }}>Pending</option> --}}
                                {{-- <option value="expired" {{ old('status', $service->status) == 'expired' ? 'selected' : '' }}>Expired</option> --}}
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="card border shadow-none mb-0">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">AMC Settings</h6>
                                        <small class="text-muted">Completed visits stay locked. Only pending visits are recalculated.</small>
                                    </div>
                                    @if($serviceAmc)
                                        <span class="badge bg-info text-dark">AMC Exists</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($serviceAmc)
                                        <div class="alert alert-warning mb-3">
                                            <strong>Current AMC:</strong>
                                            Total {{ $serviceAmc->total_visits }} visits,
                                            {{ $amcCompletedVisits }} completed,
                                            {{ $amcPendingVisits }} pending.
                                            Turning AMC off will keep this history intact and stop future resync.
                                        </div>
                                    @endif

                                    <input type="hidden" name="is_amc" value="0">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="service_is_amc" name="is_amc" value="1" {{ $isAmcEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="service_is_amc">AMC Service</label>
                                    </div>

                                    <div id="amcFields" class="{{ $isAmcEnabled ? '' : 'd-none' }}">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="amc_total_visits" class="form-label">AMC Total Visits</label>
                                                <input type="number" min="1" class="form-control @error('amc_total_visits') is-invalid @enderror" id="amc_total_visits" name="amc_total_visits" value="{{ $amcTotalVisitsValue }}" placeholder="Example: 4">
                                                @error('amc_total_visits')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="amc_start_date" class="form-label">AMC Start Date</label>
                                                <input type="date" class="form-control @error('amc_start_date') is-invalid @enderror" id="amc_start_date" name="amc_start_date" value="{{ $amcStartDateValue }}">
                                                @error('amc_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="amc_end_date" class="form-label">AMC End Date</label>
                                                <input type="date" class="form-control @error('amc_end_date') is-invalid @enderror" id="amc_end_date" name="amc_end_date" value="{{ $amcEndDateValue }}">
                                                @error('amc_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-md-flex d-grid align-items-center gap-3">
                                <button type="submit" class="btn btn-primary px-4">Update Service</button>
                                <a href="{{ route('services.index') }}" class="btn btn-light px-4">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay toggle-icon"></div>
<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('service_details');
    if (textarea) {
        ClassicEditor
            .create(textarea, {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            })
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
                });
    }

    const amcToggle = document.getElementById('service_is_amc');
    const amcFields = document.getElementById('amcFields');

    if (amcToggle && amcFields) {
        const toggleAmcFields = () => {
            amcFields.classList.toggle('d-none', !amcToggle.checked);
        };

        amcToggle.addEventListener('change', toggleAmcFields);
        toggleAmcFields();
    }
});
</script>
@endsection
