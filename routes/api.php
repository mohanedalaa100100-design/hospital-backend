<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\AiDiagnosisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. مسارات المصادقة (Auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// 2. مسارات الصفحة الرئيسية والبيانات العامة
Route::get('/home-page', [HomeController::class, 'index']); 
Route::get('/all-specialties', [HomeController::class, 'allSpecialties']); 
Route::get('/hospitals', [HomeController::class, 'allHospitals']); 
Route::get('/hospitals/nearest', [HomeController::class, 'findNearest']); 
Route::get('/hospitals/search', [HomeController::class, 'search']); 
Route::get('/hospitals/{id}', [HomeController::class, 'show']);

// 3. عرض الدكاترة وبياناتهم
Route::get('/doctors', [DoctorController::class, 'index']); 
Route::get('/doctors/{id}', [DoctorController::class, 'show']); 

// 4. طلب استغاثة سريع (يدعم الزائر والمسجل)
Route::post('/emergency/quick-send', [EmergencyRequestController::class, 'quickSend']); 

// 5. المسارات المحمية (تطلب تسجيل دخول)
Route::middleware('auth:sanctum')->group(function () {
    
    // بروفايل المستخدم
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // الملف الطبي
    Route::get('/medical-profile', [MedicalProfileController::class, 'show']); 
    Route::post('/medical-profile', [MedicalProfileController::class, 'store']); 

    // مواعيد الحجز
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']); 
    // تم استخدام السطر اللي طلبته هنا ليكون محمي بـ Sanctum
    Route::post('/appointments/book', [AppointmentController::class, 'store']); 
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']); 

    // استغاثات المستخدم المسجل
    Route::get('/emergency/my-requests', [EmergencyRequestController::class, 'userRequests']); 

    // تشخيص الذكاء الاصطناعي (AI Diagnosis)
    Route::post('/ai-diagnosis', [AiDiagnosisController::class, 'diagnose']); 
});