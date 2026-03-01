<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;

class HomeController extends Controller
{
    // 1. جلب كل المستشفيات
    public function allHospitals()
    {
        return response()->json([
            'hospitals' => Hospital::all()
        ], 200);
    }

    // 2. جلب المستشفيات المميزة (للصفحة الرئيسية)
    public function featuredHospitals()
    {
        $featured = Hospital::where('is_featured', true)->get();
        return response()->json([
            'featured' => $featured
        ], 200);
    }

    // 3. ميزة الطوارئ: إيجاد أقرب مستشفى بناءً على موقع المستخدم
    public function findNearest(Request $request)
    {
        // بنستقبل خطوط الطول والعرض من موبايل المستخدم
        $userLat = $request->lat;
        $userLng = $request->lng;

        // معادلة حساب المسافة (Haversine Formula)
        // بنجيب كل المستشفيات ونحسب المسافة ونرتبهم من الأقرب للأبعد
        $nearestHospital = Hospital::selectRaw("*, 
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", 
            [$userLat, $userLng, $userLat])
            ->orderBy('distance')
            ->first(); // بناخد أول واحدة بس (الأقرب)

        return response()->json([
            'nearest_hospital' => $nearestHospital
        ], 200);
    }
}