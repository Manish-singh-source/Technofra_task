@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <h6 class="text-uppercase">Edit Project</h6>
        <hr>
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-4">Project Details</h5>
                <form action="{{ route('update-project', $project->id) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')
                    {{-- Basic Information --}}
                    <div class="col-12">
                        <h6>Basic Information</h6>
                        <hr>
                    </div>
                    <div class="col-md-6">
                        <label for="project_name" class="form-label">Project Name *</label>
                        <input type="text" name="project_name" class="form-control @error('project_name') is-invalid @enderror" id="project_name" placeholder="Project Name" value="{{ old('project_name', $project->project_name) }}" required>
                        @error('project_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="customer" class="form-label">Customer</label>
                        <select id="customer" name="customer" class="form-select @error('customer') is-invalid @enderror">
                            <option selected>Choose...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer', $project->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->client_name }}</option>
                            @endforeach
                        </select>
                        @error('customer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option selected>Choose...</option>
                            <option value="not_started" {{ old('status', $project->status) == 'not_started' ? 'selected' : '' }}>Not Started</option>
                            <option value="in_progress" {{ old('status', $project->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Finished</option>
                            <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                            <option selected>Choose...</option>
                            <option value="low" {{ old('priority', $project->priority) == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', $project->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $project->priority) == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" id="deadline" value="{{ old('deadline', $project->deadline ? $project->deadline->format('Y-m-d') : '') }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            <option value="fixed_rate" {{ old('billing_type', $project->billing_type) == 'fixed_rate' ? 'selected' : '' }}>Fixed Rate</option>
                            <option value="hourly_rate" {{ old('billing_type', $project->billing_type) == 'hourly_rate' ? 'selected' : '' }}>Hourly Rate</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="total_rate" class="form-label">Total Rate</label>
                        <input type="number" name="total_rate" class="form-control @error('total_rate') is-invalid @enderror" id="total_rate" placeholder="e.g., 1000" value="{{ old('total_rate', $project->total_rate) }}">
                        @error('total_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" id="estimated_hours" placeholder="e.g., 100" value="{{ old('estimated_hours', $project->estimated_hours) }}">
                        @error('estimated_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Project Details --}}
                    <div class="col-12 mt-5">
                        <h6>Project Details</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <label for="tags" class="form-label">Tags</label>
                        <select id="tags" name="tags[]" class="form-select @error('tags') is-invalid @enderror" multiple data-placeholder="Select or add tags">
                            <option value="web-design">web-design</option>
                            <option value="development">development</option>
                            <option value="seo">seo</option>
                            @if($project->tags)
                                @foreach($project->tags as $tag)
                                    <option value="{{ $tag }}" selected>{{ $tag }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="technologies" class="form-label">Technologies</label>
                        <select id="technologies" name="technologies[]" class="form-select @error('technologies') is-invalid @enderror" multiple data-placeholder="Select or add technologies">
                            <option value="Laravel">Laravel</option>
                            <option value="Vue.js">Vue.js</option>
                            <option value="MySQL">MySQL</option>
                            <option value="Bootstrap">Bootstrap</option>
                            <option value="Docker">Docker</option>
                            @if($project->technologies)
                                @foreach($project->technologies as $tech)
                                    <option value="{{ $tech }}" selected>{{ $tech }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('technologies')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="members" class="form-label">Members</label>
                        <select id="members" name="members[]" class="form-select @error('members') is-invalid @enderror" multiple data-placeholder="Select members">
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ in_array($member->id, old('members', $project->members ?? [])) ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                        @error('members')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control ckeditor @error('description') is-invalid @enderror" name="description" id="description" rows="3">{{ old('description', $project->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="d-md-flex d-grid align-items-center gap-3">
                            <button type="submit" class="btn btn-primary px-4">Update</button>
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
        .then(editor => {
            // Set initial data if editing
            @if($project->description)
                editor.setData(`{{ $project->description }}`);
            @endif
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Initialize Select2 for members, tags, and technologies dropdowns
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

        $('#technologies').select2({
            placeholder: "Select or add technologies",
            tags: true,
            allowClear: true
        });
    });
</script>
@endsection