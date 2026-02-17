@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Staff</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Add Staff</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Settings</button>
                        <button type="button"
                            class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Action</a>
                            <a class="dropdown-item" href="javascript:;">Another action</a>
                            <a class="dropdown-item" href="javascript:;">Something else here</a>
                            <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated
                                link</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title">Add New Staff</h5>
                    <hr />
                    <div class="form-body mt-4">
                        <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <div class="mb-3">
                                        <label for="profileImage" class="form-label">Profile Image</label>
                                        <input type="file" class="form-control" id="profileImage" name="profileImage" accept="image/*">
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="firstName" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="firstName"
                                                placeholder="Enter first name">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="lastName" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="lastName"
                                                placeholder="Enter last name">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email">
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            placeholder="Enter phone number">
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role">
                                                <option>Select Role</option>
                                                @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="team" class="form-label">Team (Optional)</label>
                                            <select class="form-select" id="team" name="team">
                                                <option value="">Select Team</option>
                                                @foreach($teams as $team)
                                                <option value="{{ $team }}" {{ old('team') == $team ? 'selected' : '' }}>{{ $team }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Member Departments</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="departments[]"
                                                    value="Admin">
                                                <label class="form-check-label">Admin</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="departments[]"
                                                    value="Web Developers">
                                                <label class="form-check-label">Web Developers</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="departments[]"
                                                    value="Design and Graphics">
                                                <label class="form-check-label">Design and Graphics</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="departments[]"
                                                    value="Seo Developer">
                                                <label class="form-check-label">Seo Developer</label>
                                            </div>

                                        </div>
                                        <div class="col-12">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Enter password">
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sendWelcomeEmail" id="sendWelcomeEmail">
                                                <label class="form-check-label" for="sendWelcomeEmail">
                                                    Send Welcome Email
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="administrator" id="administrator">
                                                <label class="form-check-label" for="administrator">
                                                   Administrator
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary">Add Staff</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end row-->
                        </form>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!--end page wrapper -->
@endsection
