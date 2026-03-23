<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Doctor::with('hospital');

        
        if ($request->has('specialty')) {
            $query->where('specialty', $request->specialty);
        }

        $doctors = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة الأطباء المتاحة',
            'data' => $doctors
        ]);
    }

    
    public function show($id)
    {
        $doctor = Doctor::with('hospital')->find($id);

        if (!$doctor) {
            return response()->json(['status' => false, 'message' => 'الطبيب غير موجود'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $doctor
        ]);
    }
}