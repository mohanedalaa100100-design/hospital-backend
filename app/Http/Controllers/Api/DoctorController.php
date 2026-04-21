<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = Doctor::with(['hospital', 'specialty']);

        
        if ($request->has('specialty')) {
            $query->whereHas('specialty', function($q) use ($request) {
                $q->where('name', $request->specialty);
            });
        }

        if ($request->has('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        $doctors = $query->get();

        return response()->json([
            'status'  => true,
            'message' => 'قائمة الأطباء المتاحة',
            'count'   => $doctors->count(),
            'data'    => $doctors
        ]);
    }

    public function show($id)
    {
        $doctor = Doctor::with(['hospital', 'specialty'])->find($id);

        if (!$doctor) {
            return response()->json([
                'status'  => false,
                'message' => 'الطبيب غير موجود'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $doctor
        ]);
    }
}