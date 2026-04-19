<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicalProfile;
use Illuminate\Support\Facades\Auth;

class MedicalProfileController extends Controller
{
    
    public function show()
    {
        
        $profile = Auth::user()->medicalProfile;

        if (!$profile) {
            return response()->json([
                'status' => false,
                'message' => 'بروفايل طبي غير موجود، يرجى ملء البيانات'
            ], 404);
        }

        return response()->json([
            'status' => true, 
            'message' => 'Medical profile retrieved successfully',
            'data' => $profile
        ], 200);
    }

    
    public function store(Request $request)
    {
        
        $request->validate([
            'full_name'         => 'required|string|max:255',
            'age'               => 'required|integer|min:1|max:120',
            'gender'            => 'required|string|in:male,female',
            'blood_type'        => 'required|string',
            'chronic_diseases'  => 'nullable|string',
            'allergies'         => 'nullable|string',
            'special_condition' => 'nullable|string',
            'emergency_phone'   => 'nullable|string', 
        ]);

        
        $profile = MedicalProfile::updateOrCreate(
            ['user_id' => Auth::id()], 
            [
                'full_name'         => $request->full_name,
                'age'               => $request->age,
                'gender'            => $request->gender,
                'blood_type'        => $request->blood_type,
                'chronic_diseases'  => $request->chronic_diseases,
                'allergies'         => $request->allergies,
                'special_condition' => $request->special_condition,
                'emergency_phone'   => $request->emergency_phone,
            ]
        );

        return response()->json([
            'status' => true, 
            'message' => 'تم حفظ بياناتك الطبية بنجاح', 
            'data' => $profile
        ], 201);
    }
}