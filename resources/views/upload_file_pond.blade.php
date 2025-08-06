<!DOCTYPE html>
<html>

<head>
    <title>FilePond Upload with Plugins</title>

    <!-- FilePond core CSS -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">

    <!-- Plugin styles -->
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css"
        rel="stylesheet">

    <link href="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.min.css" rel="stylesheet">
</head>

<body>
    <h2>Upload nhiều ảnh có preview + resize</h2>

    <input type="file" name="file[]" id="filepond" multiple data-max-files="5">

    <!-- FilePond JS -->
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js">
    </script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>

    <script>
        // Đăng ký plugin
        FilePond.registerPlugin(
            FilePondPluginFileValidateSize,
            FilePondPluginImagePreview,
            FilePondPluginImageResize,
            FilePondPluginImageEdit
        );
        FilePond.setOptions({
            labelIdle: 'Kéo thả tệp của bạn hoặc <span class="filepond--label-action"> Tìm kiếm </span>',
            labelInvalidField: 'Trường chứa các tệp không hợp lệ',
            labelFileWaitingForSize: 'Đang chờ kích thước',
            labelFileSizeNotAvailable: 'Kích thước không có sẵn',
            labelFileLoading: 'Đang tải',
            labelFileLoadError: 'Lỗi khi tải',
            labelFileProcessing: 'Đang tải lên',
            labelFileProcessingComplete: 'Tải lên thành công',
            labelFileProcessingAborted: 'Đã huỷ tải lên',
            labelFileProcessingError: 'Lỗi khi tải lên',
            labelFileProcessingRevertError: 'Lỗi khi hoàn nguyên',
            labelFileRemoveError: 'Lỗi khi xóa',
            labelTapToCancel: 'nhấn để hủy',
            labelTapToRetry: 'nhấn để thử lại',
            labelTapToUndo: 'nhấn để hoàn tác',
            labelButtonRemoveItem: 'Xoá',
            labelButtonAbortItemLoad: 'Huỷ bỏ',
            labelButtonRetryItemLoad: 'Thử lại',
            labelButtonAbortItemProcessing: 'Hủy bỏ',
            labelButtonUndoItemProcessing: 'Hoàn tác',
            labelButtonRetryItemProcessing: 'Thử lại',
            labelButtonProcessItem: 'Tải lên',
            labelMaxFileSizeExceeded: 'Tập tin quá lớn',
            labelMaxFileSize: 'Kích thước tệp tối đa là {filesize}',
            labelMaxTotalFileSizeExceeded: 'Đã vượt quá tổng kích thước tối đa',
            labelMaxTotalFileSize: 'Tổng kích thước tệp tối đa là {filesize}',
            labelFileTypeNotAllowed: 'Tệp thuộc loại không hợp lệ',
            fileValidateTypeLabelExpectedTypes: 'Kiểu tệp hợp lệ là {allButLastType} hoặc {lastType}',
            imageValidateSizeLabelFormatError: 'Loại hình ảnh không được hỗ trợ',
            imageValidateSizeLabelImageSizeTooSmall: 'Hình ảnh quá nhỏ',
            imageValidateSizeLabelImageSizeTooBig: 'Hình ảnh quá lớn',
            imageValidateSizeLabelExpectedMinSize: 'Kích thước tối thiểu là {minWidth} × {minHeight}',
            imageValidateSizeLabelExpectedMaxSize: 'Kích thước tối đa là {maxWidth} × {maxHeight}',
            imageValidateSizeLabelImageResolutionTooLow: 'Độ phân giải quá thấp',
            imageValidateSizeLabelImageResolutionTooHigh: 'Độ phân giải quá cao',
            imageValidateSizeLabelExpectedMinResolution: 'Độ phân giải tối thiểu là {minResolution}',
            imageValidateSizeLabelExpectedMaxResolution: 'Độ phân giải tối đa là {maxResolution}'
        });
        // Khởi tạo FilePond
        const pond = FilePond.create(document.getElementById('filepond'), {
            imageResizeTargetWidth: 800,
            imageResizeTargetHeight: 800,
            imageResizeMode: 'contain',
            maxFileSize: '10MB',
            allowImageEdit: true,
            instantUpload: false,
            server: {
                process: '/upload-file-pond',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });
    </script>
</body>

</html>
