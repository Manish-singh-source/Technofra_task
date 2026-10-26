@extends('/layout/master')
@section('content')
<div class="page-wrapper"><div class="page-content">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><h4>vCard QR Manager</h4><p class="text-muted mb-0">Create offline, scan-ready contact QR codes.</p></div><a class="btn btn-primary" href="{{ route('vcards.create') }}"><i class="bx bx-plus"></i> Create New vCard</a></div>
  <div class="row row-cols-1 row-cols-md-2 g-3 mb-4"><div class="col"><div class="card"><div class="card-body"><small class="text-muted">Total Contacts</small><h2>{{ $total }}</h2></div></div></div><div class="col"><div class="card"><div class="card-body"><small class="text-muted">Active Contacts</small><h2>{{ $active }}</h2></div></div></div></div>
  <div class="card"><div class="card-body"><h5>Recent vCards</h5><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Company</th><th>Created</th><th></th></tr></thead><tbody>@forelse($recent as $contact)<tr><td>{{ $contact->full_name }}</td><td>{{ $contact->company_name ?: '—' }}</td><td>{{ $contact->created_at->format('d M Y') }}</td><td><a href="{{ route('vcards.show',$contact->id) }}">View</a></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No vCards yet.</td></tr>@endforelse</tbody></table></div></div></div>
</div></div>
@endsection
