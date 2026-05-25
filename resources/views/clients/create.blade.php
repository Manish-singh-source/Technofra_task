@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            @include('layout.errors')

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Clients</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('client') }}">Clients</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Client</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('client') }}" class="btn btn-primary">Back to Clients</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title">Add New Client</h5>
                    <p class="text-muted mb-3">Fields marked with <span class="text-danger">*</span> are mandatory.</p>
                    <hr />

                    <div class="form-body mt-4">
                        <form action="{{ route('client.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="convert_source" value="{{ old('convert_source') }}">
                            <input type="hidden" name="convert_id" value="{{ old('convert_id') }}">

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <h6>Basic Information</h6>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="profileImage" class="form-label">Profile Image</label>
                                                <input type="file" class="form-control @error('profileImage') is-invalid @enderror" id="profileImage" name="profileImage" accept="image/*">
                                                @error('profileImage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" placeholder="Enter first name" value="{{ old('first_name') }}">
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" placeholder="Enter last name" value="{{ old('last_name') }}">
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter email" value="{{ old('email') }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Phone</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Enter phone number" value="{{ old('phone') }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="password" class="form-label">Password <span class="text-danger">*</span> <small class="text-muted">(Minimum 8 characters)</small></label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter password">
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3 d-flex align-items-center">
                                                <div class="form-check mt-3">
                                                    <input class="form-check-input @error('send_invite_mail') is-invalid @enderror" type="checkbox" value="1" id="send_invite_mail" name="send_invite_mail" {{ old('send_invite_mail') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="send_invite_mail">
                                                        Send client invitation email
                                                    </label>
                                                    @error('send_invite_mail')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <h6>Address Information</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="address_line1" class="form-label">Address Line 1 </label>
                                                <input type="text" class="form-control @error('address_line1') is-invalid @enderror" id="address_line1" name="address_line1" placeholder="Enter address line 1" value="{{ old('address_line1') }}">
                                                @error('address_line1')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="address_line2" class="form-label">Address Line 2</label>
                                                <input type="text" class="form-control @error('address_line2') is-invalid @enderror" id="address_line2" name="address_line2" placeholder="Enter address line 2" value="{{ old('address_line2') }}">
                                                @error('address_line2')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="city" class="form-label">City </label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" placeholder="City" value="{{ old('city') }}">
                                                @error('city')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="state" class="form-label">State </label>
                                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" placeholder="State" value="{{ old('state') }}">
                                                @error('state')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="country" class="form-label">Country </label>
                                                <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" placeholder="Country" value="{{ old('country') }}">
                                                @error('country')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="pincode" class="form-label">Pincode </label>
                                                <input type="text" class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" placeholder="Pincode" value="{{ old('pincode') }}">
                                                @error('pincode')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Business Information</h6>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="addCompanyBtn">
                                                <i class="bx bx-plus"></i> Add More Company
                                            </button>
                                        </div>

                                        @php
                                            $companies = old('companies', [[
                                                'client_type' => old('client_type'),
                                                'company_name' => old('company_name'),
                                                'industry' => old('industry'),
                                                'website' => old('website'),
                                            ]]);
                                        @endphp

                                        <div id="companyRows">
                                            @foreach ($companies as $index => $company)
                                                <div class="company-row border rounded p-3 mb-3" data-company-row>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <strong>Company <span class="company-number">{{ $loop->iteration }}</span></strong>
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-company {{ $loop->first && count($companies) === 1 ? 'd-none' : '' }}">
                                                            <i class="bx bx-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label" for="companies_{{ $index }}_client_type">Client Type</label>
                                                            <select class="form-select @error('companies.' . $index . '.client_type') is-invalid @enderror" id="companies_{{ $index }}_client_type" name="companies[{{ $index }}][client_type]">
                                                                <option value="">Select Type</option>
                                                                @foreach (['Individual', 'Company', 'Organization'] as $type)
                                                                    <option value="{{ $type }}" {{ ($company['client_type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('companies.' . $index . '.client_type')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label" for="companies_{{ $index }}_company_name">Company Name</label>
                                                            <input type="text" class="form-control @error('companies.' . $index . '.company_name') is-invalid @enderror" id="companies_{{ $index }}_company_name" name="companies[{{ $index }}][company_name]" placeholder="Enter Company Name" value="{{ $company['company_name'] ?? '' }}">
                                                            @error('companies.' . $index . '.company_name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label" for="companies_{{ $index }}_industry">Industry</label>
                                                            <input type="text" class="form-control @error('companies.' . $index . '.industry') is-invalid @enderror" id="companies_{{ $index }}_industry" name="companies[{{ $index }}][industry]" placeholder="Enter industry" value="{{ $company['industry'] ?? '' }}">
                                                            @error('companies.' . $index . '.industry')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label" for="companies_{{ $index }}_website">Website</label>
                                                            <input type="url" class="form-control @error('companies.' . $index . '.website') is-invalid @enderror" id="companies_{{ $index }}_website" name="companies[{{ $index }}][website]" placeholder="https://example.com" value="{{ $company['website'] ?? '' }}">
                                                            @error('companies.' . $index . '.website')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Add Client</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const companyRows = document.getElementById('companyRows');
            const addCompanyBtn = document.getElementById('addCompanyBtn');

            function refreshCompanyRows() {
                companyRows.querySelectorAll('[data-company-row]').forEach((row, index) => {
                    row.querySelector('.company-number').textContent = index + 1;
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/companies\[\d+]/, `companies[${index}]`);
                    });
                    row.querySelectorAll('[id]').forEach((field) => {
                        field.id = field.id.replace(/companies_\d+_/, `companies_${index}_`);
                    });
                    row.querySelectorAll('label[for]').forEach((label) => {
                        label.htmlFor = label.htmlFor.replace(/companies_\d+_/, `companies_${index}_`);
                    });
                });

                companyRows.querySelectorAll('.remove-company').forEach((button) => {
                    button.classList.toggle('d-none', companyRows.querySelectorAll('[data-company-row]').length === 1);
                });
            }

            addCompanyBtn.addEventListener('click', function () {
                const firstRow = companyRows.querySelector('[data-company-row]');
                const newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                    input.classList.remove('is-invalid');
                });
                newRow.querySelectorAll('select').forEach((select) => {
                    select.value = '';
                    select.classList.remove('is-invalid');
                });
                newRow.querySelectorAll('.invalid-feedback').forEach((feedback) => feedback.remove());

                companyRows.appendChild(newRow);
                refreshCompanyRows();
            });

            companyRows.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-company');
                if (! removeButton) {
                    return;
                }

                removeButton.closest('[data-company-row]').remove();
                refreshCompanyRows();
            });

            refreshCompanyRows();
        });
    </script>
@endpush
