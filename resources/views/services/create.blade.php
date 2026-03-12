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
                    <h5 class="mb-4">Add Services</h5>
                    <form class="row g-3" method="POST" action="{{ route('services.store') }}" id="serviceForm">
                        @csrf

                        <div class="col-md-12">
                            <label for="client_id" class="form-label">Select Client <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                <option value="">Choose a client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ (old('client_id', $selectedClientId) == $client->id) ? 'selected' : '' }}>
                                        {{ $client->cname }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Services</h6>
                                <button type="button" class="btn btn-success btn-sm" id="addService">
                                    <i class="bx bx-plus"></i> Add Another Service
                                </button>
                            </div>

                            <div id="servicesContainer">
                                <div class="service-row border rounded p-3 mb-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                            <select class="form-select" name="services[0][vendor_id]" required>
                                                <option value="">Choose a vendor...</option>
                                                @foreach($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}" {{ (old('services.0.vendor_id', $selectedVendorId) == $vendor->id) ? 'selected' : '' }}>
                                                        {{ $vendor->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Service Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="services[0][service_name]"
                                                   value="{{ old('services.0.service_name') }}" placeholder="Enter service name" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Service Details</label>
                                            <textarea class="form-control ckeditor" name="services[0][service_details]"
                                                      id="service_details_0" placeholder="Enter detailed description of the service..." rows="4">{{ old('services.0.service_details') }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Remark Text</label>
                                            <input type="text" class="form-control remark-text" name="services[0][remark_text]"
                                                   value="{{ old('services.0.remark_text') }}" placeholder="Example: IMP">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Remark Color</label>
                                            <select class="form-select remark-color" name="services[0][remark_color]">
                                                <option value="">Choose a color...</option>
                                                <option value="yellow" {{ old('services.0.remark_color') == 'yellow' ? 'selected' : '' }}>Yellow</option>
                                                <option value="red" {{ old('services.0.remark_color') == 'red' ? 'selected' : '' }}>Red</option>
                                                <option value="green" {{ old('services.0.remark_color') == 'green' ? 'selected' : '' }}>Green</option>
                                                <option value="blue" {{ old('services.0.remark_color') == 'blue' ? 'selected' : '' }}>Blue</option>
                                                <option value="gray" {{ old('services.0.remark_color') == 'gray' ? 'selected' : '' }}>Gray</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="services[0][start_date]" value="{{ old('services.0.start_date') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="services[0][end_date]" value="{{ old('services.0.end_date') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Billing Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="services[0][billing_date]" value="{{ old('services.0.billing_date') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" name="services[0][status]" required>
                                                <option value="active" {{ old('services.0.status') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('services.0.status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="pending" {{ old('services.0.status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="expired" {{ old('services.0.status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Service Preview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0" id="servicePreviewTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Vendor</th>
                                                    <th>Service Name</th>
                                                    <th>Remark</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-md-flex d-grid align-items-center gap-3">
                                <button type="submit" class="btn btn-primary px-4">Save Services</button>
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
    let serviceIndex = document.querySelectorAll('.service-row').length;
    const editorInstances = {};
    const servicesContainer = document.getElementById('servicesContainer');
    const previewTableBody = document.querySelector('#servicePreviewTable tbody');

    const vendorOptions = `
        @foreach($vendors as $vendor)
            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
        @endforeach
    `;

    function initializeEditor(textarea) {
        if (!textarea) {
            return;
        }

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
            .then(editor => {
                editorInstances[textarea.id] = editor;
            })
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    }

    function escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getRemarkStyle(color) {
        const styles = {
            yellow: 'background-color:#fff3cd;color:#664d03;border:1px solid #ffec99;',
            red: 'background-color:#f8d7da;color:#842029;border:1px solid #f1aeb5;',
            green: 'background-color:#d1e7dd;color:#0f5132;border:1px solid #a3cfbb;',
            blue: 'background-color:#cfe2ff;color:#084298;border:1px solid #9ec5fe;',
            gray: 'background-color:#e2e3e5;color:#41464b;border:1px solid #c4c8cb;'
        };

        return styles[color] || styles.yellow;
    }

    function getRemarkBadge(text, color) {
        if (!text) {
            return '<span class="text-muted">No remark</span>';
        }

        return `<span class="badge" style="${getRemarkStyle(color)}">${escapeHtml(text)}</span>`;
    }

    function renderPreviewTable() {
        const rows = Array.from(document.querySelectorAll('.service-row'));

        previewTableBody.innerHTML = rows.map((row, index) => {
            const vendorSelect = row.querySelector('select[name*="[vendor_id]"]');
            const serviceNameInput = row.querySelector('input[name*="[service_name]"]');
            const remarkTextInput = row.querySelector('input[name*="[remark_text]"]');
            const remarkColorSelect = row.querySelector('select[name*="[remark_color]"]');
            const statusSelect = row.querySelector('select[name*="[status]"]');

            const vendorText = vendorSelect && vendorSelect.selectedIndex > 0
                ? vendorSelect.options[vendorSelect.selectedIndex].text
                : 'N/A';
            const serviceName = serviceNameInput && serviceNameInput.value.trim()
                ? escapeHtml(serviceNameInput.value.trim())
                : 'Untitled service';
            const remarkText = remarkTextInput ? remarkTextInput.value.trim() : '';
            const remarkColor = remarkColorSelect ? remarkColorSelect.value : '';
            const statusText = statusSelect && statusSelect.selectedIndex >= 0
                ? escapeHtml(statusSelect.options[statusSelect.selectedIndex].text)
                : 'N/A';

            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(vendorText)}</td>
                    <td>${serviceName}</td>
                    <td>${getRemarkBadge(remarkText, remarkColor)}</td>
                    <td>${statusText}</td>
                </tr>
            `;
        }).join('');
    }

    function attachRemoveHandler(row) {
        const removeButton = row.querySelector('.remove-service');
        if (!removeButton) {
            return;
        }

        removeButton.addEventListener('click', function() {
            const textarea = row.querySelector('.ckeditor');
            if (textarea && editorInstances[textarea.id]) {
                editorInstances[textarea.id].destroy();
                delete editorInstances[textarea.id];
            }

            row.remove();
            renderPreviewTable();
        });
    }

    initializeEditor(document.getElementById('service_details_0'));
    renderPreviewTable();

    servicesContainer.addEventListener('input', renderPreviewTable);
    servicesContainer.addEventListener('change', renderPreviewTable);

    document.getElementById('addService').addEventListener('click', function() {
        const newServiceRow = document.createElement('div');
        newServiceRow.className = 'service-row border rounded p-3 mb-3';
        newServiceRow.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select class="form-select" name="services[${serviceIndex}][vendor_id]" required>
                        <option value="">Choose a vendor...</option>
                        ${vendorOptions}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Service Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="services[${serviceIndex}][service_name]"
                           placeholder="Enter service name" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Service Details</label>
                    <textarea class="form-control ckeditor" name="services[${serviceIndex}][service_details]"
                              id="service_details_${serviceIndex}" placeholder="Enter detailed description of the service..." rows="4"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remark Text</label>
                    <input type="text" class="form-control remark-text" name="services[${serviceIndex}][remark_text]"
                           placeholder="Example: IMP">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remark Color</label>
                    <select class="form-select remark-color" name="services[${serviceIndex}][remark_color]">
                        <option value="">Choose a color...</option>
                        <option value="yellow">Yellow</option>
                        <option value="red">Red</option>
                        <option value="green">Green</option>
                        <option value="blue">Blue</option>
                        <option value="gray">Gray</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="services[${serviceIndex}][start_date]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="services[${serviceIndex}][end_date]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Billing Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="services[${serviceIndex}][billing_date]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="services[${serviceIndex}][status]" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-danger btn-sm remove-service">
                        <i class="bx bx-trash"></i> Remove Service
                    </button>
                </div>
            </div>
        `;

        servicesContainer.appendChild(newServiceRow);
        initializeEditor(newServiceRow.querySelector('.ckeditor'));
        attachRemoveHandler(newServiceRow);
        serviceIndex++;
        renderPreviewTable();
    });
});
</script>
@endsection
