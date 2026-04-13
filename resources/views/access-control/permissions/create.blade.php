@extends('/layout/master')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">

            @include('layout.errors')

            <h6 class="text-uppercase">Permissions Form</h6>
            <hr>
            <div id="stepper1" class="bs-stepper">
                <div class="card">

                    <div class="card-body p-4">
                        <h5 class="mb-4">Add Permission</h5>
                        <form action="{{ route('permission.store') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label for="name" class="form-label">Permission Name
                                </label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Permission Name" value="{{ old('name') }}">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="d-md-flex d-grid align-items-center gap-3">
                                    <button type="submit" class="btn btn-primary px-4">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection
