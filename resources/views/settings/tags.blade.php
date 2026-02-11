@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Settings</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Tags Management</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                    <div class="text-white">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    <div class="text-white">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Tags Management</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTagModal">
                            <i class="bx bx-plus"></i> Add New Tag
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="tagSearch" placeholder="Search tags...">
                            </div>
                        </div>
                    </div>

                    <!-- Tags Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tagsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Color</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tags as $tag)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $tag->color }}; color: white;">
                                            {{ $tag->name }}
                                        </span>
                                    </td>
                                    <td><code>{{ $tag->slug }}</code></td>
                                    <td>{{ $tag->description ?? '-' }}</td>
                                    <td>
                                        <input type="color" class="form-control form-control-color" value="{{ $tag->color }}" disabled style="width: 40px;">
                                    </td>
                                    <td>
                                        @if($tag->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary edit-tag" data-id="{{ $tag->id }}" data-name="{{ $tag->name }}" data-color="{{ $tag->color }}" data-description="{{ $tag->description }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-tag" data-id="{{ $tag->id }}" data-name="{{ $tag->name }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-tag-alt fs-1"></i>
                                            <p class="mt-2 mb-0">No tags found. Create your first tag!</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $tags->links() }}
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->

    <!-- Add Tag Modal -->
    <div class="modal fade" id="addTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addTagForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tagName" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" id="tagName" name="name" required placeholder="Enter tag name">
                            <div class="invalid-feedback" id="tagNameError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="tagColor" class="form-label">Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="tagColor" name="color" value="#3498db">
                                <input type="text" class="form-control ms-2" id="tagColorText" value="#3498db" style="max-width: 100px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tagDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="tagDescription" name="description" rows="2" placeholder="Optional description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveTagBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Save Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tag Modal -->
    <div class="modal fade" id="editTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTagForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTagId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTagName" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" id="editTagName" name="name" required>
                            <div class="invalid-feedback" id="editTagNameError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editTagColor" class="form-label">Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="editTagColor" name="color" value="#3498db">
                                <input type="text" class="form-control ms-2" id="editTagColorText" value="#3498db" style="max-width: 100px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editTagDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editTagDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editTagActive" name="is_active" value="1">
                                <label class="form-check-label" for="editTagActive">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="updateTagBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Update Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Tag Modal -->
    <div class="modal fade" id="deleteTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the tag "<strong id="deleteTagName"></strong>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteTagForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" id="deleteTagBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Color picker sync
    $('#tagColor').on('input', function() {
        $('#tagColorText').val($(this).val());
    });
    $('#tagColorText').on('input', function() {
        $('#tagColor').val($(this).val());
    });
    $('#editTagColor').on('input', function() {
        $('#editTagColorText').val($(this).val());
    });
    $('#editTagColorText').on('input', function() {
        $('#editTagColor').val($(this).val());
    });

    // Add Tag Form Submit
    $('#addTagForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#saveTagBtn');
        const spinner = btn.find('.spinner-border');
        
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('tags.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addTagModal').modal('hide');
                    $('#addTagForm')[0].reset();
                    $('#tagColor').val('#3498db');
                    $('#tagColorText').val('#3498db');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to create tag');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('#tagName').addClass('is-invalid');
                    $('#tagNameError').text(errors.name ? errors.name[0] : '');
                } else {
                    alert('Failed to create tag: ' + xhr.responseJSON.message);
                }
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Edit Tag - Open Modal
    $('.edit-tag').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const color = $(this).data('color');
        const description = $(this).data('description');
        const isActive = $(this).closest('tr').find('.badge.bg-success').length > 0;

        $('#editTagId').val(id);
        $('#editTagName').val(name);
        $('#editTagColor').val(color);
        $('#editTagColorText').val(color);
        $('#editTagDescription').val(description || '');
        $('#editTagActive').prop('checked', isActive);

        $('#editTagModal').modal('show');
    });

    // Edit Tag Form Submit
    $('#editTagForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#updateTagBtn');
        const spinner = btn.find('.spinner-border');
        const id = $('#editTagId').val();
        
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: '/tags/' + id,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editTagModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update tag');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('#editTagName').addClass('is-invalid');
                    $('#editTagNameError').text(errors.name ? errors.name[0] : '');
                } else {
                    alert('Failed to update tag: ' + xhr.responseJSON.message);
                }
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Delete Tag - Open Modal
    $('.delete-tag').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#deleteTagName').text(name);
        $('#deleteTagForm').attr('action', '/tags/' + id);
        
        $('#deleteTagModal').modal('show');
    });

    // Delete Tag Form Submit
    $('#deleteTagForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#deleteTagBtn');
        const spinner = btn.find('.spinner-border');
        
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'DELETE',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#deleteTagModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to delete tag');
                }
            },
            error: function(xhr) {
                alert('Failed to delete tag: ' + xhr.responseJSON.message);
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Tag Search
    $('#tagSearch').on('keyup', function() {
        const query = $(this).val().toLowerCase();
        
        $('#tagsTable tbody tr').each(function() {
            const name = $(this).find('td:first').text().toLowerCase();
            const slug = $(this).find('code').text().toLowerCase();
            const description = $(this).find('td:nth-child(3)').text().toLowerCase();
            
            if (name.includes(query) || slug.includes(query) || description.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Clear form on modal close
    $('#addTagModal').on('hidden.bs.modal', function() {
        $('#addTagForm')[0].reset();
        $('#tagName').removeClass('is-invalid');
        $('#tagColor').val('#3498db');
        $('#tagColorText').val('#3498db');
    });

    // Clear edit form errors on modal close
    $('#editTagModal').on('hidden.bs.modal', function() {
        $('#editTagName').removeClass('is-invalid');
    });
});
</script>
@endpush
