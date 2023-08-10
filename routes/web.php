<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\yandexAuthController;
use App\Http\Controllers\googleAuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth');
});


Route::get('/auth-yandex/', [yandexAuthController::class, 'auth'])->name('auth-yandex');
Route::get('/login_ya/', [yandexAuthController::class, 'handleServiceResponse']);

Route::get('/auth-google/', [googleAuthController::class, 'auth'])->name('auth-google');
Route::get('/login_google/', [googleAuthController::class, 'handleServiceResponse']);
