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
Route::get('/specialties', [HomeController::class, 'allSpecialties']); 
Route::get('/hospitals', [HomeController::class, 'allHospitals']); // ميثود جديدة
Route::get('/hospitals/nearest', [HomeController::class, 'findNearest']); 
Route::get('/hospitals/search', [HomeController::class, 'search']); 
Route::get('/hospitals/{id}', [HomeController::class, 'show']);

// Doctors
Route::get('/doctors', [DoctorController::class, 'index']); 
Route::get('/doctors/{id}', [DoctorController::class, 'show']); 


Route::post('/emergency/quick-send', [EmergencyRequestController::class, 'quickSend']); 

/*
|--------------------------------------------------------------------------
| Protected Routes (الروابط المحمية)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile & Logout
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Medical Profile
    Route::get('/medical-profile', [MedicalProfileController::class, 'show']); 
    Route::post('/medical-profile', [MedicalProfileController::class, 'store']); 

    // Appointments
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']); 
    Route::post('/appointments/book', [AppointmentController::class, 'store']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']); // ميثود جديدة

    // Emergency Status (طلبات الطوارئ الخاصة باليوزر)
    Route::get('/emergency/my-requests', [EmergencyRequestController::class, 'userRequests']); 
});