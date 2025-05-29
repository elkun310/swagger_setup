<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    
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
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/file-manager.css') }}" rel="stylesheet">
    <style>
        #pdfViewer {
            width: 100%;
            height: 70vh;
            border: none;
            display: none;
        }
        #previewImage {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
        }
        .preview-modal .modal-dialog {
            max-width: 80%;
            margin: 1.75rem auto;
        }
        @media (min-width: 768px) {
            .preview-modal .modal-dialog {
                max-width: 800px;
            }
        }
        .preview-modal .modal-body {
            padding: 1rem;
            background: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Uploaded Files
                        </h5>
                        <a href="{{ route('upload.form') }}" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload New File
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($filesData->count() > 0)
                            <div class="mass-delete-header">
                                <div class="mass-delete-actions">
                                    <label class="file-checkbox-label">
                                        <input type="checkbox" id="selectAll" class="file-checkbox">
                                        <span>Select All</span>
                                    </label>
                                    <span class="selected-count">0 selected</span>
                                </div>
                                <button type="button" class="delete-selected-btn" data-bs-toggle="modal" data-bs-target="#massDeleteModal">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Delete Selected</span>
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-responsive-stack">
                                    <thead>
                                        <tr>
                                            <th width="40px"></th>
                                            <th>File Name</th>
                                            <th>Type</th>
                                            <th>Size</th>
                                            <th>Upload Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($filesData as $file)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="file-checkbox" data-file-name="{{ $file['name'] }}">
                                                </td>
                                                <td data-label="File Name" class="file-name">{{ $file['name'] }}</td>
                                                <td data-label="Type" class="file-type">{{ strtoupper($file['extension']) ?: 'Unknown' }}</td>
                                                <td data-label="Size" class="file-size">{{ $file['size_text'] }}</td>
                                                <td data-label="Upload Date" class="file-date">{{ date('Y-m-d H:i:s', $file['created_at']) }}</td>
                                                <td data-label="Actions">
                                                    <div class="action-buttons">
                                                        @php
                                                            $isImage = in_array(strtolower($file['extension']), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                            $isPdf = strtolower($file['extension']) === 'pdf';
                                                            $isAudio = in_array(strtolower($file['extension']), ['mp3', 'wav', 'ogg', 'm4a', 'aac']);
                                                            $isVideo = in_array(strtolower($file['extension']), ['mp4', 'webm', 'ogg', 'mov']);
                                                        @endphp
                                                        
                                                        @if($isImage || $isPdf || $isAudio || $isVideo)
                                                            <button type="button" 
                                                                class="btn btn-view view-file" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#previewModal"
                                                                data-file-url="{{ route('files.view', ['filename' => $file['name']]) }}"
                                                                data-file-name="{{ $file['name'] }}"
                                                                data-file-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : ($isAudio ? 'audio' : 'video')) }}">
                                                                <i class="fas fa-eye"></i>
                                                                <span>View</span>
                                                            </button>
                                                        @endif
                                                        <a href="{{ route('files.view', ['filename' => $file['name']]) }}" 
                                                           class="btn btn-download" 
                                                           download>
                                                            <i class="fas fa-download"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-delete delete-file" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal"
                                                                data-file-name="{{ $file['name'] }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                            <span>Delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No files have been uploaded yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade preview-modal" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-file me-2"></i>File Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" alt="Preview" id="previewImage" style="display: none;">
                    <iframe id="pdfViewer" src="" style="display: none;"></iframe>
                    <audio id="audioPlayer" controls style="display: none; width: 100%;">
                        Your browser does not support the audio element.
                    </audio>
                    <video id="videoPlayer" controls style="display: none; width: 100%; max-height: 70vh;">
                        Your browser does not support the video element.
                    </video>
                </div>
                <div class="modal-footer">
                    <a href="" class="btn btn-success" id="downloadBtn" download>
                        <i class="fas fa-download me-2"></i>Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade delete-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Delete Confirmation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this file? This action cannot be undone.</p>
                    <div class="file-info">
                        <div class="file-info-label">File to be deleted:</div>
                        <div id="deleteFileName"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mass Delete Confirmation Modal -->
    <div class="modal fade delete-modal" id="massDeleteModal" tabindex="-1" aria-labelledby="massDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="massDeleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Delete Selected Files
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete the selected files? This action cannot be undone.</p>
                    <div class="selected-files-list mt-3">
                        <div class="file-info-label">Files to be deleted:</div>
                        <ul id="selectedFilesList" class="list-unstyled mb-0 mt-2"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmMassDelete">
                        <i class="fas fa-trash-alt me-2"></i>Delete Files
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('node_modules/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('node_modules/toastr/build/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/pdfjs/build/pdf.mjs') }}" type="module"></script>
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

            // Show notifications for session messages
            @if(session('success'))
                toastr.success("{{ session('success') }}", "Success");
            @endif

            @if(session('error'))
                toastr.error("{{ session('error') }}", "Error");
            @endif

            // Preview Modal
            const previewModal = document.getElementById('previewModal');
            const previewImage = document.getElementById('previewImage');
            const pdfViewer = document.getElementById('pdfViewer');
            const audioPlayer = document.getElementById('audioPlayer');
            const videoPlayer = document.getElementById('videoPlayer');
            const downloadBtn = document.getElementById('downloadBtn');
            const modalTitle = document.getElementById('previewModalLabel');

            // Reset all media players when modal is hidden
            previewModal.addEventListener('hidden.bs.modal', function () {
                previewImage.style.display = 'none';
                pdfViewer.style.display = 'none';
                audioPlayer.style.display = 'none';
                videoPlayer.style.display = 'none';
                audioPlayer.pause();
                videoPlayer.pause();
                audioPlayer.src = '';
                videoPlayer.src = '';
            });

            $('.view-file').on('click', function() {
                const fileUrl = $(this).data('file-url');
                const fileName = $(this).data('file-name');
                const fileType = $(this).data('file-type');

                // Reset visibility
                previewImage.style.display = 'none';
                pdfViewer.style.display = 'none';
                audioPlayer.style.display = 'none';
                videoPlayer.style.display = 'none';

                // Update modal content based on file type
                switch(fileType) {
                    case 'image':
                        previewImage.src = fileUrl;
                        previewImage.style.display = 'block';
                        modalTitle.innerHTML = '<i class="fas fa-file-image me-2"></i>Image Preview: ' + fileName;
                        break;
                    case 'pdf':
                        pdfViewer.src = fileUrl;
                        pdfViewer.style.display = 'block';
                        modalTitle.innerHTML = '<i class="fas fa-file-pdf me-2"></i>PDF Preview: ' + fileName;
                        break;
                    case 'audio':
                        audioPlayer.src = fileUrl;
                        audioPlayer.style.display = 'block';
                        modalTitle.innerHTML = '<i class="fas fa-file-audio me-2"></i>Audio Preview: ' + fileName;
                        break;
                    case 'video':
                        videoPlayer.src = fileUrl;
                        videoPlayer.style.display = 'block';
                        modalTitle.innerHTML = '<i class="fas fa-file-video me-2"></i>Video Preview: ' + fileName;
                        break;
                }

                downloadBtn.href = fileUrl;
            });

            previewImage.addEventListener('error', function() {
                this.src = '';
                this.alt = 'Error loading image';
                this.classList.add('text-danger');
                toastr.error('Failed to load image preview', 'Error');
            });

            // Delete Modal
            $('.delete-file').on('click', function() {
                const fileName = $(this).data('file-name');
                $('#deleteFileName').text(fileName);
                const encodedFileName = encodeURIComponent(fileName);
                $('#deleteForm').attr('action', `{{ url('/files') }}/${encodedFileName}`);
            });

            // Handle delete form submission
            $('#deleteForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const action = form.attr('action');

                $.ajax({
                    url: action,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message || 'An error occurred', 'Error');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Failed to delete file', 'Error');
                    },
                    complete: function() {
                        $('#deleteModal').modal('hide');
                    }
                });
            });

            // Mass Delete Functionality
            const selectAll = $('#selectAll');
            const fileCheckboxes = $('.file-checkbox:not(#selectAll)');
            const deleteSelectedBtn = $('.delete-selected-btn');
            const selectedCount = $('.selected-count');
            const selectedFilesList = $('#selectedFilesList');

            function updateSelectedCount() {
                const count = $('.file-checkbox:checked:not(#selectAll)').length;
                selectedCount.text(count + ' selected');
                selectedCount.toggleClass('visible', count > 0);
                deleteSelectedBtn.toggleClass('visible', count > 0);
            }

            function updateSelectedFilesList() {
                selectedFilesList.empty();
                $('.file-checkbox:checked:not(#selectAll)').each(function() {
                    const fileName = $(this).data('file-name');
                    selectedFilesList.append(`<li><i class="fas fa-file me-2"></i>${fileName}</li>`);
                });
            }

            selectAll.on('change', function() {
                fileCheckboxes.prop('checked', this.checked);
                updateSelectedCount();
                updateSelectedFilesList();
            });

            fileCheckboxes.on('change', function() {
                const allChecked = fileCheckboxes.length === $('.file-checkbox:checked:not(#selectAll)').length;
                selectAll.prop('checked', allChecked);
                updateSelectedCount();
                updateSelectedFilesList();
            });

            $('#confirmMassDelete').on('click', function() {
                const selectedFiles = [];
                $('.file-checkbox:checked:not(#selectAll)').each(function() {
                    selectedFiles.push($(this).data('file-name'));
                });

                if (selectedFiles.length === 0) return;

                const deleteRequests = selectedFiles.map(fileName => {
                    return $.ajax({
                        url: `{{ url('/files') }}/${encodeURIComponent(fileName)}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        }
                    });
                });

                Promise.all(deleteRequests)
                    .then(responses => {
                        const successCount = responses.filter(r => r.success).length;
                        if (successCount === selectedFiles.length) {
                            toastr.success(`Successfully deleted ${successCount} files`, 'Success');
                        } else {
                            toastr.warning(`Deleted ${successCount} out of ${selectedFiles.length} files`, 'Warning');
                        }
                        setTimeout(() => window.location.reload(), 1500);
                    })
                    .catch(error => {
                        toastr.error('Error deleting some files', 'Error');
                    })
                    .finally(() => {
                        $('#massDeleteModal').modal('hide');
                    });
            });
        });
    </script>
</body>
</html> 