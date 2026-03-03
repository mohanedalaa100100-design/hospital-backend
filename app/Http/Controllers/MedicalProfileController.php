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
        $profile = Auth::user()->medicalProfile;

        if (!$profile) {
            return response()->json([
                'status' => false,
                'message' => 'بروفايل طبي غير موجود'
            ], 404);
        }

        return response()->json(['status' => true, 'data' => $profile], 200);
    }

    // حفظ أو تعديل البيانات (عشان شاشة الـ No وزرار Edit)
    public function store(Request $request)
    {
        $profile = MedicalProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->all()
        );

        return response()->json([
            'status' => true, 
            'message' => 'تم حفظ البيانات الطبية بنجاح', 
            'data' => $profile
        ], 201);
    }
}