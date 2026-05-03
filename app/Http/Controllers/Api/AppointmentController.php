<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id'        => 'required|exists:doctors,id',
            'clinic_id'        => 'required|exists:clinics,id', 
            'appointment_date' => 'required|date|after_or_equal:today', 
            'appointment_day'  => 'required|string', 
            'appointment_time' => 'required|string', 
            'time_slot'        => 'required|in:morning,evening', 
            'patient_name'     => 'required|string|max:255',
            'patient_phone'    => 'required|string|min:11',
            'notes'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422, [], JSON_UNESCAPED_SLASHES);
        }

        try {
            $doctor = Doctor::findOrFail($request->doctor_id);

            $exists = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => false,
                    'message' => 'عفواً، هذا الموعد محجوز مسبقاً'
                ], 400, [], JSON_UNESCAPED_SLASHES);
            }

            $doc_fees = $doctor->consultation_fee; 
            $service_fees = 20.00; 
            $total_amount = $doc_fees + $service_fees;

            $appointment = Appointment::create([
                'user_id'          => Auth::id(), 
                'doctor_id'        => $request->doctor_id,
                'clinic_id'        => $request->clinic_id, 
                'appointment_date' => $request->appointment_date,
                'appointment_day'  => $request->appointment_day, 
                'appointment_time' => $request->appointment_time, 
                'time_slot'        => $request->time_slot,
                'patient_name'     => $request->patient_name,
                'patient_phone'    => $request->patient_phone,
                'doc_fees'         => $doc_fees,
                'service_fees'     => $service_fees,
                'total_amount'     => $total_amount,
                'payment_method'   => null,
                'status'           => 'pending',
                'notes'            => $request->notes,
            ]);

            return response()->json([
                'status'         => true,
                'message'        => 'تم حجز الموعد بنجاح، يرجى اختيار طريقة الدفع لإتمام الطلب',
                'appointment_id' => $appointment->id,
                'data'           => $appointment->load(['doctor', 'clinic.hospital']) 
            ], 201, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تسجيل الحجز',
                'error'   => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

  
    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:clinic,card,wallet,insurance',
        ]);

        try {
            $appointment = Appointment::where('user_id', Auth::id())->findOrFail($id);

            if ($appointment->status == 'cancelled') {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكن الدفع لحجز ملغي'
                ], 400, [], JSON_UNESCAPED_SLASHES);
            }

            $newStatus = ($request->payment_method == 'clinic') ? 'pending' : 'confirmed';

            $appointment->update([
                'payment_method' => $request->payment_method,
                'status'         => $newStatus,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديد طريقة الدفع وتأكيد الحجز بنجاح',
                'data'    => $appointment->load(['doctor', 'clinic.hospital'])
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشل معالجة الدفع',
                'error'   => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

  
    public function myAppointments()
    {
        $appointments = Appointment::with(['doctor', 'clinic.hospital'])
            ->where('user_id', Auth::id())
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'count'  => $appointments->count(),
            'data'   => $appointments
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

   
    public function destroy($id)
    {
        $appointment = Appointment::where('user_id', Auth::id())->find($id);

        if (!$appointment) {
            return response()->json([
                'status'  => false,
                'message' => 'الموعد غير موجود'
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'تم إلغاء الموعد بنجاح'
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}