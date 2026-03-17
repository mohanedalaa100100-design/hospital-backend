<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    // 1. عرض كل الدكاترة (مع إمكانية الفلترة حسب التخصص)
    public function index(Request $request)
    {
        $query = Doctor::with('hospital'); // بنجيب معاهم اسم المستشفى اللي شغالين فيها

        // لو اليوزر اختار تخصص معين من الـ UI
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

    // 2. عرض تفاصيل دكتور معين (لما يضغط على الكارت)
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