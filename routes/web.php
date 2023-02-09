<?php

use App\Http\Controllers\Profile\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

    $contents = Storage::disk('public')->get('.gitignore'); 
    return $contents;



});


Route::get(
    '/student/{id}',
    [UserController::class, 'showProfile']
);