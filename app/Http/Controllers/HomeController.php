<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\herosection;
use App\Models\QuickAction;

class HomeController extends Controller
{
    /**
     * 1. الدالة الأساسية للصفحة الرئيسية
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Home page data retrieved successfully',
            'data' => [
                'hero_section'  => herosection::first(),
                'quick_actions' => QuickAction::all(),
                'hospitals'     => Hospital::where('is_featured', true)->take(6)->get()
            ]
        ], 200);
    }

    /**
     * 2. ميزة البحث (Search API)
     * بتبحث في الاسم أو العنوان بناءً على كلمة بيبعتها المستخدم
     */
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json([
                'status' => false,
                'message' => 'برجاء إدخال كلمة للبحث'
            ], 400);
        }

        $hospitals = Hospital::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('address', 'LIKE', "%{$query}%")
                    ->get();

        return response()->json([
            'status' => true,
            'count' => $hospitals->count(),
            'data' => $hospitals
        ], 200);
    }

    /**
     * 3. عرض تفاصيل مستشفى محددة (Dynamic Details)
     */
    public function show($id)
    {
        $hospital = Hospital::with(['specialties', 'medicalServices'])->find($id);

        if (!$hospital) {
            return response()->json([
                'status' => false,
                'message' => 'المستشفى غير موجودة'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Hospital details retrieved successfully',
            'data' => $hospital
        ], 200);
    }

    // 4. جلب كل المستشفيات
    public function allHospitals()
    {
        return response()->json([
            'hospitals' => Hospital::all()
        ], 200);
    }

    // 5. ميزة الطوارئ: إيجاد أقرب مستشفى
    public function findNearest(Request $request)
    {
        $userLat = $request->lat;
        $userLng = $request->lng;

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