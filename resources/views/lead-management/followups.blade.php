@extends('layout.master')
@section('content')
<div class="page-wrapper"><div class="page-content">
@include('lead-management.partials.feedback')
<a class="d-inline-block mb-3 text-decoration-none" href="{{ route('lead-management.show', ['source'=>$normalized['source_type'], 'id'=>$normalized['source_id']]) }}">&larr; Back to lead journey</a>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
<h5 class="mb-1">Conversation history</h5>
<p class="text-muted small mb-4">{{ $leadModel->name ?: 'Lead' }} — conversations, outcomes, remarks and the next step recorded at that time.</p>
@include('lead-management.partials.conversations', ['conversations'=>$followups])
{{ $followups->links() }}
</div></div></div></div>
@endsection
