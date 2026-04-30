<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentAdminController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Appointment::with(['user', 'doctor', 'clinic.hospital', 'clinic.specialty']);

        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        
        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        
        if ($request->has('hospital_id')) {
            $query->whereHas('clinic', function($q) use ($request) {
                $q->where('hospital_id', $request->hospital_id);
            });
        }

        
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        return response()->json([
            'status' => true,
            'total'  => Appointment::count(),
            'data'   => $query->orderBy('appointment_date', 'desc')->paginate(10)
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    
    public function show($id)
    {
        $appointment = Appointment::with([
            'user',
            'doctor',
            'clinic.hospital',
            'clinic.specialty'
        ])->find($id);

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

    
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'status'  => false,
                'message' => 'الموعد غير موجود'
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'notes'  => 'nullable|string'
        ]);

        $appointment->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث الموعد بنجاح',
            'data'    => $appointment->load(['user', 'doctor', 'clinic.hospital', 'clinic.specialty'])
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

   
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'status'  => false,
                'message' => 'الموعد غير موجود'
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        $appointment->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الموعد بنجاح'
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}