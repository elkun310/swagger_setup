<?php

use App\Http\Controllers\api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/home', [App\Http\Controllers\api\HomeController::class, 'index']);
    Route::get('/terms', [App\Http\Controllers\api\TermController::class, 'index']);
    Route::post('/terms', [App\Http\Controllers\api\TermController::class, 'store']);
    Route::get('/terms/{id}', [App\Http\Controllers\api\TermController::class, 'show']);
    Route::put('/terms/{id}', [App\Http\Controllers\api\TermController::class, 'update']);
    Route::delete('terms/{id}', [App\Http\Controllers\api\TermController::class, 'destroy']);
});

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/upload', [UploadController::class, 'upload']);
Route::get('/files', [UploadController::class, 'listFiles']);
Route::get('/files/{filename}', [UploadController::class, 'viewFile']);
Route::delete('/files/{filename}', [UploadController::class, 'deleteFile']);
