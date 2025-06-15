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
    dd('terms');
})->middleware('auth');

Route::get('/upload', function () {
    return view('upload');
});

Route::post('/upload', [UploadController::class, 'upload'])->name('upload');

Route::get('/send-sms', function(){
    return 
    '<a href="/submit-sms"><button>Send message</button></a>
    ';
});
Route::get('submit-sms', [SmsController::class, 'sendMessage']);