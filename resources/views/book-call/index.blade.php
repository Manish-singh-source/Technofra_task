@extends('/layout/master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Book A Call</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h5 class="mb-1">Book A Call</h5>
                            <p class="text-muted mb-0">All booked call records are listed below.</p>
                        </div>
                        <span class="badge bg-primary">{{ $bookCalls->count() }} Total</span>
                    </div>

                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Booking Date</th>
                                    <th>Booking Time</th>
                                    <th>Booking DateTime</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bookCalls as $bookCall)
                                    <tr>
                                        <td>{{ $bookCall->id }}</td>
                                        <td>{{ $bookCall->name }}</td>
                                        <td>{{ $bookCall->email }}</td>
                                        <td>{{ $bookCall->phone }}</td>
                                        <td>{{ optional($bookCall->booking_date)->format('d M Y') }}</td>
                                        <td>{{ $bookCall->booking_time ? \Carbon\Carbon::parse($bookCall->booking_time)->format('h:i A') : 'N/A' }}</td>
                                        <td>{{ optional($bookCall->booking_datetime)->format('d M Y h:i A') }}</td>
                                        <td>{{ optional($bookCall->created_at)->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <form method="POST" action="{{ route('book-call.destroy', $bookCall->id) }}" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this booked call?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0 text-danger" title="Delete">
                                                        <i class='bx bxs-trash'></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class='bx bx-calendar-x' style="font-size: 48px; color: #ccc;"></i>
                                                <h6 class="mt-2 text-muted">No booked calls found</h6>
                                                <p class="text-muted mb-0">Data will appear here once records are added to the table.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
