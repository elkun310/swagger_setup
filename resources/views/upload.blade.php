<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File to S3</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<h2>Upload File to AWS S3</h2>
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
</style>
<script>
    $(document).ready(function () {
        $('#uploadBtn').click(function () {
            let file = $('#fileInput')[0].files[0];
            if (!file) {
                alert('Please select a file!');
                return;
            }

            let formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: '/upload', // Gửi request đến route Laravel
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // CSRF Token cho bảo mật
                },
                success: function (response) {
                    $('#uploadResult').html(
                        `<p>File uploaded successfully:</p>
                             <a href="${response.url}" target="_blank">${response.url}</a>`
                    ).append('<img class="img-uploaded img-thumbnail" src="" alt="Uploaded Image">');

                    $('.img-uploaded').attr('src', response.url);
                },
                error: function (xhr) {
                    let errMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Upload failed!';
                    $('#uploadResult').html(`<p style="color:red;">${errMsg}</p>`);
                }
            });
        });
    });
</script>

</body>
</html>
