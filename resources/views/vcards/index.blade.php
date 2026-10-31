@extends('/layout/master')
@section('content')
<div class="page-wrapper"><div class="page-content">
  <div class="mb-4"><h4 class="mb-1">QR Generator</h4><p class="text-muted mb-0">Generate a QR code from a URL or manage your vCard QR codes.</p></div>
  <ul class="nav nav-tabs mb-4" id="qrGeneratorTabs" role="tablist">
    <li class="nav-item" role="presentation"><button class="nav-link active" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-generator" type="button" role="tab">URL Generate</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" id="vcard-tab" data-bs-toggle="tab" data-bs-target="#vcard-generator" type="button" role="tab">VCard Generate</button></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="url-generator" role="tabpanel">
      <div class="card"><div class="card-body">
        <h5 class="card-title">Generate URL QR Code</h5><p class="text-muted">Paste a valid website URL and generate its QR code.</p>
        <form method="POST" action="{{ route('vcards.url-qr') }}" class="row g-3 align-items-end">@csrf
          <div class="col-md-9"><label class="form-label" for="url">Website URL</label><input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $urlQr['url'] ?? '') }}" placeholder="https://example.com" required>@error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
          <div class="col-md-3"><button class="btn btn-primary w-100" type="submit"><i class="bx bx-qr"></i> Generate QR</button></div>
        </form>
        @if(!empty($urlQr))<div class="text-center border-top mt-4 pt-4"><img class="img-fluid border rounded p-2 bg-white" style="max-width:330px" src="data:image/png;base64,{{ $urlQr['png'] }}" alt="QR code for {{ $urlQr['url'] }}"><p class="mt-3 mb-2 text-break">{{ $urlQr['url'] }}</p><a class="btn btn-outline-primary" download="url-qr-code.png" href="data:image/png;base64,{{ $urlQr['png'] }}">Download PNG</a></div>@endif
      </div></div>
    </div>
    <div class="tab-pane fade" id="vcard-generator" role="tabpanel">
      <div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">All vCards <small class="text-muted fs-6">({{ $contacts->total() }})</small></h4><a class="btn btn-primary" href="{{ route('vcards.create') }}">Create New vCard</a></div>
      <form class="card card-body mb-3"><input type="hidden" name="tab" value="vcard"><div class="row g-2"><div class="col-md-7"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, company, mobile or email"></div><div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option><option value="1" @selected(request('status')==='1')>Active</option><option value="0" @selected(request('status')==='0')>Inactive</option></select></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Search</button></div></div></form>
      <div class="card"><div class="card-body table-responsive"><table class="table align-middle"><thead><tr><th>Contact</th><th>Company</th><th>Mobile</th><th>Email</th><th>QR</th><th>Status</th><th>Actions</th></tr></thead><tbody>@forelse($contacts as $contact)<tr><td>{{ $contact->full_name }}</td><td>{{ $contact->company_name ?: '—' }}</td><td>{{ $contact->mobile_number }}</td><td>{{ $contact->primary_email }}</td><td>@if($contact->qrCode)<a href="{{ route('vcards.show',$contact->id) }}">View QR</a>@else <form action="{{ route('vcards.regenerate',$contact->id) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-primary">Generate</button></form>@endif</td><td><span class="badge bg-{{ $contact->status?'success':'secondary' }}">{{ $contact->status?'Active':'Inactive' }}</span></td><td class="text-nowrap"><a class="btn btn-sm btn-light" href="{{ route('vcards.show',$contact->id) }}">View</a><a class="btn btn-sm btn-light" href="{{ route('vcards.edit',$contact->id) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('vcards.destroy',$contact->id) }}" onsubmit="return confirm('Delete this contact?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-5">No contact records found.</td></tr>@endforelse</tbody></table>{{ $contacts->links() }}</div></div>
    </div>
  </div>
</div></div>
@if(request('tab') === 'vcard')<script>document.addEventListener('DOMContentLoaded',function(){var tab=document.querySelector('#vcard-tab');if(tab&&window.bootstrap){bootstrap.Tab.getOrCreateInstance(tab).show();}});</script>@endif
@endsection
