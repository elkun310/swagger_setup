<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File to S3</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    {{-- <img src="{{asset('storage/9FwzqX.jpg')}}" alt=""> --}}
{{-- @dd(storage_path('app/'. 'images.jpg')) --}}
<h2>Upload File to AWS S3</h2>
<div id="progress-container" style="width: 100%; border: 1px solid #ccc; margin-top: 10px;">
  <div id="progress-bar" style="width: 0%; height: 20px; background: green;"></div>
</div>
<p id="progress-text">0%</p>
<input type="file" id="fileInput">
<button id="uploadBtn">Upload</button>

<div id="uploadResult"></div>
<style>
    img.img-thumbnail {
        border: 1px solid #ddd; /* Gray border */
        border-radius: 4px;  /* Rounded border */
        padding: 5px; /* Some padding */
        width: 150px; /* Set a small width */
    }

    /* Add a hover effect (blue shadow) */
    img.img-thumbnail:hover {
        box-shadow: 0 0 2px 1px rgba(0, 140, 186, 0.5);
    }

    #progress-bar {
        width: 0%;
        height: 20px;
        background: linear-gradient(90deg, #28a745, #218838);
        border-radius: 5px;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
        transition: width 0.4s ease;
    }
</style>
<script>
    $(document).ready(function () {
        // let urlUpload = "{{route('upload')}}";
        // console.log(urlUpload);
        
        $('#uploadBtn').click(function () {
            let file = $('#fileInput')[0].files[0];
            if (!file) {
                alert('Please select a file!');
                return;
            }

            // ✅ Bắt đầu hiệu ứng fake progress
            let fakeProgress = 0;
            let progressInterval = setInterval(() => {
                if (fakeProgress < 90) {
                    fakeProgress += 1;
                    $('#progress-bar').css('width', fakeProgress + '%');
                    $('#progress-text').text(fakeProgress + '%');
                } else {
                    clearInterval(progressInterval);
                }
            }, 50);

            let formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: "{{route('upload')}}", // Gửi request đến route Laravel
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // CSRF Token cho bảo mật
                },
                xhr: function () {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            let percent = Math.round((e.loaded / e.total) * 100);
                            // Ghi đè fake progress nếu real progress cao hơn
                            if (percent > fakeProgress) {
                                $('#progress-bar').css('width', percent + '%');
                                $('#progress-text').text(percent + '%');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    clearInterval(progressInterval); // Ngừng fake progress
                    $('#progress-bar').css('width', '100%');
                    $('#progress-text').text('100%');

                    $('#uploadResult').html(
                        `<p>File uploaded successfully:</p>
                        <a href="${response.url}" target="_blank">${response.url}</a>
                        <br><img class="img-uploaded img-thumbnail" src="${response.url}" alt="Uploaded Image">`
                    );
                },
                error: function (xhr) {
                    clearInterval(progressInterval); // Dừng fake nếu lỗi
                    $('#progress-bar').css('width', '0%');
                    $('#progress-text').text('0%');

                    let errMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Upload failed!';
                    $('#uploadResult').html(`<p style="color:red;">${errMsg}</p>`);
                }
            });
        });
    });
</script>

</body>
</html>
