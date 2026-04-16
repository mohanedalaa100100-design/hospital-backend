<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\herosection;
use App\Models\QuickAction;
use App\Models\Specialty;

class HomeController extends Controller
{
    /**
     * بيانات الصفحة الرئيسية
     */
    public function index()
    {
        try {
            // جلب التخصصات مع روابط صور كاملة للعرض في الهوم
            $specialties = Specialty::take(10)->get()->map(function ($item) {
                if ($item->icon_url && !filter_var($item->icon_url, FILTER_VALIDATE_URL)) {
                    $item->icon_url = asset('storage/' . $item->icon_url);
                }
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'Home page data retrieved successfully',
                'data' => [
                    'hero_section'       => herosection::all(), 
                    'quick_actions'      => QuickAction::all(),
                    'specialties'        => $specialties,
                    'featured_hospitals' => Hospital::with(['specialties'])
                                            ->where('is_featured', true)
                                            ->where('is_active', true)
                                            ->take(6)
                                            ->get()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error loading home page',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب كل المستشفيات النشطة
     */
    public function allHospitals()
    {
        try {
            $hospitals = Hospital::where('is_active', true)
                                ->with(['specialties', 'medicalServices'])
                                ->get();

            return response()->json([
                'status' => true,
                'message' => 'All hospitals retrieved successfully',
                'count'  => $hospitals->count(),
                'data'   => $hospitals
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching hospitals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب كل التخصصات مع المستشفيات المربوطة بها (تعديل مهند)
     */
    public function allSpecialties()
    {
        try {
            // هنا السر: استخدمنا with عشان نجيب المستشفيات و map عشان نصلح الروابط
            $specialties = Specialty::with('hospitals')->get()->map(function ($item) {
                if ($item->icon_url && !filter_var($item->icon_url, FILTER_VALIDATE_URL)) {
                    $item->icon_url = asset('storage/' . $item->icon_url);
                }
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'Specialties retrieved successfully - Updated',
                'data' => $specialties
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching specialties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تفاصيل مستشفى محددة
     */
    public function show($id)
    {
        $hospital = Hospital::with(['specialties', 'medicalServices', 'doctors'])
                            ->find($id);

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

    /**
     * البحث عن مستشفيات
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

        $hospitals = Hospital::where('is_active', true)
                    ->where(function($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                          ->orWhere('address', 'LIKE', "%{$query}%")
                          ->orWhereHas('specialties', function($sq) use ($query) {
                              $sq->where('name', 'LIKE', "%{$query}%");
                          });
                    })
                    ->with(['specialties'])
                    ->get();

        return response()->json([
            'status' => true,
            'count'  => $hospitals->count(),
            'data'   => $hospitals
        ], 200);
    }

    /**
     * جلب أقرب مستشفيات بناءً على الـ GPS
     */
    public function findNearest(Request $request)
    {
        $userLat = $request->lat;
        $userLng = $request->lng;

        if (!$userLat || !$userLng) {
            return response()->json([
                'status' => false, 
                'message' => 'Coordinates (lat, lng) are required'
            ], 400);
        }

        $nearestHospitals = Hospital::selectRaw("*, 
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", 
            [$userLat, $userLng, $userLat])
            ->where('is_active', true)
            ->with(['specialties']) 
            ->orderBy('distance')
            ->take(10)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Nearest hospitals retrieved successfully',
            'data' => $nearestHospitals
        ], 200);
    }
}