@extends('layout.master')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lead-management.index') }}">Lead Management</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Lead</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Lead Details</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Name</th>
                                <td>{{ $lead['name'] }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $lead['email'] }}</td>
                            </tr>
                            <tr>
                                <th>Number</th>
                                <td>{{ $lead['number'] }}</td>
                            </tr>
                            <tr>
                                <th>Company</th>
                                <td>{{ $lead['company'] }}</td>
                            </tr>
                            <tr>
                                <th>Source</th>
                                <td>{{ $lead['source'] }}</td>
                            </tr>
                            <tr>
                                <th>Created Date</th>
                                <td>{{ $lead['created_at'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('lead-management.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

