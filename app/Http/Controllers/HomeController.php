<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\herosection;
use App\Models\QuickAction;
use App\Models\Specialty; // 👈 لازم يكون موجود عشان allSpecialties تشتغل

class HomeController extends Controller
{
    /**
     * جلب بيانات الصفحة الرئيسية (Hero, Quick Actions, Featured Hospitals)
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Home page data retrieved successfully',
            'data' => [
                'hero_section'       => herosection::all(), 
                'quick_actions'      => QuickAction::all(),
                'featured_hospitals' => Hospital::with(['specialties'])
                                        ->where('is_featured', true)
                                        ->where('is_active', true)
                                        ->take(6)
                                        ->get()
            ]
        ], 200);
    }

    /**
     * جلب كل التخصصات - الـ API اللي كان بيدي 500
     */
    public function allSpecialties()
    {
        try {
            $specialties = Specialty::all();
            return response()->json([
                'status' => true,
                'message' => 'Specialties retrieved successfully',
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
     * البحث الذكي (بالاسم أو العنوان أو التخصص)
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