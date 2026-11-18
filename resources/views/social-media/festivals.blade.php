@extends('layout.master')

@push('styles')
<style>
.festival-page .festival-hero{padding:1.4rem 1.5rem;border-radius:16px;color:#fff;background:linear-gradient(120deg,#176ee2,#5f3dc4)}
.festival-page .card{border:1px solid #e8eef7;border-radius:14px;box-shadow:0 4px 18px rgba(27,59,107,.06)}
.festival-page .table th{color:#718096;background:#f7f9fc;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase}
.festival-page .table td{vertical-align:middle;border-color:#edf1f6}
</style>
@endpush

@section('content')
<div class="page-wrapper"><div class="page-content social-media-page festival-page">
    <div class="festival-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div><div class="small text-uppercase fw-semibold opacity-75 mb-1">Planning library</div><h4 class="mb-1 text-white">Festival & Event Master</h4><p class="mb-0 opacity-75">Maintain reusable dates and client-specific campaign opportunities.</p></div>
        <a href="{{ route('social-media.dashboard') }}" class="btn btn-light"><i class="bx bx-calendar me-1"></i>Open Calendar</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card mb-4"><div class="card-body">
        <h6 class="mb-3">Add festival or campaign event</h6>
        <form class="row g-3" method="POST" action="{{ route('social-media.festivals.store') }}">@csrf
            <div class="col-md-4"><label class="form-label">Event name *</label><input class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. Diwali campaign" required></div>
            <div class="col-md-2"><label class="form-label">Date</label><input class="form-control" type="date" name="event_date" value="{{ old('event_date') }}"></div>
            <div class="col-md-2"><label class="form-label">Category</label><input class="form-control" name="category" value="{{ old('category') }}" placeholder="Festival, offer..."></div>
            <div class="col-md-2"><label class="form-label">Client</label><select class="form-select" name="client_id"><option value="">All clients</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Repeats</label><select name="recurrence_type" class="form-select"><option value="yearly">Every year</option><option value="none">One time</option></select></div>
            <div class="col-md-4"><label class="form-label">Region</label><input class="form-control" name="country_region" value="{{ old('country_region') }}" placeholder="India / Maharashtra"></div>
            <div class="col-md-4"><label class="form-label">Reference link</label><input class="form-control" type="url" name="reference_url" value="{{ old('reference_url') }}" placeholder="https://..."></div>
            <div class="col-md-4"><label class="form-label">Planning note</label><input class="form-control" name="description" value="{{ old('description') }}" placeholder="Suggested message or campaign note"></div>
            <div class="col-12 text-end"><button class="btn btn-primary"><i class="bx bx-plus me-1"></i>Add event</button></div>
        </form>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3"><div><h6 class="mb-1">Event library</h6><small class="text-muted">Global events are available to every client; client events are private to that account.</small></div><span class="badge bg-light text-dark border">{{ $events->total() }} events</span></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Event</th><th>Date</th><th>Client</th><th>Category / region</th><th>Repeats</th><th>Note</th><th class="text-end">Actions</th></tr></thead><tbody>
            @forelse($events as $event)<tr>
                <td><strong>{{ $event->name }}</strong>@if($event->reference_url)<br><a class="small" target="_blank" rel="noopener" href="{{ $event->reference_url }}">Reference <i class="bx bx-link-external"></i></a>@endif</td>
                <td>{{ optional($event->event_date)->format('d M Y') ?: 'No fixed date' }}</td><td>{{ $event->client->name ?? 'All clients' }}</td>
                <td>{{ $event->category ?: '-' }}@if($event->country_region)<br><small class="text-muted">{{ $event->country_region }}</small>@endif</td><td>{{ $event->recurrence_type === 'yearly' ? 'Yearly' : 'One time' }}</td>
                <td><small class="text-muted">{{ Str::limit($event->description ?: '-', 60) }}</small></td>
                <td class="text-end text-nowrap"><button type="button" class="btn btn-sm btn-outline-primary edit-festival" data-id="{{ $event->id }}" data-name="{{ $event->name }}" data-date="{{ optional($event->event_date)->format('Y-m-d') }}" data-category="{{ $event->category }}" data-client="{{ $event->client_id }}" data-region="{{ $event->country_region }}" data-repeat="{{ $event->recurrence_type }}" data-url="{{ $event->reference_url }}" data-description="{{ $event->description }}"><i class="bx bx-edit"></i></button><form method="POST" action="{{ route('social-media.festivals.destroy', $event) }}" class="d-inline" onsubmit="return confirm('Remove this event?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button></form></td>
            </tr>@empty<tr><td colspan="7" class="text-center text-muted py-5">No festivals or events have been added yet.</td></tr>@endforelse
        </tbody></table></div>{{ $events->links() }}
    </div></div>
</div></div>
<div class="modal fade" id="festivalModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" id="festivalEditForm">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title">Edit event</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
    <div class="col-md-6"><label class="form-label">Event name *</label><input required class="form-control" name="name" id="festival_name"></div><div class="col-md-3"><label class="form-label">Date</label><input type="date" class="form-control" name="event_date" id="festival_date"></div><div class="col-md-3"><label class="form-label">Repeats</label><select class="form-select" name="recurrence_type" id="festival_repeat"><option value="yearly">Every year</option><option value="none">One time</option></select></div>
    <div class="col-md-4"><label class="form-label">Category</label><input class="form-control" name="category" id="festival_category"></div><div class="col-md-4"><label class="form-label">Client</label><select class="form-select" name="client_id" id="festival_client"><option value="">All clients</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">Region</label><input class="form-control" name="country_region" id="festival_region"></div>
    <div class="col-md-6"><label class="form-label">Reference link</label><input type="url" class="form-control" name="reference_url" id="festival_url"></div><div class="col-md-6"><label class="form-label">Planning note</label><input class="form-control" name="description" id="festival_description"></div>
</div></div><div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save changes</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>document.querySelectorAll('.edit-festival').forEach(function(button){button.addEventListener('click',function(){var form=document.getElementById('festivalEditForm');form.action="{{ url('social-media/festivals') }}/"+this.dataset.id;['name','date','category','client','region','repeat','url','description'].forEach(function(key){document.getElementById('festival_'+key).value=button.dataset[key]||'';});new bootstrap.Modal(document.getElementById('festivalModal')).show();});});</script>
@endpush
