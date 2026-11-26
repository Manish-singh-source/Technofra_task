@extends('layout.master')

@push('styles')
    <style>
        .crm-user-profile .breadcrumb {
            --bs-breadcrumb-divider: "›";
            font-size: 0.88rem;
        }

        .crm-user-profile .breadcrumb-item + .breadcrumb-item::before {
            font-family: inherit;
            content: "\203A";
            color: #94a3b8;
            font-weight: 500;
            padding-right: 0.5rem;
        }

        .crm-user-profile .crm-action-btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.52rem 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .crm-user-profile .crm-hero-banner {
            position: relative;
            border-radius: 16px;
            background: linear-gradient(125deg, #071936 0%, #0b2d62 55%, #0b4a8c 100%);
            padding: 24px 28px;
            color: #ffffff;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(7, 25, 54, 0.16);
        }

        .crm-user-profile .crm-hero-waves {
            position: absolute;
            right: -10px;
            top: -10px;
            bottom: -10px;
            width: 340px;
            pointer-events: none;
            opacity: 0.48;
        }

        .crm-user-profile .crm-hero-avatar,
        .crm-user-profile .crm-hero-initials {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.85);
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.22);
        }

        .crm-user-profile .crm-hero-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: 0.03em;
        }

        .crm-user-profile .crm-hero-code-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            color: #e2e8f0;
            font-size: 0.78rem;
            font-weight: 600;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .crm-user-profile .crm-hero-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .crm-user-profile .crm-hero-status-pill.is-active {
            background: #bbf7d0;
            color: #166534;
        }

        .crm-user-profile .crm-hero-status-pill.is-inactive {
            background: #fecaca;
            color: #991b1b;
        }

        .crm-user-profile .crm-section-card {
            border: 1px solid #e6ecf5;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .crm-user-profile .crm-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 22px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .crm-user-profile .crm-section-head-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .crm-user-profile .crm-section-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .crm-user-profile .crm-badge-editable {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .crm-user-profile .crm-badge-readonly {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
        }

        .crm-user-profile .crm-upload-zone {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
        }

        .crm-user-profile .crm-form-avatar,
        .crm-user-profile .crm-form-initials {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
            flex-shrink: 0;
        }

        .crm-user-profile .crm-form-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .crm-user-profile .crm-kv-list {
            padding: 6px 22px 16px;
        }

        .crm-user-profile .crm-kv-row {
            display: grid;
            grid-template-columns: 185px minmax(0, 1fr);
            align-items: center;
            gap: 14px;
            padding: 11px 0;
            border-bottom: 1px solid #f3f6fa;
        }

        .crm-user-profile .crm-kv-row:last-child {
            border-bottom: 0;
        }

        @media (max-width: 575.98px) {
            .crm-user-profile .crm-kv-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }

        .crm-user-profile .crm-kv-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #64748b;
            font-size: 0.87rem;
            font-weight: 500;
        }

        .crm-user-profile .crm-kv-label i {
            font-size: 1rem;
            color: #94a3b8;
            width: 16px;
            text-align: center;
        }

        .crm-user-profile .crm-kv-value {
            color: #0f172a;
            font-size: 0.9rem;
            font-weight: 600;
            word-break: break-word;
        }

        .crm-user-profile .crm-kv-value.is-empty {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }

        .crm-user-profile .crm-doc-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 14px 22px 20px;
        }

        @media (max-width: 767.98px) {
            .crm-user-profile .crm-doc-grid {
                grid-template-columns: 1fr;
            }
        }

        .crm-user-profile .crm-doc-tile {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            overflow: hidden;
            text-align: left;
            padding: 0;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .crm-user-profile button.crm-doc-tile {
            cursor: pointer;
            width: 100%;
        }

        .crm-user-profile button.crm-doc-tile:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .crm-user-profile .crm-doc-thumb {
            position: relative;
            height: 112px;
            background: #eef2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .crm-user-profile .crm-doc-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .crm-user-profile .crm-doc-caption {
            padding: 9px 11px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            background: #ffffff;
            border-top: 1px solid #eef2f7;
        }

        html[data-bs-theme="dark"] .crm-user-profile .crm-section-card,
        body.dark-theme .crm-user-profile .crm-section-card {
            background-color: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        html[data-bs-theme="dark"] .crm-user-profile .crm-section-head,
        body.dark-theme .crm-user-profile .crm-section-head,
        html[data-bs-theme="dark"] .crm-user-profile .crm-kv-row,
        body.dark-theme .crm-user-profile .crm-kv-row {
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-bs-theme="dark"] .crm-user-profile .crm-kv-value,
        body.dark-theme .crm-user-profile .crm-kv-value {
            color: #f1f5f9;
        }

        html[data-bs-theme="dark"] .crm-user-profile .crm-upload-zone,
        body.dark-theme .crm-user-profile .crm-upload-zone,
        html[data-bs-theme="dark"] .crm-user-profile .crm-doc-tile,
        body.dark-theme .crm-user-profile .crm-doc-tile {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.12);
        }

        html[data-bs-theme="dark"] .crm-user-profile .crm-doc-caption,
        body.dark-theme .crm-user-profile .crm-doc-caption {
            background: #1e293b;
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.08);
        }
    </style>
@endpush

@section('content')
    @php
        $staff = $user->staff;
        $customer = $user->customer;
        $rawProfileImage = $user->getRawOriginal('profile_image');
        $profileImageUrl = $user->resolved_profile_image_url;
        if (! $profileImageUrl && $rawProfileImage) {
            $profileCandidate = 'uploads/profile/' . ltrim(str_replace('\\', '/', (string) $rawProfileImage), '/');
            if (file_exists(public_path($profileCandidate))) {
                $profileImageUrl = asset($profileCandidate);
            }
        }
        $userInitials = $user->initials;

        $currentSpatieRole = optional($user->roles->first())->name;
        $accountType = $currentSpatieRole
            ? ucwords(str_replace('_', ' ', $currentSpatieRole))
            : ($staff ? 'Staff' : ($customer ? 'Customer' : 'Admin'));
        $designationDisplay = $user->designation ?: ($accountType);
        $employeeCodeDisplay = $user->employee_code
            ?: (optional($user->salaryProfile)->employee_code ?: 'TF' . str_pad((string) $user->id, 3, '0', STR_PAD_LEFT));

        $phoneValue = old('phone', $user->phone ?: (optional($staff)->phone ?? optional($customer)->phone));
        $dobValue = old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '');

        $address = $user->address;
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
        $currentAddressValue = old('current_address', $user->current_address ?: $fullAddressText);

        $bankNameDisplay = $user->bank_name ?: optional($user->salaryProfile)->bank_name;
        $documentCards = [
            [
                'label' => 'Aadhaar Front',
                'url' => $user->aadhar_front_url,
            ],
            [
                'label' => 'Aadhaar Back',
                'url' => $user->aadhar_back_url,
            ],
            [
                'label' => 'PAN Front',
                'url' => $user->pan_front_url,
            ],
        ];
    @endphp

    <div class="page-wrapper">
        <div class="page-content crm-user-profile">

            @include('layout.errors')

            {{-- Top Breadcrumb & Download Salary Slip Action --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 bg-transparent align-items-center">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">My Profile</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary crm-action-btn" id="openDownloadSalarySlipBtn"
                        data-bs-toggle="modal" data-bs-target="#downloadSalarySlipModal">
                        <i class="bx bx-download"></i>Download Salary Slip
                    </button>
                </div>
            </div>

            {{-- Navy Hero Banner --}}
            <div class="crm-hero-banner mb-4">
                <svg class="crm-hero-waves" viewBox="0 0 360 180" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M10 170C80 115 150 135 215 78C265 34 315 22 365 4" stroke="#38BDF8" stroke-width="1.3"/>
                    <path d="M32 175C100 124 166 142 228 86C276 42 322 30 370 14" stroke="#38BDF8" stroke-width="1.1"/>
                    <path d="M54 180C120 133 182 149 242 94C288 52 330 40 374 26" stroke="#38BDF8" stroke-width="1"/>
                    <path d="M78 184C142 141 198 156 256 104C300 62 338 50 378 38" stroke="#0EA5E9" stroke-width="0.9"/>
                    <path d="M102 188C164 149 214 162 270 114C312 74 346 62 382 50" stroke="#0EA5E9" stroke-width="0.8"/>
                </svg>

                <div class="d-flex align-items-center gap-3 flex-wrap position-relative" style="z-index: 1;">
                    @if ($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" alt="{{ $user->name }}" class="crm-hero-avatar">
                    @else
                        <div class="crm-hero-initials">{{ $userInitials }}</div>
                    @endif

                    <div>
                        <h3 class="mb-1 fw-bold text-white">{{ $user->name }}</h3>
                        <div class="mb-2" style="color: rgba(255,255,255,0.82); font-size: 0.94rem;">
                            {{ $designationDisplay }}
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="crm-hero-code-pill">{{ $employeeCodeDisplay }}</span>
                            @if ($user->status === 'active')
                                <span class="crm-hero-status-pill is-active">
                                    <i class="bx bxs-circle" style="font-size: 8px;"></i>Active
                                </span>
                            @else
                                <span class="crm-hero-status-pill is-inactive">
                                    <i class="bx bxs-circle" style="font-size: 8px;"></i>Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 1: Editable Personal Details & Address Details --}}
            <form action="{{ route('user-profile.update') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Editable: Personal Details --}}
                    <div class="col-lg-7">
                        <div class="crm-section-card h-100">
                            <div class="crm-section-head">
                                <div class="crm-section-head-left">
                                    <div class="crm-section-icon"><i class="bx bx-user"></i></div>
                                    <h6 class="mb-0 fw-bold">Personal Details</h6>
                                </div>
                                <span class="crm-badge-editable">Editable</span>
                            </div>
                            <div class="p-4">
                                <div class="crm-upload-zone mb-4">
                                    @if ($profileImageUrl)
                                        <img src="{{ $profileImageUrl }}" alt="Profile Image" class="crm-form-avatar" id="userProfileAvatarPreview">
                                    @else
                                        <div class="crm-form-initials" id="userProfileAvatarInitials">{{ $userInitials }}</div>
                                        <img src="" alt="Profile Image" class="crm-form-avatar d-none" id="userProfileAvatarPreview">
                                    @endif
                                    <div class="flex-grow-1">
                                        <label for="profileImage" class="form-label mb-1 fw-semibold">Profile Image</label>
                                        <input type="file" id="profileImage" name="profileImage"
                                            class="form-control form-control-sm @error('profileImage') is-invalid @enderror"
                                            accept="image/*">
                                        <div class="form-text mb-0">JPG, PNG, GIF, or WEBP. Max 2MB.</div>
                                        @error('profileImage')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" id="name" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">
                                            Mobile @if ($staff)<span class="text-danger">*</span>@endif
                                        </label>
                                        <input type="text" id="phone" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ $phoneValue }}" placeholder="Enter mobile number"
                                            @if ($staff) required @endif>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">Personal Email <span class="text-danger">*</span></label>
                                        <input type="email" id="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
                                        <input type="date" id="date_of_birth" name="date_of_birth"
                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                            value="{{ $dobValue }}">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="gender" class="form-label fw-semibold">Gender</label>
                                        <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                            <option value="">Select Gender</option>
                                            @foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $genderOption)
                                                <option value="{{ $genderOption }}" {{ old('gender', $user->gender) === $genderOption ? 'selected' : '' }}>
                                                    {{ $genderOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="marital_status" class="form-label fw-semibold">Marital Status</label>
                                        <select id="marital_status" name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                            <option value="">Select Marital Status</option>
                                            @foreach (['Unmarried', 'Married', 'Divorced', 'Widowed'] as $maritalOption)
                                                <option value="{{ $maritalOption }}" {{ old('marital_status', $user->marital_status) === $maritalOption ? 'selected' : '' }}>
                                                    {{ $maritalOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('marital_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="blood_group" class="form-label fw-semibold">Blood Group</label>
                                        <select id="blood_group" name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                            <option value="">Select Blood Group</option>
                                            @foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bloodOption)
                                                <option value="{{ $bloodOption }}" {{ old('blood_group', $user->blood_group) === $bloodOption ? 'selected' : '' }}>
                                                    {{ $bloodOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('blood_group')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Editable: Address Details & Security --}}
                    <div class="col-lg-5 d-flex flex-column gap-4">
                        <div class="crm-section-card">
                            <div class="crm-section-head">
                                <div class="crm-section-head-left">
                                    <div class="crm-section-icon"><i class="bx bx-map"></i></div>
                                    <h6 class="mb-0 fw-bold">Address Details</h6>
                                </div>
                                <span class="crm-badge-editable">Editable</span>
                            </div>
                            <div class="p-4">
                                <label for="current_address" class="form-label fw-semibold">Address</label>
                                <textarea id="current_address" name="current_address" rows="3"
                                    class="form-control @error('current_address') is-invalid @enderror"
                                    placeholder="Enter your address">{{ $currentAddressValue }}</textarea>
                                @error('current_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="crm-section-card flex-grow-1">
                            <div class="crm-section-head">
                                <div class="crm-section-head-left">
                                    <div class="crm-section-icon"><i class="bx bx-lock-alt"></i></div>
                                    <h6 class="mb-0 fw-bold">Change Password</h6>
                                </div>
                                <span class="crm-badge-editable">Optional</span>
                            </div>
                            <div class="p-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                    <input type="password" id="current_password" name="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        autocomplete="current-password" placeholder="Required only when changing password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">New Password</label>
                                    <input type="password" id="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        autocomplete="new-password" placeholder="Min. 8 characters">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control" autocomplete="new-password" placeholder="Re-enter new password">
                                </div>
                                <div class="d-grid pt-1">
                                    <button type="submit" class="btn btn-primary crm-action-btn justify-content-center" id="saveUserProfileBtn">
                                        <i class="bx bx-save"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Row 2: Read-Only Account Details & Documentation Details --}}
            <div class="row g-4 mb-4">
                {{-- Read-Only: Account Details --}}
                <div class="col-lg-6">
                    <div class="crm-section-card h-100">
                        <div class="crm-section-head">
                            <div class="crm-section-head-left">
                                <div class="crm-section-icon"><i class="bx bx-credit-card"></i></div>
                                <h6 class="mb-0 fw-bold">Account Details</h6>
                            </div>
                            <span class="crm-badge-readonly">Read Only</span>
                        </div>
                        <div class="crm-kv-list">
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-hash"></i>Account Number</span>
                                <span class="crm-kv-value font-monospace {{ $user->account_number ? '' : 'is-empty' }}">{{ $user->account_number ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-buildings"></i>Bank Name</span>
                                <span class="crm-kv-value {{ $bankNameDisplay ? '' : 'is-empty' }}">{{ $bankNameDisplay ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-barcode"></i>IFSC Code</span>
                                <span class="crm-kv-value font-monospace {{ $user->ifsc_code ? '' : 'is-empty' }}">{{ $user->ifsc_code ?: 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Read-Only: Documentation Details --}}
                <div class="col-lg-6">
                    <div class="crm-section-card h-100">
                        <div class="crm-section-head">
                            <div class="crm-section-head-left">
                                <div class="crm-section-icon"><i class="bx bx-file"></i></div>
                                <h6 class="mb-0 fw-bold">Documentation Details</h6>
                            </div>
                            <span class="crm-badge-readonly">Read Only</span>
                        </div>
                        <div class="crm-kv-list pb-1">
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-id-card"></i>Aadhaar Number</span>
                                <span class="crm-kv-value font-monospace {{ $user->aadhar_number ? '' : 'is-empty' }}">{{ $user->aadhar_number ?: 'Not provided' }}</span>
                            </div>
                            <div class="crm-kv-row">
                                <span class="crm-kv-label"><i class="bx bx-card"></i>PAN Number</span>
                                <span class="crm-kv-value font-monospace {{ $user->pan_number ? '' : 'is-empty' }}">{{ $user->pan_number ?: 'Not provided' }}</span>
                            </div>
                        </div>

                        <div class="crm-doc-grid">
                            @foreach ($documentCards as $doc)
                                @if ($doc['url'])
                                    <button type="button" class="crm-doc-tile"
                                        data-bs-toggle="modal" data-bs-target="#documentPreviewModal"
                                        data-doc-title="{{ $doc['label'] }}"
                                        data-doc-url="{{ $doc['url'] }}">
                                        <div class="crm-doc-thumb">
                                            <img src="{{ $doc['url'] }}" alt="{{ $doc['label'] }}">
                                        </div>
                                        <div class="crm-doc-caption">{{ $doc['label'] }}</div>
                                    </button>
                                @else
                                    <div class="crm-doc-tile">
                                        <div class="crm-doc-thumb text-muted">
                                            <div class="text-center">
                                                <i class="bx bx-image fs-3 d-block mb-1 opacity-50"></i>
                                                <span class="small">Not uploaded</span>
                                            </div>
                                        </div>
                                        <div class="crm-doc-caption">{{ $doc['label'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Download Salary Slip Modal --}}
    @php
        $slipMonths = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        $selectedSlipMonth = (int) ($salarySlipDefaultMonth ?? now()->month);
        $selectedSlipYear = (int) ($salarySlipDefaultYear ?? now()->year);
        $slipYearOptions = $salarySlipYears ?? range((int) now()->year + 1, max(2020, (int) now()->year - 5));
    @endphp
    <div class="modal fade" id="downloadSalarySlipModal" tabindex="-1" aria-labelledby="downloadSalarySlipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="downloadSalarySlipModalLabel">
                        <i class="bx bx-receipt text-primary me-2"></i>Download Salary Slip
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="GET" action="{{ route('staff.salary-slip.download', $user->id) }}" id="downloadSalarySlipForm">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="salary_slip_month" class="form-label fw-semibold">Month <span class="text-danger">*</span></label>
                                <select id="salary_slip_month" name="month" class="form-select" required>
                                    @foreach ($slipMonths as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}" {{ $selectedSlipMonth === $monthNum ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="salary_slip_year" class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                                <select id="salary_slip_year" name="year" class="form-select" required>
                                    @foreach ($slipYearOptions as $yearOption)
                                        <option value="{{ $yearOption }}" {{ $selectedSlipYear === (int) $yearOption ? 'selected' : '' }}>
                                            {{ $yearOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitDownloadSalarySlipBtn">
                            <i class="bx bx-download me-1"></i>Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Document Image Preview Modal --}}
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="documentPreviewModalLabel">Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 text-center bg-dark bg-opacity-10">
                    <img src="" alt="Document" id="documentPreviewModalImage" class="img-fluid rounded-3 shadow-sm" style="max-height: 75vh; object-fit: contain;">
                </div>
                <div class="modal-footer border-top-0">
                    <a href="#" id="documentPreviewOpenLink" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-link-external me-1"></i>Open in New Tab
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileInput = document.getElementById('profileImage');
            if (profileInput) {
                profileInput.addEventListener('change', function(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file || !file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const src = e.target && e.target.result;
                        if (!src) return;
                        const img = document.getElementById('userProfileAvatarPreview');
                        const initials = document.getElementById('userProfileAvatarInitials');
                        if (img) {
                            img.src = src;
                            img.classList.remove('d-none');
                        }
                        if (initials) {
                            initials.classList.add('d-none');
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            const salarySlipForm = document.getElementById('downloadSalarySlipForm');
            const salarySlipModalEl = document.getElementById('downloadSalarySlipModal');
            if (salarySlipForm && salarySlipModalEl && window.bootstrap) {
                salarySlipForm.addEventListener('submit', function() {
                    setTimeout(function() {
                        const modalInstance = bootstrap.Modal.getInstance(salarySlipModalEl);
                        if (modalInstance) modalInstance.hide();
                    }, 150);
                });
            }

            const previewModal = document.getElementById('documentPreviewModal');
            if (previewModal) {
                const titleEl = document.getElementById('documentPreviewModalLabel');
                const imgEl = document.getElementById('documentPreviewModalImage');
                const linkEl = document.getElementById('documentPreviewOpenLink');

                previewModal.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;
                    const docTitle = trigger.getAttribute('data-doc-title') || 'Document';
                    const docUrl = trigger.getAttribute('data-doc-url') || '';
                    if (titleEl) titleEl.textContent = docTitle;
                    if (imgEl) {
                        imgEl.src = docUrl;
                        imgEl.alt = docTitle;
                    }
                    if (linkEl) linkEl.href = docUrl;
                });

                previewModal.addEventListener('hidden.bs.modal', function() {
                    if (imgEl) imgEl.src = '';
                });
            }
        });
    </script>
@endsection
