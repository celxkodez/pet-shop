<?php

use App\Http\Controllers\V1\AdminController;
use App\Http\Controllers\V1\OrderController;
use App\Http\Controllers\V1\UserController;
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

Route::prefix('v1')
    ->group(function () {
        //User EndPoints
        Route::prefix('user')
            ->controller(UserController::class)
            ->middleware('auth:api')
            ->group(function () {

                Route::withoutMiddleware('auth:api')->group(function () {

                    Route::post('/create', 'store');
                    Route::post('/forgot-password', 'forgotPassword');
                    Route::post('/login', 'login');
                    Route::post('/reset-password-token', 'resetPasswordToken');
                });

                Route::match(['get', 'head'], '/', 'user');
                Route::delete('/', 'destroy');
                Route::match(['get', 'head'], '/orders', 'userOrders');
                Route::match(['get', 'head'],'/logout', 'logout');
                Route::put('/edit', 'update');
        });

        //Admin EndPoints
        Route::prefix('admin')
            ->controller(AdminController::class)
            ->middleware(['admin'])
            ->group(function () {

                Route::withoutMiddleware(['admin'])->group(function () {

                    Route::post('/create', 'createAdmin');
                    Route::post('/login', 'loginAdmin');
                    Route::match(['get', 'head'],'/logout', 'logout');
                });

                Route::delete('/user-delete/{uuid}', 'deleteUser');
                Route::match(['get', 'head'], '/user-listing', 'userListing');
                Route::put('/user-edit/{uuid}', 'userEdit');
            });

        //Order EndPoints
        Route::prefix('order')
            ->controller(OrderController::class)
            ->middleware('auth:api')
            ->group(function () {
                Route::post('/create', 'store');
                Route::match(['get', 'head'], '/{uuid}', 'show');
                Route::match(['put', 'patch'], '/{uuid}', 'update');
                Route::delete('/{uuid}', 'destroy');
                Route::match(['get', 'head'], '/{uuid}/download', 'download');
                Route::match(['get', 'head'],'/', 'index');
                Route::match(['get', 'head'], '/dashboard', 'dashboard');
                Route::match(['get', 'head'], '/shipment-locator', 'shipmentLocator');
            });
});
