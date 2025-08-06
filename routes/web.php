<?php

use App\Http\Controllers\UploadController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/terms', function () {
//    $arr = [1, 2, 3, 4];
//    foreach ($arr as &$value) {
//        $value *= 2;
//    }
//    dd($arr);

    function &getReference(&$var) {
        return $var;
    }

    $a = 5;
    $b = &getReference($a);
    $b = 10;

    echo $a;
});

Route::get('/upload', function () {
    return view('upload');
})->name('upload.form');

Route::post('/upload', [UploadController::class, 'upload'])->name('upload');

Route::get('/files', [UploadController::class, 'listFiles'])->name('files.list');
Route::get('/files/view/{filename}', [UploadController::class, 'viewFile'])->name('files.view');
Route::delete('/files/{filename}', [UploadController::class, 'deleteFile'])->name('files.delete');

Route::get('test-image-base64', function () {
   return view('test-image-base64');
});
Route::post('upload-base64', [UploadController::class, 'uploadBase64'])->name('upload-base64');

Route::get('/send-sms', function(){
    return
    '<a href="/submit-sms"><button>Send message</button></a>
    ';
});
Route::get('submit-sms', [SmsController::class, 'sendMessage']);

Route::get('/upload-file-pond', function () {
    return view('upload_file_pond');
});

Route::post('/upload-file-pond', [UploadController::class, 'storeFilePond'])->name('upload-file-pond');
