@extends('/layout/master')
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
                            <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('permission.create') }}" class="btn btn-primary">Add Permission</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th> <input class="form-check-input" type="checkbox" id="select-all"></th>
                                    <th>ID</th>
                                    <th>Permission Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr>
                                        <td> <input class="form-check-input row-checkbox" type="checkbox" name="ids[]"
                                                value="{{ $permission->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 font-14">{{ $permission->id }}</h6>
                                            </div>
                                        </td>
                                        <td>{{ $permission->name }}</td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('permission.edit', $permission->id) }}" class="ms-2"><i
                                                        class='bx bxs-edit'></i></a>
                                                <form action="{{ route('permission.destroy', $permission->id) }}"
                                                    method="POST" onsubmit="return confirm('Are you sure?')"
                                                    class="ms-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a>
                                                        <button type="submit" style="border: none;"><i
                                                                class='bx bxs-trash'></i></button>
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
    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection
