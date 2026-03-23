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

    /**
     * حفظ أو تعديل البيانات (عشان شاشة الـ No وزرار Edit)
     */
    public function store(Request $request)
    {
        // 1. مرحلة الـ Validation: تأكد إن الداتا مطابقة لمتطلبات الطوارئ
        $request->validate([
            'full_name'         => 'required|string|max:255',
            'age'               => 'required|integer|min:1|max:120',
            'gender'            => 'required|string|in:male,female',
            'blood_type'        => 'required|string',
            'chronic_diseases'  => 'nullable|string',
            'allergies'         => 'nullable|string',
            'special_condition' => 'nullable|string',
            'emergency_phone'   => 'nullable|string', // إضافة رقم طوارئ إضافي
        ]);

        // 2. مرحلة الحفظ: استخدام updateOrCreate لضمان وجود سجل واحد لكل مستخدم
        $profile = MedicalProfile::updateOrCreate(
            ['user_id' => Auth::id()], // شرط البحث بالـ User ID الخاص بالتوكن
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