<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
    
        $request->validate([
            'doctor_id'        => 'required|exists:doctors,id',
            'hospital_id'      => 'required|exists:hospitals,id',
            'appointment_date' => 'required|date|after_or_equal:today', 
            'appointment_day'  => 'required|string', 
            'appointment_time' => 'required|string', 
            'time_slot'        => 'required|in:morning,evening', 
            'patient_name'     => 'required|string|max:255',
            'patient_phone'    => 'required|string|min:11',
            'payment_method'   => 'required|in:hospital,card,fawry', 
        ]);

        try {
            $doctor = Doctor::findOrFail($request->doctor_id);

            
            if (!in_array($request->appointment_day, $doctor->working_days) || 
                !in_array($request->appointment_time, $doctor->available_slots)) {
                return response()->json([
                    'status' => false,
                    'message' => 'عفواً، هذا الموعد خارج أوقات عمل الطبيب المحددة'
                ], 422);
            }

        
            $exists = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'عفواً، هذا الموعد محجوز مسبقاً، يرجى اختيار موعد آخر'
                ], 400);
            }

            // 4. الحسابات المالية
            $doc_fees = $doctor->consultation_fee; 
            $service_fees = 20.00; 
            $total_amount = $doc_fees + $service_fees;

            // 5. إنشاء الحجز
            $appointment = Appointment::create([
                'user_id'          => Auth::id(), 
                'doctor_id'        => $request->doctor_id,
                'hospital_id'      => $request->hospital_id,
                'appointment_date' => $request->appointment_date,
                'appointment_day'  => $request->appointment_day, 
                'appointment_time' => $request->appointment_time, 
                'time_slot'        => $request->time_slot,
                'patient_name'     => $request->patient_name,
                'patient_phone'    => $request->patient_phone,
                'doc_fees'         => $doc_fees,
                'service_fees'     => $service_fees,
                'total_amount'     => $total_amount,
                'payment_method'   => $request->payment_method,
                'status'           => 'pending',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل طلب الحجز بنجاح، سيتم التواصل معك للتأكيد',
                'data' => $appointment->load(['doctor', 'hospital']) 
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
        $appointment = Appointment::where('user_id', Auth::id())->find($id);

        if (!$appointment) {
            return response()->json([
                'status' => false,
                'message' => 'الموعد غير موجود أو لا تملك صلاحية لإلغائه'
            ], 404);
        }

        try {
            
            $appointment->update(['status' => 'cancelled']);
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