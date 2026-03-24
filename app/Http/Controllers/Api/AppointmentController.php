<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    
    public function store(Request $request)
    {
        // 1. التحقق من البيانات المرسلة
        $request->validate([
            'doctor_id'        => 'required|exists:doctors,id',
            'hospital_id'      => 'required|exists:hospitals,id',
            'appointment_date' => 'required|date|after_or_equal:today', // التأكد إن التاريخ مش قديم
            'appointment_time' => 'required',
            'patient_name'     => 'required|string|max:255',
            'patient_phone'    => 'required|string|min:11',
        ]);

        
        $exists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'عفواً، هذا الموعد محجوز مسبقاً عند هذا الطبيب، يرجى اختيار موعد آخر'
            ], 400);
        }

    
        try {
            $appointment = Appointment::create([
                'user_id'          => Auth::id(), 
                'doctor_id'        => $request->doctor_id,
                'hospital_id'      => $request->hospital_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'patient_name'     => $request->patient_name,
                'patient_phone'    => $request->patient_phone,
                'status'           => 'pending',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل طلب الحجز بنجاح، سيتم التواصل معك للتأكيد',
                'data' => $appointment->load(['doctor', 'hospital']) // تحميل البيانات لعرضها فوراً للفرونت
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تسجيل الحجز',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function myAppointments()
    {
        $appointments = Appointment::with(['doctor', 'hospital'])
            ->where('user_id', Auth::id())
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'count'  => $appointments->count(),
            'data'   => $appointments
        ], 200);
    }

    public function destroy($id)
    {
        // البحث عن الحجز الخاص باليوزر المسجل فقط لضمان الأمان
        $appointment = Appointment::where('user_id', Auth::id())->find($id);

        if (!$appointment) {
            return response()->json([
                'status' => false,
                'message' => 'الموعد غير موجود أو لا تملك صلاحية لإلغائه'
            ], 404);
        }

        try {
            $appointment->delete();
            return response()->json([
                'status' => true,
                'message' => 'تم إلغاء موعد الحجز بنجاح'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل إلغاء الحجز، حاول مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}