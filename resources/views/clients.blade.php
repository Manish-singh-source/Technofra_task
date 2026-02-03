@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Clients</div>
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
                            <input type="text" class="form-control ps-5 radius-30" placeholder="Search Clients"> <span
                                class="position-absolute top-50 product-show translate-middle-y"><i
                                    class="bx bx-search"></i></span>
                        </div>
                        <div class="ms-auto"><a href="{{route('add-clients')}}" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                                    class="bx bxs-plus-square"></i>Add New Client</a></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
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
                                @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $customer->client_name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->industry }}</td>
                                    <td>{{ $customer->website }}</td>
                                    <td>{{ $customer->role }}</td></td>
                                    <td>
                                        @if($customer->status == 'Active')
                                        <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                            <i class='bx bxs-circle me-1'></i>{{ $customer->status }}
                                        </div>
                                        @elseif($customer->status == 'Inactive')
                                        <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                            <i class='bx bxs-circle me-1'></i>{{ $customer->status }}
                                        </div>
                                        @else
                                        <div class="badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3">
                                            <i class='bx bxs-circle me-1'></i>{{ $customer->status }}
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{ route('clients-details', $customer->id) }}" class=""><i class='bx bxs-show'></i></a>
                                            {{-- <a href="javascript:;" class="ms-3"><i class='bx bxs-edit'></i></a> --}}
                                            <a href="#" class="ms-3" onclick="if(confirm('Are you sure you want to delete this client?')) { document.getElementById('delete-form-{{$customer->id}}').submit(); }"><i class='bx bxs-trash'></i></a>
                                            <form id="delete-form-{{$customer->id}}" action="{{ route('clients.delete', $customer->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
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
