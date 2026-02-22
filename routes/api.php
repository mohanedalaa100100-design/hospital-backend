<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ================= Public Routes (لا تحتاج توكن) =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// ================= Protected Routes (تحتاج Bearer Token) =================
Route::middleware('auth:sanctum')->group(function () {
    
    // عرض بيانات المستخدم
    Route::get('/user', [AuthController::class, 'userProfile']);
    
    // تعديل بيانات المستخدم
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    
    // تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout']);
});