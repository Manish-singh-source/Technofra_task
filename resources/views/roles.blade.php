@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
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

            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Roles</li>
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
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                            <a class="dropdown-item cursor-pointer" id="delete-selected">Delete All</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Features Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Features</h5>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#dashboard" role="tab">
                                <i class="bx bx-grid-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#capabilities" role="tab">
                                <i class="bx bx-list-check me-2"></i>Capabilities
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                            <p>Dashboard feature content will be displayed here.</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Roles</h6>
                                            <h3 class="mb-0">{{ $roles->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light-success">
                                        <div class="card-body">
                                            <h6 class="card-title">Active Roles</h6>
                                            <h3 class="mb-0">{{ $roles->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light-warning">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Permissions</h6>
                                            <h3 class="mb-0">{{ \Spatie\Permission\Models\Permission::count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="capabilities" role="tabpanel">
                            <p>Role capabilities and permissions management view.</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Permission Category</th>
                                            <th>Permissions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $permissionCategories = [
                                                'User Management' => ['view_users', 'create_users', 'edit_users', 'delete_users'],
                                                'Role Management' => ['view_roles', 'create_roles', 'edit_roles', 'delete_roles'],
                                                'Project Management' => ['view_projects', 'create_projects', 'edit_projects', 'delete_projects'],
                                                'Client Management' => ['view_clients', 'create_clients', 'edit_clients', 'delete_clients'],
                                                'Lead Management' => ['view_leads', 'create_leads', 'edit_leads', 'delete_leads'],
                                            ];
                                        @endphp
                                        @foreach($permissionCategories as $category => $perms)
                                        <tr>
                                            <td><strong>{{ $category }}</strong></td>
                                            <td>
                                                @foreach($perms as $perm)
                                                <span class="badge bg-light text-dark mb-1">{{ $perm }}</span>
                                                @endforeach
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

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="ms-auto">
                            <a href="{{route('add-role')}}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
                                <i class="bx bxs-plus-square"></i>Add New Role
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th><input class="form-check-input" type="checkbox" id="select-all"></th>
                                    <th>ID</th>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="{{ $role->id }}"></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 font-14">{{ $role->id }}</h6>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span>{{ $role->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $role->name }} role with permissions</td>
                                    <td>{{ $role->permissions->count() }} permissions</td>
                                    <td>
                                        <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                            <i class='bx bxs-circle me-1'></i>Active
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('role.edit', $role->id) }}" class="ms-3"><i class='bx bxs-edit'></i></a>
                                            <form action="{{ route('role.delete', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0 ms-3" style="color: #f54242;">
                                                    <i class='bx bxs-trash'></i>
                                                </button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All functionality
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });

            // Delete Selected functionality
            document.getElementById('delete-selected').addEventListener('click', function() {
                let selected = [];
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    selected.push(cb.value);
                });
                if (selected.length === 0) {
                    alert('Please select at least one record.');
                    return;
                }
                if (confirm('Are you sure you want to delete selected records?')) {
                    // Create a form and submit
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('delete.selected.role') }}';
                    form.innerHTML = `
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="ids" value="${selected.join(',')}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
    <!--end page wrapper -->
@endsection
