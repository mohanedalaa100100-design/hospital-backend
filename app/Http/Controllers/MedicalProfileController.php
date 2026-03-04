<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicalProfile;
use Illuminate\Support\Facades\Auth;

class MedicalProfileController extends Controller
{
    // عرض البيانات (عشان شاشة الـ Yes)
    public function show()
    {
        // بنستخدم Eager Loading عشان نجيب الداتا بسرعة
        $profile = Auth::user()->medicalProfile;

        if (!$profile) {
            return response()->json([
                'status' => false,
                'message' => 'بروفايل طبي غير موجود، يرجى ملء البيانات'
            ], 404);
        }

        return response()->json([
            'status' => true, 
            'data' => $profile
        ], 200);
    }

    // حفظ أو تعديل البيانات (عشان شاشة الـ No وزرار Edit)
    public function store(Request $request)
    {
        // 1. مرحلة الـ Validation: عشان نضمن إن الداتا أنواعها صح
        $request->validate([
            'full_name'        => 'required|string|max:255',
            'age'              => 'required|integer|min:1|max:120',
            'gender'           => 'required|string|in:male,female',
            'blood_type'       => 'required|string',
            'chronic_diseases' => 'nullable|string',
            'allergies'        => 'nullable|string',
            'special_condition'=> 'nullable|string',
        ]);

        // 2. مرحلة الحفظ: updateOrCreate بتدور بالـ user_id
        // لو لقت بروفايل بتعدله، ولو ملقتش بتعمل New Record
        $profile = MedicalProfile::updateOrCreate(
            ['user_id' => Auth::id()], // شرط البحث
            $request->only([           // البيانات اللي مسموح تدخل
                'full_name', 'age', 'gender', 'blood_type', 
                'chronic_diseases', 'allergies', 'special_condition'
            ])
        );

        return response()->json([
            'status' => true, 
            'message' => 'تم حفظ بياناتك الطبية بنجاح', 
            'data' => $profile
        ], 201);
    }
}