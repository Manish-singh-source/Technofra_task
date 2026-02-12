@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('task') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('task') }}">Tasks</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Task Details</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('task') }}" class="btn btn-secondary">Back to Tasks</a>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Task Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <h4 class="text-primary">{{ $task->title }}</h4>
                                <p class="text-muted">Task ID: #T{{ str_pad($task->id, 3, '0', STR_PAD_LEFT) }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Project</label>
                                <p>{{ $task->project->project_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Priority</label>
                                <p>
                                    @if($task->priority == 'high')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($task->priority == 'medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date</label>
                                <p>{{ $task->start_date ? $task->start_date->format('Y-m-d') : 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Due Date</label>
                                <p>{{ $task->deadline ? $task->deadline->format('Y-m-d') : 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Hours</label>
                                <p>N/A</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <p>
                                    @if($task->status == 'in_progress')
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                    @elseif($task->status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($task->status == 'on_hold')
                                        <span class="badge bg-secondary">On Hold</span>
                                    @elseif($task->status == 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        <span class="badge bg-info">Not Started</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Description</label>
                                <p>{{ $task->description ?? 'No description provided.' }}</p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Tags</label>
                                <p>
                                    @if($task->tags)
                                        @foreach($task->tags as $tag)
                                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No tags</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Assignees & Followers</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assignees</label>
                            @if($task->assignees)
                                @foreach($task->assignees as $assigneeId)
                                    @if(isset($staff[$assigneeId]))
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="{{ $staff[$assigneeId]->profile_image ? asset('uploads/staff/' . $staff[$assigneeId]->profile_image) : 'https://placehold.co/40x40' }}" class="rounded-circle me-2" alt="Assignee" width="40" height="40">
                                            <div>
                                                <p class="mb-0 fw-bold">{{ $staff[$assigneeId]->first_name }} {{ $staff[$assigneeId]->last_name }}</p>
                                                <small class="text-muted">{{ $staff[$assigneeId]->designation ?? 'Staff' }}</small>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-muted">No assignees</p>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Followers</label>
                            @if($task->followers)
                                @foreach($task->followers as $followerId)
                                    @if(isset($staff[$followerId]))
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="{{ $staff[$followerId]->profile_image ? asset('uploads/staff/' . $staff[$followerId]->profile_image) : 'https://placehold.co/40x40' }}" class="rounded-circle me-2" alt="Follower" width="40" height="40">
                                            <div>
                                                <p class="mb-0">{{ $staff[$followerId]->first_name }} {{ $staff[$followerId]->last_name }}</p>
                                                <small class="text-muted">{{ $staff[$followerId]->designation ?? 'Staff' }}</small>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-muted">No followers</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attachments</h5>
                    </div>
                    <div class="card-body">
                        @if($task->attachments->count() > 0)
                            <div class="row">
                                @foreach($task->attachments as $attachment)
                                    <div class="col-md-4 mb-3">
                                        <div class="attachment-item">
                                            @if(strpos($attachment->file_type, 'image/') === 0)
                                                <img src="{{ asset('storage/' . $attachment->file_path) }}" class="img-thumbnail me-2" alt="{{ $attachment->file_name }}" style="width: 50px; height: 50px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ asset('storage/' . $attachment->file_path) }}">
                                            @else
                                                <i class="bx bx-file me-2"></i>
                                            @endif
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" class="text-decoration-none" target="_blank">{{ $attachment->file_name }}</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No attachments</p>
                        @endif
                    </div>
                </div>

                <!-- Image Preview Modal -->
                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="modalImage" src="" class="img-fluid" alt="Preview">
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    var imageModal = document.getElementById('imageModal');
                    imageModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        var src = button.getAttribute('data-src');
                        var modalImage = document.getElementById('modalImage');
                        modalImage.src = src;
                    });
                </script>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Comments</h5>
                    </div>
                    <div class="card-body">
                        <div id="comments-list" class="mb-3">
                            @forelse($task->comments as $comment)
                                <div class="d-flex align-items-start mb-3 comment-item">
                                    <img src="{{ $comment->user && $comment->user->staff && $comment->user->staff->profile_image ? asset('uploads/staff/' . $comment->user->staff->profile_image) : 'https://placehold.co/40x40' }}" class="rounded-circle me-3" alt="User" width="40" height="40">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $comment->user->name ?? 'Unknown User' }}</h6>
                                        <p class="mb-1">{{ $comment->comment }}</p>
                                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">No comments yet.</p>
                            @endforelse
                        </div>
                        <div class="border-top pt-3">
                            <form action="{{ route('task.comment.store', $task->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="3" placeholder="Add a comment..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->



@endsection
