@extends('/layout/master')
@section('content')
@php
use Illuminate\Support\Facades\Storage;

$labels = is_array($task->labels_data) ? $task->labels_data : (json_decode($task->labels_data, true) ?? []);
$checklist = is_array($task->checklist_data) ? $task->checklist_data : (json_decode($task->checklist_data, true) ?? []);
$completedCount = count(array_filter($checklist, function($item) { return isset($item['completed']) && $item['completed']; }));
$totalCount = count($checklist);
$progressPercent = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;

$attachments = is_array($task->attachments) ? $task->attachments : (json_decode($task->attachments, true) ?? []);
if (count($attachments) === 0 && $task->attachment) {
    $attachments = [is_array($task->attachment) ? ($task->attachment['path'] ?? null) : $task->attachment];
}
$normalizedAttachments = [];
foreach ($attachments as $item) {
    if (is_array($item)) {
        $normalizedAttachments[] = [
            'path' => $item['path'] ?? null,
            'name' => $item['name'] ?? null,
        ];
    } else {
        $normalizedAttachments[] = [
            'path' => $item,
            'name' => null,
        ];
    }
}
@endphp

<style>
.task-view-hero {
    background: linear-gradient(135deg, #0d6efd 0%, #6ea8fe 100%);
    color: #fff;
    border-radius: 16px;
    padding: 20px 24px;
}
.task-view-hero .badge {
    font-size: 12px;
}
.task-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f6f7fb;
    color: #495057;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 12px;
}
.task-section-title {
    font-weight: 600;
    font-size: 15px;
    color: #343a40;
}
.task-label-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
.task-progress-wrap {
    height: 8px;
    background: #e9ecef;
    border-radius: 999px;
    overflow: hidden;
}
.task-progress-bar {
    height: 100%;
    background: #198754;
}
.task-attachment-box {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 12px;
    background: #f8fafc;
}
.task-attachment-box img {
    width: 100%;
    max-height: 280px;
    object-fit: cover;
    border-radius: 10px;
    cursor: pointer;
}
.task-attachment-box .pdf-preview {
    width: 100%;
    height: 280px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
}
.attachment-thumb {
    transition: transform 0.2s ease;
}
.attachment-thumb:hover {
    transform: scale(1.02);
}
.issue-mini-card {
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 12px;
    padding: 14px 16px;
}
.issue-mini-card .label {
    font-size: 12px;
    color: #6c757d;
}
.status-badge {
    text-transform: capitalize;
}
</style>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">
                <a href="{{ route('client-issue') }}" class="text-decoration-none">Client Issue</a>
            </div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('client-issue.show', $clientIssue->id) }}">Issue #{{ $clientIssue->id }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Task View</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="task-view-hero mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h4 class="mb-1">{{ $task->title }}</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark status-badge">{{ str_replace('_', ' ', $task->status) }}</span>
                        <span class="badge bg-dark status-badge">{{ $task->priority }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('client-issue.show', $clientIssue->id) }}" class="btn btn-light">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                    <a href="{{ route('client-issue.show', $clientIssue->id) }}?edit_task={{ $task->id }}" class="btn btn-outline-light">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="task-section-title mb-2">Description</div>
                        <p class="text-muted mb-4">{{ $task->description ? $task->description : 'No description provided.' }}</p>

                        <div class="task-section-title mb-2">Task Meta</div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @if($task->assigned_to)
                                <span class="task-meta-pill"><i class="bx bx-user"></i> {{ $task->assigned_to }}</span>
                            @endif
                            @if($task->start_date)
                                <span class="task-meta-pill"><i class="bx bx-calendar"></i> Start: {{ \Carbon\Carbon::parse($task->start_date)->format('M d, Y') }}</span>
                            @endif
                            @if($task->due_date)
                                <span class="task-meta-pill"><i class="bx bx-calendar-check"></i> Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                            @endif
                            @if($task->due_time)
                                <span class="task-meta-pill"><i class="bx bx-time"></i> {{ \Carbon\Carbon::parse($task->due_time)->format('h:i A') }}</span>
                            @endif
                            @if($task->reminder_date)
                                <span class="task-meta-pill"><i class="bx bx-bell"></i> Reminder: {{ \Carbon\Carbon::parse($task->reminder_date)->format('M d, Y') }}</span>
                            @endif
                            @if($task->reminder_time)
                                <span class="task-meta-pill"><i class="bx bx-alarm"></i> {{ \Carbon\Carbon::parse($task->reminder_time)->format('h:i A') }}</span>
                            @endif
                        </div>

                        <div class="task-section-title mb-2">Checklist</div>
                        @if($totalCount > 0)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted">{{ $completedCount }}/{{ $totalCount }} completed</small>
                                <small class="text-muted">{{ round($progressPercent) }}%</small>
                            </div>
                            <div class="task-progress-wrap mb-3">
                                <div class="task-progress-bar" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <ul class="list-group">
                                @foreach($checklist as $item)
                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx {{ (isset($item['completed']) && $item['completed']) ? 'bx-check-square text-success' : 'bx-square text-muted' }}"></i>
                                            <span class="{{ (isset($item['completed']) && $item['completed']) ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $item['text'] ?? 'Checklist item' }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">No checklist items.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="task-section-title mb-3">Issue Summary</div>
                        <div class="issue-mini-card mb-2">
                            <div class="label">Project</div>
                            <div class="fw-semibold">{{ $clientIssue->project->project_name ?? 'N/A' }}</div>
                        </div>
                        <div class="issue-mini-card mb-2">
                            <div class="label">Client</div>
                            <div class="fw-semibold">{{ $clientIssue->customer->client_name ?? 'N/A' }}</div>
                        </div>
                        <div class="issue-mini-card">
                            <div class="label">Issue Priority</div>
                            <div class="fw-semibold text-capitalize">{{ $clientIssue->priority ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="task-section-title mb-2">Labels</div>
                        @if(count($labels) > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($labels as $label)
                                    <span class="badge text-dark" style="background: #f1f5f9;">
                                        <span class="task-label-dot" style="background-color: {{ $label['color'] ?? '#0d6efd' }}"></span>
                                        {{ $label['text'] ?? 'Label' }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No labels assigned.</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="task-section-title mb-2">Attachment</div>
                        @if(count($normalizedAttachments) > 0)
                            <div class="task-attachment-box">
                                <div class="row g-2">
                                    @foreach($normalizedAttachments as $att)
                                        @php
                                            $attachmentPath = $att['path'] ? Storage::url($att['path']) : null;
                                            $extension = $att['path'] ? strtolower(pathinfo($att['path'], PATHINFO_EXTENSION)) : '';
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                            $isPdf = $extension === 'pdf';
                                            $displayName = $att['name'] ? pathinfo($att['name'], PATHINFO_FILENAME) : ($att['path'] ? pathinfo(basename($att['path']), PATHINFO_FILENAME) : 'File');
                                        @endphp
                                        <div class="col-6">
                                            @if($isImage && $attachmentPath)
                                                <img src="{{ $attachmentPath }}" alt="Task Attachment" class="attachment-thumb" data-full-url="{{ $attachmentPath }}">
                                            @elseif($isPdf && $attachmentPath)
                                                <iframe class="pdf-preview" src="{{ $attachmentPath }}#toolbar=0&navpanes=0&scrollbar=0"></iframe>
                                                <div class="mt-2 d-flex align-items-center gap-2">
                                                    <i class="bx bxs-file-pdf text-danger"></i>
                                                    <span class="text-muted">{{ $displayName }}</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bx bx-paperclip"></i>
                                                    <span class="text-muted">{{ $displayName }}</span>
                                                </div>
                                            @endif
                                            <div class="mt-2">
                                                @if($attachmentPath)
                                                <a class="btn btn-sm btn-outline-primary" href="{{ $attachmentPath }}" target="_blank" rel="noopener">
                                                    <i class="bx bx-link-external me-1"></i> Open
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">No attachment uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Attachment Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img id="fullImagePreview" src="" alt="Full Preview" style="width: 100%; max-height: 80vh; object-fit: contain; background: #000;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.attachment-thumb').forEach(img => {
        img.addEventListener('click', function() {
            const fullUrl = this.getAttribute('data-full-url');
            const fullImage = document.getElementById('fullImagePreview');
            fullImage.src = fullUrl;
            const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            modal.show();
        });
    });
});
</script>
@endsection
