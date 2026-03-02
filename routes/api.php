<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\HospitalAdminController;

// ================= Public Routes =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ================= Home Page API (الرابط الشامل لكل الداتا) =================
// ده الرابط اللي هينادي عليه زميلك بتاع الـ Front-end
Route::get('/home-page', [HomeController::class, 'index']); 

// ================= Hospital Specific Routes =================
Route::get('/hospitals', [HomeController::class, 'allHospitals']);
Route::get('/hospitals/featured', [HomeController::class, 'featuredHospitals']);
Route::post('/hospitals/nearest', [HomeController::class, 'findNearest']); 

// ================= Protected Routes (auth:sanctum) =================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin Routes
    Route::prefix('admin/hospitals')->group(function () {
        Route::get('/', [HospitalAdminController::class, 'index']);
        Route::post('/create', [HospitalAdminController::class, 'store']);
        Route::put('/update/{id}', [HospitalAdminController::class, 'update']);
        Route::delete('/delete/{id}', [HospitalAdminController::class, 'destroy']);
    });
});