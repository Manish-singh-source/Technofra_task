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
                            <li class="breadcrumb-item active" aria-current="page">Staff</li>
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
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <input type="text" class="form-control ps-5 radius-30" placeholder="Search Staff"> <span
                                class="position-absolute top-50 product-show translate-middle-y"><i
                                    class="bx bx-search"></i></span>
                        </div>
                        <div class="ms-auto"><a href="{{route('add-staff')}}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                    class="bx bxs-plus-square"></i>Add New Staff</a></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staff as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar online avatar-rounded">
                                                <img src="{{ asset('uploads/staff/' . $member->profile_image) }}" style="width: 30px; height: 30px; border-radius: 50%;" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fs-14 fw-medium m-0"><a href="#">{{ $member->first_name . ' ' . $member->last_name }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ ucwords( str_replace('_', ' ', $member->role)) }}</td>
                                    <td>{{ $member->created_at->format('Y-m-d H:i A') }}</td>
                                    <td>
                                        <div class="badge rounded-pill {{ $member->status == 'active' ? 'text-success bg-light-success' : 'text-warning bg-light-warning' }} p-2 text-uppercase px-3">
                                            <i class='bx bxs-circle me-1'></i>{{ ucfirst($member->status) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('view-staff', $member->id) }}" class=""><i class='bx bxs-show'></i></a>
                                            <form method="POST" action="{{ route('staff.destroy', $member->id) }}"
                                                class="d-inline ms-3"
                                                onsubmit="return confirm('Are you sure you want to delete this staff?')">
                                                @csrf
                                                @method('DELETE')
                                                <a>
                                                    <button type="submit" style="border: none;"><i class='bx bxs-trash'></i></button>
                                                </a>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!--end page wrapper -->
@endsection
