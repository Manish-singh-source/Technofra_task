@extends('/layout/master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('web-enquiry.career') }}">Web Enquiry Career</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Career Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('web-enquiry.career') }}" class="btn btn-light">Back to Career List</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1">Career Enquiry Detail</h5>
                        <p class="text-muted mb-0">Record ID #{{ $careerEnquiry->id }}</p>
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $careerEnquiry->source_page }}</span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Candidate Information</h6>
                            <p class="mb-2"><strong>First Name:</strong> {{ $careerEnquiry->fname }}</p>
                            <p class="mb-2"><strong>Email:</strong> {{ $careerEnquiry->email }}</p>
                            <p class="mb-2"><strong>Contact:</strong> {{ $careerEnquiry->contact }}</p>
                            <p class="mb-2"><strong>Role:</strong> {{ $careerEnquiry->role }}</p>
                            <p class="mb-2"><strong>Experience:</strong> {{ $careerEnquiry->experience }}</p>
                            <p class="mb-0"><strong>Location:</strong> {{ $careerEnquiry->location }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Compensation & Joining</h6>
                            <p class="mb-2"><strong>Current CTC:</strong> {{ $careerEnquiry->ctc }}</p>
                            <p class="mb-2"><strong>Expected CTC:</strong> {{ $careerEnquiry->ectc }}</p>
                            <p class="mb-2"><strong>Notice Period:</strong> {{ $careerEnquiry->notice }}</p>
                            <p class="mb-2"><strong>Reference Name (RN):</strong> {{ $careerEnquiry->rn }}</p>
                            <p class="mb-2"><strong>Reference:</strong> {{ $careerEnquiry->refrence }}</p>
                            <p class="mb-0"><strong>Created At:</strong> {{ $careerEnquiry->created_at }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            @php
                                $resumePath = ltrim((string) ($careerEnquiry->resume_file ?? ''), '/');
                                $resumeUrl = $resumePath !== '' ? 'https://technofra.com/' . $resumePath : '';
                            @endphp
                            <h6 class="mb-3">Files & Links</h6>
                            <p class="mb-2"><strong>Resume File:</strong> {{ $careerEnquiry->resume_file }}</p>
                            <p class="mb-2"><strong>Resume URL:</strong>
                                @if($resumeUrl !== '')
                                    <a href="{{ $resumeUrl }}" target="_blank" rel="noopener noreferrer">{{ $resumeUrl }}</a>
                                @else
                                    N/A
                                @endif
                            </p>
                            @if($resumeUrl !== '')
                                <a href="{{ $resumeUrl }}" class="btn btn-primary btn-sm mb-2" target="_blank" rel="noopener noreferrer" download>
                                    <i class='bx bx-download'></i> Download Resume
                                </a>
                            @endif
                            <p class="mb-0"><strong>Portfolio Link:</strong>
                                @if(!empty($careerEnquiry->portfolio_link))
                                    <a href="{{ $careerEnquiry->portfolio_link }}" target="_blank" rel="noopener noreferrer">{{ $careerEnquiry->portfolio_link }}</a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Skills (Text)</h6>
                            <div class="bg-light rounded p-3" style="white-space: pre-wrap;">{{ $careerEnquiry->skills_text }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">AI Tools (Text)</h6>
                            <div class="bg-light rounded p-3" style="white-space: pre-wrap;">{{ $careerEnquiry->ai_tools_text }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
