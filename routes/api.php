<?php

use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\RegisterController;
use App\Http\Controllers\Account\ForgotPasswordController;
use App\Http\Controllers\ParKingLot\BlockParkingCarController;
use App\Http\Controllers\ParKingLot\BookingController;
use App\Http\Controllers\ParKingLot\ParKingLotController;
use App\Http\Controllers\Profile\OwnerController;
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
    Route::post("confirm-registration", "confirmRegistration");
});

## reset password
Route::controller(ForgotPasswordController::class)->prefix("/password/")->group(function () {
    Route::post("email", "sendCode");
    Route::post("confirm-reset", "checkCode");
    Route::post("reset", "resetPassword");
});
## Profile
Route::controller(UserController::class)->prefix("/user/")->group(function () {
    Route::get("{id}/info", "showProfile");
    Route::get("{id}/role", "getRole");
    Route::get("", "getAllUser");
    Route::put("update/{id}", "updateProfile");

});

Route::controller(ParKingLotController::class)->prefix("/parking-lot/")->group(function () {
    Route::get("", "index");
    Route::get("{id}/info", "getInfoParkingLot");
    Route::get("{id}/info/price", "getPriceOfParkingLot");
    Route::get("{id}/info/comment", "showCommentOfParking");
    Route::get("location", "showParkingLotNearLocation");
    Route::post("create", "createParkingLot");
});
Route::controller(BlockParkingCarController::class)->prefix("/parking-lot/")->group(function () {
    Route::get("{id}/blocks", "getBlock");
    Route::get("parking-blocks/{id}/slots", "getSlotStatusByBookingDateTime");
    Route::get("{id}/slots", "getSlotStatusByBookingDateTime2");
    Route::post("block/create", "createBlockSlot");
});

Route::controller(BookingController::class)->prefix("/booking/")->group(function () {
    Route::get("slots", "getSlotsByIdWithBlockName");
    Route::post("", "bookParkingLot");
    Route::get("show", "getDetailQRcode");
});

Route::controller(OwnerController::class)->prefix("/owner/")->group(function () {
    Route::put("create/{id}", "becomeSpaceOwner");
});