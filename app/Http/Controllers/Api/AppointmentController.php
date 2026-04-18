<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
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
            'appointment_time' => 'required',
            'patient_name'     => 'required|string|max:255',
            'patient_phone'    => 'required|string|min:11',
        ]);

        try {
            
            $formattedTime = Carbon::parse($request->appointment_time)->format('H:i:s');

            // 3. التأكد من عدم تكرار الموعد لنفس الدكتور في نفس الوقت
            $exists = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $formattedTime)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'عفواً، هذا الموعد محجوز مسبقاً عند هذا الطبيب، يرجى اختيار موعد آخر'
                ], 400);
            }

            // 4. إنشاء الحجز
            $appointment = Appointment::create([
                'user_id'          => Auth::id(), 
                'doctor_id'        => $request->doctor_id,
                'hospital_id'      => $request->hospital_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $formattedTime, 
                'patient_name'     => $request->patient_name,
                'patient_phone'    => $request->patient_phone,
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
                'message' => 'حدث خطأ أثناء تسجيل الحجز، تأكد من تنسيق الوقت الصحيح',
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
        // التأكد إن اليوزر بيمسح حجزه هو بس
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