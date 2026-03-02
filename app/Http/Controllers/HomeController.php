<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\herosection;
use App\Models\QuickAction;

class HomeController extends Controller
{
    /**
     * 1. الدالة الأساسية للصفحة الرئيسية (تجمع كل السكاشن)
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Home page data retrieved successfully',
            'data' => [
                'hero_section'  => herosection::first(), // بيانات العنوان والوصف اللي فوق
                'quick_actions' => QuickAction::all(),   // الزرارين (الطوارئ والعادي)
                'hospitals'     => Hospital::where('is_featured', true)->take(6)->get() // أهم 6 مستشفيات
            ]
        ], 200);
    }

    // 2. جلب كل المستشفيات
    public function allHospitals()
    {
        return response()->json([
            'hospitals' => Hospital::all()
        ], 200);
    }

    // 3. جلب المستشفيات المميزة (لو احتاجتها منفصلة)
    public function featuredHospitals()
    {
        $featured = Hospital::where('is_featured', true)->get();
        return response()->json([
            'featured' => $featured
        ], 200);
    }

    // 4. ميزة الطوارئ: إيجاد أقرب مستشفى بناءً على موقع المستخدم
    public function findNearest(Request $request)
    {
        $userLat = $request->lat;
        $userLng = $request->lng;

        // معادلة حساب المسافة (Haversine Formula) لترتيب المستشفيات حسب الموقع
        $nearestHospital = Hospital::selectRaw("*, 
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", 
            [$userLat, $userLng, $userLat])
            ->orderBy('distance')
            ->first();

        return response()->json([
            'nearest_hospital' => $nearestHospital
        ], 200);
    }
}