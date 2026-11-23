<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingsController;
use App\Http\Controllers\CategoryController;
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

    // Only staff can create, update and delete categories and books
    Route::post('/category',              [CategoryController::class, 'store']);
    Route::put('/category/{category}',    [CategoryController::class, 'update']);
    Route::delete('/category/{category}', [CategoryController::class, 'destroy']);
    Route::post('/book',          [BookController::class, 'store']);
    Route::put('/book/{book}',    [BookController::class, 'update']);
    Route::delete('/book/{book}', [BookController::class, 'destroy']);

    // Staff mark a borrowed book as returned (takes a customer_id, so staff only)
    Route::apiResource('customer', CustomerController::class)
        ->parameter('customer', 'customer');
    Route::put('/borrow', [BorrowingsController::class, 'markAsReturned']);
    Route::get('/borrow/all', [BorrowingsController::class, 'all']);
});

// Customer routes: only a CUSTOMER token is accepted
Route::middleware('auth:customers')->group(function () {
    Route::get('/customer-me',      [CustomerAuthController::class, 'me']);
    Route::post('/customer-logout', [CustomerAuthController::class, 'logout']);
    

    // Borrowings: the customer comes from the token
    Route::post('/borrow',        [BorrowingsController::class, 'borrow']);
    Route::get('/borrow',         [BorrowingsController::class, 'index']);
    Route::delete('/borrow/{id}', [BorrowingsController::class, 'destroy']);
});

// Reading categories and books: ANY valid token (staff or customer)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/category',            [CategoryController::class, 'index']);
    Route::get('/category/{category}', [CategoryController::class, 'show']);
    Route::get('/book/{book}',         [BookController::class, 'show']);
    Route::get('/book',                [BookController::class, 'index']);
});

// Public: the two logins
Route::post('/staff/login',    [StaffAuthController::class, 'login']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
