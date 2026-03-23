<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;

/*
|--------------------------------------------------------------------------
| Public Routes (الروابط المتاحة للكل)
|--------------------------------------------------------------------------
*/

// Auth - تسجيل الدخول والحماية
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Discovery - روابط الصفحة الرئيسية والبحث (مهمة للـ Figma)
Route::get('/home-page', [HomeController::class, 'index']); 
Route::get('/specialties', [HomeController::class, 'allSpecialties']); // ضفت لك ده عشان الـ 404
Route::get('/hospitals', [HomeController::class, 'allHospitals']);
Route::get('/hospitals/nearest', [HomeController::class, 'findNearest']); 
Route::get('/hospitals/search', [HomeController::class, 'search']); 
Route::get('/hospitals/{hospital}', [HomeController::class, 'show']);

// Doctors
Route::get('/doctors', [DoctorController::class, 'index']); 
Route::get('/doctors/{doctor}', [DoctorController::class, 'show']); 

// Guest Emergency - طوارئ سريعة
Route::post('/emergency/quick-send', [EmergencyRequestController::class, 'quickSend']); 

/*
|--------------------------------------------------------------------------
| Protected Routes (الروابط اللي محتاجة Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Account & Logout
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Medical Profile (التاريخ المرضي)
    Route::get('/medical-profile', [MedicalProfileController::class, 'show']); 
    Route::post('/medical-profile', [MedicalProfileController::class, 'store']); 

    // Appointments (المواعيد)
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']); 
    Route::post('/appointments/book', [AppointmentController::class, 'store']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    // Emergency Requests
    Route::post('/emergency/send', [EmergencyRequestController::class, 'sendRequest']); 
    Route::get('/emergency/my-requests', [EmergencyRequestController::class, 'userRequests']); 
});