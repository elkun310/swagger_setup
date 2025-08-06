<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Files</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
    <meta name="theme-color" content="#0D6EFD">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="{{ asset('assets/css/fontawesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/fontawesome-custom.css') }}" rel="stylesheet">
    <!-- Toastr CSS -->
    <link href="{{ asset('node_modules/toastr/build/toastr.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/upload.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </h5>
                        <a href="{{ route('files.list') }}" class="btn btn-info">
                            <i class="fas fa-list me-2"></i>View Files
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h5 class="upload-text">Drag & Drop files here</h5>
                                <p class="upload-hint">or click to select files</p>
                                <input type="file" class="form-control" id="file" name="files[]" multiple required>
                            </div>
                            <div id="filePreviewList" class="file-preview-list"></div>
                            <div class="upload-progress-container">
                                <div class="overall-progress">
                                    <div class="progress-label">Overall Progress</div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('node_modules/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('node_modules/toastr/build/toastr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Toastr Configuration
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Show session messages
            @if(session('success'))
                toastr.success("{{ session('success') }}", "Success");
            @endif

            @if(session('error'))
                toastr.error("{{ session('error') }}", "Error");
            @endif

            const uploadArea = $('#uploadArea');
            const fileInput = $('#file');
            const form = $('#uploadForm');
            const filePreviewList = $('#filePreviewList');
            const progress = $('.progress');
            const progressBar = $('.progress-bar');
            let totalFiles = 0;
            let uploadedFiles = 0;

            // Handle drag and drop
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            uploadArea.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    fileInput.prop('files', files);
                    handleFiles(files);
                }
            });

            // Handle click to select files
            uploadArea.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.target === this || $(e.target).closest('.upload-area').length) {
                    fileInput.trigger('click');
                }
            });

            fileInput.on('click', function(e) {
                e.stopPropagation();
            });

            fileInput.on('change', function(e) {
                const files = this.files;
                if (files.length) {
                    handleFiles(files);
                }
            });

            function handleFiles(files) {
                filePreviewList.empty();
                totalFiles = files.length;
                uploadedFiles = 0;

                // Create preview items for each file
                Array.from(files).forEach((file, index) => {
                    const fileSize = formatFileSize(file.size);
                    const previewItem = $(`
                        <div class="file-preview-item" data-index="${index}">
                            <div class="file-info">
                                <i class="fas ${getFileIcon(file.name)}"></i>
                                <div class="file-details">
                                    <div class="file-name">${file.name}</div>
                                    <div class="file-size">${fileSize}</div>
                                </div>
                            </div>
                            <div class="file-progress">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                            </div>
                            <button type="button" class="remove-file" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                    filePreviewList.append(previewItem);
                });

                // Show progress container
                $('.upload-progress-container').show();

                // Start uploading files
                uploadFiles(files);
            }

            function uploadFiles(files) {
                Array.from(files).forEach((file, index) => {
                    uploadFile(file, index);
                });
            }

            function uploadFile(file, index) {
                console.log(file, index, 9)
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');
                console.log(formData, 888);

                const previewItem = $(`.file-preview-item[data-index="${index}"]`);
                const fileProgressBar = previewItem.find('.progress-bar');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                        xhr: function() {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener('progress', function(e) {
                                if (e.lengthComputable) {
                                    const percent = Math.round((e.loaded / e.total) * 100);
                                    fileProgressBar.css('width', percent + '%').text(percent + '%');
                                    updateOverallProgress();
                                }
                            });
                            return xhr;
                        },
                        success: function(response) {
                            if (response.success) {
                                uploadedFiles++;
                                previewItem.addClass('upload-success');
                                updateOverallProgress();

                                if (uploadedFiles === totalFiles) {
                                    toastr.success('All files uploaded successfully!', 'Success');
                                    setTimeout(function() {
                                        window.location.href = "{{ route('files.list') }}";
                                    }, 1500);
                                }
                            } else {
                                previewItem.addClass('upload-error');
                                toastr.error(`Error uploading ${file.name}: ${response.message}`, 'Error');
                            }
                        },
                        error: function(xhr) {
                            previewItem.addClass('upload-error');
                            const response = xhr.responseJSON;
                            toastr.error(`Error uploading ${file.name}: ${response?.message || 'Upload failed'}`, 'Error');
                        }
                    });
            }

            function updateOverallProgress() {
                const totalProgress = Array.from(document.querySelectorAll('.file-preview-item .progress-bar'))
                    .reduce((sum, progressBar) => sum + parseFloat(progressBar.style.width), 0);
                const overallPercent = Math.round(totalProgress / totalFiles);
                progressBar.css('width', overallPercent + '%').text(overallPercent + '%');
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function getFileIcon(filename) {
                const ext = filename.split('.').pop().toLowerCase();
                const icons = {
                    pdf: 'fa-file-pdf',
                    doc: 'fa-file-word',
                    docx: 'fa-file-word',
                    xls: 'fa-file-excel',
                    xlsx: 'fa-file-excel',
                    png: 'fa-file-image',
                    jpg: 'fa-file-image',
                    jpeg: 'fa-file-image',
                    gif: 'fa-file-image',
                    mp3: 'fa-file-audio',
                    wav: 'fa-file-audio',
                    mp4: 'fa-file-video',
                    mov: 'fa-file-video',
                    zip: 'fa-file-archive',
                    rar: 'fa-file-archive',
                    txt: 'fa-file-alt'
                };
                return icons[ext] || 'fa-file';
            }

            // Remove file from list
            $(document).on('click', '.remove-file', function() {
                const index = $(this).data('index');
                $(`.file-preview-item[data-index="${index}"]`).remove();

                // Create new FileList without the removed file
                const dt = new DataTransfer();
                const files = fileInput[0].files;
                for (let i = 0; i < files.length; i++) {
                    if (i !== index) {
                        dt.items.add(files[i]);
                    }
                }
                fileInput[0].files = dt.files;
                totalFiles = dt.files.length;

                if (totalFiles === 0) {
                    $('.upload-progress-container').hide();
                    filePreviewList.empty();
                }

                updateOverallProgress();
        });
    });
</script>
</body>
</html>
