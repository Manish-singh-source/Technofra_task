@extends('/layout/master')
@section('content')
@php($editing = $contact->exists)
<div class="page-wrapper"><div class="page-content"><div class="d-flex justify-content-between mb-3"><h4>{{ $editing ? 'Edit vCard' : 'Create New vCard' }}</h4><a class="btn btn-light" href="{{ route('vcards.index') }}">Cancel</a></div>
<form class="card" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('vcards.update',$contact->id) : route('vcards.store') }}">@csrf @if($editing) @method('PUT') @endif <div class="card-body"><div class="row g-3">
@php($groups = ['Personal Information'=>['first_name'=>'First Name','middle_name'=>'Middle Name','last_name'=>'Last Name','designation'=>'Job Title / Designation','department'=>'Department'], 'Company Information'=>['company_name'=>'Company Name','company_website'=>'Company Website'], 'Contact Information'=>['mobile_number'=>'Mobile Number','alternate_mobile'=>'Alternative Mobile Number','office_telephone'=>'Office Telephone','home_telephone'=>'Home Telephone','fax_number'=>'Fax Number','primary_email'=>'Primary Email','secondary_email'=>'Secondary Email','additional_email'=>'Additional Email','whatsapp_number'=>'WhatsApp Number'], 'Address Information'=>['address_line_1'=>'Address Line 1','address_line_2'=>'Address Line 2','landmark'=>'Area / Landmark','city'=>'City','state'=>'State / Province','postal_code'=>'Postal / ZIP Code','country'=>'Country'], 'Additional Information'=>['linkedin_url'=>'LinkedIn Profile URL','facebook_url'=>'Facebook URL','instagram_url'=>'Instagram URL','notes'=>'Notes / Description']])
@php($requiredFields = ['first_name', 'last_name', 'mobile_number', 'primary_email'])
@foreach($groups as $heading => $fields)
<div class="col-12"><h6 class="text-uppercase text-primary mt-2">{{ $heading }}</h6></div>
@foreach($fields as $field => $label)
<div class="col-md-6"><label class="form-label" for="{{ $field }}">{{ $label }} @if(in_array($field, $requiredFields))<span class="text-danger">*</span>@endif</label>
@if(in_array($field, ['profile_photo', 'company_logo']))
<input class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}" type="file" name="{{ $field }}" id="{{ $field }}" accept="image/png,image/jpeg,image/webp">
@elseif($field === 'notes')
<textarea class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}" name="{{ $field }}" id="{{ $field }}" rows="3">{{ old($field, $contact->$field) }}</textarea>
@else
@php($type = str_contains($field,'email') ? 'email' : (str_contains($field,'url') || $field === 'company_website' ? 'url' : (str_contains($field,'telephone') || str_contains($field,'mobile') || $field === 'fax_number' || $field === 'whatsapp_number' ? 'tel' : 'text')))
<input class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}" type="{{ $type }}" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $contact->$field) }}">
@endif
@error($field)
<div class="invalid-feedback">{{ $message }}</div>
@enderror
</div>
@endforeach
@endforeach
<div class="col-md-6"><label class="form-label">Status</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" value="1" {{ old('status',$contact->exists ? $contact->status : true) ? 'checked':'' }}><label class="form-check-label">Active</label></div></div>
</div></div><div class="card-footer d-flex gap-2"><button class="btn btn-primary" type="submit">Save</button><button class="btn btn-success" type="submit" name="generate_qr" value="1">Save & Generate QR</button><button class="btn btn-light" type="reset">Reset</button></div></form></div></div>
@endsection
