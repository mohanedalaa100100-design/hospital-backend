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
        // 1. التأكد من إرسال الإحداثيات بشكل صحيح
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $userLat = $request->lat;
        $userLng = $request->lng;

        // 2. معادلة الـ Haversine للبحث عن أقرب مستشفى
        // بنختار الاسم واللوكيشن والمسافة (6371 هي نصف قطر الأرض بالكيلومتر)
        $nearestHospital = Hospital::select('id', 'name', 'latitude', 'longitude', 'address', DB::raw("
            (6371 * acos(cos(radians($userLat)) 
            * cos(radians(latitude)) 
            * cos(radians(longitude) - radians($userLng)) 
            + sin(radians($userLat)) 
            * sin(radians(latitude)))) AS distance
        "))
        ->orderBy('distance', 'asc')
        ->first();

        // 3. لو مفيش مستشفيات في الداتابيز أصلاً
        if (!$nearestHospital) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، لا توجد مستشفيات مسجلة في النظام حالياً'
            ], 404);
        }

        // 4. تسجيل طلب الاستغاثة في قاعدة البيانات (للمتابعة لاحقاً)
        $emergency = EmergencyRequest::create([
            'user_id'     => Auth::id(),
            'hospital_id' => $nearestHospital->id,
            'user_lat'    => $userLat,
            'user_lng'    => $userLng,
            'status'      => 'pending',
            'note'        => $request->note ?? 'طلب استغاثة طارئ من التطبيق'
        ]);

        // 5. الرد النهائي للفرونت إند بكل المعلومات اللي يحتاجها
        return response()->json([
            'status' => true,
            'message' => 'تم تحديد أقرب مستشفى وإرسال طلبك',
            'hospital_details' => [
                'name'     => $nearestHospital->name,
                'address'  => $nearestHospital->address,
                'lat'      => $nearestHospital->latitude,
                'lng'      => $nearestHospital->longitude,
                'distance' => round($nearestHospital->distance, 2) . ' KM'
            ],
            'request_id' => $emergency->id
        ], 201);
    }
}