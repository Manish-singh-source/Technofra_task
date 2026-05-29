@extends('layout.master')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            @include('layout.errors')

            @php
                $address = $client->address;
                $businessDetail = $client->businessDetail;
                $companies = $client->companies->isNotEmpty()
                    ? $client->companies
                    : collect($businessDetail ? [$businessDetail] : []);
            @endphp

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Clients</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('client') }}">Clients</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Client Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('client.edit', $client->id) }}" class="btn btn-primary">Edit Client</a>
                    <a href="{{ route('client') }}" class="btn btn-outline-secondary">Back to Clients</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between"><b>Client Name :</b>
                            <p>{{ $client->first_name . ' ' . $client->last_name }}</p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between"><b>Status :</b>
                            <p>{{ ucfirst($client->status ?? 'inactive') }}</p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between"><b>Email ID :</b>
                            <p>{{ $client->email ?? 'N/A' }}</p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between"><b>Contact No :</b>
                            <p>{{ $client->phone ?? 'N/A' }}</p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between"><b>Address :</b>
                            <p>
                                {{ $address ? collect([$address->address_line_1, $address->address_line_2, $address->city, $address->state, $address->country, $address->pincode])->filter()->implode(', ') : 'N/A' }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Client Type</th>
                                    <th>Company Name</th>
                                    <th>Industry</th>
                                    <th>Website</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($companies as $company)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $company->client_type ?: 'N/A' }}</td>
                                        <td>{{ $company->company_name ?: 'N/A' }}</td>
                                        <td>{{ $company->industry ?: 'N/A' }}</td>
                                        <td>
                                            @if ($company->website)
                                                <a href="{{ $company->website }}" target="_blank" rel="noopener">{{ $company->website }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No company details added.</td>
                                    </tr>
                                @endforelse
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
