@extends('layout.master')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body">
<h5 class="mb-3">Followup History</h5>
@forelse($followups as $followup)
<div class="border rounded p-2 mb-2">
<div class="fw-semibold">{{ ucwords(str_replace('_', ' ', (string) $followup->followup_type)) }}</div>
<div class="small text-muted">{{ $followup->followup_date?->format('d M Y h:i A') }} | {{ $followup->outcome ?: '-' }}</div>
<div>{{ $followup->discussion_notes ?: '-' }}</div>
</div>
@empty <p class="text-muted">No followup records found.</p> @endforelse
{{ $followups->links() }}
</div></div></div></div>
@endsection
