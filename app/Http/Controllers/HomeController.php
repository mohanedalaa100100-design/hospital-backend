<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\HeroSection;
use App\Models\QuickAction;
use App\Models\Specialty;
use App\Models\Doctor;

class HomeController extends Controller
{
    // الصفحة الرئيسية
    public function index()
    {
        try {
            // زودنا العدد لـ 10 عشان الصفحة تبان مليانة
            $specialties = Specialty::select('id', 'name', 'icon_url')->take(10)->get();

            $data = [
                'hero_section'       => HeroSection::all(),
                'quick_actions'      => QuickAction::all(),
                'specialties'        => $specialties,
                'featured_hospitals' => Hospital::with(['specialties:id,name', 'medicalServices:id,hospital_id,name'])
                                            ->where('is_featured', true)
                                            ->where('is_active', true)
                                            ->select('id', 'name', 'address', 'image_url', 'rating', 'type', 'accreditation', 'emergency_days')
                                            ->take(10) 
                                            ->get()
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Home page data retrieved successfully',
                'data'    => $data
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error loading home page',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // عرض كل التخصصات مع الدكاترة
    public function allSpecialties()
    {
        try {
            $specialties = Specialty::with(['doctors' => function($query) {
                $query->where('is_available', true)
                      ->select(
                          'id', 'specialty_id', 'hospital_id', 'name', 
                          'title', 'experience_years', 'rating', 
                          'consultation_fee', 'available_slots', 
                          'working_days', 'image'
                      )->take(10);
            }])->get();

            return response()->json([
                'status'  => true,
                'message' => 'Specialties with doctors retrieved successfully',
                'data'    => $specialties
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching specialties',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // البحث عن المستشفيات
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json([
                'status'  => false,
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
                    ->with(['specialties:id,name', 'medicalServices:id,hospital_id,name'])
                    ->get();

        return response()->json([
            'status' => true,
            'count'  => $hospitals->count(),
            'data'   => $hospitals
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // عرض كل المستشفيات
    public function allHospitals()
    {
        try {
            $hospitals = Hospital::where('is_active', true)
                                ->with(['specialties', 'medicalServices'])
                                ->get();

            return response()->json([
                'status' => true,
                'data'   => $hospitals
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    // تفاصيل مستشفى محددة
    public function show($id)
    {
        $hospital = Hospital::with(['specialties', 'medicalServices', 'doctors'])->find($id);

        if (!$hospital) {
            return response()->json([
                'status'  => false,
                'message' => 'المستشفى غير موجودة'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $hospital
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // أقرب المستشفيات بناءً على اللوكيشن
    public function findNearest(Request $request)
    {
        $userLat = $request->lat;
        $userLng = $request->lng;

        if (!$userLat || !$userLng) {
            return response()->json([
                'status'  => false,
                'message' => 'Coordinates required'
            ], 400);
        }

        $nearestHospitals = Hospital::selectRaw("*,
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$userLat, $userLng, $userLat])
            ->where('is_active', true)
            ->with(['specialties', 'medicalServices'])
            ->orderBy('distance')
            ->take(10)
            ->get()
            ->map(function($hospital) {
                $hospital->distance_km     = round($hospital->distance, 1) . ' km';
                $hospital->distance_meters = round($hospital->distance * 1000) . ' m';
                $hospital->eta_minutes     = round(($hospital->distance / 30) * 60) . ' min';
                return $hospital;
            });

        return response()->json([
            'status' => true,
            'data'   => $nearestHospitals
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}