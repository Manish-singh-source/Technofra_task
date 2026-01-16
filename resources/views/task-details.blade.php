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
                                <h4 class="text-primary">Design Homepage</h4>
                                <p class="text-muted">Task ID: #T001</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Project</label>
                                <p>Website Redesign</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Priority</label>
                                <p><span class="badge bg-danger">High</span></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date</label>
                                <p>2024-07-15</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Due Date</label>
                                <p>2024-07-30</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Hours</label>
                                <p>25 hours</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <p><span class="badge bg-warning text-dark">In Progress</span></p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Description</label>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Tags</label>
                                <p>
                                    <span class="badge bg-secondary me-1">UI/UX</span>
                                    <span class="badge bg-secondary me-1">Design</span>
                                    <span class="badge bg-secondary">Frontend</span>
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
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://placehold.co/40x40" class="rounded-circle me-2" alt="Assignee">
                                <div>
                                    <p class="mb-0 fw-bold">John Doe</p>
                                    <small class="text-muted">Lead Designer</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Followers</label>
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://placehold.co/40x40" class="rounded-circle me-2" alt="Follower">
                                <div>
                                    <p class="mb-0">Jane Smith</p>
                                    <small class="text-muted">Project Manager</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <img src="https://placehold.co/40x40" class="rounded-circle me-2" alt="Follower">
                                <div>
                                    <p class="mb-0">Peter Jones</p>
                                    <small class="text-muted">Developer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attachments</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bx bx-file me-2"></i>
                            <a href="#" class="text-decoration-none">homepage_mockup.pdf</a>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bx bx-image me-2"></i>
                            <a href="#" class="text-decoration-none">wireframe.png</a>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-file me-2"></i>
                            <a href="#" class="text-decoration-none">requirements.docx</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Comments</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-3">
                                <img src="https://placehold.co/40x40" class="rounded-circle me-3" alt="User">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">John Doe</h6>
                                    <p class="mb-1">Great progress on the homepage design! The new layout looks amazing.</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <img src="https://placehold.co/40x40" class="rounded-circle me-3" alt="User">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Jane Smith</h6>
                                    <p class="mb-1">Can we add a call-to-action button in the hero section?</p>
                                    <small class="text-muted">1 hour ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <form>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="3" placeholder="Add a comment..."></textarea>
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