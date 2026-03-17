<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmergencyRequest;
use App\Models\Hospital;
use App\Models\MedicalProfile; // ضفنا الموديل ده
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmergencyRequestController extends Controller
{
    public function __construct()
    {
        config(['app.timezone' => 'Africa/Cairo']);
    }

    /**
     * إرسال استغاثة سريعة (SOS)
     * بتربط المريض بأقرب مستشفى وبتبعت ملفه الطبي فوراً
     */
    public function quickSend(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $userLat = $request->lat;
        $userLng = $request->lng;
        $currentDay = Carbon::now()->format('l'); 
        $user = Auth::user(); // جلب بيانات اليوزر المسجل

        // 1. البحث عن أقرب مستشفى (حكومي مناوب أو خاص 24/7)
        // دمجناهم في Query واحد أسرع وأكفأ
        $nearestHospital = Hospital::select('id', 'name', 'lat', 'lng', 'address', 'type')
            ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", 
            [$userLat, $userLng, $userLat])
            ->where('is_active', true)
            ->where(function($q) use ($currentDay) {
                $q->where('type', 'private')
                  ->orWhere(function($sub) use ($currentDay) {
                      $sub->where('type', 'government')
                          ->where('emergency_days', 'LIKE', "%$currentDay%");
                  });
            })
            ->orderBy('distance', 'asc')
            ->first();

        if (!$nearestHospital) {
            return response()->json([
                'status' => false, 
                'message' => 'عذراً، لا توجد مستشفيات طوارئ متاحة في نطاقك حالياً'
            ], 404);
        }

        // 2. جلب الملف الطبي للمريض (عشان يظهر للمستشفى في الـ Dashboard)
        $medicalProfile = $user ? $user->medicalProfile : null;

        // 3. تسجيل طلب الطوارئ الفعلي
        $emergency = EmergencyRequest::create([
            'user_id'            => Auth::id(), // ربط باليوزر لو مسجل
            'hospital_id'        => $nearestHospital->id,
            'user_lat'           => $userLat,
            'user_lng'           => $userLng,
            'status'             => 'pending', // الحالة الافتراضية
            'note'               => $request->note ?? 'استغاثة طارئة',
            // لو عندك حقل في جدول الطوارئ بيخزن الداتا الطبية كـ JSON (اختياري)
            // 'medical_snapshot' => $medicalProfile ? json_encode($medicalProfile) : null 
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال الاستغاثة لأقرب مستشفى بنجاح',
            'data' => [
                'request_id'    => $emergency->id,
                'hospital_name' => $nearestHospital->name,
                'distance'      => round($nearestHospital->distance, 2) . ' KM',
                'user_profile'  => $medicalProfile, // دي الداتا اللي هتظهر للمستشفى فوراً
                'eta'           => round(($nearestHospital->distance / 30) * 60) . ' mins' // حساب تقريبي لوقت الوصول
            ]
        ], 201);
    }
}