<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\yandexAuthController;
use App\Http\Controllers\googleAuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth-yandex/', [yandexAuthController::class, 'auth']);
Route::get('/login_ya/', [yandexAuthController::class, 'handleServiceResponse']);

Route::post('/auth-google/', [googleAuthController::class, 'auth']);
Route::get('/login_google/', [googleAuthController::class, 'handleServiceResponse']);
