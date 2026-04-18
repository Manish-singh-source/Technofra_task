@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Roles</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @include('layout.errors')

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
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" placeholder="Enter role name"
                                                value="{{ old('name', $role->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Permissions</label>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Sr. No.</th>
                                                        <th>Permission Name</th>
                                                        <th class="text-center">
                                                            <span>
                                                                Select Permission
                                                            </span>
                                                            <input type="checkbox" class="permission-checkbox select-all">
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($permissions as $key => $permission)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>{{ ucfirst(str_replace('_', ' ', $permission->name)) }}</td>
                                                            <td class="text-center">
                                                                <input type="checkbox" class="permission-checkbox"
                                                                    name="permission[]" value="{{ $permission->id }}"
                                                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">Update Role</button>
                                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('.select-all').change(function() {
                $('.permission-checkbox').not('.select-all').prop('checked', this.checked);
            });
        });
    </script>
@endsection
