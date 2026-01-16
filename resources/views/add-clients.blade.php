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
                            <li class="breadcrumb-item"><a href="{{ route('clients') }}"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Add Client</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Add New Client</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('store-client') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <h6 class="mb-3">Basic Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_name" class="form-label">Client Name *</label>
                                <input type="text" class="form-control" id="client_name" name="client_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contact_person" class="form-label">Contact Person *</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website">
                            </div>
                        </div>

                        <!-- Address Details -->
                        <h6 class="mb-3">Address Details</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="address_line1" class="form-label">Address Line 1 *</label>
                                <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="address_line2" class="form-label">Address Line 2</label>
                                <input type="text" class="form-control" id="address_line2" name="address_line2">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State / Province *</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country *</label>
                                <input type="text" class="form-control" id="country" name="country" required>
                            </div>
                        </div>

                        <!-- Business & Status -->
                        <h6 class="mb-3">Business & Status</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_type" class="form-label">Client Type *</label>
                                <select class="form-select" id="client_type" name="client_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Company">Company</option>
                                    <option value="Organization">Organization</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="industry" class="form-label">Industry *</label>
                                <input type="text" class="form-control" id="industry" name="industry" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Suspended">Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="priority_level" class="form-label">Priority Level</label>
                                <select class="form-select" id="priority_level" name="priority_level">
                                    <option value="">Select Priority</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                        </div>

                        <!-- Task & Project Relation -->
                        <h6 class="mb-3">Task & Project Relation</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="assigned_manager_id" class="form-label">Assigned Manager</label>
                                <select class="form-select" id="assigned_manager_id" name="assigned_manager_id">
                                    <option value="">Select Manager</option>
                                    <!-- Add options dynamically if needed -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="default_due_days" class="form-label">Default Due Days</label>
                                <input type="number" class="form-control" id="default_due_days" name="default_due_days">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="billing_type" class="form-label">Billing Type</label>
                                <select class="form-select" id="billing_type" name="billing_type">
                                    <option value="">Select Billing Type</option>
                                    <option value="Hourly">Hourly</option>
                                    <option value="Fixed">Fixed</option>
                                    <option value="Retainer">Retainer</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Client</button>
                            <a href="{{ route('clients') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <!--end page wrapper -->
@endsection
