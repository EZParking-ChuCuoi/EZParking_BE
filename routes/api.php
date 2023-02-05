<?php

use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\RegisterController;
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

Route::controller(AuthController::class)->prefix("/auth/")->group(function () {
    Route::post("login", "login");
});

Route::controller(RegisterController::class)->prefix("/account/")->group(function () {
    Route::post("register", "register");
    Route::post("confirm-registration","confirmRegistration");
});

