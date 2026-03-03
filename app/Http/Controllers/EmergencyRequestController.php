<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmergencyRequest;
use App\Models\Hospital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmergencyRequestController extends Controller
{
    public function sendRequest(Request $request)
    {
        // 1. استلام موقع المريض الحالي
        $userLat = $request->lat;
        $userLng = $request->lng;

        // 2. معادلة الـ Haversine للبحث عن أقرب مستشفى (نصف قطر 50 كم مثلاً)
        $nearestHospital = Hospital::select('id', 'name', DB::raw("
            (6371 * acos(cos(radians($userLat)) 
            * cos(radians(latitude)) 
            * cos(radians(longitude) - radians($userLng)) 
            + sin(radians($userLat)) 
            * sin(radians(latitude)))) AS distance
        "))
        ->orderBy('distance', 'asc')
        ->first(); // بناخد أول واحدة اللي هي الأقرب

        if (!$nearestHospital) {
            return response()->json(['message' => 'عذراً، لا توجد مستشفيات قريبة متاحة حالياً'], 404);
        }

        // 3. تسجيل الطلب في الجدول الجديد
        $emergency = EmergencyRequest::create([
            'user_id' => Auth::id(),
            'hospital_id' => $nearestHospital->id,
            'user_lat' => $userLat,
            'user_lng' => $userLng,
            'status' => 'pending',
            'note' => $request->note ?? 'طلب استغاثة طارئ'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال طلبك بنجاح لأقرب مستشفى',
            'hospital' => $nearestHospital->name,
            'distance_km' => round($nearestHospital->distance, 2),
            'request_details' => $emergency
        ], 201);
    }
}