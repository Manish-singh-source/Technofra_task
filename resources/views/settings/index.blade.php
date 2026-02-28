@extends('layout.master')

@section('content')
    @php
        $activeSettingsTab = session('active_settings_tab', 'general');
        if ($errors->any() && old('teams')) {
            $activeSettingsTab = 'teams';
        }

        $availableTabs = [];
        if (auth()->user()->hasPermissionTo('view_general_settings')) $availableTabs[] = 'general';
        if (auth()->user()->hasPermissionTo('view_company_information')) $availableTabs[] = 'company';
        if (auth()->user()->hasPermissionTo('view_email_settings')) $availableTabs[] = 'email';
        if (auth()->user()->hasPermissionTo('view_general_settings')) $availableTabs[] = 'teams';
        if (!in_array($activeSettingsTab, $availableTabs, true)) {
            $activeSettingsTab = $availableTabs[0] ?? 'general';
        }

        $teamRows = old('teams', $teams ?? []);
        if (!is_array($teamRows) || count($teamRows) === 0) {
            $teamRows = [['name' => '', 'description' => '', 'icon_path' => '']];
        }
    @endphp
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Settings</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">CRM Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                    <div class="text-white">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    <div class="text-white">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                

                <!-- RIGHT SIDE: Vertical Nav Tabs -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Settings Menu</h5>
                            <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                                @if(auth()->user()->hasPermissionTo('view_general_settings'))
                                <button class="nav-link text-start py-3 px-3 mb-2 {{ $activeSettingsTab === 'general' ? 'active' : '' }}" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="{{ $activeSettingsTab === 'general' ? 'true' : 'false' }}">
                                    <i class="bx bx-cog me-2"></i> General
                                </button>
                                @endif
                                @if(auth()->user()->hasPermissionTo('view_company_information'))
                                <button class="nav-link text-start py-3 px-3 mb-2 {{ $activeSettingsTab === 'company' ? 'active' : '' }}" id="company-tab" data-bs-toggle="pill" data-bs-target="#company" type="button" role="tab" aria-controls="company" aria-selected="{{ $activeSettingsTab === 'company' ? 'true' : 'false' }}">
                                    <i class="bx bx-building me-2"></i> Company Information
                                </button>
                                @endif
                                @if(auth()->user()->hasPermissionTo('view_email_settings'))
                                <button class="nav-link text-start py-3 px-3 mb-2 {{ $activeSettingsTab === 'email' ? 'active' : '' }}" id="email-tab" data-bs-toggle="pill" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="{{ $activeSettingsTab === 'email' ? 'true' : 'false' }}">
                                    <i class="bx bx-envelope me-2"></i> Email Settings
                                </button>
                                @endif
                                @if(auth()->user()->hasPermissionTo('view_general_settings'))
                                <button class="nav-link text-start py-3 px-3 {{ $activeSettingsTab === 'teams' ? 'active' : '' }}" id="teams-tab" data-bs-toggle="pill" data-bs-target="#teams" type="button" role="tab" aria-controls="teams" aria-selected="{{ $activeSettingsTab === 'teams' ? 'true' : 'false' }}">
                                    <i class="bx bx-group me-2"></i> Teams
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info Card -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Quick Info</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Database Settings Active
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    File Storage Configured
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- LEFT SIDE: Form Card -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="tab-content" id="settingsTabContent">
                                <!-- TAB 1: GENERAL -->
                                <div class="tab-pane fade {{ $activeSettingsTab === 'general' ? 'show active' : '' }}" id="general" role="tabpanel">
                                    <h5 class="card-title">General Settings</h5>
                                    <hr />
                                    <form action="{{ route('settings.update.general') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="company_name" class="form-label">Company Name *</label>
                                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                                    value="{{ old('company_name', $settings['company_name'] ?? '') }}" placeholder="Enter company name" required>
                                                @error('company_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="crm_logo" class="form-label">CRM Logo</label>
                                                <input type="file" class="form-control" id="crm_logo" name="crm_logo" accept="image/*">
                                                <small class="text-muted">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF, WEBP</small>
                                                @error('crm_logo')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                @if(!empty($settings['crm_logo']) && Storage::exists('public/settings/' . $settings['crm_logo']))
                                                <div class="mt-2">
                                                    <img src="{{ Storage::url('public/settings/' . $settings['crm_logo']) }}" alt="CRM Logo" style="max-height: 60px;" class="img-thumbnail">
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="favicon" class="form-label">Favicon</label>
                                                <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                                <small class="text-muted">Max size: 2MB. Max dimensions: 32x32. Formats: ICO, PNG, JPG</small>
                                                @error('favicon')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                @if(!empty($settings['favicon']) && Storage::exists('public/settings/' . $settings['favicon']))
                                                <div class="mt-2">
                                                    <img src="{{ Storage::url('public/settings/' . $settings['favicon']) }}" alt="Favicon" style="max-height: 32px;" class="img-thumbnail">
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- TAB 2: COMPANY INFORMATION -->
                                <div class="tab-pane fade {{ $activeSettingsTab === 'company' ? 'show active' : '' }}" id="company" role="tabpanel">
                                    <h5 class="card-title">Company Information</h5>
                                    <hr />
                                    <form action="{{ route('settings.update.company') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="company_name" class="form-label">Company Name *</label>
                                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                                    value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                                                @error('company_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="company_email" class="form-label">Company Email *</label>
                                                <input type="email" class="form-control" id="company_email" name="company_email" 
                                                    value="{{ old('company_email', $settings['company_email'] ?? '') }}" required>
                                                @error('company_email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="company_phone" class="form-label">Phone</label>
                                                <input type="text" class="form-control" id="company_phone" name="company_phone" 
                                                    value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                                                @error('company_phone')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="website" class="form-label">Website</label>
                                                <input type="text" class="form-control" id="website" name="website" 
                                                    value="{{ old('website', $settings['website'] ?? '') }}" placeholder="https://example.com">
                                                @error('website')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="address" class="form-label">Address</label>
                                                <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $settings['address'] ?? '') }}</textarea>
                                                @error('address')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text" class="form-control" id="city" name="city" 
                                                    value="{{ old('city', $settings['city'] ?? '') }}">
                                                @error('city')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="state" class="form-label">State</label>
                                                <input type="text" class="form-control" id="state" name="state" 
                                                    value="{{ old('state', $settings['state'] ?? '') }}">
                                                @error('state')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="zip" class="form-label">ZIP Code</label>
                                                <input type="text" class="form-control" id="zip" name="zip" 
                                                    value="{{ old('zip', $settings['zip'] ?? '') }}">
                                                @error('zip')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="country" class="form-label">Country</label>
                                                <input type="text" class="form-control" id="country" name="country" 
                                                    value="{{ old('country', $settings['country'] ?? '') }}">
                                                @error('country')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="gst_number" class="form-label">GST Number</label>
                                                <input type="text" class="form-control" id="gst_number" name="gst_number" 
                                                    value="{{ old('gst_number', $settings['gst_number'] ?? '') }}">
                                                @error('gst_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <h6 class="mb-2 mt-2">Office Work Time</h6>
                                                <small class="text-muted d-block mb-2">Working slots will use: Office Start to Lunch Start, and Lunch End to Office End. Weekly off remains Sunday.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="office_start_time" class="form-label">Office Start Time *</label>
                                                <input type="time" class="form-control" id="office_start_time" name="office_start_time"
                                                    value="{{ old('office_start_time', $settings['office_start_time'] ?? '09:00') }}" required>
                                                @error('office_start_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="office_end_time" class="form-label">Office End Time *</label>
                                                <input type="time" class="form-control" id="office_end_time" name="office_end_time"
                                                    value="{{ old('office_end_time', $settings['office_end_time'] ?? '18:00') }}" required>
                                                @error('office_end_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="lunch_start_time" class="form-label">Lunch Start Time *</label>
                                                <input type="time" class="form-control" id="lunch_start_time" name="lunch_start_time"
                                                    value="{{ old('lunch_start_time', $settings['lunch_start_time'] ?? '13:00') }}" required>
                                                @error('lunch_start_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="lunch_end_time" class="form-label">Lunch End Time *</label>
                                                <input type="time" class="form-control" id="lunch_end_time" name="lunch_end_time"
                                                    value="{{ old('lunch_end_time', $settings['lunch_end_time'] ?? '14:00') }}" required>
                                                @error('lunch_end_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary">Save Company Information</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- TAB 3: EMAIL SETTINGS -->
                                <div class="tab-pane fade {{ $activeSettingsTab === 'email' ? 'show active' : '' }}" id="email" role="tabpanel">
                                    <h5 class="card-title">Email Settings / SMTP</h5>
                                    <hr />
                                    <form action="{{ route('settings.update.email') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="mail_engine" class="form-label">Mail Engine *</label>
                                                <select class="form-select" id="mail_engine" name="mail_engine" required>
                                                    <option value="phpmailer" {{ old('mail_engine', $settings['mail_engine'] ?? '') == 'phpmailer' ? 'selected' : '' }}>PHPMailer</option>
                                                    <option value="codeigniter" {{ old('mail_engine', $settings['mail_engine'] ?? '') == 'codeigniter' ? 'selected' : '' }}>CodeIgniter</option>
                                                </select>
                                                @error('mail_engine')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="email_protocol" class="form-label">Email Protocol *</label>
                                                <select class="form-select" id="email_protocol" name="email_protocol" required>
                                                    <option value="smtp" {{ old('email_protocol', $settings['email_protocol'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                    <option value="microsoft_oauth" {{ old('email_protocol', $settings['email_protocol'] ?? '') == 'microsoft_oauth' ? 'selected' : '' }}>Microsoft OAuth</option>
                                                    <option value="gmail_oauth" {{ old('email_protocol', $settings['email_protocol'] ?? '') == 'gmail_oauth' ? 'selected' : '' }}>Gmail OAuth</option>
                                                    <option value="sendmail" {{ old('email_protocol', $settings['email_protocol'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                                    <option value="mail" {{ old('email_protocol', $settings['email_protocol'] ?? '') == 'mail' ? 'selected' : '' }}>PHP Mail</option>
                                                </select>
                                                @error('email_protocol')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="email_encryption" class="form-label">Email Encryption</label>
                                                <select class="form-select" id="email_encryption" name="email_encryption">
                                                    <option value="tls" {{ old('email_encryption', $settings['email_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                                    <option value="ssl" {{ old('email_encryption', $settings['email_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                    <option value="none" {{ old('email_encryption', $settings['email_encryption'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="email_charset" class="form-label">Email Charset</label>
                                                <input type="text" class="form-control" id="email_charset" name="email_charset" 
                                                    value="{{ old('email_charset', $settings['email_charset'] ?? 'utf-8') }}">
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="smtp_host" class="form-label">SMTP Host *</label>
                                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                    value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" placeholder="smtp.example.com">
                                                @error('smtp_host')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="smtp_port" class="form-label">SMTP Port *</label>
                                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                    value="{{ old('smtp_port', $settings['smtp_port'] ?? 587) }}" placeholder="587">
                                                @error('smtp_port')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="email" class="form-label">Email Address *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                    value="{{ old('email', $settings['email'] ?? '') }}" placeholder="your@email.com" required>
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="smtp_username" class="form-label">SMTP Username *</label>
                                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                    value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}" placeholder="your@email.com">
                                                @error('smtp_username')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="smtp_password" class="form-label">SMTP Password *</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                        value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}" placeholder="********">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                </div>
                                                @error('smtp_password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="bcc_all" class="form-label">BCC All Emails To</label>
                                                <input type="email" class="form-control" id="bcc_all" name="bcc_all" 
                                                    value="{{ old('bcc_all', $settings['bcc_all'] ?? '') }}" placeholder="bcc@example.com">
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="email_signature" class="form-label">Email Signature</label>
                                                <textarea class="form-control" id="email_signature" name="email_signature" rows="3" placeholder="Best regards,&#10;Your Company">{{ old('email_signature', $settings['email_signature'] ?? '') }}</textarea>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="predefined_header" class="form-label">Predefined Email Header</label>
                                                <textarea class="form-control" id="predefined_header" name="predefined_header" rows="3" placeholder="<h1>Welcome</h1>">{{ old('predefined_header', $settings['predefined_header'] ?? '') }}</textarea>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="predefined_footer" class="form-label">Predefined Email Footer</label>
                                                <textarea class="form-control" id="predefined_footer" name="predefined_footer" rows="3" placeholder="<p>Thank you for your business!</p>">{{ old('predefined_footer', $settings['predefined_footer'] ?? '') }}</textarea>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="d-grid gap-2 d-md-flex">
                                                    <button type="submit" class="btn btn-primary">Save Email Settings</button>
                                                </div>
                                            </div>
                                            
                                            <!-- Test Email Section -->
                                            <div class="col-12 mt-4">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6>Send Test Email</h6>
                                                        <form action="{{ route('settings.test.email') }}" method="POST" class="d-flex gap-2">
                                                            @csrf
                                                            <input type="email" class="form-control" name="test_email" placeholder="Enter email address" required>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="bx bx-paper-plane"></i> Send Test
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- TAB 4: TEAMS -->
                                <div class="tab-pane fade {{ $activeSettingsTab === 'teams' ? 'show active' : '' }}" id="teams" role="tabpanel">
                                    <h5 class="card-title">Team Settings</h5>
                                    <hr />
                                    <form action="{{ route('settings.update.teams') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Teams</label>
                                                <div id="teams-rows" class="d-flex flex-column gap-2">
                                                    @foreach($teamRows as $index => $teamRow)
                                                        <div class="team-row border rounded p-3">
                                                            <div class="row g-2 align-items-end">
                                                                <div class="col-md-4">
                                                                    <label class="form-label mb-1">Team Name</label>
                                                                    <input type="text" class="form-control" name="teams[{{ $index }}][name]" value="{{ $teamRow['name'] ?? '' }}" placeholder="Enter team name" required>
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <label class="form-label mb-1">Description</label>
                                                                    <input type="text" class="form-control" name="teams[{{ $index }}][description]" value="{{ $teamRow['description'] ?? '' }}" placeholder="Enter team description">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label mb-1">Icon</label>
                                                                    <input type="file" class="form-control" name="teams[{{ $index }}][icon]" accept="image/*">
                                                                    <input type="hidden" name="teams[{{ $index }}][existing_icon_path]" value="{{ $teamRow['icon_path'] ?? '' }}">
                                                                </div>
                                                                <div class="col-md-1 d-grid">
                                                                    <button type="button" class="btn btn-outline-danger remove-team-row">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            @if(!empty($teamRow['icon_path']))
                                                                <div class="mt-2">
                                                                    <img src="{{ Storage::url($teamRow['icon_path']) }}" alt="Team Icon" style="height: 34px; width: 34px; object-fit: cover;" class="rounded border">
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('teams')
                                                    <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                @error('teams.*.name')
                                                    <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                @error('teams.*.icon')
                                                    <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted d-block mt-2">These teams will appear in staff forms and client issue assignment popup.</small>
                                            </div>

                                            <div class="col-12 d-flex gap-2">
                                                <button type="button" class="btn btn-light border" id="add-team-row">
                                                    <i class="bx bx-plus"></i> Add Team
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    Save Teams
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->

    <!-- Add Tag Modal -->
    <div class="modal fade" id="addTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addTagForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tagName" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" id="tagName" name="name" required>
                            <div class="invalid-feedback" id="tagNameError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="tagColor" class="form-label">Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="tagColor" name="color" value="#3498db">
                                <input type="text" class="form-control ms-2" id="tagColorText" value="#3498db" style="max-width: 100px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tagDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="tagDescription" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveTagBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Save Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tag Modal -->
    <div class="modal fade" id="editTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTagForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTagId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTagName" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" id="editTagName" name="name" required>
                            <div class="invalid-feedback" id="editTagNameError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editTagColor" class="form-label">Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="editTagColor" name="color" value="#3498db">
                                <input type="text" class="form-control ms-2" id="editTagColorText" value="#3498db" style="max-width: 100px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editTagDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editTagDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editTagActive" name="is_active" value="1">
                                <label class="form-check-label" for="editTagActive">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="updateTagBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Update Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Tag Modal -->
    <div class="modal fade" id="deleteTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the tag "<strong id="deleteTagName"></strong>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteTagForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" id="deleteTagBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        const input = $(this).siblings('input');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bx-show').addClass('bx-hide');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bx-hide').addClass('bx-show');
        }
    });

    // Color picker sync
    $('#tagColor').on('input', function() {
        $('#tagColorText').val($(this).val());
    });
    $('#tagColorText').on('input', function() {
        $('#tagColor').val($(this).val());
    });
    $('#editTagColor').on('input', function() {
        $('#editTagColorText').val($(this).val());
    });
    $('#editTagColorText').on('input', function() {
        $('#editTagColor').val($(this).val());
    });

    $('#add-team-row').on('click', function() {
        const nextIndex = Date.now();
        $('#teams-rows').append(`
            <div class="team-row border rounded p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Team Name</label>
                        <input type="text" class="form-control" name="teams[${nextIndex}][name]" placeholder="Enter team name" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label mb-1">Description</label>
                        <input type="text" class="form-control" name="teams[${nextIndex}][description]" placeholder="Enter team description">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Icon</label>
                        <input type="file" class="form-control" name="teams[${nextIndex}][icon]" accept="image/*">
                        <input type="hidden" name="teams[${nextIndex}][existing_icon_path]" value="">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger remove-team-row">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
    });

    $(document).on('click', '.remove-team-row', function() {
        const rows = $('#teams-rows .team-row');
        if (rows.length <= 1) {
            rows.find('input[type=\"text\"]').val('');
            rows.find('input[type=\"file\"]').val('');
            rows.find('input[type=\"hidden\"]').val('');
            return;
        }
        $(this).closest('.team-row').remove();
    });

    // Add Tag Form Submit
    $('#addTagForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#saveTagBtn');
        const spinner = btn.find('.spinner-border');
        
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('tags.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addTagModal').modal('hide');
                    $('#addTagForm')[0].reset();
                    $('#tagColor').val('#3498db');
                    $('#tagColorText').val('#3498db');
                    
                    // Reload page or update table
                    location.reload();
                } else {
                    alert(response.message || 'Failed to create tag');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('#tagName').addClass('is-invalid');
                    $('#tagNameError').text(errors.name ? errors.name[0] : '');
                } else {
                    alert('Failed to create tag: ' + xhr.responseJSON.message);
                }
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Edit Tag - Open Modal
    $('.edit-tag').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const color = $(this).data('color');
        const description = $(this).data('description');
        const isActive = $(this).closest('tr').find('.badge.bg-success').length > 0;

        $('#editTagId').val(id);
        $('#editTagName').val(name);
        $('#editTagColor').val(color);
        $('#editTagColorText').val(color);
        $('#editTagDescription').val(description || '');
        $('#editTagActive').prop('checked', isActive);

        $('#editTagModal').modal('show');
    });

    // Edit Tag Form Submit
    $('#editTagForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#updateTagBtn');
        const spinner = btn.find('.spinner-border');
        const id = $('#editTagId').val();
        
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: '/tags/' + id,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editTagModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update tag');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('#editTagName').addClass('is-invalid');
                    $('#editTagNameError').text(errors.name ? errors.name[0] : '');
                } else {
                    alert('Failed to update tag: ' + xhr.responseJSON.message);
                }
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Delete Tag - Open Modal
    $('.delete-tag').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#deleteTagName').text(name);
        $('#deleteTagForm').attr('action', '/tags/' + id);
        
        $('#deleteTagModal').modal('show');
    });

    // Delete Tag Form Submit
    $('#deleteTagForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#deleteTagBtn');
        const spinner = btn.find('.spinner-border');
        
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'DELETE',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#deleteTagModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to delete tag');
                }
            },
            error: function(xhr) {
                alert('Failed to delete tag: ' + xhr.responseJSON.message);
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Tag Search
    $('#tagSearch').on('keyup', function() {
        const query = $(this).val().toLowerCase();
        
        $('#tagsTable tbody tr').each(function() {
            const name = $(this).find('td:first').text().toLowerCase();
            const slug = $(this).find('code').text().toLowerCase();
            
            if (name.includes(query) || slug.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Clear form on modal close
    $('#addTagModal').on('hidden.bs.modal', function() {
        $('#addTagForm')[0].reset();
        $('#tagName').removeClass('is-invalid');
        $('#tagColor').val('#3498db');
        $('#tagColorText').val('#3498db');
    });

    // Clear edit form errors on modal close
    $('#editTagModal').on('hidden.bs.modal', function() {
        $('#editTagName').removeClass('is-invalid');
    });
});
</script>
@endpush
