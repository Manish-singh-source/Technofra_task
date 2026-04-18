@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">

            @include('layout.errors')

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
                        <a class="btn btn-primary" id="delete-selected">Delete All</a>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Features</h5>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#dashboard" role="tab">
                                <i class="bx bx-grid-alt me-2"></i>Dashboard
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                            <p>Dashboard feature content will be displayed here.</p>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-light-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Roles</h6>
                                            <h3 class="mb-0">{{ $roles->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light-success">
                                        <div class="card-body">
                                            <h6 class="card-title">Active Roles</h6>
                                            <h3 class="mb-0">{{ $activeRoles }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light-success">
                                        <div class="card-body">
                                            <h6 class="card-title">Inactive Roles</h6>
                                            <h3 class="mb-0">{{ $inactiveRoles }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light-warning">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Permissions</h6>
                                            <h3 class="mb-0">{{ $permissionsCount }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="ms-auto">
                            <a href="{{ route('role.create') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
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
                                    {{-- <th>Description</th> --}}
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]"
                                                value="{{ $role->id }}"></td>
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
                                        {{-- <td>{{ $role->name }} role with permissions</td> --}}
                                        <td>{{ $role->permissions->count() }} permissions</td>
                                        <td>
                                            <div
                                                class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                <i class='bx bxs-circle me-1'></i>{{ $role->status }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('role.edit', $role->id) }}" class="ms-3"><i
                                                        class='bx bxs-edit'></i></a>
                                                <form action="{{ route('role.delete', $role->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0 ms-3"
                                                        style="color: #f54242;">
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
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {

            // Select All functionality
            $('#select-all').on('change', function() {
                $('.row-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Delete Selected functionality
            $('#delete-selected').on('click', function() {
                let selected = [];

                $('.row-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    alert('Please select at least one record.');
                    return;
                }

                if (confirm('Are you sure you want to delete selected records?')) {

                    // Create and submit form
                    let form = $('<form>', {
                        method: 'POST',
                        action: '{{ route('delete.selected.role') }}'
                    });

                    form.append(`
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="ids" value="${selected.join(',')}">
                    `);

                    $('body').append(form);
                    form.submit();
                }
            });

        });
    </script>
@endsection
