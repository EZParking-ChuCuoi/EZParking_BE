<?php

use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\RegisterController;
use App\Http\Controllers\Account\ForgotPasswordController;
use App\Http\Controllers\OptimizePhotoController;
use App\Http\Controllers\Profile\UserController;
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

Route::post("/test", function (Request $request) {
    return $request->all();
});
Route::controller(AuthController::class)->prefix("/auth/")->group(function () {
    Route::post("login", "login");
});

Route::controller(RegisterController::class)->prefix("/account/")->group(function () {
    Route::post("register", "register");
    Route::post("confirm-registration","confirmRegistration");
});

## reset password
Route::controller(ForgotPasswordController::class)->prefix("/password/")->group(function () {
    Route::post("email", "sendCode");
    Route::post("confirm-reset","checkCode");
    Route::post("reset","resetPassword");
});
## Profile
Route::controller(UserController::class)->prefix("/user/")->group(function () {
    Route::get("{id}", "showProfile");
    Route::post("update/{id}","updateProfile");
    


});
Route::post('upload-image/{id}',[OptimizePhotoController::class,'submit']);

 