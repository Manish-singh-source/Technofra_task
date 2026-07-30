@extends('layout.master')

@section('content')
    @php
        $oldSelectedAssets = old('selected_assets', []);
        if (!is_array($oldSelectedAssets)) {
            $oldSelectedAssets = [];
        }
        $totalForms = collect($pages)->sum(fn($page) => count($page['forms'] ?? []));
    @endphp

    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Settings</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('settings') }}">Meta Credentials</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Select Lead Forms</li>
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
                            <h5 class="card-title mb-1">Select Facebook Page and Lead Forms</h5>
                            <p class="text-muted mb-0">Choose the Facebook page and the lead forms this CRM account should sync.</p>
                        </div>
                        <a href="{{ route('settings') }}" class="btn btn-light border align-self-md-start">
                            <i class="bx bx-arrow-back me-1"></i> Back to Settings
                        </a>
                    </div>

                    <div class="alert alert-info d-flex align-items-start gap-2">
                        <i class="bx bx-info-circle fs-5 mt-1"></i>
                        <div>
                            Found <strong>{{ count($pages) }}</strong> page(s) and <strong>{{ $totalForms }}</strong> lead form(s). Select multiple forms from one page, or use Select all for that page.
                        </div>
                    </div>

                    <form action="{{ route('settings.meta.select.store') }}" method="POST">
                        @csrf

                        @error('selected_assets')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        @error('selected_assets.*')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="d-flex flex-column gap-3">
                            @foreach ($pages as $pageIndex => $page)
                                @php
                                    $pageForms = $page['forms'] ?? [];
                                    $pageId = (string) ($page['id'] ?? '');
                                    $pageName = (string) ($page['name'] ?? 'Facebook Page');
                                @endphp
                                <div class="border rounded p-3 meta-page-card" data-page-id="{{ $pageId }}">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
                                        <div>
                                            <h6 class="mb-1">{{ $pageName }}</h6>
                                            <div class="text-muted small">
                                                Page ID: <code>{{ $pageId }}</code> - {{ count($pageForms) }} form(s)
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm select-page-forms" data-page-id="{{ $pageId }}">
                                            <i class="bx bx-check-square me-1"></i> Select all forms
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px;">Sync</th>
                                                    <th>Form</th>
                                                    <th>Form ID</th>
                                                    <th>Status</th>
                                                    <th>Leads Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($pageForms as $form)
                                                    @php
                                                        $formId = (string) ($form['id'] ?? '');
                                                        $assetValue = $pageId.'|'.$formId;
                                                        $inputId = 'meta-form-'.$pageIndex.'-'.$loop->index;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <input class="form-check-input meta-form-checkbox" type="checkbox"
                                                                id="{{ $inputId }}"
                                                                name="selected_assets[]"
                                                                value="{{ $assetValue }}"
                                                                data-page-id="{{ $pageId }}"
                                                                @checked(in_array($assetValue, $oldSelectedAssets, true))>
                                                        </td>
                                                        <td>
                                                            <label for="{{ $inputId }}" class="fw-semibold mb-0">
                                                                {{ $form['name'] ?? 'Lead Form' }}
                                                            </label>
                                                        </td>
                                                        <td><code>{{ $formId }}</code></td>
                                                        <td>{{ ($form['status'] ?? '') ?: '-' }}</td>
                                                        <td>{{ $form['leads_count'] ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-muted">No lead forms were found for this page.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Save Selected Forms
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = Array.from(document.querySelectorAll('.meta-form-checkbox'));

            function keepOnePageSelected(pageId) {
                checkboxes.forEach(function(checkbox) {
                    if (checkbox.dataset.pageId !== pageId) {
                        checkbox.checked = false;
                    }
                });
            }

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (checkbox.checked) {
                        keepOnePageSelected(checkbox.dataset.pageId);
                    }
                });
            });

            document.querySelectorAll('.select-page-forms').forEach(function(button) {
                button.addEventListener('click', function() {
                    const pageId = button.dataset.pageId;
                    keepOnePageSelected(pageId);
                    checkboxes.forEach(function(checkbox) {
                        if (checkbox.dataset.pageId === pageId) {
                            checkbox.checked = true;
                        }
                    });
                });
            });
        });
    </script>
@endpush
