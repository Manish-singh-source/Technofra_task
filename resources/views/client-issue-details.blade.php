@extends('/layout/master')
@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<style>
    .modal-header{
        background-color: #000;
    }
    .assign-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .assign-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .assign-card.selected {
        border-color: #000;
        background-color: #f8f9fa;
    }
    .assign-card .card-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    .assign-card .card-title {
        font-weight: 600;
        margin-bottom: 5px;
    }
    .assign-card .card-text {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .card{
        margin-bottom: 0px;
    }
</style>
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">
                <a href="{{ route('client-issue') }}" class="text-decoration-none">
                    Client Issue
                </a>
            </div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item active" aria-current="page">Issue Details</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                @can('edit_raise_issue')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="bx bx-user-plus"></i> Assign
                </button>
                @endcan
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Issue Details Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Issue Details - #{{ $clientIssue->id }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Project:</strong> {{ $clientIssue->project->project_name ?? 'N/A' }}</p>
                                <p><strong>Client:</strong> {{ $clientIssue->customer->client_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Priority:</strong>
                                    @if($clientIssue->priority == 'low')
                                        <span class="badge bg-secondary">Low</span>
                                    @elseif($clientIssue->priority == 'medium')
                                        <span class="badge bg-primary">Medium</span>
                                    @elseif($clientIssue->priority == 'high')
                                        <span class="badge bg-warning">High</span>
                                    @elseif($clientIssue->priority == 'critical')
                                        <span class="badge bg-danger">Critical</span>
                                    @endif
                                </p>
                                <p><strong>Status:</strong>
                                    @if($clientIssue->status == 'open')
                                        <span class="badge bg-danger">Open</span>
                                    @elseif($clientIssue->status == 'in_progress')
                                        <span class="badge bg-warning">In Progress</span>
                                    @elseif($clientIssue->status == 'resolved')
                                        <span class="badge bg-success">Resolved</span>
                                    @elseif($clientIssue->status == 'closed')
                                        <span class="badge bg-info">Closed</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-12">
                                <p><strong>Description:</strong></p>
                                <p>{{ $clientIssue->issue_description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Details Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Assignments</h5>
                    </div>
                    <div class="card-body">
                        @if($clientIssue->teamAssignments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Team</th>
                                            <th>Assigned To</th>
                                            <th>Assigned By</th>
                                            <th>Note</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clientIssue->teamAssignments->sortByDesc('created_at') as $assignment)
                                            <tr>
                                                <td>{{ $assignment->team_name }}</td>
                                                <td>
                                                    {{ $assignment->assignedStaff ? $assignment->assignedStaff->first_name . ' ' . $assignment->assignedStaff->last_name : 'N/A' }}
                                                </td>
                                                <td>{{ $assignment->assignedBy->name ?? 'System' }}</td>
                                                <td>{{ $assignment->note ?? '-' }}</td>
                                                <td>{{ $assignment->created_at ? $assignment->created_at->format('d M Y, h:i A') : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="mb-0 text-muted">No assignments yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="row align-items-center">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h5 class="mb-3">Tasks</h5>
                @can('create_raise_issue')
                <button class="btn btn-outline-primary mb-3 add-task-btn" data-status="todo">
                    <i class="bx bx-plus"></i> Add Task
                </button>
                @endcan
            </div>
        </div>

        <div class="row kanban-board g-3">
            <!-- Todo Column -->
            <div class="col-md-3">
                <div class="card kanban-column h-100">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Todo</h6>
                        <span class="badge bg-light text-dark">{{ $clientIssue->tasks->where('status', 'todo')->count() }}</span>
                    </div>
                    <div class="card-body kanban-column-body" data-status="todo">
                        @foreach($clientIssue->tasks->where('status', 'todo') as $task)
                            @php
                                $dueDateClass = '';
                                if ($task->due_date) {
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                    $today = \Carbon\Carbon::today();
                                    $diffDays = $today->diffInDays($dueDate, false);
                                    if ($diffDays < 0) {
                                        $dueDateClass = 'overdue';
                                    } elseif ($diffDays <= 2) {
                                        $dueDateClass = 'due-soon';
                                    }
                                }
                                $labels = is_array($task->labels_data) ? $task->labels_data : (json_decode($task->labels_data, true) ?? []);
                                $checklist = is_array($task->checklist_data) ? $task->checklist_data : (json_decode($task->checklist_data, true) ?? []);
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
                                $completedCount = count(array_filter($checklist, function($item) { return isset($item['completed']) && $item['completed']; }));
                                $totalCount = count($checklist);
                                $progressPercent = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                            @endphp
                            <div class="card mb-2 task-card" data-task-id="{{ $task->id }}">
                                <div class="card-body p-3">
                                    <div class="task-card-grid">
                                        <div>
                                            <h6 class="task-card-title">{{ $task->title }}</h6>
                                        </div>
                                        <div class="task-card-actions">
                                            @can('view_raise_issue')
                                                
                                            
                                            <a class="btn btn-sm btn-outline-success" href="{{ route('client-issue.task.show', ['clientIssue' => $clientIssue->id, 'task' => $task->id]) }}" title="View Task">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @endcan
                                            @can('edit_raise_issue')
                                            <button class="btn btn-sm btn-outline-primary edit-task-btn" data-task-id="{{ $task->id }}" title="Edit Task">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @endcan
                                            @can('delete_raise_issue')
                                                
                                            
                                            <button class="btn btn-sm btn-outline-danger delete-task-btn" data-task-id="{{ $task->id }}" title="Delete Task">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                    
                                    @if($task->description)
                                        <p class="task-description-text">{{ Str::limit($task->description, 80) }}</p>
                                    @endif
                                    
                                    <!-- Labels -->
                                    @if(count($labels) > 0)
                                        <div class="task-labels">
                                            @foreach($labels as $label)
                                                <span class="task-label" style="background-color: {{ $label['color'] ?? '#007bff' }}" title="{{ $label['text'] ?? '' }}"></span>
                                                @if(isset($label['text']) && strlen($label['text']) > 0)
                                                    <small class="text-muted" style="font-size: 10px;">{{ $label['text'] }}</small>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <!-- Meta Information -->
                                    <div class="task-meta">
                                        <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                                        
                                        @if($task->due_date)
                                            <span class="task-meta-item {{ $dueDateClass }}">
                                                <i class='bx bx-calendar'></i>
                                                {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                                @if($task->due_time)
                                                    {{ \Carbon\Carbon::parse($task->due_time)->format('h:i A') }}
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->assigned_to)
                                            <span class="task-meta-item task-assignee">
                                                <span class="task-assignee-avatar">{{ strtoupper(substr($task->assigned_to, 0, 1)) }}</span>
                                                {{ $task->assigned_to }}
                                            </span>
                                        @endif
                                        
                                        @if(count($normalizedAttachments) > 0)
                                            @php
                                                $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                                $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                            @endphp
                                            <span class="task-meta-item task-attachment">
                                                <i class='bx bx-paperclip'></i>
                                                {{ $isImage ? 'Image' : 'File' }}
                                                @if(count($normalizedAttachments) > 1)
                                                    <small class="text-muted">({{ count($normalizedAttachments) }})</small>
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->reminder_date)
                                            <span class="task-meta-item task-reminder">
                                                <i class='bx bx-bell'></i>
                                                {{ \Carbon\Carbon::parse($task->reminder_date)->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Checklist Progress -->
                                    @if($totalCount > 0)
                                        <div class="task-checklist">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class='bx bx-check-square'></i> {{ $completedCount }}/{{ $totalCount }}</span>
                                                <span>{{ round($progressPercent) }}%</span>
                                            </div>
                                            <div class="task-checklist-progress">
                                                <div class="task-checklist-progress-bar" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Image Preview at the end -->
                                    @if(count($normalizedAttachments) > 0)
                                        @php
                                            $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                            $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                        @endphp
                                        @if($isImage)
                                            <div class="task-image-preview mt-2">
                                                <img src="{{ $attachmentPath }}" alt="Task Image" class="task-card-image img-thumbnail" data-full-url="{{ $attachmentPath }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="col-md-3">
                <div class="card kanban-column h-100">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">In Progress</h6>
                        <span class="badge bg-light text-dark">{{ $clientIssue->tasks->where('status', 'in_progress')->count() }}</span>
                    </div>
                    <div class="card-body kanban-column-body" data-status="in_progress">
                        @foreach($clientIssue->tasks->where('status', 'in_progress') as $task)
                            @php
                                $dueDateClass = '';
                                if ($task->due_date) {
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                    $today = \Carbon\Carbon::today();
                                    $diffDays = $today->diffInDays($dueDate, false);
                                    if ($diffDays < 0) {
                                        $dueDateClass = 'overdue';
                                    } elseif ($diffDays <= 2) {
                                        $dueDateClass = 'due-soon';
                                    }
                                }
                                $labels = is_array($task->labels_data) ? $task->labels_data : (json_decode($task->labels_data, true) ?? []);
                                $checklist = is_array($task->checklist_data) ? $task->checklist_data : (json_decode($task->checklist_data, true) ?? []);
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
                                $completedCount = count(array_filter($checklist, function($item) { return isset($item['completed']) && $item['completed']; }));
                                $totalCount = count($checklist);
                                $progressPercent = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                            @endphp
                            <div class="card mb-2 task-card" data-task-id="{{ $task->id }}">
                                <div class="card-body p-3">
                                    <div class="task-card-grid">
                                        <div>
                                            <h6 class="task-card-title">{{ $task->title }}</h6>
                                        </div>
                                        <div class="task-card-actions">
                                            <a class="btn btn-sm btn-outline-success" href="{{ route('client-issue.task.show', ['clientIssue' => $clientIssue->id, 'task' => $task->id]) }}" title="View Task">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary edit-task-btn" data-task-id="{{ $task->id }}" title="Edit Task">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-task-btn" data-task-id="{{ $task->id }}" title="Delete Task">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($task->description)
                                        <p class="task-description-text">{{ Str::limit($task->description, 80) }}</p>
                                    @endif
                                    
                                    @if(count($labels) > 0)
                                        <div class="task-labels">
                                            @foreach($labels as $label)
                                                <span class="task-label" style="background-color: {{ $label['color'] ?? '#007bff' }}" title="{{ $label['text'] ?? '' }}"></span>
                                                @if(isset($label['text']) && strlen($label['text']) > 0)
                                                    <small class="text-muted" style="font-size: 10px;">{{ $label['text'] }}</small>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="task-meta">
                                        <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                                        
                                        @if($task->due_date)
                                            <span class="task-meta-item {{ $dueDateClass }}">
                                                <i class='bx bx-calendar'></i>
                                                {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                                @if($task->due_time)
                                                    {{ \Carbon\Carbon::parse($task->due_time)->format('h:i A') }}
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->assigned_to)
                                            <span class="task-meta-item task-assignee">
                                                <span class="task-assignee-avatar">{{ strtoupper(substr($task->assigned_to, 0, 1)) }}</span>
                                                {{ $task->assigned_to }}
                                            </span>
                                        @endif
                                        
                                        @if(count($normalizedAttachments) > 0)
                                            @php
                                                $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                                $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                            @endphp
                                            <span class="task-meta-item task-attachment">
                                                <i class='bx bx-paperclip'></i>
                                                {{ $isImage ? 'Image' : 'File' }}
                                                @if(count($normalizedAttachments) > 1)
                                                    <small class="text-muted">({{ count($normalizedAttachments) }})</small>
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->reminder_date)
                                            <span class="task-meta-item task-reminder">
                                                <i class='bx bx-bell'></i>
                                                {{ \Carbon\Carbon::parse($task->reminder_date)->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($totalCount > 0)
                                        <div class="task-checklist">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class='bx bx-check-square'></i> {{ $completedCount }}/{{ $totalCount }}</span>
                                                <span>{{ round($progressPercent) }}%</span>
                                            </div>
                                            <div class="task-checklist-progress">
                                                <div class="task-checklist-progress-bar" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Image Preview at the end -->
                                    @if(count($normalizedAttachments) > 0)
                                        @php
                                            $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                            $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                        @endphp
                                        @if($isImage)
                                            <div class="task-image-preview mt-2">
                                                <img src="{{ $attachmentPath }}" alt="Task Image" class="task-card-image img-thumbnail" data-full-url="{{ $attachmentPath }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Review Column -->
            <div class="col-md-3">
                <div class="card kanban-column h-100">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Review</h6>
                        <span class="badge bg-light text-dark">{{ $clientIssue->tasks->where('status', 'review')->count() }}</span>
                    </div>
                    <div class="card-body kanban-column-body" data-status="review">
                        @foreach($clientIssue->tasks->where('status', 'review') as $task)
                            @php
                                $dueDateClass = '';
                                if ($task->due_date) {
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                    $today = \Carbon\Carbon::today();
                                    $diffDays = $today->diffInDays($dueDate, false);
                                    if ($diffDays < 0) {
                                        $dueDateClass = 'overdue';
                                    } elseif ($diffDays <= 2) {
                                        $dueDateClass = 'due-soon';
                                    }
                                }
                                $labels = is_array($task->labels_data) ? $task->labels_data : (json_decode($task->labels_data, true) ?? []);
                                $checklist = is_array($task->checklist_data) ? $task->checklist_data : (json_decode($task->checklist_data, true) ?? []);
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
                                $completedCount = count(array_filter($checklist, function($item) { return isset($item['completed']) && $item['completed']; }));
                                $totalCount = count($checklist);
                                $progressPercent = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                            @endphp
                            <div class="card mb-2 task-card" data-task-id="{{ $task->id }}">
                                <div class="card-body p-3">
                                    <div class="task-card-grid">
                                        <div>
                                            <h6 class="task-card-title">{{ $task->title }}</h6>
                                        </div>
                                        <div class="task-card-actions">
                                            <a class="btn btn-sm btn-outline-success" href="{{ route('client-issue.task.show', ['clientIssue' => $clientIssue->id, 'task' => $task->id]) }}" title="View Task">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary edit-task-btn" data-task-id="{{ $task->id }}" title="Edit Task">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-task-btn" data-task-id="{{ $task->id }}" title="Delete Task">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($task->description)
                                        <p class="task-description-text">{{ Str::limit($task->description, 80) }}</p>
                                    @endif
                                    
                                    @if(count($labels) > 0)
                                        <div class="task-labels">
                                            @foreach($labels as $label)
                                                <span class="task-label" style="background-color: {{ $label['color'] ?? '#007bff' }}" title="{{ $label['text'] ?? '' }}"></span>
                                                @if(isset($label['text']) && strlen($label['text']) > 0)
                                                    <small class="text-muted" style="font-size: 10px;">{{ $label['text'] }}</small>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="task-meta">
                                        <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                                        
                                        @if($task->due_date)
                                            <span class="task-meta-item {{ $dueDateClass }}">
                                                <i class='bx bx-calendar'></i>
                                                {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                                @if($task->due_time)
                                                    {{ \Carbon\Carbon::parse($task->due_time)->format('h:i A') }}
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->assigned_to)
                                            <span class="task-meta-item task-assignee">
                                                <span class="task-assignee-avatar">{{ strtoupper(substr($task->assigned_to, 0, 1)) }}</span>
                                                {{ $task->assigned_to }}
                                            </span>
                                        @endif
                                        
                                        @if(count($normalizedAttachments) > 0)
                                            @php
                                                $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                                $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                            @endphp
                                            <span class="task-meta-item task-attachment">
                                                <i class='bx bx-paperclip'></i>
                                                {{ $isImage ? 'Image' : 'File' }}
                                                @if(count($normalizedAttachments) > 1)
                                                    <small class="text-muted">({{ count($normalizedAttachments) }})</small>
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->reminder_date)
                                            <span class="task-meta-item task-reminder">
                                                <i class='bx bx-bell'></i>
                                                {{ \Carbon\Carbon::parse($task->reminder_date)->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($totalCount > 0)
                                        <div class="task-checklist">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class='bx bx-check-square'></i> {{ $completedCount }}/{{ $totalCount }}</span>
                                                <span>{{ round($progressPercent) }}%</span>
                                            </div>
                                            <div class="task-checklist-progress">
                                                <div class="task-checklist-progress-bar" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Image Preview at the end -->
                                    @if(count($normalizedAttachments) > 0)
                                        @php
                                            $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                            $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                        @endphp
                                        @if($isImage)
                                            <div class="task-image-preview mt-2">
                                                <img src="{{ $attachmentPath }}" alt="Task Image" class="task-card-image img-thumbnail" data-full-url="{{ $attachmentPath }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Done Column -->
            <div class="col-md-3">
                <div class="card kanban-column h-100">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Done</h6>
                        <span class="badge bg-light text-dark">{{ $clientIssue->tasks->where('status', 'done')->count() }}</span>
                    </div>
                    <div class="card-body kanban-column-body" data-status="done">
                        @foreach($clientIssue->tasks->where('status', 'done') as $task)
                            @php
                                $dueDateClass = '';
                                if ($task->due_date) {
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                    $today = \Carbon\Carbon::today();
                                    $diffDays = $today->diffInDays($dueDate, false);
                                    if ($diffDays < 0) {
                                        $dueDateClass = 'overdue';
                                    } elseif ($diffDays <= 2) {
                                        $dueDateClass = 'due-soon';
                                    }
                                }
                                $labels = is_array($task->labels_data) ? $task->labels_data : (json_decode($task->labels_data, true) ?? []);
                                $checklist = is_array($task->checklist_data) ? $task->checklist_data : (json_decode($task->checklist_data, true) ?? []);
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
                                $completedCount = count(array_filter($checklist, function($item) { return isset($item['completed']) && $item['completed']; }));
                                $totalCount = count($checklist);
                                $progressPercent = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                            @endphp
                            <div class="card mb-2 task-card" data-task-id="{{ $task->id }}">
                                <div class="card-body p-3">
                                    <div class="task-card-grid">
                                        <div>
                                            <h6 class="task-card-title">{{ $task->title }}</h6>
                                        </div>
                                        <div class="task-card-actions">
                                            <a class="btn btn-sm btn-outline-success" href="{{ route('client-issue.task.show', ['clientIssue' => $clientIssue->id, 'task' => $task->id]) }}" title="View Task">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary edit-task-btn" data-task-id="{{ $task->id }}" title="Edit Task">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-task-btn" data-task-id="{{ $task->id }}" title="Delete Task">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($task->description)
                                        <p class="task-description-text">{{ Str::limit($task->description, 80) }}</p>
                                    @endif
                                    
                                    @if(count($labels) > 0)
                                        <div class="task-labels">
                                            @foreach($labels as $label)
                                                <span class="task-label" style="background-color: {{ $label['color'] ?? '#007bff' }}" title="{{ $label['text'] ?? '' }}"></span>
                                                @if(isset($label['text']) && strlen($label['text']) > 0)
                                                    <small class="text-muted" style="font-size: 10px;">{{ $label['text'] }}</small>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="task-meta">
                                        <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                                        
                                        @if($task->due_date)
                                            <span class="task-meta-item {{ $dueDateClass }}">
                                                <i class='bx bx-calendar'></i>
                                                {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                                @if($task->due_time)
                                                    {{ \Carbon\Carbon::parse($task->due_time)->format('h:i A') }}
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->assigned_to)
                                            <span class="task-meta-item task-assignee">
                                                <span class="task-assignee-avatar">{{ strtoupper(substr($task->assigned_to, 0, 1)) }}</span>
                                                {{ $task->assigned_to }}
                                            </span>
                                        @endif
                                        
                                        @if(count($normalizedAttachments) > 0)
                                            @php
                                                $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                                $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                            @endphp
                                            <span class="task-meta-item task-attachment">
                                                <i class='bx bx-paperclip'></i>
                                                {{ $isImage ? 'Image' : 'File' }}
                                                @if(count($normalizedAttachments) > 1)
                                                    <small class="text-muted">({{ count($normalizedAttachments) }})</small>
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if($task->reminder_date)
                                            <span class="task-meta-item task-reminder">
                                                <i class='bx bx-bell'></i>
                                                {{ \Carbon\Carbon::parse($task->reminder_date)->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($totalCount > 0)
                                        <div class="task-checklist">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class='bx bx-check-square'></i> {{ $completedCount }}/{{ $totalCount }}</span>
                                                <span>{{ round($progressPercent) }}%</span>
                                            </div>
                                            <div class="task-checklist-progress">
                                                <div class="task-checklist-progress-bar" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Image Preview at the end -->
                                    @if(count($normalizedAttachments) > 0)
                                        @php
                                            $attachmentPath = $normalizedAttachments[0]['path'] ? Storage::url($normalizedAttachments[0]['path']) : null;
                                            $extension = $normalizedAttachments[0]['path'] ? strtolower(pathinfo($normalizedAttachments[0]['path'], PATHINFO_EXTENSION)) : '';
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                        @endphp
                                        @if($isImage)
                                            <div class="task-image-preview mt-2">
                                                <img src="{{ $attachmentPath }}" alt="Task Image" class="task-card-image img-thumbnail" data-full-url="{{ $attachmentPath }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="taskModalLabel">
                    <i class='bx bx-task me-2'></i>Add New Task
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm" action="{{ route('client-issue.task.store', $clientIssue->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="POST">
                    <input type="hidden" id="task_id" name="task_id">
                    
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="Enter task title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter task description"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Status and Priority Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="todo">Todo</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="done">Done</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label fw-bold">Priority</label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Dates Section -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class='bx bx-calendar me-2'></i>Dates
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="due_time" class="form-label">Time</label>
                                    <input type="time" class="form-control" id="due_time" name="due_time">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Labels Section -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class='bx bx-purchase-tag me-2'></i>Labels
                            </h6>
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label for="label_color" class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color" id="label_color" name="label_color" value="#007bff">
                                </div>
                                <div class="col-md-9">
                                    <label for="label_text" class="form-label">Label Text</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="label_text" name="label_text" placeholder="Enter label">
                                        <button type="button" class="btn btn-outline-primary" id="addLabelBtn">
                                            <i class='bx bx-plus'></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Labels List -->
                            <div class="mt-3" id="labelsList">
                                <!-- Labels will be added dynamically here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checklist Section -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class='bx bx-check-square me-2'></i>Checklist
                            </h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="checklist_item" placeholder="Add a checklist item">
                                <button type="button" class="btn btn-outline-primary" id="addChecklistBtn">
                                    <i class='bx bx-plus'></i> Add
                                </button>
                            </div>
                            <!-- Checklist Items -->
                            <ul class="list-group" id="checklistItems">
                                <!-- Checklist items will be added dynamically here -->
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Reminder Section -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class='bx bx-bell me-2'></i>Reminder
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="reminder_date" class="form-label">Reminder Date</label>
                                    <input type="date" class="form-control" id="reminder_date" name="reminder_date">
                                </div>
                                <div class="col-md-6">
                                    <label for="reminder_time" class="form-label">Reminder Time</label>
                                    <input type="time" class="form-control" id="reminder_time" name="reminder_time">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attachment Section -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class='bx bx-paperclip me-2'></i>Attachment
                            </h6>
                            <div class="mb-3">
                                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                                <small class="text-muted">Max file size: 10MB</small>
                            </div>
                            <!-- Image Preview -->
                            <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                                <p class="mb-2"><strong>Preview:</strong></p>
                                <div class="attachment-preview">
                                    <div id="imagePreviewList" class="d-flex flex-wrap gap-2"></div>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeImageBtn">
                                        <i class='bx bx-trash'></i> Remove All
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden field for checklist data -->
                    <input type="hidden" id="checklist_data" name="checklist_data">
                    <!-- Hidden field for labels data -->
                    <input type="hidden" id="labels_data" name="labels_data">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class='bx bx-x me-1'></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveTaskBtn">
                    <i class='bx bx-save me-1'></i>Save Task
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaskModalLabel">Delete Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this task?</p>
                <form id="deleteTaskForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img id="fullImagePreview" src="" alt="Full Image Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<style>
.kanban-column {
    min-height: 400px;
}

.kanban-column-body {
    min-height: 300px;
    overflow-y: auto;
    padding: 8px;
}

.task-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.task-card.dragging {
    opacity: 0.5;
}

.kanban-column-body.drag-over {
    background-color: rgba(0,123,255,0.1);
}

/* Task Card Grid Layout */
.task-card-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    align-items: start;
}

.task-card-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    line-height: 1.3;
    margin: 0;
}

.task-card-actions {
    display: flex;
    gap: 4px;
}

.task-card-actions .btn {
    padding: 2px 6px;
    font-size: 12px;
}

.task-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
}

.task-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #666;
    background: #f5f5f5;
    padding: 3px 8px;
    border-radius: 12px;
}

.task-meta-item i {
    font-size: 12px;
}

.task-meta-item.due-soon {
    background: #fff3cd;
    color: #856404;
}

.task-meta-item.overdue {
    background: #f8d7da;
    color: #721c24;
}

/* Label badges */
.task-labels {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 8px;
}

.task-label {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

/* Checklist progress */
.task-checklist {
    margin-top: 8px;
    font-size: 11px;
    color: #666;
}

.task-checklist-progress {
    height: 4px;
    background: #e0e0e0;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 4px;
}

.task-checklist-progress-bar {
    height: 100%;
    background: #28a745;
    transition: width 0.3s ease;
}

/* Priority badges */
.priority-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: capitalize;
    font-weight: 600;
}

.priority-low {
    background: #e0e0e0;
    color: #666;
}

.priority-medium {
    background: #cce5ff;
    color: #004085;
}

.priority-high {
    background: #fff3cd;
    color: #856404;
}

.priority-critical {
    background: #f8d7da;
    color: #721c24;
}

/* Attachment indicator */
.task-attachment {
    color: #007bff;
    font-size: 12px;
}

/* Reminder indicator */
.task-reminder {
    color: #fd7e14;
}

/* Description in card */
.task-card-description {
    font-size: 12px;
    color: #666;
    margin-top: 6px;
    line-height: 1.4;
}

/* Avatar for assigned to */
.task-assignee {
    display: flex;
    align-items: center;
    gap: 4px;
}

.task-assignee-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #007bff;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 600;
}

/* Modal Styles */
#taskModal .modal-header {
    border-bottom: 2px solid #007bff;
}

#taskModal .modal-body .card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

#taskModal .card-title {
    color: #333;
    font-weight: 600;
}

#taskModal .form-label {
    font-weight: 500;
    color: #555;
}

#taskModal .form-control:focus,
#taskModal .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

#taskModal .input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

#checklistItems .list-group-item {
    border-radius: 4px;
    margin-bottom: 4px;
}

#labelsList .badge {
    font-size: 0.85rem;
    padding: 6px 10px;
}

#taskModal .modal-body .bg-light {
    background-color: #f8f9fa !important;
}

.form-control-color {
    height: 38px;
    padding: 4px;
    border-radius: 4px;
}

/* Checklist checkbox animation */
#checklistItems .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

/* Remove button hover effect */
#checklistItems .btn-outline-danger:hover,
#labelsList .btn:hover {
    opacity: 0.8;
}

/* Task card description text */
.task-description-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 6px;
    line-height: 1.5;
}

/* Attachment preview styles */
.attachment-preview {
    display: flex;
    align-items: center;
    gap: 10px;
}

.attachment-preview img {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Task card image preview */
.task-image-preview {
    margin-top: 10px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.task-image-preview img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.task-image-preview img:hover {
    transform: scale(1.02);
}

/* Image preview modal */
#imagePreviewModal .modal-body {
    padding: 0;
    background: #000;
}

#imagePreviewModal .modal-body img {
    width: 100%;
    max-height: 80vh;
    object-fit: contain;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
    const deleteTaskModal = new bootstrap.Modal(document.getElementById('deleteTaskModal'));
    const taskForm = document.getElementById('taskForm');
    const saveTaskBtn = document.getElementById('saveTaskBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Checklist functionality
    const checklistInput = document.getElementById('checklist_item');
    const addChecklistBtn = document.getElementById('addChecklistBtn');
    const checklistItems = document.getElementById('checklistItems');
    let checklistData = [];
    
    // Labels functionality
    const labelColorInput = document.getElementById('label_color');
    const labelTextInput = document.getElementById('label_text');
    const addLabelBtn = document.getElementById('addLabelBtn');
    const labelsList = document.getElementById('labelsList');
    let labelsData = [];
    
    // Add Checklist Item
    addChecklistBtn.addEventListener('click', function() {
        const itemText = checklistInput.value.trim();
        if (itemText) {
            checklistData.push({ text: itemText, completed: false });
            renderChecklistItems();
            checklistInput.value = '';
            updateChecklistData();
        }
    });
    
    checklistInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addChecklistBtn.click();
        }
    });
    
    function renderChecklistItems() {
        checklistItems.innerHTML = '';
        checklistData.forEach((item, index) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center justify-content-between';
            li.innerHTML = `
                <div class="d-flex align-items-center">
                    <input type="checkbox" class="form-check-input me-2" ${item.completed ? 'checked' : ''} onchange="toggleChecklistItem(${index})">
                    <span class="${item.completed ? 'text-decoration-line-through text-muted' : ''}">${item.text}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChecklistItem(${index})">
                    <i class='bx bx-trash'></i>
                </button>
            `;
            checklistItems.appendChild(li);
        });
    }
    
    window.toggleChecklistItem = function(index) {
        checklistData[index].completed = !checklistData[index].completed;
        renderChecklistItems();
        updateChecklistData();
    };
    
    window.removeChecklistItem = function(index) {
        checklistData.splice(index, 1);
        renderChecklistItems();
        updateChecklistData();
    };
    
    function updateChecklistData() {
        document.getElementById('checklist_data').value = JSON.stringify(checklistData);
    }
    
    // Add Label
    addLabelBtn.addEventListener('click', function() {
        const color = labelColorInput.value;
        const text = labelTextInput.value.trim();
        if (text) {
            labelsData.push({ color: color, text: text });
            renderLabels();
            labelTextInput.value = '';
            updateLabelsData();
        }
    });
    
    labelTextInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addLabelBtn.click();
        }
    });
    
    function renderLabels() {
        labelsList.innerHTML = '';
        labelsData.forEach((label, index) => {
            const badge = document.createElement('span');
            badge.className = 'badge me-2 mb-1';
            badge.style.backgroundColor = label.color;
            badge.innerHTML = `
                ${label.text}
                <button type="button" class="btn btn-sm text-white ms-1 p-0 border-0" onclick="removeLabel(${index})">
                    <i class='bx bx-x'></i>
                </button>
            `;
            labelsList.appendChild(badge);
        });
    }
    
    window.removeLabel = function(index) {
        labelsData.splice(index, 1);
        renderLabels();
        updateLabelsData();
    };
    
    function updateLabelsData() {
        document.getElementById('labels_data').value = JSON.stringify(labelsData);
    }
    
    // Add Task button click handler
    document.querySelectorAll('.add-task-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Reset form
            document.getElementById('task_id').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('status').value = 'todo';
            document.getElementById('priority').value = 'medium';
            document.getElementById('due_date').value = '';
            document.getElementById('due_time').value = '';
            document.getElementById('start_date').value = '';
            document.getElementById('reminder_date').value = '';
            document.getElementById('reminder_time').value = '';
            document.getElementById('attachments').value = '';
            
            // Reset checklist and labels
            checklistData = [];
            labelsData = [];
            renderChecklistItems();
            renderLabels();
            updateChecklistData();
            updateLabelsData();
            
            document.getElementById('taskModalLabel').innerHTML = '<i class="bx bx-task me-2"></i>Add New Task';
            taskForm.action = '{{ route('client-issue.task.store', $clientIssue->id) }}';
            document.getElementById('form_method').value = 'POST';
            
            taskModal.show();
        });
    });
    
    // Edit Task button click handler
    document.querySelectorAll('.edit-task-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const taskId = this.getAttribute('data-task-id');
            const taskCard = this.closest('.task-card');
            const title = taskCard.querySelector('.task-card-title').textContent;
            const description = taskCard.querySelector('.task-description-text')?.textContent || '';
            const priorityBadge = taskCard.querySelector('.priority-badge');
            const priority = priorityBadge ? priorityBadge.textContent.toLowerCase() : 'medium';
            
            // Set form values
            document.getElementById('task_id').value = taskId;
            document.getElementById('title').value = title;
            document.getElementById('description').value = description;
            document.getElementById('priority').value = priority;
            
            // Find the task data from the page
            const tasks = @json($clientIssue->tasks);
            const task = tasks.find(t => t.id == taskId);
            
            if (task) {
                document.getElementById('status').value = task.status;
                document.getElementById('due_date').value = task.due_date || '';
                document.getElementById('due_time').value = task.due_time || '';
                document.getElementById('start_date').value = task.start_date || '';
                document.getElementById('reminder_date').value = task.reminder_date || '';
                document.getElementById('reminder_time').value = task.reminder_time || '';
                
                // Load checklist data
                if (task.checklist_data) {
                    try {
                        checklistData = typeof task.checklist_data === 'string' ? JSON.parse(task.checklist_data) : task.checklist_data;
                    } catch (e) {
                        checklistData = [];
                    }
                } else {
                    checklistData = [];
                }
                renderChecklistItems();
                
                // Load labels data
                if (task.labels_data) {
                    try {
                        labelsData = typeof task.labels_data === 'string' ? JSON.parse(task.labels_data) : task.labels_data;
                    } catch (e) {
                        labelsData = [];
                    }
                } else {
                    labelsData = [];
                }
                renderLabels();
                
                updateChecklistData();
                updateLabelsData();
            }
            
            document.getElementById('taskModalLabel').innerHTML = '<i class="bx bx-edit-alt me-2"></i>Edit Task';
            taskForm.action = '{{ route('client-issue.task.update', ['clientIssue' => $clientIssue->id, 'task' => '__task_id__']) }}'.replace('__task_id__', taskId);
            document.getElementById('form_method').value = 'PUT';
            
            taskModal.show();
        });
    });

    // Auto-open edit modal from query param
    const editTaskIdFromQuery = new URLSearchParams(window.location.search).get('edit_task');
    if (editTaskIdFromQuery) {
        const editBtn = document.querySelector(`.edit-task-btn[data-task-id="${editTaskIdFromQuery}"]`);
        if (editBtn) {
            editBtn.click();
        }
    }
    
    // Delete Task button click handler
    document.querySelectorAll('.delete-task-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const taskId = this.getAttribute('data-task-id');
            document.getElementById('deleteTaskForm').action = '{{ route('client-issue.task.destroy', ['clientIssue' => $clientIssue->id, 'task' => '__task_id__']) }}'.replace('__task_id__', taskId);
            deleteTaskModal.show();
        });
    });
    
    // Save Task button click handler
    saveTaskBtn.addEventListener('click', function() {
        const titleInput = document.getElementById('title');
        
        if (!titleInput.value.trim()) {
            titleInput.classList.add('is-invalid');
            return;
        }
        
        titleInput.classList.remove('is-invalid');
        
        // Update hidden fields with current data
        updateChecklistData();
        updateLabelsData();
        
        taskForm.submit();
    });
    
    // Confirm Delete button click handler
    confirmDeleteBtn.addEventListener('click', function() {
        document.getElementById('deleteTaskForm').submit();
    });
    
    // Drag and Drop functionality
    const taskCards = document.querySelectorAll('.task-card');
    const columnBodies = document.querySelectorAll('.kanban-column-body');
    
    taskCards.forEach(card => {
        card.addEventListener('dragstart', function() {
            this.classList.add('dragging');
        });
        
        card.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            
            // Update status via AJAX
            const taskId = this.getAttribute('data-task-id');
            const newStatus = this.closest('.kanban-column-body').getAttribute('data-status');
            
            // Update status using fetch
            fetch('{{ route('client-issue.task.update-status', ['clientIssue' => $clientIssue->id, 'task' => '__task_id__']) }}'.replace('__task_id__', taskId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Move the card to the new column
                    const column = document.querySelector(`.kanban-column-body[data-status="${newStatus}"]`);
                    column.appendChild(this);
                    
                    // Update column counts
                    updateColumnCounts();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    columnBodies.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        column.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
    });
    
    function updateColumnCounts() {
        columnBodies.forEach(column => {
            const status = column.getAttribute('data-status');
            const count = column.querySelectorAll('.task-card').length;
            const badge = column.closest('.kanban-column').querySelector('.badge');
            if (badge) {
                badge.textContent = count;
            }
        });
    }
    
    // Image Preview Functionality
    const attachmentInput = document.getElementById('attachments');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreviewList = document.getElementById('imagePreviewList');
    const removeImageBtn = document.getElementById('removeImageBtn');
    
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files || []);
            imagePreviewList.innerHTML = '';
            
            const imageFiles = files.filter(file => file.type.startsWith('image/'));
            if (imageFiles.length > 0) {
                imageFiles.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        const img = document.createElement('img');
                        img.src = ev.target.result;
                        img.alt = 'Image Preview';
                        img.className = 'img-thumbnail';
                        img.style.maxWidth = '120px';
                        img.style.maxHeight = '90px';
                        img.style.objectFit = 'cover';
                        imagePreviewList.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
                imagePreviewContainer.style.display = 'block';
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });
    }
    
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            attachmentInput.value = '';
            imagePreviewContainer.style.display = 'none';
            imagePreviewList.innerHTML = '';
        });
    }
    
    // Task card image click handler for full preview
    document.querySelectorAll('.task-card-image').forEach(img => {
        img.addEventListener('click', function() {
            const fullImageUrl = this.getAttribute('data-full-url');
            const fullImagePreview = document.getElementById('fullImagePreview');
            fullImagePreview.src = fullImageUrl;
            const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            imagePreviewModal.show();
        });
    });
});
</script>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="assignModalLabel">Assign Issue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm" method="POST" action="{{ route('client-issue.assign', $clientIssue->id) }}">
                    @csrf
                    <p class="text-muted mb-4">Select a team to assign this issue:</p>
                    <div class="d-flex flex-column gap-3">
                        <!-- Web Team Card -->
                        <div class="card assign-card" data-team="Web Team">
                            <div class="card-body d-flex align-items-center">
                                <div class="card-icon text-primary me-3">
                                    <i class="bx bx-code-alt fs-2"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Web Team</h6>
                                    <p class="card-text mb-0 text-muted">Website development, bugs, and technical changes</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="bx bx-chevron-right fs-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Graphic Team Card -->
                        <div class="card assign-card" data-team="Graphic Team">
                            <div class="card-body d-flex align-items-center">
                                <div class="card-icon text-success me-3">
                                    <i class="bx bx-palette fs-2"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Graphic Team</h6>
                                    <p class="card-text mb-0 text-muted">Design requests, creatives, and branding materials</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="bx bx-chevron-right fs-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Team Card -->
                        <div class="card assign-card" data-team="Social Media Team">
                            <div class="card-body d-flex align-items-center">
                                <div class="card-icon text-info me-3">
                                    <i class="bx bxl-instagram fs-2"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Social Media Team</h6>
                                    <p class="card-text mb-0 text-muted">Posts, campaigns, promotions, and engagement</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="bx bx-chevron-right fs-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Accounts Team Card -->
                        <div class="card assign-card" data-team="Accounts Team">
                            <div class="card-body d-flex align-items-center">
                                <div class="card-icon text-warning me-3">
                                    <i class="bx bx-credit-card fs-2"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Accounts Team</h6>
                                    <p class="card-text mb-0 text-muted">Invoices, payments, and billing related matters</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="bx bx-chevron-right fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="team_name" id="team_name">

                    <div class="mt-3">
                        <label for="assigned_to" class="form-label">Assign To Staff</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" data-team="{{ $member->team ?? '' }}">
                                    {{ $member->first_name }} {{ $member->last_name }}@if($member->team) ({{ $member->team }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3">
                        <label for="assignNote" class="form-label">Add a note (optional):</label>
                        <textarea class="form-control" id="assignNote" name="note" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="assignBtn" form="assignForm">
                    <i class="bx bx-check"></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Card selection handler
    document.querySelectorAll('.assign-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            document.querySelectorAll('.assign-card').forEach(c => c.classList.remove('selected'));
            // Add selected class to clicked card
            this.classList.add('selected');

            const team = this.getAttribute('data-team');
            const teamInput = document.getElementById('team_name');
            teamInput.value = team;

            const staffSelect = document.getElementById('assigned_to');
            if (staffSelect) {
                const options = Array.from(staffSelect.options);
                const matches = options.filter(option => {
                    if (!option.value) return false;
                    const optionTeam = option.getAttribute('data-team') || '';
                    return optionTeam === team;
                });
                const hasMatches = matches.length > 0;

                Array.from(staffSelect.options).forEach(option => {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }
                    const optionTeam = option.getAttribute('data-team') || '';
                    const show = hasMatches ? (optionTeam === team) : true;
                    option.hidden = !show;
                    option.disabled = !show;
                    if (!show && option.selected) {
                        option.selected = false;
                    }
                });
            }
        });
    });

    // Assign form validation
    const assignForm = document.getElementById('assignForm');
    if (assignForm) {
        const staffSelect = document.getElementById('assigned_to');
        if (staffSelect) {
            staffSelect.addEventListener('change', function() {
                const selectedOption = staffSelect.options[staffSelect.selectedIndex];
                const teamInput = document.getElementById('team_name');
                const optionTeam = selectedOption ? (selectedOption.getAttribute('data-team') || '') : '';
                if (!teamInput.value && optionTeam) {
                    teamInput.value = optionTeam;
                }
            });
        }

        assignForm.addEventListener('submit', function(e) {
            const teamInput = document.getElementById('team_name');
            if (!teamInput.value) {
                const selectedOption = staffSelect ? staffSelect.options[staffSelect.selectedIndex] : null;
                const fallbackTeam = selectedOption ? (selectedOption.getAttribute('data-team') || '') : '';
                if (fallbackTeam) {
                    teamInput.value = fallbackTeam;
                }
            }
            if (!staffSelect.value) {
                e.preventDefault();
                alert('Please select a staff member');
                return;
            }
        });
    });
</script>

@endsection
