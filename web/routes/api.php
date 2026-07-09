<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ServiceApiController;
use App\Http\Controllers\Api\V1\EmployeeApiController;
use App\Http\Controllers\Api\V1\MobileApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::get('/services', [ServiceApiController::class, 'index']);
    Route::get('/employees', [EmployeeApiController::class, 'index']);

    // Mobile App endpoints
    Route::prefix('mobile')->group(function () {
        Route::post('/login', [MobileApiController::class, 'login']);
        Route::post('/register', [MobileApiController::class, 'register']);
        Route::get('/store', [MobileApiController::class, 'showDocument'])->defaults('collection', 'store')->defaults('id', 'main');
        Route::post('/query', [MobileApiController::class, 'query']);
        Route::get('/barber-availability', [MobileApiController::class, 'query'])->defaults('collection', 'barberAvailability');
        Route::get('/services', [MobileApiController::class, 'query'])->defaults('collection', 'services');
        Route::get('/barbers', [MobileApiController::class, 'query'])->defaults('collection', 'barbers');
        Route::get('/appointments/{appointment}/ical', [MobileApiController::class, 'generateIcal']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [MobileApiController::class, 'showDocument'])->defaults('collection', 'users')->defaults('id', 'me');
            Route::post('/me/update', [MobileApiController::class, 'updateProfile']);
            Route::post('/me/update-password', [MobileApiController::class, 'updatePassword']);
            Route::post('/me/upload-photo', [MobileApiController::class, 'uploadPhoto']);
            Route::post('/save-token', [MobileApiController::class, 'saveToken']);
            Route::post('/reviews', [MobileApiController::class, 'createReview']);
            Route::post('/validate-coupon', [MobileApiController::class, 'validateCoupon']);
            
            Route::get('/document/{collection}/{id}', [MobileApiController::class, 'showDocument']);
            Route::post('/document/{collection}', [MobileApiController::class, 'addDocument']);
            Route::put('/document/{collection}/{id}', [MobileApiController::class, 'updateDocument']);
            Route::delete('/document/{collection}/{id}', [MobileApiController::class, 'deleteDocument']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    });
});
