<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\EmergencyRequestController; // المُنقذ الجديد
use App\Http\Controllers\Admin\HospitalAdminController;
use App\Http\Controllers\Admin\SpecialtyAdminController;
use App\Http\Controllers\Admin\MedicalServiceAdminController;

// ================= Public Routes =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ================= Home & Search API =================
Route::get('/home-page', [HomeController::class, 'index']); 
Route::get('/hospitals', [HomeController::class, 'allHospitals']);
Route::get('/hospitals/featured', [HomeController::class, 'index']); 

Route::post('/hospitals/nearest', [HomeController::class, 'findNearest']); 
Route::get('/hospitals/search', [HomeController::class, 'search']); 
Route::get('/hospitals/{id}', [HomeController::class, 'show']); 

// ================= Protected Routes (auth:sanctum) =================
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. User Account Profile
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. Mini Medical Profile (بيانات شاشات الـ Yes والـ No)
    Route::get('/medical-profile', [MedicalProfileController::class, 'show']); 
    Route::post('/medical-profile/save', [MedicalProfileController::class, 'store']); 

    // 3. Emergency Requests (قلب المشروع - زرار Continue Emergency)
    Route::post('/emergency/send', [EmergencyRequestController::class, 'sendRequest']); // إرسال طلب استغاثة لأقرب مستشفى
    Route::get('/emergency/my-requests', [EmergencyRequestController::class, 'userRequests']); // تاريخ طلبات المريض

    // 4. Admin - Hospitals Management
    Route::prefix('admin/hospitals')->group(function () {
        Route::get('/', [HospitalAdminController::class, 'index']);
        Route::post('/create', [HospitalAdminController::class, 'store']);
        Route::post('/update/{id}', [HospitalAdminController::class, 'update']); 
        Route::delete('/delete/{id}', [HospitalAdminController::class, 'destroy']);
    });

    // 5. Admin - Specialties Management
    Route::prefix('admin/specialties')->group(function () {
        Route::get('/', [SpecialtyAdminController::class, 'index']);
        Route::post('/create', [SpecialtyAdminController::class, 'store']);
        Route::delete('/delete/{id}', [SpecialtyAdminController::class, 'destroy']);
    });

    // 6. Admin - Medical Services Management
    Route::prefix('admin/services')->group(function () {
        Route::get('/', [MedicalServiceAdminController::class, 'index']);
        Route::post('/create', [MedicalServiceAdminController::class, 'store']);
        Route::delete('/delete/{id}', [MedicalServiceAdminController::class, 'destroy']);
    });
});