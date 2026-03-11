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
                            <li class="breadcrumb-item active" aria-current="page">Add Role</li>
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
                    <h5 class="card-title">Add New Role</h5>
                    <hr />
                    <form action="{{ route('store-role') }}" method="POST">
                        @csrf
                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Role Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter role name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Permissions</label>
                                            <table class="table table-bordered align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Features</th>
                                                        <th>Capabilities</th>
                                                        <th class="text-center">Select All</th>
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
                                                                    <input class="form-check-input permission-checkbox" data-group="{{ $module }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="{{ $action . '_' . $module }}">
                                                                    <label class="form-check-label" for="{{ $action . '_' . $module }}">{{ ucfirst($action) }}</label>
                                                                </div>
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                        <td class="text-center">
                                                            <input class="form-check-input row-select-all" type="checkbox" data-group="{{ $module }}" aria-label="Select all {{ ucfirst(str_replace('_', ' ', $module)) }} permissions">
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td>Settings</td>
                                                        <td>
                                                            @foreach($settingsPermissions as $permissionName)
                                                                @php
                                                                    $permission = $permissions->where('name', $permissionName)->first();
                                                                @endphp
                                                                @if($permission)
                                                                <div class="form-check">
                                                                    <input class="form-check-input permission-checkbox" data-group="settings" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="{{ $permissionName }}">
                                                                    <label class="form-check-label" for="{{ $permissionName }}">{{ ucfirst(str_replace('_', ' ', $permissionName)) }}</label>
                                                                </div>
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                        <td class="text-center">
                                                            <input class="form-check-input row-select-all" type="checkbox" data-group="settings" aria-label="Select all settings permissions">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Add Role</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const syncRowToggle = function (group) {
                const rowToggle = document.querySelector('.row-select-all[data-group="' + group + '"]');
                const permissions = document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]');

                if (!rowToggle || permissions.length === 0) {
                    return;
                }

                rowToggle.checked = Array.from(permissions).every(function (checkbox) {
                    return checkbox.checked;
                });
            };

            document.querySelectorAll('.row-select-all').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const group = this.dataset.group;
                    document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]').forEach(function (checkbox) {
                        checkbox.checked = toggle.checked;
                    });
                });

                syncRowToggle(toggle.dataset.group);
            });

            document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    syncRowToggle(this.dataset.group);
                });
            });
        });
    </script>
    <!--end page wrapper -->
@endsection
