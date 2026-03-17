<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\EmergencyRequestController;
// تأكد من المسارات دي حسب مشروعك
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Discovery
Route::get('/home-page', [HomeController::class, 'index']); 
Route::get('/hospitals', [HomeController::class, 'allHospitals']);
Route::get('/hospitals/nearest', [HomeController::class, 'findNearest']); // جرب تخليها GET
Route::get('/hospitals/search', [HomeController::class, 'search']); 
Route::get('/hospitals/{hospital}', [HomeController::class, 'show']);

Route::get('/doctors', [DoctorController::class, 'index']); 
Route::get('/doctors/{doctor}', [DoctorController::class, 'show']); 

// Guest Emergency
Route::post('/emergency/quick-send', [EmergencyRequestController::class, 'quickSend']); 


/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Account
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Medical Profile
    Route::get('/medical-profile', [MedicalProfileController::class, 'show']); 
    Route::post('/medical-profile', [MedicalProfileController::class, 'store']); // شيلنا /save عشان الـ RESTful

    // Appointments
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']); // تأكد من اسم الدالة
    Route::post('/appointments/book', [AppointmentController::class, 'store']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    // Emergency
    Route::post('/emergency/send', [EmergencyRequestController::class, 'sendRequest']); 
    Route::get('/emergency/my-requests', [EmergencyRequestController::class, 'userRequests']); 
});