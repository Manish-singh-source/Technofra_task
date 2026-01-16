@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <h6 class="text-uppercase">Add Task</h6>
        <hr>
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-4">Add Task</h5>
                <form action="" method="POST" class="row g-3" enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-12">
                        <label for="task_title" class="form-label">Task Title</label>
                        <input type="text" name="task_title" class="form-control" id="task_title" placeholder="Task Title">
                    </div>

                    <div class="col-md-6">
                        <label for="project_related" class="form-label">Project Related To</label>
                        <select id="project_related" name="project_related" class="form-select">
                            <option selected disabled value="">Choose...</option>
                            <option>Project 1</option>
                            <option>Project 2</option>
                            <option>Project 3</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select">
                            <option selected disabled value="">Choose...</option>
                            <option>High</option>
                            <option>Medium</option>
                            <option>Low</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" id="start_date">
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" id="due_date">
                    </div>

                    <div class="col-md-6">
                        <label for="assignees" class="form-label">Assignees</label>
                        <select id="assignees" name="assignees[]" class="form-select select2" multiple>
                            <option>User 1</option>
                            <option>User 2</option>
                            <option>User 3</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="followers" class="form-label">Followers</label>
                        <select id="followers" name="followers[]" class="form-select select2" multiple>
                            <option>User A</option>
                            <option>User B</option>
                            <option>User C</option>
                        </select>
                    </div>

                     <div class="col-md-6">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" id="tags" placeholder="Tags (comma separated)">
                    </div>

                    <div class="col-md-6">
                        <label for="attach_files" class="form-label">Attach Files</label>
                        <input type="file" name="attach_files[]" class="form-control" id="attach_files" multiple>
                    </div>

                    <div class="col-md-12">
                        <label for="task_description" class="form-label">Task Description</label>
                        <textarea class="form-control" name="task_description" id="task_description" rows="3" placeholder="Task Description"></textarea>
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
    });
</script>
@endsection
