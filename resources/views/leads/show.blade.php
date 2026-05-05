@extends('layout.master')

@section('content')
    @php
        $name = $lead->full_name ?: $lead->lead_id;
        $parts = preg_split('/\s+/', trim((string) $lead->full_name));
        $initials = '';
        if (!empty($parts[0])) {
            $initials .= strtoupper(substr($parts[0], 0, 1));
        }
        if (!empty($parts[1])) {
            $initials .= strtoupper(substr($parts[1], 0, 1));
        }
        if ($initials === '') {
            $initials = 'ML';
        }

        $statusClass = 'bg-secondary';
        $statusLabel = 'Incomplete';
        if ($lead->email && $lead->phone) {
            $statusClass = 'bg-success';
            $statusLabel = 'Complete';
        } elseif ($lead->email || $lead->phone) {
            $statusClass = 'bg-warning text-dark';
            $statusLabel = 'Partial';
        }
    @endphp

    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Meta Leads</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Lead Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('leads.index') }}" class="btn btn-light">← Back to Leads</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                            style="width:56px;height:56px;font-weight:600;">
                            {{ $initials }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $name }}</h4>
                            <p class="text-muted mb-1">Lead ID: {{ $lead->lead_id }}</p>
                            <p class="text-muted mb-0">Submitted on {{ $lead->created_time?->format('d M Y \a\t H:i') ?? '—' }}</p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Contact Information</h6>
                            <p class="mb-2"><strong>Full Name:</strong> {{ $lead->full_name ?: '—' }}</p>
                            <p class="mb-2">
                                <strong>Email:</strong>
                                @if ($lead->email)
                                    <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                @else
                                    —
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Phone:</strong>
                                @if ($lead->phone)
                                    <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                                @else
                                    —
                                @endif
                            </p>
                            <p class="mb-2"><strong>City:</strong> {{ $lead->city ?: '—' }}</p>
                            <p class="mb-0"><strong>State:</strong> {{ $lead->state ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Meta Information</h6>
                            <p class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                                <strong>Lead ID:</strong>
                                <span>{{ $lead->lead_id }}</span>
                                <button
                                    onclick="navigator.clipboard.writeText('{{ $lead->lead_id }}').then(()=>{ this.textContent='Copied!'; setTimeout(()=>{ this.textContent='Copy'; },2000); })"
                                    type="button" class="btn btn-sm btn-outline-secondary">Copy</button>
                            </p>
                            <p class="mb-2"><strong>Form ID:</strong> {{ $lead->form_id ?: '—' }}</p>
                            <p class="mb-2"><strong>Page ID:</strong> {{ $lead->page_id ?: '—' }}</p>
                            <p class="mb-2"><strong>Ad ID:</strong> {{ $lead->ad_id ?: '—' }}</p>
                            <p class="mb-2"><strong>Submitted At:</strong> {{ $lead->created_time?->format('d M Y, H:i') ?? '—' }}</p>
                            <p class="mb-0"><strong>Stored At:</strong> {{ $lead->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-3">All Form Fields</h6>
                    @if (is_array($lead->field_data) && count($lead->field_data))
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field Name</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lead->field_data as $field)
                                        <tr>
                                            <td>{{ ucwords(str_replace('_', ' ', $field['name'] ?? '')) ?: '—' }}</td>
                                            <td>{{ $field['values'][0] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No additional fields captured.</p>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('leads.index') }}" class="btn btn-light">← Back to Leads</a>
                <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this lead?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Lead</button>
                </form>
            </div>
        </div>
    </div>
@endsection
