@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
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
                            <li class="breadcrumb-item active" aria-current="page">Clients</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    @can('delete_clients')
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
                    @endcan
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="ms-auto">
                            @can('create_clients')
                            <a href="{{ route('add-clients') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
                                <i class="bx bxs-plus-square"></i>Add New Client
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    @can('delete_clients')
                                    <th><input class="form-check-input" type="checkbox" id="select-all"></th>
                                    @endcan
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Email</th>
                                    <th>Industry</th>
                                    <th>Website</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $customer)
                                    <tr>
                                        @can('delete_clients')
                                        <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]"
                                                value="{{ $customer->id }}"></td>
                                        @endcan
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 font-14">{{ $customer->id }}</h6>
                                            </div>
                                        </td>
                                        <td>{{ $customer->client_name }}</td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->industry }}</td>
                                        <td>{{ $customer->website }}</td>
                                        <td>{{ $customer->role }}</td>
                                        <td>
                                            @if ($customer->status == 'Active')
                                                <div
                                                    class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>{{ $customer->status }}
                                                </div>
                                            @elseif($customer->status == 'Inactive')
                                                <div
                                                    class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>{{ $customer->status }}
                                                </div>
                                            @else
                                                <div
                                                    class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                                    <i class='bx bxs-circle me-1'></i>{{ $customer->status }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                @can('view_clients')
                                                <a href="{{ route('clients-details', $customer->id) }}"><i
                                                        class='bx bxs-show'></i></a>
                                                @endcan
                                                @can('edit_clients')
                                                <a href="{{ route('client.edit', $customer->id) }}" class="ms-2"><i
                                                        class='bx bxs-edit'></i></a>
                                                @endcan
                                                @can('delete_clients')
                                                    <form id="delete-form-{{ $customer->id }}"
                                                        action="{{ route('clients.delete', $customer->id) }}" method="POST"
                                                        class="d-inline ms-3"
                                                        onsubmit="return confirm('Are you sure you want to delete this client?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            style="border: none; background: none; color: #f54242;">
                                                            <i class='bx bxs-trash'></i>
                                                        </button>
                                                    </form>
                                                @endcan
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
                    form.action = '{{ route('delete.selected.clients') }}';
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
