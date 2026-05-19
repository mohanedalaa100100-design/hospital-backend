<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicalProfileController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\AiDiagnosisController;
use App\Http\Controllers\Admin\HospitalAdminController;
use App\Http\Controllers\Admin\ClinicAdminController;
use App\Http\Controllers\Admin\AppointmentAdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Models\Clinic;



Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});


Route::get('/home-page', [HomeController::class, 'index']);
Route::get('/all-specialties', [HomeController::class, 'allSpecialties']);
Route::get('/specialties/{id}', [HomeController::class, 'showSpecialty']);


Route::prefix('hospitals')->group(function () {
    Route::get('/nearest', [HomeController::class, 'findNearest']);
    Route::get('/search', [HomeController::class, 'search']);
    Route::get('/', [HomeController::class, 'allHospitals']);
    Route::get('/{id}', [HomeController::class, 'show']);
});


Route::prefix('doctors')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('/{id}', [DoctorController::class, 'show']);
});


Route::prefix('clinics')->group(function () {
    Route::get('/', function () {
        $clinics = Clinic::with(['hospital', 'specialty', 'doctors'])
            ->paginate(20);
        return response()->json([
            'status' => true,
            'data'   => $clinics
        ], 200, [], JSON_UNESCAPED_SLASHES);
    });

    Route::get('/{id}', function ($id) {
        $clinic = Clinic::with(['hospital', 'specialty', 'doctors'])
            ->find($id);

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'العيادة غير موجودة'
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status' => true,
            'data'   => $clinic
        ], 200, [], JSON_UNESCAPED_SLASHES);
    });
});


Route::prefix('emergency')->group(function () {
    Route::post('/quick-send', [EmergencyRequestController::class, 'quickSend']);
});

Route::post('/triage', [TriageController::class, 'assess']);
Route::post('/ai-diagnosis', [AiDiagnosisController::class, 'diagnose']);



Route::middleware('auth:sanctum')->group(function () {


    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    
    Route::prefix('medical-profile')->group(function () {
        Route::get('/', [MedicalProfileController::class, 'show']);
        Route::post('/', [MedicalProfileController::class, 'store']);
    });

    
    Route::prefix('appointments')->group(function () {
        
        
        Route::get('/available-slots/{doctor_id}/{date}',
            [AppointmentController::class, 'showAvailableSlots']);

        
        Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);

        
        Route::get('/{id}', [AppointmentController::class, 'show']);

        
        Route::post('/book', [AppointmentController::class, 'store']);

        
        Route::delete('/{id}', [AppointmentController::class, 'destroy']);
    });

    
    Route::prefix('emergency')->group(function () {
        Route::get('/my-requests', [EmergencyRequestController::class, 'userRequests']);
    });


  
    Route::prefix('admin')->middleware('admin')->group(function () {

    
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        
        Route::prefix('hospitals')->group(function () {
            Route::get('/', [HospitalAdminController::class, 'index']);
            Route::post('/', [HospitalAdminController::class, 'store']);
            Route::get('{id}', [HospitalAdminController::class, 'show']);
            Route::put('{id}', [HospitalAdminController::class, 'update']);
            Route::delete('{id}', [HospitalAdminController::class, 'destroy']);
        });

        
        Route::prefix('clinics')->group(function () {
            Route::get('/', [ClinicAdminController::class, 'index']);
            Route::post('/', [ClinicAdminController::class, 'store']);
            Route::get('{id}', [ClinicAdminController::class, 'show']);
            Route::put('{id}', [ClinicAdminController::class, 'update']);
            Route::delete('{id}', [ClinicAdminController::class, 'destroy']);
            Route::get('{id}/stats', [ClinicAdminController::class, 'stats']);
        });

        
        Route::prefix('appointments')->group(function () {
            Route::get('/', [AppointmentAdminController::class, 'index']);
            Route::get('{id}', [AppointmentAdminController::class, 'show']);
            Route::put('{id}', [AppointmentAdminController::class, 'update']);
            Route::delete('{id}', [AppointmentAdminController::class, 'destroy']);
        });
    });
});