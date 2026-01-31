@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Leads</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('leads') }}">Leads</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Lead</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Settings</button>
                        <button type="button"
                            class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Action</a>
                            <a class="dropdown-item" href="javascript:;">Another action</a>
                            <a class="dropdown-item" href="javascript:;">Something else here</a>
                            <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated
                                link</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title">Edit Lead</h5>
                    <hr />
                    <div class="form-body mt-4">
                        <form action="{{ route('lead.update', $lead->id) }}" method="POST" class="row">
                            @csrf
                            @method('PUT')
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="source" class="form-label">Source</label>
                                            <select class="form-select" id="source" name="source">
                                                <option value="">Select Source</option>
                                                <option value="website" {{ $lead->source == 'website' ? 'selected' : '' }}>Website</option>
                                                <option value="referral" {{ $lead->source == 'referral' ? 'selected' : '' }}>Referral</option>
                                                <option value="social_media" {{ $lead->source == 'social_media' ? 'selected' : '' }}>Social Media</option>
                                                <option value="cold_call" {{ $lead->source == 'cold_call' ? 'selected' : '' }}>Cold Call</option>
                                                <option value="email_campaign" {{ $lead->source == 'email_campaign' ? 'selected' : '' }}>Email Campaign</option>
                                                <option value="other" {{ $lead->source == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                                                <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                                <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Converted</option>
                                                <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="assigned" class="form-label">Assigned (Multi-select)</label>
                                            <select class="form-select" id="assigned" name="assigned[]" multiple data-placeholder="Select staff members">
                                                @foreach($staff as $member)
                                                    <option value="{{ $member->id }}" {{ in_array($member->id, old('assigned', $lead->assigned ?? [])) ? 'selected' : '' }}>
                                                        {{ $member->first_name }} {{ $member->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('assigned')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="tags" class="form-label">Tags (Multi-select)</label>
                                            <select class="form-select" id="tags" name="tags[]" multiple data-placeholder="Select or add tags">
                                                <option value="hot" {{ in_array('hot', old('tags', $lead->tags ?? [])) ? 'selected' : '' }}>Hot</option>
                                                <option value="warm" {{ in_array('warm', old('tags', $lead->tags ?? [])) ? 'selected' : '' }}>Warm</option>
                                                <option value="cold" {{ in_array('cold', old('tags', $lead->tags ?? [])) ? 'selected' : '' }}>Cold</option>
                                                <option value="urgent" {{ in_array('urgent', old('tags', $lead->tags ?? [])) ? 'selected' : '' }}>Urgent</option>
                                                <option value="follow-up" {{ in_array('follow-up', old('tags', $lead->tags ?? [])) ? 'selected' : '' }}>Follow-up</option>
                                                <option value="nurture" {{ in_array('nurture', old('tags', $lead->tags ?? [])) ? 'selected' : '' }}>Nurture</option>
                                            </select>
                                            @error('tags')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter name" value="{{ old('name', $lead->name) }}">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter email address" value="{{ old('email', $lead->email) }}">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="company" class="form-label">Company</label>
                                            <input type="text" class="form-control @error('company') is-invalid @enderror" id="company" name="company" placeholder="Enter company" value="{{ old('company', $lead->company) }}">
                                            @error('company')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="position" class="form-label">Position</label>
                                            <input type="text" class="form-control @error('position') is-invalid @enderror" id="position" name="position" placeholder="Enter position" value="{{ old('position', $lead->position) }}">
                                            @error('position')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Enter phone" value="{{ old('phone', $lead->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" placeholder="Enter website" value="{{ old('website', $lead->website) }}">
                                            @error('website')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Enter address" value="{{ old('address', $lead->address) }}">
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" placeholder="Enter city" value="{{ old('city', $lead->city) }}">
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" placeholder="Enter state" value="{{ old('state', $lead->state) }}">
                                            @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                                                <option value="">Select Country</option>
                                                <option value="US" {{ $lead->country == 'US' ? 'selected' : '' }}>United States</option>
                                                <option value="UK" {{ $lead->country == 'UK' ? 'selected' : '' }}>United Kingdom</option>
                                                <option value="IN" {{ $lead->country == 'IN' ? 'selected' : '' }}>India</option>
                                                <option value="CA" {{ $lead->country == 'CA' ? 'selected' : '' }}>Canada</option>
                                                <option value="AU" {{ $lead->country == 'AU' ? 'selected' : '' }}>Australia</option>
                                                <option value="DE" {{ $lead->country == 'DE' ? 'selected' : '' }}>Germany</option>
                                                <option value="FR" {{ $lead->country == 'FR' ? 'selected' : '' }}>France</option>
                                                <option value="other" {{ $lead->country == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="zipCode" class="form-label">Zip Code</label>
                                            <input type="text" class="form-control @error('zipCode') is-invalid @enderror" id="zipCode" name="zipCode" placeholder="Enter zip code" value="{{ old('zipCode', $lead->zipCode) }}">
                                            @error('zipCode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="lead_value" class="form-label">Lead Value</label>
                                            <input type="number" class="form-control @error('lead_value') is-invalid @enderror" id="lead_value" name="lead_value" placeholder="Enter lead value" value="{{ old('lead_value', $lead->lead_value) }}">
                                            @error('lead_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter description">{{ old('description', $lead->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Update Lead</button>
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for assigned (multi-select staff)
        $('#assigned').select2({
            placeholder: "Select staff members",
            allowClear: true
        });

        // Initialize Select2 for tags (multi-select with tags enabled)
        $('#tags').select2({
            placeholder: "Select or add tags",
            tags: true,
            allowClear: true
        });
    });
</script>
@endsection
