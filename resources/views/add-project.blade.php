@extends('/layout/master')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <h6 class="text-uppercase">Create Project</h6>
        <hr>
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-4">Project Details</h5>
                <form action="{{ route('store-project') }}" method="POST" class="row g-3" enctype="multipart/form-data">
                    @csrf
                    {{-- Basic Information --}}
                    <div class="col-12">
                        <h6>Basic Information</h6>
                        <hr>
                    </div>
                    <div class="col-md-6">
                        <label for="project_name" class="form-label">Project Name *</label>
                        <input type="text" name="project_name" class="form-control @error('project_name') is-invalid @enderror" id="project_name" placeholder="Project Name" value="{{ old('project_name') }}" required>
                        @error('project_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="customer" class="form-label">Customer</label>
                        <select id="customer" name="customer" class="form-select @error('customer') is-invalid @enderror">
                            <option selected>Choose...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer') == $customer->id ? 'selected' : '' }}>{{ $customer->client_name }}</option>
                            @endforeach
                        </select>
                        @error('customer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option selected>Choose...</option>
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Finished</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                            <option selected>Choose...</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" value="{{ old('start_date') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" id="deadline" value="{{ old('deadline') }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Billing Information --}}
                    <div class="col-12 mt-5">
                        <h6>Billing Information</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <label for="billing_type" class="form-label">Billing Type</label>
                        <select id="billing_type" name="billing_type" class="form-select">
                            <option selected>Choose...</option>
                            <option value="fixed_rate">Fixed Rate</option>
                            <option value="hourly_rate">Hourly Rate</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="total_rate" class="form-label">Total Rate</label>
                        <input type="number" name="total_rate" class="form-control @error('total_rate') is-invalid @enderror" id="total_rate" placeholder="e.g., 1000" value="{{ old('total_rate') }}">
                        @error('total_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" id="estimated_hours" placeholder="e.g., 100" value="{{ old('estimated_hours') }}">
                        @error('estimated_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Project Details --}}
                    <div class="col-12 mt-5">
                        <h6>Project Details</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <label for="tags" class="form-label">Tags</label>
                        <select id="tags" name="tags[]" class="form-select @error('tags') is-invalid @enderror" multiple data-placeholder="Select or add tags">
                            <option value="web-design">web-design</option>
                            <option value="development">development</option>
                            <option value="seo">seo</option>
                        </select>
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="technologies" class="form-label">Technologies</label>
                        <select id="technologies" name="technologies[]" class="form-select @error('technologies') is-invalid @enderror" multiple data-placeholder="Select or add technologies">
                            <option value="Laravel">Laravel</option>
                            <option value="Vue.js">Vue.js</option>
                            <option value="MySQL">MySQL</option>
                            <option value="Bootstrap">Bootstrap</option>
                            <option value="Docker">Docker</option>
                        </select>
                        @error('technologies')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="members" class="form-label">Members</label>
                        <select id="members" name="members[]" class="form-select @error('members') is-invalid @enderror" multiple data-placeholder="Select members">
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ in_array($member->id, old('members', [])) ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                        @error('members')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control ckeditor @error('description') is-invalid @enderror" name="description" id="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Project Files --}}
                    <div class="col-12 mt-5">
                        <h6>Project Files</h6>
                        <hr>
                    </div>
                    <div class="col-12">
                        <label for="project_files" class="form-label">Upload Files (Images & PDFs)</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-area" id="fileDropArea">
                                <i class='bx bx-cloud-upload bx-lg text-primary mb-2'></i>
                                <h6 class="mb-2">Drag & Drop files here or click to browse</h6>
                                <p class="text-muted small mb-0">Supported: JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, PDF (Max 10MB each)</p>
                                <input type="file" name="project_files[]" class="form-control @error('project_files') is-invalid @enderror" id="project_files" multiple accept=".jpg,.jpeg,.png,.gif,.svg,.webp,.bmp,.pdf">
                            </div>
                            <div id="filePreviewContainer" class="mt-3">
                                @if(old('project_files'))
                                    @foreach(old('project_files') as $index => $file)
                                        @if(is_object($file))
                                        <div class="file-preview-item d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class='bx bx-file bx-sm text-primary me-2'></i>
                                                <span>{{ $file->getClientOriginalName() }}</span>
                                                <small class="text-muted ms-2">({{ number_format($file->getSize() / 1024, 2) }} KB)</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(this)"><i class='bx bx-x'></i></button>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @error('project_files')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <div class="d-md-flex d-grid align-items-center gap-3">
                            <button type="submit" class="btn btn-primary px-4">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->

@endsection

@section('scripts')

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<style>
.file-upload-wrapper {
    position: relative;
}

.file-upload-area {
    border: 2px dashed #ced4da;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.file-upload-area:hover {
    border-color: #0d6efd;
    background-color: #e9ecef;
}

.file-upload-area.dragover {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

.file-upload-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-preview-item {
    background-color: #f8f9fa;
}

.file-preview-item:hover {
    background-color: #e9ecef;
}
</style>
<script>
    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('.ckeditor'))
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Initialize Select2 for members, tags, and technologies dropdowns
    $(document).ready(function() {
        $('#members').select2({
            placeholder: "Select members",
            allowClear: true
        });

        $('#tags').select2({
            placeholder: "Select or add tags",
            tags: true,
            allowClear: true
        });

        $('#technologies').select2({
            placeholder: "Select or add technologies",
            tags: true,
            allowClear: true
        });
    });

    // File Upload Drag & Drop
    $(document).ready(function() {
        const fileDropArea = $('#fileDropArea');
        const fileInput = $('#project_files');
        const previewContainer = $('#filePreviewContainer');
        
        // Drag and drop events
        fileDropArea.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });
        
        fileDropArea.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });
        
        fileDropArea.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                // Replace files with new ones
                handleFiles(files, true);
            }
        });
        
        // File input change
        fileInput.on('change', function() {
            const files = this.files;
            if (files.length > 0) {
                // Replace files with new ones
                handleFiles(files, true);
            }
        });
        
        // Handle selected files
        function handleFiles(files, replace = false) {
            if (replace) {
                // Replace all files with new selection
                const dataTransfer = new DataTransfer();
                for (let i = 0; i < files.length; i++) {
                    dataTransfer.items.add(files[i]);
                }
                fileInput[0].files = dataTransfer.files;
            } else {
                // Add to existing files
                const currentFiles = fileInput[0].files;
                const dataTransfer = new DataTransfer();
                
                // Add existing files
                for (let i = 0; i < currentFiles.length; i++) {
                    dataTransfer.items.add(currentFiles[i]);
                }
                
                // Add new files
                for (let i = 0; i < files.length; i++) {
                    dataTransfer.items.add(files[i]);
                }
                
                // Update file input
                fileInput[0].files = dataTransfer.files;
            }
            
            // Update preview
            updateFilePreview();
        }
        
        // Update file preview
        function updateFilePreview() {
            const files = fileInput[0].files;
            previewContainer.empty();
            
            if (files.length === 0) {
                return;
            }
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileSize = file.size >= 1048576 
                    ? (file.size / 1048576).toFixed(2) + ' MB' 
                    : (file.size / 1024).toFixed(2) + ' KB';
                
                const fileIcon = getFileIcon(file.name);
                
                const fileItem = `
                    <div class="file-preview-item d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class='${fileIcon} bx-sm text-primary me-2'></i>
                            <span>${file.name}</span>
                            <small class="text-muted ms-2">(${fileSize})</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(this, ${i})">
                            <i class='bx bx-x'></i>
                        </button>
                    </div>
                `;
                previewContainer.append(fileItem);
            }
        }
        
        // Get file icon based on extension
        function getFileIcon(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp'];
            
            if (imageExts.includes(ext)) {
                return 'bx bx-image';
            } else if (ext === 'pdf') {
                return 'bx bx-file-pdf';
            } else if (['doc', 'docx'].includes(ext)) {
                return 'bx bx-file-doc';
            } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
                return 'bx bx-file';
            } else if (['zip', 'rar', '7z'].includes(ext)) {
                return 'bx bx-archive';
            } else if (['ppt', 'pptx'].includes(ext)) {
                return 'bx bx-file-present';
            } else {
                return 'bx bx-file-blank';
            }
        }
        
        // Remove file function (global)
        window.removeFile = function(button, index) {
            const files = fileInput[0].files;
            const dataTransfer = new DataTransfer();
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dataTransfer.items.add(files[i]);
                }
            }
            
            fileInput[0].files = dataTransfer.files;
            updateFilePreview();
        };
        
        // Initialize preview if files exist
        if (fileInput[0].files.length > 0) {
            updateFilePreview();
        }
    });
</script>
@endsection
