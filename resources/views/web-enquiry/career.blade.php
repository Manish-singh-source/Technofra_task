@extends('/layout/master')

@push('styles')
    <style>
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right !important;
        }

        div.dataTables_wrapper div.dataTables_filter label {
            width: auto !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .career-filter-btn.active {
            background: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }
    </style>
@endpush

@section('content')
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

                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item">Web Enquiry</li>
                            <li class="breadcrumb-item active" aria-current="page">Career</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h5 class="mb-1">Career Enquiries</h5>
                            <p class="text-muted mb-0">Records from the <code>jobapplication</code> on the website</p>
                        </div>
                        <span class="badge bg-primary">Total: {{ $careerEnquiries->count() }}</span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm career-filter-btn active" data-applicant-type="all">All</button>
                        <button type="button" class="btn btn-outline-primary btn-sm career-filter-btn" data-applicant-type="fresher">Fresher</button>
                        <button type="button" class="btn btn-outline-primary btn-sm career-filter-btn" data-applicant-type="experience">Experience</button>
                    </div>

                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Applicant Type</th>
                                    <th>Experience</th>
                                    <th>CTC</th>
                                    <th>ECTC</th>
                                    <th>Location</th>
                                    <th>Reference</th>
                                    <th>Resume</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($careerEnquiries as $index => $enquiry)
                                    @php
                                        $resumePath = ltrim((string) ($enquiry->resume_file ?? ''), '/');
                                        $resumeUrl = $resumePath !== '' ? 'https://technofra.com/' . $resumePath : '';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $enquiry->fname }}</td>
                                        <td>{{ $enquiry->email }}</td>
                                        <td>{{ $enquiry->contact }}</td>
                                        <td>{{ $enquiry->role }}</td>
                                        <td>{{ $enquiry->applicant_type ?: 'N/A' }}</td>
                                        <td>{{ $enquiry->experience }}</td>
                                        <td>{{ $enquiry->ctc }}</td>
                                        <td>{{ $enquiry->ectc }}</td>
                                        <td>{{ $enquiry->location }}</td>
                                        <td>{{ $enquiry->refrence }}</td>
                                        <td>
                                            @if($resumeUrl !== '')
                                                <a href="{{ $resumeUrl }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer" download>
                                                    <i class='bx bx-download'></i> Download
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('web-enquiry.career.show', $enquiry->id) }}" title="View">
                                                    <i class='bx bxs-show'></i>
                                                </a>
                                                <form method="POST" action="{{ route('web-enquiry.career.destroy', $enquiry->id) }}" class="ms-2"
                                                    onsubmit="return confirm('Are you sure you want to delete this career enquiry?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0 border-0 text-danger" title="Delete">
                                                        <i class='bx bxs-trash'></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">No career enquiries found.</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = $('#example').DataTable({
            order: [],
            columnDefs: [
                { orderable: false, targets: [11, 12] }
            ]
        });

        const buttons = document.querySelectorAll('.career-filter-btn');
        const applicantTypeColumnIndex = 5;

        function setActiveButton(activeButton) {
            buttons.forEach((button) => {
                button.classList.toggle('active', button === activeButton);
            });
        }

        function escapeRegex(value) {
            return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        buttons.forEach((button) => {
            button.addEventListener('click', function () {
                const applicantType = (this.dataset.applicantType || '').trim();

                if (applicantType === 'all') {
                    table.column(applicantTypeColumnIndex).search('').draw();
                } else {
                    table
                        .column(applicantTypeColumnIndex)
                        .search(`^${escapeRegex(applicantType)}$`, true, false)
                        .draw();
                }

                setActiveButton(this);
            });
        });
    });
</script>
@endpush
