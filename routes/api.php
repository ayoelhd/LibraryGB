<?php

use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// Staff routes: only a STAFF token is accepted
Route::middleware('auth:staff')->group(function () {
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);
    Route::get('/staff',         [StaffController::class, 'index']);
    Route::get('/staff/{id}',    [StaffController::class, 'show']);
    Route::put('/staff/{id}',    [StaffController::class, 'update']);
    Route::post('/staff',        [StaffController::class, 'store']);
    Route::get('/staff-me',      [StaffAuthController::class, 'me']);
    Route::post('/staff-logout', [StaffAuthController::class, 'logout']);
});

// Customer routes: only a CUSTOMER token is accepted
Route::middleware('auth:customers')->group(function () {
    Route::get('/customer-me',      [CustomerAuthController::class, 'me']);
    Route::post('/customer-logout', [CustomerAuthController::class, 'logout']);
    Route::apiResource('customer', CustomerController::class)
        ->parameter('customer', 'customer');
});

// Public: the two logins
Route::post('/staff/login',    [StaffAuthController::class, 'login']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
