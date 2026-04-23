<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentAdminController extends Controller
{
    // ✅ عرض كل المواعيد
    public function index(Request $request)
    {
        $query = Appointment::with(['user', 'doctor', 'hospital']);

        // فلترة بالحالة
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // فلترة بـ hospital
        if ($request->has('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        return response()->json([
            'status' => true,
            'total'  => Appointment::count(),
            'data'   => $query->paginate(10)
        ], 200);
    }

    // ✅ عرض موعد واحد
    public function show($id)
    {
        $appointment = Appointment::with(['user', 'doctor', 'hospital'])->find($id);

        if (!$appointment) {
            return response()->json(['status' => false, 'message' => 'Appointment not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $appointment], 200);
    }

    // ✅ تعديل حالة الموعد
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['status' => false, 'message' => 'Appointment not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'notes'  => 'nullable|string'
        ]);

        $appointment->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Appointment updated successfully',
            'data'    => $appointment
        ], 200);
    }

    // ✅ حذف موعد
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['status' => false, 'message' => 'Appointment not found'], 404);
        }

        $appointment->delete();

        return response()->json(['status' => true, 'message' => 'Appointment deleted successfully'], 200);
    }
}