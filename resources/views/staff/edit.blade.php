@extends('layout.master')

@push('styles')
    <style>
        .crm-staff-edit .crm-card {
            border: 1px solid #e6ecf5;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(27, 59, 107, 0.05);
            overflow: hidden;
        }

        .crm-staff-edit .crm-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 1rem 1.25rem;
            background: #f8fafd;
            border-bottom: 1px solid #edf2f7;
        }

        .crm-staff-edit .crm-header-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .crm-staff-edit .crm-subbar {
            background: #f4f7fb;
            border: 1px solid #e6ecf5;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #334766;
            margin-bottom: 16px;
        }

        .crm-staff-edit .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .crm-staff-edit .form-control,
        .crm-staff-edit .form-select {
            border-color: #dce5f2;
            border-radius: 8px;
            font-size: 0.92rem;
        }

        .crm-staff-edit .form-control:focus,
        .crm-staff-edit .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
        }

        .crm-staff-edit .crm-upload-zone {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
        }

        .crm-staff-edit .crm-avatar-preview,
        .crm-staff-edit .crm-avatar-initials {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
            flex-shrink: 0;
        }

        .crm-staff-edit .crm-avatar-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e0edff;
            color: #0d6efd;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .crm-staff-edit .crm-doc-upload-card {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 14px;
            background: #f8fafc;
            height: 100%;
        }

        .crm-staff-edit .crm-doc-preview-box {
            width: 100%;
            height: 148px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 10px;
            color: #94a3b8;
        }

        .crm-staff-edit .crm-doc-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        html[data-bs-theme="dark"] .crm-staff-edit .crm-card,
        body.dark-theme .crm-staff-edit .crm-card {
            border-color: rgba(255, 255, 255, 0.1);
        }

        html[data-bs-theme="dark"] .crm-staff-edit .crm-card-header,
        body.dark-theme .crm-staff-edit .crm-card-header,
        html[data-bs-theme="dark"] .crm-staff-edit .crm-subbar,
        body.dark-theme .crm-staff-edit .crm-subbar,
        html[data-bs-theme="dark"] .crm-staff-edit .crm-upload-zone,
        body.dark-theme .crm-staff-edit .crm-upload-zone,
        html[data-bs-theme="dark"] .crm-staff-edit .crm-doc-upload-card,
        body.dark-theme .crm-staff-edit .crm-doc-upload-card {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
        }

        html[data-bs-theme="dark"] .crm-staff-edit .crm-doc-preview-box,
        body.dark-theme .crm-staff-edit .crm-doc-preview-box {
            background-color: rgba(15, 23, 42, 0.6);
            border-color: rgba(255, 255, 255, 0.12);
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content crm-staff-edit">

            @include('layout.errors')

            @php
                $staffDepartments = is_array($staff->departments)
                    ? $staff->departments
                    : (json_decode($staff->departments, true) ?: []);
                $oldDepartments = old('departments');
                $departmentsInputValue = is_array($oldDepartments)
                    ? implode(', ', $oldDepartments)
                    : ($oldDepartments ?? implode(', ', $staffDepartments));
                $currentSpatieRole = old('role', optional($staff->roles->first())->name);
                $profileImageUrl = $staff->resolved_profile_image_url;
                $staffInitials = $staff->initials;
                $fullNameValue = old('full_name', trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''))) ?: $staff->name;

                $address = $staff->address;
                $fullAddressText = $address
                    ? collect([
                        $address->address_line_1,
                        $address->address_line_2,
                        $address->city,
                        $address->state,
                        $address->country,
                        $address->pincode,
                    ])->filter()->implode(', ')
                    : '';

                $currentAddressValue = old('current_address', $staff->current_address ?: $fullAddressText);
                $permanentAddressValue = old('permanent_address', $staff->permanent_address ?? '');
                $employeeCodeValue = old('employee_code', $staff->employee_code ?: (optional($staff->salaryProfile)->employee_code ?: 'TF' . str_pad((string) $staff->id, 3, '0', STR_PAD_LEFT)));
                $designationValue = old('designation', $staff->designation ?: ($staff->team ?: ($staffDepartments[0] ?? '')));
                $employeeTypeValue = str_replace('-', ' ', old('employee_type', $staff->employee_type ?: 'Full Time'));
                $joiningDateValue = old('date_of_joining', $staff->date_of_joining ? $staff->date_of_joining->format('Y-m-d') : ($staff->created_at ? $staff->created_at->format('Y-m-d') : ''));
                $leavingDateValue = old('date_of_leaving', $staff->date_of_leaving ? $staff->date_of_leaving->format('Y-m-d') : '');
                $dobValue = old('date_of_birth', $staff->date_of_birth ? $staff->date_of_birth->format('Y-m-d') : '');
                $rawMonthlySalary = optional($staff->salaryProfile)->monthly_salary;
                $defaultSalaryValue = $rawMonthlySalary !== null
                    ? (fmod((float) $rawMonthlySalary, 1.0) == 0.0 ? (string) (int) $rawMonthlySalary : (string) (float) $rawMonthlySalary)
                    : '';
                $salaryValue = old('salary', $defaultSalaryValue);
            @endphp

            <div class="page-breadcrumb d-flex align-items-center flex-wrap gap-2 mb-3">
                <div class="breadcrumb-title pe-3">Staff</div>
                <div class="ps-3 d-none d-sm-block">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('staff') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('view-staff', $staff->id) }}">{{ $staff->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Staff</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('view-staff', $staff->id) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Profile
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('staff.update', $staff->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-12">
                        {{-- 1. Personal Details --}}
                        <div class="card crm-card mb-4" id="edit-personal">
                            <div class="crm-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="crm-header-icon bg-light-primary text-primary"><i class="bx bx-user"></i></div>
                                    <h5 class="mb-0 fw-bold">Personal Details</h5>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="crm-upload-zone mb-4">
                                    @if ($profileImageUrl)
                                        <img src="{{ $profileImageUrl }}" alt="Profile Image" class="crm-avatar-preview" id="formAvatarPreview">
                                    @else
                                        <div class="crm-avatar-initials" id="formAvatarInitials">{{ $staffInitials }}</div>
                                        <img src="" alt="Profile Image" class="crm-avatar-preview d-none" id="formAvatarPreview">
                                    @endif
                                    <div class="flex-grow-1">
                                        <label for="profileImageInput" class="form-label mb-1">Profile Image</label>
                                        <input type="file" id="profileImageInput" name="profileImage" class="form-control form-control-sm" accept="image/*">
                                        <div class="form-text mb-0">JPG, PNG, GIF, or WEBP. Max 2MB.</div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" id="full_name" name="full_name" class="form-control"
                                            value="{{ $fullNameValue }}" placeholder="Enter full name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="text" id="phone" name="phone" class="form-control"
                                            value="{{ old('phone', $staff->phone) }}" placeholder="Enter mobile number" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Personal Email ID <span class="text-danger">*</span></label>
                                        <input type="email" id="email" name="email" class="form-control"
                                            value="{{ old('email', $staff->email) }}" placeholder="Enter email" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="{{ $dobValue }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select id="gender" name="gender" class="form-select">
                                            <option value="">Select Gender</option>
                                            @foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $genderOption)
                                                <option value="{{ $genderOption }}" {{ old('gender', $staff->gender) === $genderOption ? 'selected' : '' }}>
                                                    {{ $genderOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="marital_status" class="form-label">Marital Status</label>
                                        <select id="marital_status" name="marital_status" class="form-select">
                                            <option value="">Select Marital Status</option>
                                            @foreach (['Unmarried', 'Married', 'Divorced', 'Widowed'] as $maritalOption)
                                                <option value="{{ $maritalOption }}" {{ old('marital_status', $staff->marital_status) === $maritalOption ? 'selected' : '' }}>
                                                    {{ $maritalOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="blood_group" class="form-label">Blood Group</label>
                                        <select id="blood_group" name="blood_group" class="form-select">
                                            <option value="">Select Blood Group</option>
                                            @foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bloodOption)
                                                <option value="{{ $bloodOption }}" {{ old('blood_group', $staff->blood_group) === $bloodOption ? 'selected' : '' }}>
                                                    {{ $bloodOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control"
                                            value="{{ old('emergency_contact_name', $staff->emergency_contact_name) }}" placeholder="Contact name">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emergency_contact_phone" class="form-label">Emergency Contact Mobile Number</label>
                                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control"
                                            value="{{ old('emergency_contact_phone', $staff->emergency_contact_phone) }}" placeholder="Mobile number">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emergency_contact_relationship" class="form-label">Emergency Contact Relationship</label>
                                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" class="form-control"
                                            value="{{ old('emergency_contact_relationship', $staff->emergency_contact_relationship) }}" placeholder="e.g. Father, Spouse">
                                    </div>
                                    <div class="col-12">
                                        <label for="current_address" class="form-label">Address</label>
                                        <input type="text" id="current_address" name="current_address" class="form-control"
                                            value="{{ $currentAddressValue }}" placeholder="Enter address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Employment Details --}}
                        <div class="card crm-card mb-4" id="edit-employment">
                            <div class="crm-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="crm-header-icon bg-light-success text-success"><i class="bx bx-id-card"></i></div>
                                    <h5 class="mb-0 fw-bold">Employment Details</h5>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="crm-subbar">Current Employment</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="branch" class="form-label"><span class="text-danger">*</span> Branch</label>
                                        <input type="text" id="branch" name="branch" class="form-control bg-light text-muted"
                                            value="Technofra" readonly aria-readonly="true" style="cursor: not-allowed;">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="employee_type" class="form-label">Employee Type</label>
                                        <select id="employee_type" name="employee_type" class="form-select">
                                            @foreach ($employeeTypeOptions ?? ['Full Time', 'Part Time', 'Contract', 'Intern', 'Probation', 'Freelance'] as $typeOption)
                                                <option value="{{ $typeOption }}" {{ $employeeTypeValue === $typeOption ? 'selected' : '' }}>
                                                    {{ $typeOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_of_joining" class="form-label">Date of Joining</label>
                                        <input type="date" id="date_of_joining" name="date_of_joining" class="form-control" value="{{ $joiningDateValue }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_of_leaving" class="form-label">Date of Leaving</label>
                                        <input type="date" id="date_of_leaving" name="date_of_leaving" class="form-control" value="{{ $leavingDateValue }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="employee_code" class="form-label">Employee ID</label>
                                        <input type="text" id="employee_code" name="employee_code" class="form-control"
                                            value="{{ $employeeCodeValue }}" placeholder="e.g. TF189">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="designation" class="form-label">Job Title</label>
                                        <input type="text" id="designation" name="designation" class="form-control"
                                            value="{{ $designationValue }}" placeholder="e.g. Web Developer">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="official_email" class="form-label">Official Email ID</label>
                                        <input type="email" id="official_email" name="official_email" class="form-control"
                                            value="{{ old('official_email', $staff->official_email) }}" placeholder="e.g. name@technofra.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select id="role" name="role" class="form-select" required>
                                            <option value="">Select Role</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}" {{ $currentSpatieRole == $role->name ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="departments_input" class="form-label">Department</label>
                                        <input type="text" id="departments_input" name="departments" class="form-control"
                                            list="departmentOptionsList" value="{{ $departmentsInputValue }}"
                                            placeholder="Enter department (comma-separated for multiple)">
                                        @if (!empty($departments))
                                            <datalist id="departmentOptionsList">
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department }}"></option>
                                                @endforeach
                                            </datalist>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label for="salary" class="form-label">Salary</label>
                                        <input type="number" step="0.01" min="0" id="salary" name="salary" class="form-control"
                                            value="{{ $salaryValue }}" placeholder="e.g. 15000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php
                            $docUploadSlots = [
                                [
                                    'name' => 'aadhar_front_image',
                                    'label' => 'Aadhaar Front Image',
                                    'previewId' => 'aadharFrontPreview',
                                    'placeholderId' => 'aadharFrontPlaceholder',
                                    'url' => $staff->aadhar_front_url,
                                ],
                                [
                                    'name' => 'aadhar_back_image',
                                    'label' => 'Aadhaar Back Image',
                                    'previewId' => 'aadharBackPreview',
                                    'placeholderId' => 'aadharBackPlaceholder',
                                    'url' => $staff->aadhar_back_url,
                                ],
                                [
                                    'name' => 'pan_front_image',
                                    'label' => 'PAN Front Image',
                                    'previewId' => 'panFrontPreview',
                                    'placeholderId' => 'panFrontPlaceholder',
                                    'url' => $staff->pan_front_url,
                                ],
                            ];
                            $bankNameValue = old('bank_name', $staff->bank_name ?: optional($staff->salaryProfile)->bank_name);
                        @endphp

                        {{-- 3. Documentation --}}
                        <div class="card crm-card mb-4" id="edit-documentation">
                            <div class="crm-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="crm-header-icon bg-light-info text-info"><i class="bx bx-file"></i></div>
                                    <h5 class="mb-0 fw-bold">Documentation</h5>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="aadhar_number" class="form-label">Aadhaar Number</label>
                                        <input type="text" id="aadhar_number" name="aadhar_number" class="form-control"
                                            value="{{ old('aadhar_number', $staff->aadhar_number) }}" placeholder="e.g. 1234 5678 9012" maxlength="32">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="pan_number" class="form-label">PAN Number</label>
                                        <input type="text" id="pan_number" name="pan_number" class="form-control text-uppercase"
                                            value="{{ old('pan_number', $staff->pan_number) }}" placeholder="e.g. ABCDE1234F" maxlength="32">
                                    </div>
                                </div>

                                <div class="row g-3">
                                    @foreach ($docUploadSlots as $slot)
                                        <div class="col-md-4">
                                            <div class="crm-doc-upload-card">
                                                <label for="{{ $slot['name'] }}" class="form-label fw-semibold">{{ $slot['label'] }}</label>
                                                <div class="crm-doc-preview-box">
                                                    @if ($slot['url'])
                                                        <img src="{{ $slot['url'] }}" alt="{{ $slot['label'] }}" id="{{ $slot['previewId'] }}">
                                                        <div class="text-center d-none" id="{{ $slot['placeholderId'] }}">
                                                            <i class="bx bx-image-add fs-2 d-block mb-1"></i>
                                                            <span class="small">No image uploaded</span>
                                                        </div>
                                                    @else
                                                        <img src="" alt="{{ $slot['label'] }}" class="d-none" id="{{ $slot['previewId'] }}">
                                                        <div class="text-center" id="{{ $slot['placeholderId'] }}">
                                                            <i class="bx bx-image-add fs-2 d-block mb-1"></i>
                                                            <span class="small">No image uploaded</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <input type="file" id="{{ $slot['name'] }}" name="{{ $slot['name'] }}"
                                                    class="form-control form-control-sm" accept="image/*"
                                                    data-doc-preview-target="{{ $slot['previewId'] }}"
                                                    data-doc-placeholder-target="{{ $slot['placeholderId'] }}">
                                                <div class="form-text mb-0">JPG, PNG, GIF, or WEBP. Max 4MB.</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- 4. Account Details --}}
                        <div class="card crm-card mb-4" id="edit-account">
                            <div class="crm-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="crm-header-icon bg-light-warning text-warning"><i class="bx bx-credit-card"></i></div>
                                    <h5 class="mb-0 fw-bold">Account Details</h5>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="text" id="account_number" name="account_number" class="form-control"
                                            value="{{ old('account_number', $staff->account_number) }}" placeholder="Enter bank account number" maxlength="64">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" id="bank_name" name="bank_name" class="form-control"
                                            value="{{ $bankNameValue }}" placeholder="Enter bank name" maxlength="150">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ifsc_code" class="form-label">IFSC Code</label>
                                        <input type="text" id="ifsc_code" name="ifsc_code" class="form-control text-uppercase"
                                            value="{{ old('ifsc_code', $staff->ifsc_code) }}" placeholder="e.g. HDFC0001234" maxlength="32">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card crm-card">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 px-4">
                                <span class="text-muted small">
                                    <i class="bx bx-time-five me-1"></i>
                                    Last updated: {{ $staff->updated_at ? $staff->updated_at->format('d M Y, h:i A') : 'N/A' }}
                                </span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('view-staff', $staff->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileImageInput = document.getElementById('profileImageInput');
            if (profileImageInput) {
                profileImageInput.addEventListener('change', function(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file || !file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const src = e.target && e.target.result;
                        if (!src) return;
                        ['formAvatarPreview'].forEach(function(id) {
                            const img = document.getElementById(id);
                            if (img) {
                                img.src = src;
                                img.classList.remove('d-none');
                            }
                        });
                        ['formAvatarInitials'].forEach(function(id) {
                            const initEl = document.getElementById(id);
                            if (initEl) initEl.classList.add('d-none');
                        });
                    };
                    reader.readAsDataURL(file);
                });
            }

            document.querySelectorAll('[data-doc-preview-target]').forEach(function(input) {
                input.addEventListener('change', function(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file || !file.type.startsWith('image/')) return;
                    const previewEl = document.getElementById(input.dataset.docPreviewTarget);
                    const placeholderEl = document.getElementById(input.dataset.docPlaceholderTarget);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const src = e.target && e.target.result;
                        if (!src || !previewEl) return;
                        previewEl.src = src;
                        previewEl.classList.remove('d-none');
                        if (placeholderEl) placeholderEl.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
@endsection
