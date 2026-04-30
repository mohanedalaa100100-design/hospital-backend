<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        
        $query = Doctor::with(['clinic.hospital', 'specialty']);

        
        if ($request->has('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        } elseif ($request->has('specialty')) {
            $query->whereHas('specialty', function($q) use ($request) {
                $q->where('name', $request->specialty);
            });
        }

        
        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        
        if ($request->has('hospital_id')) {
            $query->whereHas('clinic', function($q) use ($request) {
                $q->where('hospital_id', $request->hospital_id);
            });
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
        
        $doctor = Doctor::with(['clinic.hospital', 'specialty'])->find($id);

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