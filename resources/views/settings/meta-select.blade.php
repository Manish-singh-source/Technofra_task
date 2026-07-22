@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Settings</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('settings') }}">Meta Credentials</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Select Lead Form</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="card-title mb-1">Select Facebook Page and Lead Form</h5>
                            <p class="text-muted mb-0">Choose the lead form this CRM account should sync.</p>
                        </div>
                        <a href="{{ route('settings') }}" class="btn btn-light border align-self-md-start">
                            <i class="bx bx-arrow-back me-1"></i> Back to Settings
                        </a>
                    </div>

                    <form action="{{ route('settings.meta.select.store') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Select</th>
                                        <th>Page</th>
                                        <th>Page ID</th>
                                        <th>Form</th>
                                        <th>Form ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pages as $page)
                                        @foreach (($page['forms'] ?? []) as $form)
                                            @php($inputId = 'meta-form-'.$page['id'].'-'.$form['id'])
                                            <tr>
                                                <td>
                                                    <input class="form-check-input" type="radio" id="{{ $inputId }}"
                                                        name="selected_asset"
                                                        value="{{ $page['id'] }}|{{ $form['id'] }}"
                                                        @checked(old('selected_asset') === $page['id'].'|'.$form['id']) required>
                                                </td>
                                                <td>
                                                    <label for="{{ $inputId }}" class="fw-semibold mb-0">
                                                        {{ $page['name'] ?? 'Facebook Page' }}
                                                    </label>
                                                </td>
                                                <td><code>{{ $page['id'] }}</code></td>
                                                <td>{{ $form['name'] ?? 'Lead Form' }}</td>
                                                <td><code>{{ $form['id'] }}</code></td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @error('selected_asset')
                            <div class="text-danger mb-3">{{ $message }}</div>
                        @enderror


                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Save Selection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

