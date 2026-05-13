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
            'appointment_time' => 'required|string', // "10:00 AM"
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

            
            $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($existingAppointment) {
                return response()->json([
                    'status'  => false,
                    'message' => 'عفواً، هذا الموعد محجوز مسبقاً. اختر موعد آخر.',
                    'available_slots' => $this->getAvailableSlots($request->doctor_id, $request->appointment_date)
                ], 409, [], JSON_UNESCAPED_SLASHES);
            }

            
            $userAppointmentToday = Appointment::where('user_id', Auth::id())
                ->where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($userAppointmentToday) {
                return response()->json([
                    'status'  => false,
                    'message' => 'أنت بالفعل لديك حجز مع هذا الدكتور في هذا اليوم!'
                ], 409, [], JSON_UNESCAPED_SLASHES);
            }

            
            $appointmentDate = \Carbon\Carbon::parse($request->appointment_date);
            if ($appointmentDate < \Carbon\Carbon::today()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكن الحجز لتاريخ في الماضي'
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
                'appointment_time' => $request->appointment_time,
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
                'total_amount'   => $total_amount,
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

   
    public function getAvailableSlots($doctorId, $date)
    {
        
        $allSlots = [
            '09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM',
            '11:00 AM', '11:30 AM', '12:00 PM', '12:30 PM',
            '01:00 PM', '01:30 PM', '02:00 PM', '02:30 PM',
            '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM',
            '05:00 PM'
        ];

        
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('appointment_time')
            ->toArray();

        
        $availableSlots = array_diff($allSlots, $bookedSlots);

        return [
            'total_slots' => count($allSlots),
            'booked_count' => count($bookedSlots),
            'available_count' => count($availableSlots),
            'booked' => array_values($bookedSlots),
            'available' => array_values($availableSlots)
        ];
    }

   
    public function showAvailableSlots($doctorId, $date)
    {
        try {
            
            $appointmentDate = \Carbon\Carbon::parse($date)->format('Y-m-d');

            
            Doctor::findOrFail($doctorId);

            $slots = $this->getAvailableSlots($doctorId, $appointmentDate);

            return response()->json([
                'status' => true,
                'date' => $appointmentDate,
                'doctor_id' => $doctorId,
                'data' => $slots
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ في الحصول على الأوقات المتاحة',
                'error'   => $e->getMessage()
            ], 400, [], JSON_UNESCAPED_SLASHES);
        }
    }

  
    public function processPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:clinic,card,wallet,insurance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422, [], JSON_UNESCAPED_SLASHES);
        }

        try {
            $appointment = Appointment::where('user_id', Auth::id())->findOrFail($id);

            
            if ($appointment->status == 'cancelled') {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكن الدفع لحجز ملغي'
                ], 400, [], JSON_UNESCAPED_SLASHES);
            }

            
            if ($appointment->status == 'paid' || $appointment->status == 'confirmed') {
                return response()->json([
                    'status'  => false,
                    'message' => 'تم الدفع لهذا الموعد بالفعل'
                ], 400, [], JSON_UNESCAPED_SLASHES);
            }

            
            $newStatus = ($request->payment_method == 'clinic') ? 'pending' : 'confirmed';

            $appointment->update([
                'payment_method' => $request->payment_method,
                'status'         => $newStatus,
                'paid_at'        => now(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديد طريقة الدفع وتأكيد الحجز بنجاح',
                'appointment_status' => $newStatus,
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
            ->paginate(10);

        return response()->json([
            'status' => true,
            'total'  => $appointments->total(),
            'current_page' => $appointments->currentPage(),
            'data'   => $appointments->items()
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

  
    public function show($id)
    {
        $appointment = Appointment::with(['doctor', 'clinic.hospital'])
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$appointment) {
            return response()->json([
                'status'  => false,
                'message' => 'الموعد غير موجود'
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status' => true,
            'data'   => $appointment
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

        
        if ($appointment->status === 'cancelled') {
            return response()->json([
                'status'  => false,
                'message' => 'الموعد ملغى بالفعل'
            ], 400, [], JSON_UNESCAPED_SLASHES);
        }

        
        $appointmentDateTime = \Carbon\Carbon::parse(
            $appointment->appointment_date . ' ' . $appointment->appointment_time
        );

        if ($appointmentDateTime <= \Carbon\Carbon::now()->addHours(24)) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يمكن إلغاء الموعد إلا قبل 24 ساعة من موعد الكشف'
            ], 400, [], JSON_UNESCAPED_SLASHES);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'تم إلغاء الموعد بنجاح'
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}