<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // 1. تسجيل حجز جديد
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'hospital_id' => 'required|exists:hospitals,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'patient_name' => 'required|string',
            'patient_phone' => 'required|string',
        ]);

        // حركة احترافية: نمنع الحجز المزدوج لنفس الدكتور في نفس الساعة
        $exists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'عفواً، هذا الموعد محجوز مسبقاً عند هذا الطبيب'
            ], 400);
        }

        $appointment = Appointment::create([
            'user_id' => Auth::id(), // المريض اللي عامل Login دلوقتي
            'doctor_id' => $request->doctor_id,
            'hospital_id' => $request->hospital_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'patient_name' => $request->patient_name,
            'patient_phone' => $request->patient_phone,
            'status' => 'pending', // الحالة الافتراضية
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل طلب الحجز بنجاح، سيتم التواصل معك للتأكيد',
            'data' => $appointment
        ], 201);
    }

    // 2. عرض حجوزات المستخدم الحالي
    public function myAppointments()
    {
        $appointments = Appointment::with(['doctor', 'hospital'])
            ->where('user_id', Auth::id())
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $appointments
        ]);
    }
}