@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Clients</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Add Client</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <div class="btn-group">
                        <a href="{{ route('clients') }}" class="btn btn-primary">Back to Clients</a>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title">Add New Client</h5>
                    <hr />
                    <div class="form-body mt-4">
                        <form action="{{ route('store-client') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <h6>Basic Information</h6>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="client_name" class="form-label">Client Name</label>
                                            <input type="text" class="form-control" id="client_name" name="client_name"
                                                placeholder="Enter client name" value="{{ old('client_name') }}">
                                            @error('client_name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="contact_person" class="form-label">Contact Person</label>
                                            <input type="text" class="form-control" id="contact_person" name="contact_person"
                                                placeholder="Enter contact person name" value="{{ old('contact_person') }}">
                                            @error('contact_person')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                placeholder="Enter email" value="{{ old('email') }}">
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                placeholder="Enter phone number" value="{{ old('phone') }}">
                                            @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="website" class="form-label">Website</label>
                                        <input type="url" class="form-control" id="website" name="website"
                                            placeholder="https://example.com" value="{{ old('website') }}">
                                        @error('website')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <h6>Address Information</h6>
                                    <div class="mb-3">
                                        <label for="address_line1" class="form-label">Address Line 1</label>
                                        <input type="text" class="form-control" id="address_line1" name="address_line1"
                                            placeholder="Enter address line 1" value="{{ old('address_line1') }}">
                                        @error('address_line1')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="address_line2" class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control" id="address_line2" name="address_line2"
                                            placeholder="Enter address line 2 (optional)" value="{{ old('address_line2') }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-3 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                placeholder="City" value="{{ old('city') }}">
                                            @error('city')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-3 mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control" id="state" name="state"
                                                placeholder="State" value="{{ old('state') }}">
                                            @error('state')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-3 mb-3">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" id="postal_code" name="postal_code"
                                                placeholder="Postal Code" value="{{ old('postal_code') }}">
                                            @error('postal_code')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-3 mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="country" name="country"
                                                placeholder="Country" value="{{ old('country') }}">
                                            @error('country')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <h6>Business Information</h6>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="client_type" class="form-label">Client Type</label>
                                            <select class="form-select" id="client_type" name="client_type">
                                                <option value="">Select Type</option>
                                                <option value="Individual" {{ old('client_type') == 'Individual' ? 'selected' : '' }}>Individual</option>
                                                <option value="Company" {{ old('client_type') == 'Company' ? 'selected' : '' }}>Company</option>
                                                <option value="Organization" {{ old('client_type') == 'Organization' ? 'selected' : '' }}>Organization</option>
                                            </select>
                                            @error('client_type')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="industry" class="form-label">Industry</label>
                                            <input type="text" class="form-control" id="industry" name="industry"
                                                placeholder="Enter industry" value="{{ old('industry') }}">
                                            @error('industry')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                            </select>
                                            @error('status')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="priority_level" class="form-label">Priority Level</label>
                                            <select class="form-select" id="priority_level" name="priority_level">
                                                <option value="">Select Priority</option>
                                                <option value="Low" {{ old('priority_level') == 'Low' ? 'selected' : '' }}>Low</option>
                                                <option value="Medium" {{ old('priority_level') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                                <option value="High" {{ old('priority_level') == 'High' ? 'selected' : '' }}>High</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="billing_type" class="form-label">Billing Type</label>
                                            <select class="form-select" id="billing_type" name="billing_type">
                                                <option value="">Select Billing Type</option>
                                                <option value="Hourly" {{ old('billing_type') == 'Hourly' ? 'selected' : '' }}>Hourly</option>
                                                <option value="Fixed" {{ old('billing_type') == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                                <option value="Retainer" {{ old('billing_type') == 'Retainer' ? 'selected' : '' }}>Retainer</option>
                                            </select>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="default_due_days" class="form-label">Default Due Days</label>
                                            <input type="number" class="form-control" id="default_due_days" name="default_due_days"
                                                placeholder="Due days" value="{{ old('default_due_days') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <h6>Login Information (Optional)</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Enter password (minimum 6 characters)">
                                            @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sendWelcomeEmail" id="sendWelcomeEmail">
                                                <label class="form-check-label" for="sendWelcomeEmail">
                                                    Send Welcome Email
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary">Add Client</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end row-->
                        </form>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!--end page wrapper -->
@endsection
