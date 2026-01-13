@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <h6 class="text-uppercase">Create Project</h6>
        <hr>
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-4">Project Details</h5>
                <form action="{{ route('add-project') }}" method="POST" class="row g-3">
                    @csrf
                    {{-- Basic Information --}}
                    <div class="col-12">
                        <h6>Basic Information</h6>
                        <hr>
                    </div>
                    <div class="col-md-6">
                        <label for="project_name" class="form-label">Project Name *</label>
                        <input type="text" name="project_name" class="form-control" id="project_name" placeholder="Project Name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="customer" class="form-label">Customer</label>
                        <select id="customer" name="customer" class="form-select">
                            <option selected>Choose...</option>
                            <option value="1">Customer 1</option>
                            <option value="2">Customer 2</option>
                            <option value="3">Customer 3</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option selected>Choose...</option>
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" id="start_date">
                    </div>
                    <div class="col-md-4">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" name="deadline" class="form-control" id="deadline">
                    </div>

                    {{-- Billing Information --}}
                    <div class="col-12 mt-5">
                        <h6>Billing Information</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <label for="billing_type" class="form-label">Billing Type</label>
                        <select id="billing_type" name="billing_type" class="form-select">
                            <option selected>Choose...</option>
                            <option value="fixed_rate">Fixed Rate</option>
                            <option value="hourly_rate">Hourly Rate</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="total_rate" class="form-label">Total Rate</label>
                        <input type="number" name="total_rate" class="form-control" id="total_rate" placeholder="e.g., 1000">
                    </div>
                    <div class="col-md-4">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-control" id="estimated_hours" placeholder="e.g., 100">
                    </div>

                    {{-- Project Details --}}
                    <div class="col-12 mt-5">
                        <h6>Project Details</h6>
                        <hr>
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
                        <label for="members" class="form-label">Members</label>
                        <select id="members" name="members[]" class="form-select" multiple data-placeholder="Select members">
                            <option value="1">Member 1</option>
                            <option value="2">Member 2</option>
                            <option value="3">Member 3</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control ckeditor" name="description" id="description" rows="3"></textarea>
                    </div>
                    
                    <div class="col-12">
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
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->

@endsection

@section('scripts')

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('.ckeditor'))
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Initialize Select2 for members and tags dropdowns
    $(document).ready(function() {
        $('#members').select2({
            placeholder: "Select members",
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
