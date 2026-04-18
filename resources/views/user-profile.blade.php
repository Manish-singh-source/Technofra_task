@extends('/layout/master')

@section('content')
    @php
        $staff = $user->staff;
        $customer = $user->customer;
        $phone = old('phone', optional($staff)->phone ?? optional($customer)->phone);
        $profileImage = asset('assets/images/avatars/technofra.png');

        if ($staff && $staff->profile_image) {
            $profileImage = asset('uploads/staff/' . $staff->profile_image);
        } elseif ($user->profile_image) {
            $profileImage = asset('uploads/profile/' . $user->profile_image);
        }

        $accountType = $staff ? 'Staff' : ($customer ? 'Customer' : 'Admin');
    @endphp

    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Profile</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                        </ol>
                    </nav>
                </div>
            </div>

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

            <div class="container">
                <div class="main-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <img src="{{ $profileImage }}" alt="Profile" class="rounded-circle p-1 bg-primary"
                                            width="110" height="110" style="object-fit: cover;">
                                        <div class="mt-3">
                                            <h4>{{ $user->name }}</h4>
                                            <p class="text-secondary mb-1">{{ $accountType }}</p>
                                            <p class="text-muted font-size-sm mb-0">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <form action="{{ route('user-profile.update') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="profileImage" class="form-label mb-0">Profile Image</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="file" id="profileImage" name="profileImage"
                                                    class="form-control @error('profileImage') is-invalid @enderror"
                                                    accept="image/*">
                                                @error('profileImage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="name" class="form-label mb-0">Full Name</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="text" id="name" name="name"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    value="{{ old('name', $user->name) }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="email" class="form-label mb-0">Email</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="email" id="email" name="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    value="{{ old('email', $user->email) }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="phone" class="form-label mb-0">Phone</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="text" id="phone" name="phone"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    value="{{ $phone }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="current_password" class="form-label mb-0">Current Password</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="password" id="current_password" name="current_password"
                                                    class="form-control @error('current_password') is-invalid @enderror"
                                                    autocomplete="current-password">
                                                @error('current_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="password" class="form-label mb-0">New Password</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="password" id="password" name="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    autocomplete="new-password">
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <label for="password_confirmation" class="form-label mb-0">Confirm Password</label>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="password" id="password_confirmation"
                                                    name="password_confirmation" class="form-control"
                                                    autocomplete="new-password">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-3"></div>
                                            <div class="col-sm-9 text-secondary">
                                                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay toggle-icon"></div>
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection
