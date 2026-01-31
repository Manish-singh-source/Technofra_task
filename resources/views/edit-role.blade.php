@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Roles</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('roles') }}">Roles</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
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

            <!-- Display success message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Display error message -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title">Edit Role - {{ $role->name }}</h5>
                    <hr />
                    <form action="{{ route('role.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Role Name</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Enter role name" value="{{ old('name', $role->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Permissions</label>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Features</th>
                                                        <th>Capabilities</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($modules as $module)
                                                    <tr>
                                                        <td>{{ ucfirst(str_replace('_', ' ', $module)) }}</td>
                                                        <td>
                                                            @php
                                                                $actions = ['view', 'create', 'edit', 'delete'];
                                                            @endphp
                                                            @foreach($actions as $action)
                                                                @php
                                                                    $permission = $permissions->where('name', $action . '_' . $module)->first();
                                                                @endphp
                                                                @if($permission)
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="{{ $action . '_' . $module }}" {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="{{ $action . '_' . $module }}">{{ ucfirst($action) }}</label>
                                                                </div>
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">Update Role</button>
                                                <a href="{{ route('roles') }}" class="btn btn-secondary">Cancel</a>
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
    <!--end page wrapper -->
@endsection
