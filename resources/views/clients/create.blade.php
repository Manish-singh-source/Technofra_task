@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
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
                        <a href="{{ route('client') }}" class="btn btn-primary">Back to Clients</a>
                    </div>
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

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <h6>Basic Information</h6>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="profileImage" class="form-label">Profile Image</label>
                                                <input type="file" class="form-control" id="profileImage"
                                                    name="profileImage" accept="image/*">
                                            </div>

                                            <div class="col-6 mb-3">
                                                <label for="first_name" class="form-label">First Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="first_name" name="first_name"
                                                    placeholder="Enter first name" value="{{ old('first_name') }}">
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-6 mb-3">
                                                <label for="last_name" class="form-label">Last Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="last_name" name="last_name"
                                                    placeholder="Enter last name" value="{{ old('last_name') }}">
                                                @error('last_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label for="email" class="form-label">Email <span
                                                        class="text-danger">*</span></label>
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

                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <h6>Address Information</h6>
                                        <div class="mb-3">
                                            <label for="address_line1" class="form-label">Address Line 1 <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="address_line1"
                                                name="address_line1" placeholder="Enter address line 1"
                                                value="{{ old('address_line1') }}">
                                            @error('address_line1')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="address_line2" class="form-label">Address Line 2</label>
                                            <input type="text" class="form-control" id="address_line2"
                                                name="address_line2" placeholder="Enter address line 2 (optional)"
                                                value="{{ old('address_line2') }}">
                                        </div>
                                        <div class="row">
                                            <div class="col-3 mb-3">
                                                <label for="city" class="form-label">City <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="city" name="city"
                                                    placeholder="City" value="{{ old('city') }}">
                                                @error('city')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-3 mb-3">
                                                <label for="state" class="form-label">State <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="state" name="state"
                                                    placeholder="State" value="{{ old('state') }}">
                                                @error('state')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-3 mb-3">
                                                <label for="pincode" class="form-label">Pincode<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pincode" name="pincode"
                                                    placeholder="Postal Code" value="{{ old('pincode') }}">
                                                @error('pincode')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-3 mb-3">
                                                <label for="country" class="form-label">Country <span
                                                        class="text-danger">*</span></label>
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
                                                <label for="client_type" class="form-label">Client Type <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="client_type" name="client_type">
                                                    <option value="">Select Type</option>
                                                    <option value="Individual"
                                                        {{ old('client_type') == 'Individual' ? 'selected' : '' }}>
                                                        Individual</option>
                                                    <option value="Company"
                                                        {{ old('client_type') == 'Company' ? 'selected' : '' }}>Company
                                                    </option>
                                                    <option value="Organization"
                                                        {{ old('client_type') == 'Organization' ? 'selected' : '' }}>
                                                        Organization</option>
                                                </select>
                                                @error('client_type')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="industry" class="form-label">Industry <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="industry"
                                                    name="industry" placeholder="Enter industry"
                                                    value="{{ old('industry') }}">
                                                @error('industry')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
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
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <h6>Login Information</h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="role" class="form-label">Role</label>
                                                <select class="form-select" id="role" name="role">
                                                    <option value="">Select Role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            {{ old('role') == $role->name ? 'selected' : '' }}>
                                                            {{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label for="password" class="form-label">Password <span
                                                        class="text-danger">*</span> <small class="text-muted">(Minimum 8
                                                        characters)</small></label>
                                                <input type="password" class="form-control" id="password"
                                                    name="password" required
                                                    placeholder="Enter password (minimum 8 characters)">
                                                @error('password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="sendWelcomeEmail" id="sendWelcomeEmail" value="1"
                                                        {{ old('sendWelcomeEmail', 1) ? 'checked' : '' }}>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
