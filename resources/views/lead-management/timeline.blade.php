@extends('layout.master')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body">
<h5 class="mb-3">Lead Timeline</h5>
@forelse($activities as $activity)
<div class="border-start ps-3 mb-3">
<div class="small text-muted">{{ $activity->created_at?->format('d M Y h:i A') }}</div>
<div><strong>{{ ucwords(str_replace('_', ' ', $activity->activity_type)) }}</strong></div>
<div>{{ $activity->description }}</div>
</div>
@empty <p class="text-muted">No timeline entries found.</p> @endforelse
{{ $activities->links() }}
</div></div></div></div>
@endsection
