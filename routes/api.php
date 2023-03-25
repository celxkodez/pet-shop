<?php

use App\Http\Controllers\V1\Usercontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('user')->controller(Usercontroller::class)->group(function () {
        Route::match(['get', 'head'],'/', 'user'); //auth middleware
        Route::delete('/', 'deleteAuthUser');//auth middleware
        Route::match(['get', 'head'],'/orders', 'userOrders'); //auth middleware
        Route::post('/create', 'store');
        Route::post('/forgot-password', 'forgotPassword');
        Route::match(['get', 'head'],'/logout', 'logout');
        Route::post('/login', 'login');
        Route::post('/reset-password-token', 'resetPasswordToken');
        Route::put('/edit', 'update');
    });
});
