<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/home', [App\Http\Controllers\api\HomeController::class, 'index']);
Route::get('terms', [App\Http\Controllers\api\TermController::class, 'index']);
Route::post('terms', [App\Http\Controllers\api\TermController::class, 'store']);
Route::get('terms/{id}', [App\Http\Controllers\api\TermController::class, 'show']);
Route::put('terms/{id}', [App\Http\Controllers\api\TermController::class, 'update']);
Route::delete('terms/{id}', [App\Http\Controllers\api\TermController::class, 'destroy']);
