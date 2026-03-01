<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\HospitalAdminController;

// ================= Public Routes (لا تحتاج توكن) =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ================= Home Page Routes =================
Route::get('/hospitals', [HomeController::class, 'allHospitals']);
Route::get('/hospitals/featured', [HomeController::class, 'featuredHospitals']);

// --- السطر الجديد لميزة الطوارئ ---
Route::post('/hospitals/nearest', [HomeController::class, 'findNearest']); 
// ---------------------------------

// ================= Protected Routes (تحتاج Bearer Token) =================
Route::middleware('auth:sanctum')->group(function () {
    
    // عرض بيانات المستخدم
    Route::get('/user', [AuthController::class, 'userProfile']);
    
    // تعديل بيانات المستخدم
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    
    // تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout']);

    // ================= Admin Routes for Hospitals =================
    Route::prefix('admin/hospitals')->group(function () {
        Route::get('/', [HospitalAdminController::class, 'index']);        // جلب كل المستشفيات
        Route::post('/create', [HospitalAdminController::class, 'store']); // إضافة مستشفى جديد
        Route::put('/update/{id}', [HospitalAdminController::class, 'update']); // تعديل مستشفى
        Route::delete('/delete/{id}', [HospitalAdminController::class, 'destroy']); // حذف مستشفى
    });

});