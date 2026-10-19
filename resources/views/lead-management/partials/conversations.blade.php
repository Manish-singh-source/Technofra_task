@forelse($conversations as $conversation)
<div class="conversation border rounded p-3 mb-3">
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2"><strong class="small">{{ ucwords(str_replace('_', ' ', $conversation->followup_type)) }}</strong><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $conversation->outcome ?: 'No outcome recorded')) }}</span></div>
    <p class="small mb-2" style="white-space:pre-line;overflow-wrap:anywhere">{{ $conversation->discussion_notes ?: 'No remarks recorded.' }}</p>
    <div class="small text-muted">{{ $conversation->followup_date?->format('d M Y, h:i A') }} · {{ $conversation->staff ? trim($conversation->staff->first_name . ' ' . $conversation->staff->last_name) : 'Team member' }}</div>
    @if($conversation->next_followup_date)<div class="small text-muted mt-1">Next step recorded then: {{ $conversation->next_followup_date->format('d M Y, h:i A') }}</div>@endif
    @if($conversation->lead_status_after_followup)<div class="small text-muted mt-1">Stage after conversation: {{ ucwords(str_replace('_',' ',$conversation->lead_status_after_followup)) }}</div>@endif
</div>
@empty
<div class="text-center py-4"><i class="bx bx-message-rounded-dots fs-2 text-muted"></i><p class="text-muted small mb-0 mt-2">No conversations logged yet. Record the first call, message or meeting to start the journey.</p></div>
@endforelse
