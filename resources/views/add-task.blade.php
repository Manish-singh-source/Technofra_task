@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <h6 class="text-uppercase">Add Task</h6>
        <hr>
        <div class="card">`````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````  
            <div class="card-body p-4">
                <h5 class="mb-4">Add Task</h5>
                <form action="{{ route('add-task.store') }}" method="POST" class="row g-3" enctype="multipart/form-data">
                    @csrf
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">   
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="col-md-12">
                        <label for="task_title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="task_title" class="form-control" id="task_title" placeholder="Task Title" value="{{ old('task_title') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="project_related" class="form-label">Project Related To</label>
                        <select id="project_related" name="project_related" class="form-select">
                            <option value="" {{ old('project_related', $selectedProjectId) ? '' : 'selected' }}>No Project (Optional)</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ (string) old('project_related', $selectedProjectId) === (string) $project->id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select">
                            <option value="" {{ old('priority') ? '' : 'selected' }}>Choose...</option>
                            <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                            <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Main Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="" {{ old('status') ? '' : 'selected' }}>Choose...</option>
                            <option value="not_started" {{ old('status', 'not_started') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" id="start_date" value="{{ old('start_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" id="due_date" value="{{ old('due_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="assignees" class="form-label">Assignees</label>
                        <select id="assignees" name="assignees[]" class="form-select select2" multiple>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ in_array($member->id, old('assignees', [])) ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="followers" class="form-label">Followers</label>
                        <select id="followers" name="followers[]" class="form-select select2" multiple>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ in_array($member->id, old('followers', [])) ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                     <div class="col-md-6">
                        <label for="tags" class="form-label">Tags</label>
                        <select id="tags" name="tags[]" class="form-select" multiple data-placeholder="Select or add tags">
                            <option value="web-design">web-design</option>
                            <option value="development">development</option>
                            <option value="seo">seo</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="attach_files" class="form-label">Attach Files</label>
                        <input type="file" name="attach_files[]" class="form-control" id="attach_files" multiple>
                    </div>

                    <div class="col-md-12">
                        <label for="task_description" class="form-label">Task Description</label>
                        <textarea class="form-control" name="task_description" id="task_description" rows="3" placeholder="Task Description">{{ old('task_description') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="d-md-flex d-grid align-items-center gap-3">
                            <button type="submit" class="btn btn-primary px-4">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Choose...",
            allowClear: true
        });

        $('#tags').select2({
            placeholder: "Select or add tags",
            tags: true,
            allowClear: true
        });
    });
</script>
@endsection
