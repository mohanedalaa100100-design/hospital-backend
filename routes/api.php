<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\AiDiagnosisController;
use App\Http\Controllers\Admin\HospitalAdminController;
use App\Http\Controllers\Admin\AppointmentAdminController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


Route::get('/home-page', [HomeController::class, 'index']); 
Route::get('/all-specialties', [HomeController::class, 'allSpecialties']); 


Route::get('/specialties/{id}', [HomeController::class, 'showSpecialty']);


Route::prefix('hospitals')->group(function () {
    Route::get('/', [HomeController::class, 'allHospitals']); 
    Route::get('/nearest', [HomeController::class, 'findNearest']); 
    Route::get('/search', [HomeController::class, 'search']);
    Route::get('/{id}', [HomeController::class, 'show']);
});


Route::get('/search', [HomeController::class, 'search']); 


Route::get('/doctors', [DoctorController::class, 'index']); 
Route::get('/doctors/{id}', [DoctorController::class, 'show']); 


Route::post('/emergency/quick-send', [EmergencyRequestController::class, 'quickSend']); 


Route::post('/ai-diagnosis', [AiDiagnosisController::class, 'diagnose']); 


Route::middleware('auth:sanctum')->group(function () {
    
    
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    
    Route::get('/medical-profile', [MedicalProfileController::class, 'show']); 
    Route::post('/medical-profile', [MedicalProfileController::class, 'store']); 

    
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']); 
    Route::post('/appointments/book', [AppointmentController::class, 'store']); 
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']); 

    
    Route::get('/emergency/my-requests', [EmergencyRequestController::class, 'userRequests']); 

    
    // Admin
    
    // Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    
    // Hospital Management
    Route::prefix('admin/hospitals')->group(function () {
        Route::get('/', [HospitalAdminController::class, 'index']);
        Route::post('/', [HospitalAdminController::class, 'store']);
        Route::put('{id}', [HospitalAdminController::class, 'update']);
        Route::delete('{id}', [HospitalAdminController::class, 'destroy']);
        Route::get('{id}', [HospitalAdminController::class, 'show']);
    });
    
    // Appointments Management
    Route::prefix('admin/appointments')->group(function () {
        Route::get('/', [AppointmentAdminController::class, 'index']);
        Route::put('{id}', [AppointmentAdminController::class, 'update']);
        Route::delete('{id}', [AppointmentAdminController::class, 'destroy']);
        Route::get('{id}', [AppointmentAdminController::class, 'show']);
    });
});