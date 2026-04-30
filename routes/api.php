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
use App\Models\Clinic; 

// 1. روابط المصادقة
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// 2. الصفحة الرئيسية والتخصصات
Route::get('/home-page', [HomeController::class, 'index']); 
Route::get('/all-specialties', [HomeController::class, 'allSpecialties']); 
Route::get('/specialties/{id}', [HomeController::class, 'showSpecialty']);

// 3. المستشفيات
Route::prefix('hospitals')->group(function () {
    Route::get('/nearest', [HomeController::class, 'findNearest']); 
    Route::get('/search', [HomeController::class, 'search']);
    Route::get('/', [HomeController::class, 'allHospitals']); 
    Route::get('/{id}', [HomeController::class, 'show']);
});

// 4. الدكاترة
Route::prefix('doctors')->group(function () {
    Route::get('/', [DoctorController::class, 'index']); 
    Route::get('/{id}', [DoctorController::class, 'show']);
});

// ====== 5. العيادات (تم التعديل ليكون 20 عيادة في الصفحة) ======
Route::get('/clinics', function () {
    // غيرنا الرقم لـ 20 عشان نقلل عدد الصفحات الكلي
    $clinics = Clinic::with(['hospital', 'specialty'])->paginate(20);
    
    return response()->json([
        'status' => true,
        'data'   => $clinics
    ]);
});

// 6. خدمات الطوارئ والتشخيص الذكي (عام)
Route::prefix('emergency')->group(function () {
    Route::post('/quick-send', [EmergencyRequestController::class, 'quickSend']);
});

Route::post('/ai-diagnosis', [AiDiagnosisController::class, 'diagnose']);

// 7. الروابط المحمية (تتطلب Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // ====== بروفايل المستخدم ======
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ====== السجل الطبي ======
    Route::prefix('medical-profile')->group(function () {
        Route::get('/', [MedicalProfileController::class, 'show']);
        Route::post('/', [MedicalProfileController::class, 'store']);
    });

    // ====== المواعيد والحجوزات ======
    Route::prefix('appointments')->group(function () {
        Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
        Route::post('/book', [AppointmentController::class, 'store']);
        Route::post('/{id}/pay', [AppointmentController::class, 'processPayment']);
        Route::delete('/{id}', [AppointmentController::class, 'destroy']);
    });

    // ====== طلبات الطوارئ ======
    Route::prefix('emergency')->group(function () {
        Route::get('/my-requests', [EmergencyRequestController::class, 'userRequests']);
    });

    // Admin Routes (لوحة التحكم)
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        Route::prefix('hospitals')->group(function () {
            Route::get('/', [HospitalAdminController::class, 'index']);
            Route::post('/', [HospitalAdminController::class, 'store']);
            Route::put('{id}', [HospitalAdminController::class, 'update']);
            Route::delete('{id}', [HospitalAdminController::class, 'destroy']);
            Route::get('{id}', [HospitalAdminController::class, 'show']);
        });
        
        Route::prefix('appointments')->group(function () {
            Route::get('/', [AppointmentAdminController::class, 'index']);
            Route::put('{id}', [AppointmentAdminController::class, 'update']);
            Route::delete('{id}', [AppointmentAdminController::class, 'destroy']);
            Route::get('{id}', [AppointmentAdminController::class, 'show']);
        });
    });
});